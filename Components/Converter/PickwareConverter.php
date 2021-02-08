<?php


namespace VanWittlaerTaxdooConnector\Components\Converter;


use Monolog\Logger;
use Shopware\CustomModels\ViisonPickwareERP\StockLedger\StockLedgerEntry;
use stdClass;
use VanWittlaerTaxdooConnector\Services\Configuration;
use VanWittlaerTaxdooConnector\Structs\Order;
use VanWittlaerTaxdooConnector\Structs\OrderItem;
use VanWittlaerTaxdooConnector\Structs\SenderAddress;


class PickwareConverter implements ConverterInterface
{
    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * PickwareConverter constructor.
     * @param Configuration $configuration
     * @param Logger $logger
     */
    public function __construct(Configuration $configuration, Logger $logger)
    {
        $this->configuration = $configuration;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function convertOrder(stdClass $order, Order $taxdooOrder): void
    {
        if (!isset($order->pickware)) {

            return;
        }
        if ($this->orderIsSplit($order)) {
            $this->logger->notice('Split orders not supported, ordernumber: ' . $order->number .
                ', using first item only to determine sender address.');
        }

        $this->setSenderAddress($order, $taxdooOrder);
    }

    /**
     * @inheritDoc
     */
    public function convertRefund(stdClass $order, Order $taxdooRefund): void
    {
        if (!isset($order->pickware)) {

            return;
        }
        if ($this->orderIsSplit($order)) {
            $this->logger->notice('Split refunds not supported, ordernumber: ' . $order->number .
                ', using first item only to determine sender address.');
        }

        $this->setSenderAddress($order, $taxdooRefund);
    }

    /**
     * @param int $id
     * @return SenderAddress|null
     */
    private function getSenderAddress(int $id): ?SenderAddress
    {
        if (!isset($this->configuration->get('pickware')['warehouses'])) {
            $this->logger->notice('Pickware warehouses not defined in Taxdoo.');

            return null;
        }
        $warehouses = $this->configuration->get('pickware')['warehouses'];
        if (!isset($warehouses[$id])) {
            $this->logger->notice('Pickware warehouse id: ' . $id . ' not defined in Taxdoo.');

            return null;
        }
        $address = (new SenderAddress());
        $address->setId($warehouses[$id]['taxdooId']);

        return $address;
    }

    /**
     * @param stdClass $order
     * @param Order $taxdooOrder
     */
    private function setSenderAddress(stdClass $order, Order $taxdooOrder): void
    {
        /** @var OrderItem $item */
        foreach ($taxdooOrder->getItems() as $item) {
            if (!isset($order->pickware[$item->getSourceItemNumber()])) {

                continue;
            }
            /** @var StockLedgerEntry $entry */
            $entry = $order->pickware[$item->getSourceItemNumber()]['stockLedgerEntry'];

            $senderAddress = $this->getSenderAddress($entry->getWarehouse()->getId());
            if ($senderAddress instanceof SenderAddress) {
                $taxdooOrder->setSenderAddress($senderAddress);

                break;
            }
        }
    }

    /**
     * @param stdClass $order
     * @return bool
     */
    private function orderIsSplit(stdClass $order): bool
    {
        $ids = [];
        foreach ($order->pickware as $item) {
            if (isset($item['stockLedgerEntry'])) {
                $ids[] = $item['stockLedgerEntry']->getWarehouse()->getId();
            }
        }
        if (count(array_flip($ids)) > 1) {

            return true;
        }

        return false;
    }
}