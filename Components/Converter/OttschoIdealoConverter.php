<?php


namespace VanWittlaerTaxdooConnector\Components\Converter;


use stdClass;
use VanWittlaerTaxdooConnector\Structs\Order;
use VanWittlaerTaxdooConnector\Structs\Platform;

class OttschoIdealoConverter implements ConverterInterface
{
    /**
     * @inheritDoc
     */
    public function convertOrder(stdClass $order, Order $taxdooOrder): void
    {
        if (!isset($order->ottschoIdealo)) {

            return;
        }
        $taxdooOrder->setChannel(new Platform('IDE', $order->ottschoIdealo['idealoOrderId']));
    }

    /**
     * @inheritDoc
     */
    public function convertRefund(stdClass $order, Order $taxdooRefund): void
    {
    }
}