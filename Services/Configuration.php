<?php


namespace VanWittlaerTaxdooConnector\Services;


use Shopware\Components\Logger;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Plugin\ConfigReader;
use Shopware\Models\Shop\Shop;

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
     * @var bool
     */
    private $initialized = false;

    public function __construct(
        array $config,
        ConfigReader $configReader,
        Logger $logger,
        ModelManager $modelManager
    ) {
        $this->configReader = $configReader;
        $this->logger = $logger;
        $this->modelManager = $modelManager;

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
            'monthlyInterval' => 'P64D',
            'dailyInterval' => 'P2D',
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
        if (!$this->initialized) {
            $this->initialized = true;
            $this->config['pluginConfig'] = $this->configReader->getByPluginName('VanWittlaerTaxdooConnector');
            $this->config['shops'] = $this->getPluginConfigForShops();
            foreach ($this->logger->getHandlers() as $handler) {
                $handler->setLevel($this->config['pluginConfig']['taxdooLoggerLevel']);
            }
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