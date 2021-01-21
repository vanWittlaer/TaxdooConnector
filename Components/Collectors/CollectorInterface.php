<?php


namespace VanWittlaerTaxdooConnector\Components\Collectors;


use stdClass;

interface CollectorInterface
{
    /**
     * @param stdClass $order
     */
    public function collectOrder(stdClass $order): void;
}