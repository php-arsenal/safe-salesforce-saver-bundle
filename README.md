# SafeSalesforceSaver

[![Release](https://img.shields.io/github/v/release/comsave/safe-salesforce-saver-bundle)](https://github.com/comsave/safe-salesforce-saver-bundle/releases)
[![Travis](https://img.shields.io/travis/comsave/safe-salesforce-saver-bundle)](https://travis-ci.org/comsave/safe-salesforce-saver-bundle)
[![Test Coverage](https://img.shields.io/codeclimate/coverage/comsave/safe-salesforce-saver-bundle)](https://codeclimate.com/github/comsave/safe-salesforce-saver-bundle)

## About

With this bundle you can stop worrying about your data getting lost when trying to save information to Salesforce. 
The SafeSalesforceSaver will take the objects you give it and place them in a queue.
The items are taken out of the queue one by one to prevent Salesforce from getting overwhelmed if you decide to save hundreds (or thoussands) of objects at once. 
If an exception does occur during the save process, rabbit will simply retry the save a few moments later while logging the error away so you can debug what went wrong.

## Installation

`$ composer require comsave/safe-salesforce-saver-bundle`

Depending on your Symfony version you either have to register the bundle in `app/AppKernel.php` (Symfony 3.4 and lower):

```php
public function registerBundles()
{
    $bundles = [
        new Comsave\SafeSalesforceSaverBundle\ComsaveSafeSalesforceSaverBundle(),
    ];

    return $bundles;
}
```

Or (Symfony 4.0 and higher) in your `config/bundles.php`:

```php
return [
     Comsave\SafeSalesforceSaverBundle\ComsaveSafeSalesforceSaverBundle::class => ['all' => true],
];
}
```

## Usage

To get this bundle to work you will have to start the queues in your rabbit client. To do this you have to add the following configuration to your projects config.yml:
```yaml
old_sound_rabbit_mq:
    producers:
        sss_async_processor:
            class: Comsave\SafeSalesforceSaverBundle\Producer\AsyncSfSaverProducer
            connection: default
            exchange_options:
                name: 'sss_async_queue'
                type: direct
    consumers:
        sss_async_processor:
            connection: default
            exchange_options:
                name: 'sss_async_queue'
                type: direct
            queue_options:
                name: 'sss_async_queue'
            qos_options:
                prefetch_size: 0
                prefetch_count: 1
                global: false
            callback: Comsave\SafeSalesforceSaverBundle\Consumers\AsyncSfSaveConsumer
    rpc_clients:
        parallel:
            connection: default
            expect_serialized_response: false
    rpc_servers:
        safe_salesforce_saver_server:
            connection: default
            callback: Comsave\SafeSalesforceSaverBundle\Consumers\SafeSalesforceSaverServer
            qos_options: { prefetch_size: 0, prefetch_count: 1, global: false }
            queue_options: { name: sss_rpc_queue, durable: true, auto_delete: false }
```
It is important that you do not change the names of the queues as this could lead to issues. 
The above configuration assumes that you already have the default configuration for rabbitMQ set up. If not, please refer to the readme file of the [rabbit bundle on github](https://github.com/php-amqplib/RabbitMqBundle).

In order to actually save your objects to Salesforce they have to be annotated in the right way. See [the mapper bundle on github](https://github.com/comsave/salesforce-mapper-bundle).

When you have updated your configuration and models you can save them in two different ways. Synchronous or a-synchronous:
```php
<?php

use Comsave\SafeSalesforceSaverBundle\Services\SafeSalesforceSaver;

class ObjectSaver
{
    /** @var SafeSalesforceSaver */
    private $safeSalesforceSaver;

    /**
     * @param SafeSalesforceSaver $safeSalesforceSaver
     */
    public function __construct(SafeSalesforceSaver $safeSalesforceSaver)
    {
        $this->safeSalesforceSaver = $safeSalesforceSaver;
    }

    // This way of saving will wait for the save result from Salesforce. This means that you can immediately access the newly inserted ID after Salesforce saved the record.
    public function saveSingle(Object $object): string
    {
        $this->safeSalesforceSaver->save($object);

        return $object->getId();
    }
    
    // This function lets you save multiple objects at once. Simply put all the objects you want to save in an array and pass it to the SafeSalesforceSaver. 
    public function saveMultiple(Object $object, Object $object2, Object $object3): array
    {
        $this->safeSalesforceSaver->save([$object, $object2, $object3]);

        return [$object->getId(), $object2->getId(), $object3->getId()];
    }

    // If you do not want to wait for the result you can simply put your object into the queue and continue with the rest of your code. This is recommended if you don't need the ID or if you don't need a confirmation that the save succeeded.
    public function aSyncSaveSingle(Object $object): void
    {
        $this->safeSalesforceSaver->aSyncSave($object);
    }

    // As with the other save function, it is also possible to save multiple objects to Salesforce at once without waiting for the response.
    public function aSyncSaveMultiple(Object $object, Object $object2, Object $object3): void
    {
        $this->safeSalesforceSaver->aSyncSave([$object, $object2, $object3]);
    }
}
```

