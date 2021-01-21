<?php

namespace VanWittlaerTaxdooConnector\Commands;

use Exception;
use Shopware\Commands\ShopwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use VanWittlaerTaxdooConnector\Components\TaxdooClient;

class Cleanup extends ShopwareCommand
{
    const COMMAND = 'taxdoo:cleanup';
    const SECRET = 'Taxdoo';

    /**
     * @var TaxdooClient
     */
    private $taxdooClient;

    public function __construct(TaxdooClient $taxdooClient, $name = null)
    {
        $this->taxdooClient = $taxdooClient;

        parent::__construct($name);
    }

    /**
     *
     */
    public function configure()
    {
        $this->setName(self::COMMAND);
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $style = new SymfonyStyle($input, $output);

        $answer = $style->askQuestion(new Question('Do you really want to cleanup all data? Enter the secret:', 'don\'t know'));
        if ($answer !== self::SECRET) {
            $style->error('please see documentation or contact Taxdoo support to obtain the secret');

            return 32;
        }
        $this->deleteTransactions();
        $this->deleteProducts();

        return 0;
    }

    /**
     * @throws Exception
     */
    private function deleteTransactions()
    {
        $result = $this->taxdooClient->get('transactions', ['limit' => 100, 'page' => 1]);

        while (($result['status'] === 'success') && (count($result['transactions']) > 0)) {
            $ids = array_column($result['transactions'], 'id');
            $deleted = $this->taxdooClient->delete('transactions', ['ids' => implode(',', $ids)]);
            if ($deleted['status'] !== 'success') {

                break;
            }
            $result = $this->taxdooClient->get('transactions', ['limit' => 100, 'page' => 1]);
        }
    }

    /**
     * @throws Exception
     */
    private function deleteProducts()
    {
        $result = $this->taxdooClient->get('products', ['limit' => 50, 'page' => 1]);

        while (($result['status'] === 'success') && (count($result['products']) > 0)) {
            $ids = array_column($result['products'], 'productIdentifier');

            $deleted = $this->taxdooClient->delete('products', ['product_identifiers' => implode(',',$ids)]);
            if ($deleted['status'] !== 'success') {

                break;
            }
            $result = $this->taxdooClient->get('products', ['limit' => 50, 'page' => 1]);
        }
    }
}