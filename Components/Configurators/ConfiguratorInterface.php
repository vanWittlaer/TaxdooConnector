<?php


namespace VanWittlaerTaxdooConnector\Components\Configurators;


interface ConfiguratorInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return array
     */
    public function init(): array;
}