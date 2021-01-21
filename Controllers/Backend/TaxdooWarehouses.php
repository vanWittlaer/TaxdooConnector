<?php

class Shopware_Controllers_Backend_TaxdooWarehouses extends Shopware_Controllers_Backend_Application
{
    protected $model = 'must_not_be_empty';
    protected $alias = 'must_not_be_empty';

    public function preDispatch()
    {
        $this->container->get('van_wittlaer_taxdoo_connector.services.configuration')->init();

        parent::preDispatch();
    }

    public function listAction()
    {
        $taxdooClient = $this->container->get('van_wittlaer_taxdoo_connector.components.taxdoo_client');
        $result = $taxdooClient->get('warehouses');
        if ($result['status'] !== 'success') {

            return;
        }
        $data = [];
        foreach ($result['warehouses'] as $warehouse) {
            $data[] = [
                'id' => $warehouse['id'],
                'address' => '[' . (string)$warehouse['id'] . '] ' . $warehouse['country'] . ' - ' .
                    $warehouse['city'] . ' - ' . $warehouse['street'],
            ];
        }
        $this->view->assign([
            'data' => $data,
            'total' => count($data),
        ]);
    }
}