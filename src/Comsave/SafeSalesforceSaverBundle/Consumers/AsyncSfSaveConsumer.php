<?php

namespace Comsave\SafeSalesforceSaverBundle\Consumers;

use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class AsyncSfSaveConsumer
 * @package Comsave\SafeSalesforceSaverBundle\Consumer
 */
class AsyncSfSaveConsumer extends SalesforceSaverConsumer
{
    public function execute(AMQPMessage $message): void
    {
        parent::execute($message);
    }
}