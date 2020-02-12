<?php

namespace Comsave\SafeSalesforceSaver\Consumers;

use LogicItLab\Salesforce\MapperBundle\Mapper;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

/**
 * Class SafeSalesforceSaverServer
 * @package Comsave\SafeSalesforceSaver\Consumer
 */
class SafeSalesforceSaverServer
{
    /**  @var LoggerInterface */
    private $logger;

    /** @var Mapper */
    private $mapper;

    /**
     * @param LoggerInterface $logger
     * @param Mapper $mapper
     * @codeCoverageIgnore
     */
    public function __construct(LoggerInterface $logger, Mapper $mapper)
    {
        $this->logger = $logger;
        $this->mapper = $mapper;
    }

    public function execute(AMQPMessage $message)
    {
        var_dump('FIX ME');die;
        $payload = unserialize($message->body);
        if (count($payload) == 1) {
            $this->mapper->save($payload[0]);
        } else {
            foreach ($payload as $model) {
                $this->mappedBulkSaver->save($model);
            }
            $this->mappedBulkSaver->flush();
        }

        $models = unserialize($message->body);

        $this->mapper->save($lead);

        return json_encode([
            'leadId' => $lead->getId()
        ]);
    }
}