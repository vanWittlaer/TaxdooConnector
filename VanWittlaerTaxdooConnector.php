<?php

namespace VanWittlaerTaxdooConnector;

use Doctrine\ORM\EntityRepository;
use Exception;
use Mpdf\Tag\Ins;
use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\ActivateContext;
use Shopware\Components\Plugin\Context\DeactivateContext;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use Shopware\Models\Shop\Shop;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;

class VanWittlaerTaxdooConnector extends Plugin
{
    const CACHE_LIST = [
        InstallContext::CACHE_TAG_CONFIG,
        InstallContext::CACHE_TAG_ROUTER,
        InstallContext::CACHE_TAG_PROXY,
    ];

    /**
     * {@inheritDoc}
     */
    public function install(InstallContext $context)
    {
        parent::install($context);

        $this->setDefaultConfig();

        if ($context->getPlugin()->getActive()) {
            $context->scheduleClearCache(self::CACHE_LIST);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function activate(ActivateContext $context)
    {
        $context->scheduleClearCache(self::CACHE_LIST);
    }

    /**
     * {@inheritDoc}
     */
    public function deactivate(DeactivateContext $context)
    {
        $context->scheduleClearCache(self::CACHE_LIST);
    }

    /**
     * {@inheritDoc}
     */
    public function uninstall(UninstallContext $context)
    {
        if ($context->getPlugin()->getActive()) {
            $context->scheduleClearCache(self::CACHE_LIST);
        }
    }

    /**
     * {@inheritDoc}
     * @throws Exception
     */
    public function build(ContainerBuilder $container)
    {
        if (version_compare($container->getParameter('shopware.release.version'), '5.6.0', '<')) {
            $this->loadFile($container, __DIR__ . '/Resources/services/5.5/logger.xml');
        }

        $plugins = $container->getParameter('active_plugins');
        if (isset($plugins['ViisonPickwareERP']) && version_compare($plugins['ViisonPickwareERP'], '6.6.7', '>=')) {
            $this->loadFile($container, __DIR__ . '/Resources/services/pickware.xml');
        }
        if (isset($plugins['RedMagnalister']) && version_compare($plugins['RedMagnalister'], '3.0.10', '>=')) {
            $this->loadFile($container, __DIR__ . '/Resources/services/magnalister.xml');
        }

        parent::build($container);
    }

    /**
     * @param ContainerBuilder $container
     * @param $filename
     *
     * @throws Exception
     */
    private function loadFile(ContainerBuilder $container, $filename)
    {
        if (!is_file($filename)) {
            return;
        }
        $loader = new XmlFileLoader(
            $container,
            new FileLocator()
        );
        $loader->load($filename);
    }

    /**
     *
     */
    private function setDefaultConfig()
    {
        $models = $this->container->get('models');

        /** @var EntityRepository $pluginRepo */
        $pluginRepo = $models->getRepository(\Shopware\Models\Plugin\Plugin::class);
        $plugin = $pluginRepo->findOneBy(['name' => $this->getName()]);

        /** @var EntityRepository $shopRepo */
        $shopRepo = $models->getRepository(\Shopware\Models\Shop\Shop::class);
        $shop = $shopRepo->findOneBy([
            'mainId' => null,
            'default' => true,
            'active' => true,
        ]);

        $writer = $this->container->get('shopware.plugin.config_writer');
        $pluginConfig = $this->container->get('shopware.plugin.config_reader')->getByPluginName($this->getName());

        !empty($pluginConfig['taxdooSentStatus']) ?:
            $writer->saveConfigElement($plugin, 'taxdooSentStatus', [2, 3, 6, 7], $shop);
        !empty($pluginConfig['taxdooPaidStatus']) ?:
            $writer->saveConfigElement($plugin, 'taxdooPaidStatus', [11, 12], $shop);
        !empty($pluginConfig['taxdooRefundStatus']) ?:
            $writer->saveConfigElement($plugin, 'taxdooRefundStatus', [20], $shop);
        !empty($pluginConfig['taxdooInvoiceDocTypes']) ?:
            $writer->saveConfigElement($plugin, 'taxdooInvoiceDocTypes', [1], $shop);
        !empty($pluginConfig['taxdooRefundDocTypes']) ?:
            $writer->saveConfigElement($plugin, 'taxdooRefundDocTypes', [3, 4], $shop);
    }
}
