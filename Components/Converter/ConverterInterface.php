<?php


namespace VanWittlaerTaxdooConnector\Components\Converter;


use stdClass;
use VanWittlaerTaxdooConnector\Structs\Order;

interface ConverterInterface
{
    /**
     * @param stdClass $order
     * @param Order $taxdooOrder
     */
    public function convertOrder(stdClass $order, Order $taxdooOrder): void;

    /**
     * @param stdClass $order
     * @param Order $taxdooOrder
     */
    public function convertRefund(stdClass $order, Order $taxdooRefund): void;
}