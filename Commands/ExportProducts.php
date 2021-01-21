<?php

namespace VanWittlaerTaxdooConnector\Commands;

use DateInterval;
use DateTime;
use Exception;
use Shopware\Commands\ShopwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use VanWittlaerTaxdooConnector\Services\ProductExporter;

class ExportProducts extends ShopwareCommand
{
    const COMMAND = 'taxdoo:export:products';

    /**
     * @var ProductExporter
     */
    private $productExporter;

    public function __construct(ProductExporter $productExporter, $name = null)
    {
        $this->productExporter = $productExporter;

        parent::__construct($name);
    }

    public function configure()
    {
        $this->setName(self::COMMAND);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->container->get('van_wittlaer_taxdoo_connector.services.configuration')->init();
        $this->productExporter->setOutputInterface(new SymfonyStyle($input, $output));

        return $this->productExporter->submit();
    }
}