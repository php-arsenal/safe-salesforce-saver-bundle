<?php

/**
 * Class SafeSalesforceSaver
 */
class SafeSalesforceSaver {

    /**
     * Use this function to save your model to Salesforce without waiting for a response.
     * @param $model
     */
    public function ASyncSave($model): void
    {
//        $rabbitMq->publish();
    }

    /**
     * Use this function to save your model to Salesforce and wait for the response.
     * @param $model
     * @return string
     */
    public function Save($model): string
    {
        $response = '';
//        $client = $this->get('old_sound_rabbit_mq.integer_store_rpc');
//        $client->addRequest(serialize(array('min' => 0, 'max' => 10)), 'random_int', 'request_id');
//        $replies = $client->getReplies();
        return $response;
    }
}