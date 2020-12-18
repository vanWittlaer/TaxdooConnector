<?php

namespace VanWittlaerTaxdooConnector\Tests;

use VanWittlaerTaxdooConnector\VanWittlaerTaxdooConnector as Plugin;
use Shopware\Components\Test\Plugin\TestCase;

class PluginTest extends TestCase
{
    protected static $ensureLoadedPlugins = [
        'VanWittlaerTaxdooConnector' => []
    ];

    public function testCanCreateInstance()
    {
        /** @var Plugin $plugin */
        $plugin = Shopware()->Container()->get('kernel')->getPlugins()['VanWittlaerTaxdooConnector'];

        $this->assertInstanceOf(Plugin::class, $plugin);
    }
}
