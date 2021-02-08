<?php


namespace VanWittlaerTaxdooConnector\Components\Collectors;


use Doctrine\ORM\EntityRepository;
use Shopware\Components\Model\ModelManager;
use Shopware\CustomModels\ViisonPickwareERP\StockLedger\StockLedgerEntry;
use Shopware\Models\Order\Detail;
use stdClass;
use VanWittlaerTaxdooConnector\Components\Converter\ShopwareConverter;

class PickwareCollector implements CollectorInterface
{
    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var EntityRepository
     */
    private $stockLedgerRepository;

    /**
     * PickwareCollector constructor.
     * @param ModelManager $modelManager
     */
    public function __construct(ModelManager $modelManager)
    {
        $this->modelManager = $modelManager;
    }

    /**
     * @inheritDoc
     */
    public function collectOrder(stdClass $order): void
    {
        /** @var Detail $detail */
        foreach ($order->shopware->getDetails() as $detail) {
            if ($detail->getMode() !== ShopwareConverter::ITEM_PRODUCT) {

                continue;
            }
            $entry = $this->getStockLedgerEntry($detail);
            if ($entry instanceof StockLedgerEntry) {
                $order->pickware[$detail->getId()] = [
                    'orderId' => $detail->getId(),
                    'stockLedgerEntry' => $entry,
                ];
            }
        }
    }

    /**
     * @param Detail $detail
     * @return StockLedgerEntry|null
     */
    private function getStockLedgerEntry(Detail $detail): ?StockLedgerEntry
    {
        return $this->getStockLedgerRepository()->findOneBy([
            'orderDetailId' => $detail->getId(),
            'type' => StockLedgerEntry::TYPE_SALE,
        ]);
    }

    /**
     * @return EntityRepository
     */
    private function getStockLedgerRepository(): EntityRepository
    {
        if (!$this->stockLedgerRepository instanceof EntityRepository) {
            $this->stockLedgerRepository = $this->modelManager->getRepository(StockLedgerEntry::class);
        }

        return $this->stockLedgerRepository;
    }
}