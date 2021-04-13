<?php

namespace PhpArsenal\SafeSalesforceSaverBundle\Consumers;

use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class AsyncSfSaveConsumer
 * @package PhpArsenal\SafeSalesforceSaverBundle\Consumer
 */
class AsyncSfSaveConsumer extends SalesforceSaverConsumer
{
    public function execute(AMQPMessage $message): void
    {
        parent::execute($message);
    }
}