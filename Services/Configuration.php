<?php


namespace VanWittlaerTaxdooConnector\Services;


use Exception;
use IteratorAggregate;
use Shopware\Components\Logger;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Plugin\ConfigReader;
use Shopware\Models\Shop\Shop;
use Symfony\Component\DependencyInjection\ContainerInterface;
use VanWittlaerTaxdooConnector\Components\Configurators\ConfiguratorInterface;

class Configuration
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var array
     */
    private $configReader;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var ConfiguratorInterface[]
     */
    private $configurators;

    /**
     * @var int
     */
    private $defaultWarehouseId;

    /**
     * @var bool
     */
    private $initialized = false;

    /**
     * Configuration constructor.
     * @param array $config
     * @param ConfigReader $configReader
     * @param Logger $logger
     * @param ModelManager $modelManager
     * @param ContainerInterface $container
     * @param IteratorAggregate $configurators
     */
    public function __construct(
        array $config,
        ConfigReader $configReader,
        Logger $logger,
        ModelManager $modelManager,
        ContainerInterface $container,
        IteratorAggregate $configurators
    ) {
        $this->configReader = $configReader;
        $this->logger = $logger;
        $this->modelManager = $modelManager;
        $this->container = $container;
        $this->configurators = iterator_to_array($configurators, false);

        $this->config = array_replace_recursive([
            'baseUrl' => 'https://api.taxdoo.com/',
            'sandboxUrl' => 'https://sandbox-api.taxdoo.com/',
            'connect_timeout' => 1.0,
            'timeout' => 30.0,
            'synchronous' => true,
            'apiChunkSize' => 500,
            'defaultSourceCode' => 'SWR5',
            'defaultChannelCode' => 'WEB',
            'historyInterval' => 'P2Y',
            'monthlyInterval' => 'P2M',
            'dailyInterval' => 'P3D',
        ], $config);
    }

    /**
     * Make sure this method is called as early as possible in your flow, i.e. in
     *  - controllers put it in preDispatch(),
     *  - in commands put is as one of the first actions w/in execute().
     * but never attempt to call it from within a constructor.
     */
    public function init()
    {
        if ($this->initialized) {

            return;
        }
        $this->initialized = true;

        $this->config['pluginConfig'] = $this->configReader->getByPluginName('VanWittlaerTaxdooConnector');
        $this->config['shops'] = $this->getPluginConfigForShops();
        foreach ($this->logger->getHandlers() as $handler) {
            $handler->setLevel($this->config['pluginConfig']['taxdooLoggerLevel']);
        }

        foreach ($this->configurators as $configurator) {
            $this->config[$configurator->getName()] = $configurator->init();
        }
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        $this->init();

        return $this->config;
    }

    /**
     * @param string $parameter
     * @return mixed|null
     */
    public function get(string $parameter)
    {
        $this->init();

        return ($this->config[$parameter] ?: null);
    }

    /**
     * @param int $shopId
     * @return int
     * @throws Exception
     */
    public function getDefaultWarehouseId(int $shopId): int
    {
        $this->init();

        $id = $this->config['shops'][$shopId]['taxdooShopWarehouse'];
        if ($id === null) {
            if ($this->defaultWarehouseId === null) {
                $taxdooClient = $this->container->get('van_wittlaer_taxdoo_connector.components.taxdoo_client');
                $result = $taxdooClient->get('warehouses/standard');
                if ($result['status'] !== 'success') {

                    throw new Exception('Taxdoo-Configuration - failed to retrieve warehouses from Taxdoo API');
                }
                $this->defaultWarehouseId = $result['warehouse']['id'];
            }
            $id = $this->defaultWarehouseId;
        }

        return $id;
    }

    /**
     * @return array
     */
    private function getPluginConfigForShops(): array
    {
        $shopRepo = $this->modelManager->getRepository(Shop::class);
        $shops = $shopRepo->findAll();
        $configs = [];
        /** @var Shop $shop */
        foreach ($shops as $shop) {
            $configs[$shop->getId()] = $this->configReader->getByPluginName('VanWittlaerTaxdooConnector', $shop);
        }

        return $configs;
    }
}