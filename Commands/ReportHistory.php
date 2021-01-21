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
use VanWittlaerTaxdooConnector\Services\Reporter;

class ReportHistory extends ShopwareCommand
{
    const COMMAND = 'taxdoo:report:history';

    /**
     * @var Reporter
     */
    private $reporter;

    public function __construct(Reporter $reporter, $name = null)
    {
        $this->reporter = $reporter;

        parent::__construct($name);
    }

    public function configure()
    {
        $this->setName(self::COMMAND)
            ->addOption('from', 'f', InputOption::VALUE_OPTIONAL, 'First day (incl.) for reporting history', null)
            ->addOption('to', '-t', InputOption::VALUE_OPTIONAL, 'Last day (incl.) for reporting history', null);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->container->get('van_wittlaer_taxdoo_connector.services.configuration')->init();

        $style = new SymfonyStyle($input, $output);

        $options = $input->getOptions();
        if (($options['from'] === null && $options['to'] !== null) || ($options['from'] !== null && $options['to'] === null)) {
            $style->error("Options 'from' and 'to' must be set either both or none.");

            return 1;
        }

        $end = new DateTime('now');
        if ($options['to'] !== null) {
            try {
                $end = new DateTime($options['to']);
            } catch (Exception $e) {
                $style->error("Invalid date format for last day.");

                return 1;
            }
        }

        $begin = clone $end;
        $begin->sub(new DateInterval('P2Y'));
        if ($options['from'] !== null) {
            try {
                $begin = (new DateTime($options['from']))->sub(new DateInterval('P1D'));
            } catch (Exception $e) {
                $style->error("Invalid date format for first day.");

                return 1;
            }
        }

        $this->reporter->setOutputInterface($style);

        return $this->reporter->submit($begin, $end);
    }
}