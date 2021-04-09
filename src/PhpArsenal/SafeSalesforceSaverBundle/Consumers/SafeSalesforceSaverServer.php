<?php

namespace PhpArsenal\SafeSalesforceSaverBundle\Consumers;

use PhpAmqpLib\Message\AMQPMessage;

class SafeSalesforceSaverServer extends SalesforceSaverConsumer
{
    public function execute(AMQPMessage $message): array
    {
        return parent::execute($message);
    }
}