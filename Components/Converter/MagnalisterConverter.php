<?php


namespace VanWittlaerTaxdooConnector\Components\Converter;


use stdClass;
use VanWittlaerTaxdooConnector\Structs\FulfillmentCenter;
use VanWittlaerTaxdooConnector\Structs\Order;
use VanWittlaerTaxdooConnector\Structs\Platform;
use VanWittlaerTaxdooConnector\Structs\SenderAddress;

class MagnalisterConverter implements ConverterInterface
{
    /**
     * @inheritDoc
     */
    public function convertOrder(stdClass $order, Order $taxdooOrder): void
    {
        if (!isset($order->magnalister)) {

            return;
        }

        $taxdooOrder->setChannel($this->getChannel($order));
        $this->setSenderAddress($order, $taxdooOrder);
    }

    /**
     * @inheritDoc
     */
    public function convertRefund(stdClass $order, Order $taxdooRefund): void
    {
    }

    /**
     * @param stdClass $order
     * @param Order $taxdoo
     */
    private function setSenderAddress(stdClass $order, Order $taxdoo): void
    {
        if (($order->magnalister['channel'] === 'amazon') && isset($order->magnalister['fulfillmentCenterId'])) {
            $address = new SenderAddress();
            $address->setFulfillmentCenter(
                new FulfillmentCenter('amazon', $order->magnalister['fulfillmentCenterId'])
            );

            $taxdoo->setSenderAddress($address);
        }
    }

    /**
     * @param $order
     * @return Platform
     */
    private function getChannel($order): Platform
    {
        $channel = strtolower($order->magnalister['channel']);
        switch ($channel) {
            case 'amazon':
                $channel = 'MFN';
                if (isset($order->magnalister['fulfillmentCenterId'])) {
                    $channel = 'AFN';
                }
                break;
            case 'ebay':
                $channel = 'EBY';
                break;
            case 'hitmeister':
                $channel = 'REAL';
                break;
            case 'otto':
                $channel = 'OTTO';
                break;
        }

        return new Platform($channel, $order->magnalister['orderId']);
    }
}