<?php

namespace VanWittlaerTaxdooConnector\Services;

use DateInterval;
use DateTime;
use Exception;
use IteratorAggregate;
use Monolog\Logger;
use stdClass;
use Symfony\Component\Console\Style\SymfonyStyle;
use VanWittlaerTaxdooConnector\Components\Collectors\CollectorInterface;
use VanWittlaerTaxdooConnector\Components\Converter\ConverterInterface;
use VanWittlaerTaxdooConnector\Components\Resource\Order as OrderResource;
use VanWittlaerTaxdooConnector\Structs\Order;

class Reporter
{
    use TraitProgressBar;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var OrderResource
     */
    private $orderResource;

    /**
     * @var Transmitter
     */
    private $transmitter;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var CollectorInterface[]
     */
    private $collectors;

    /**
     * @var ConverterInterface[]
     */
    private $converters;

    /**
     * Reporter constructor.
     * @param Configuration $configuration
     * @param OrderResource $orderResource
     * @param Transmitter $transmitter
     * @param Logger $logger
     * @param IteratorAggregate $collectors
     * @param IteratorAggregate $converters
     */
    public function __construct(
        Configuration $configuration,
        OrderResource $orderResource,
        Transmitter $transmitter,
        Logger $logger,
        IteratorAggregate $collectors,
        IteratorAggregate $converters
    ) {
        $this->configuration = $configuration;
        $this->orderResource = $orderResource;
        $this->transmitter = $transmitter;
        $this->logger = $logger;
        $this->collectors = iterator_to_array($collectors, false);
        $this->converters = iterator_to_array($converters, false);
    }

    /**
     * @param SymfonyStyle $symfonyStyle
     */
    public function setOutputInterface(SymfonyStyle $symfonyStyle)
    {
        $this->symfonyStyle = $symfonyStyle;
    }

    /**
     * @param DateTime $from
     * @param DateTime $to
     * @return int
     * @throws Exception
     */
    public function submit(DateTime $from, DateTime $to): int
    {
        $pluginConfig = $this->configuration->get('pluginConfig');

        $orders = $this->orderResource->getTaxableOrders(
            $from, $to,
            $pluginConfig['taxdooSentStatus'],
            $pluginConfig['taxdooPaidStatus'],
            $pluginConfig['taxdooRefundStatus']
        );
        $fromIncl = $from->add(new DateInterval('P1D'));
        if (count($orders) <= 0) {
            $this->logger->notice('TaxdooConverter - no orders to report from ' .
                $fromIncl->format('Y-m-d') . ' to ' . $to->format('Y-m-d') . ' (inclusive)'
            );

            if ($this->symfonyStyle instanceof SymfonyStyle) {
                $this->symfonyStyle->warning('No orders to report for the given dates.');
            }

            return 0;
        }

        $this->logger->notice('TaxdooConverter - reporting ' . count($orders) . ' orders from ' .
            $fromIncl->format('Y-m-d') . ' to ' . $to->format('Y-m-d') . ' (inclusive)'
        );
        try {
            $insertedRows = $this->buildAndTransmit($orders);
        } catch (Exception $e) {
            $this->logger->error('TaxdooConverter - reporting abended.', [
                'message' => $e->getMessage(),
                'trace' => $e->getTrace(),
            ]);

            return 4;
        }
        $this->logger->notice('TaxdooConverter - inserted ' . $insertedRows . ' new orders & refunds');

        return 0;
    }

    /**
     * @param $orders
     * @return int
     * @throws Exception
     */
    private function buildAndTransmit($orders): int
    {

        $queue = clone $this->transmitter;
        $queue->setEndpoint('orders');

        $progress = $this->createProgressBar(count($orders), 'taxdoo:report - processing orders');
        foreach ($orders as $order) {
            $this->progressAdvance($progress);

            $this->collectAdditionalData($order);

            if (!$this->isReportingActiveForShop($order)) {

                continue;
            }
            if ($order->sentState !== '1' && $order->paidState !== '1') {

                continue;
            }
            $taxdooOrder = new Order();
            $this->convertOrder($order, $taxdooOrder);
            $queue->add($taxdooOrder);

            if ($order->refundState !== '1') {

                continue;
            }
            $taxdooRefund = clone $taxdooOrder;
            $this->convertRefund($order, $taxdooRefund);
            $queue->add($taxdooRefund);
        }
        $this->progressFinish($progress);

        return $queue->flush();
    }

    /**
     * @param stdClass $order
     * @return bool
     */
    private function isReportingActiveForShop(stdClass $order): bool
    {
        $shopId = $order->shopware->getShop()->getId();
        return $this->configuration->get('shops')[$shopId]['taxdooActive'];
    }

    /**
     * @param stdClass $order
     */
    private function collectAdditionalData(stdClass $order): void
    {
        foreach ($this->collectors as $collector) {
            $collector->collectOrder($order);
        }
    }

    /**
     * @param stdClass $order
     * @param Order $taxdooOrder
     */
    private function convertOrder(stdClass $order, Order $taxdooOrder): void
    {
        $taxdooOrder->setType('Sale');
        foreach ($this->converters as $converter) {
            $converter->convertOrder($order, $taxdooOrder);
        }
    }

    /**
     * @param stdClass $order
     * @param Order $taxdooRefund
     */
    private function convertRefund(stdClass $order, Order $taxdooRefund): void
    {
        $taxdooRefund->setType('Refund');
        foreach ($this->converters as $converter) {
            $converter->convertRefund($order, $taxdooRefund);
        }
    }
}