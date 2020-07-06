<?php

namespace Comsave\SafeSalesforceSaverBundle\Services;

use Comsave\SafeSalesforceSaverBundle\Services\SalesforceSaver\AsyncSalesforceSaver;
use Comsave\SafeSalesforceSaverBundle\Services\SalesforceSaver\SyncSalesforceSaver;

class SafeSalesforceSaver
{
    /** @var AsyncSalesforceSaver */
    private $asyncSalesforceSaver;

    /** @var SyncSalesforceSaver */
    private $syncSalesforceSaver;

    /**
     * SafeSalesforceSaver constructor.
     * @param AsyncSalesforceSaver $asyncSalesforceSaver
     * @param SyncSalesforceSaver $syncSalesforceSaver
     */
    public function __construct(
        AsyncSalesforceSaver $asyncSalesforceSaver,
        SyncSalesforceSaver $syncSalesforceSaver
    ) {
        $this->asyncSalesforceSaver = $asyncSalesforceSaver;
        $this->syncSalesforceSaver = $syncSalesforceSaver;
    }

    /**
     * Use this function to save your model(s) to Salesforce without waiting for a response.
     * @param $models mixed You can either pass a single object or an array of objects.
     */
    public function aSyncSave($models): void
    {
        $this->asyncSalesforceSaver->save($models);
    }

    /**
     * Use this function to save your model(s) to Salesforce and get the IDs set on your object(s).
     * The IDs will only be set if your model has a public `setId` function.
     * @param $models mixed You can either pass a single object or an array of objects.
     * @throws \Exception
     */
    public function save($models): void
    {
        $this->syncSalesforceSaver->save($models);
    }
}
