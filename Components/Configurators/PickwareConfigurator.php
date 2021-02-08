<?php


namespace VanWittlaerTaxdooConnector\Components\Configurators;


use Shopware\Components\Model\ModelManager;
use Shopware\CustomModels\ViisonPickwareERP\Warehouse\Warehouse;

class PickwareConfigurator implements ConfiguratorInterface
{
    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * PickwareConfigurator constructor.
     * @param ModelManager $modelManager
     */
    public function __construct(ModelManager $modelManager)
    {
        $this->modelManager = $modelManager;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'pickware';
    }

    /**
     * @inheritDoc
     */
    public function init(): array
    {
        $warehouseRepository = $this->modelManager->getRepository(Warehouse::class);
        $warehouses = $warehouseRepository->findAll();
        $pickware = [];

        /** @var Warehouse $warehouse */
        foreach ($warehouses as $warehouse) {
            if (preg_match("/\[TaxdooWarehouseId:\s*(\d{1,8}\s*)\]/", $warehouse->getComment(), $hits) === 1) {
                $pickwareId = $warehouse->getId();
                $pickware['warehouses'][$pickwareId] = [
                    'pickwareId' => $pickwareId,
                    'taxdooId' => (int)$hits[1],
                ];
            }
        }

        return $pickware;
    }
}