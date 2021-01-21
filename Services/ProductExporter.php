<?php

namespace VanWittlaerTaxdooConnector\Services;

use Shopware\Components\Model\ModelManager;
use Shopware\Models\Article\Detail;
use Shopware\Models\Shop\Shop;
use Symfony\Component\Console\Style\SymfonyStyle;
use VanWittlaerTaxdooConnector\Structs\Product;
use VanWittlaerTaxdooConnector\Structs\Weight;

class ProductExporter
{
    use TraitProgressBar;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var ModelManager
     */
    private $models;

    /**
     * @var Transmitter
     */
    private $transmitter;

    public function __construct(
        Configuration $configuration,
        ModelManager $models,
        Transmitter $transmitter
    ) {
        $this->configuration = $configuration;
        $this->models = $models;
        $this->transmitter = $transmitter;
    }

    /**
     * @param SymfonyStyle $symfonyStyle
     */
    public function setOutputInterface(SymfonyStyle $symfonyStyle)
    {
        $this->symfonyStyle = $symfonyStyle;
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function submit(): int
    {
        $currency = $this->getCurrency();

        $queue = clone $this->transmitter;
        $queue->setEndpoint('products');

        $detailRepository = $this->models->getRepository(Detail::class);
        $articles = $detailRepository->findAll();
        $progress = $this->createProgressBar(count($articles), 'taxdoo:export:products');
        foreach ($articles as $article) {
            $this->progressAdvance($progress);

            $product = new Product();
            $product->setProductIdentifier($article->getNumber());
            $product->setCurrency($currency);
            $product->setPurchasePrice($article->getPurchasePrice());

            if ($article->getWeight()) {
                $product->setWeight(new Weight($article->getWeight(), 'kg'));
            }

            $queue->add($product);
        }
        $queue->flush();
        $this->progressFinish($progress);

        return 0;
    }

    /**
     * @return string
     */
    private function getCurrency(): string
    {
        $shopRepository = $this->models->getRepository(Shop::class);

        /** @var Shop $shop */
        $shop = $shopRepository->getDefault();

        return $shop->getCurrency()->getCurrency();
    }
}