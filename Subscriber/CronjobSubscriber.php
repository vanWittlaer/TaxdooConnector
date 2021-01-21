<?php


namespace VanWittlaerTaxdooConnector\Subscriber;


use DateInterval;
use DateTime;
use Enlight\Event\SubscriberInterface;
use Exception;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Plugin\ConfigWriter;
use Shopware\Models\Plugin\Plugin;
use Shopware\Models\Shop\Shop;
use Shopware_Components_Cron_CronJob;
use VanWittlaerTaxdooConnector\Services\Configuration;
use VanWittlaerTaxdooConnector\Services\Reporter;

class CronjobSubscriber implements SubscriberInterface
{
    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var Reporter
     */
    private $reporter;

    /**
     * @var ConfigWriter
     */
    private $configWriter;

    /**
     * @var ModelManager
     */
    private $modelManager;

    public function __construct(
        Configuration $configuration,
        Reporter $reporter,
        ConfigWriter $configWriter,
        ModelManager $modelManager
    ) {
        $this->configuration = $configuration;
        $this->reporter = $reporter;
        $this->configWriter = $configWriter;
        $this->modelManager = $modelManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'Shopware_CronJob_TaxdooDailyReport' => 'onDailyCron',
        ];
    }

    /**
     * @param Shopware_Components_Cron_CronJob $cronjob
     * @return string
     * @throws Exception
     */
    public function onDailyCron(Shopware_Components_Cron_CronJob $cronjob): string
    {
        $this->configuration->init();

        $to = new DateTime('now');
        $to->setTime(0, 0);
        $from = clone $to;
        if ($this->configuration->get('pluginConfig')['taxdooHistory']) {

            return $this->historyRun($from, $to);
        }
        if ((int)(new DateTime('now'))->format('d') === $this->configuration->get('pluginConfig')['taxdooMonthlyDoM']) {

            return $this->monthlyRun($from, $to);
        }

        return $this->dailyRun($from, $to);
    }

    /**
     * @param DateTime $from
     * @param DateTime $to
     * @return string
     * @throws Exception
     */
    private function dailyRun(DateTime $from, DateTime $to): string
    {
        $from->sub(new DateInterval($this->configuration->get('dailyInterval')));
        if ($this->reporter->submit($from, $to) !== 0) {

            return 'Daily run finished with error - see log for details.';
        }

        return 'Daily run successful - see log for details.';
    }

    /**
     * @param DateTime $from
     * @param DateTime $to
     * @return string
     * @throws Exception
     */
    private function monthlyRun(DateTime $from, DateTime $to): string
    {
        $from->sub(new DateInterval($this->configuration->get('monthlyInterval')));
        if ($this->reporter->submit($from, $to) !== 0) {

            return 'Monthly run finished with error - see log for details.';
        }

        return 'Monthly run successful - see log for details.';
    }

    /**
     * @param DateTime $from
     * @param DateTime $to
     * @return string
     * @throws Exception
     */
    private function historyRun(DateTime $from, DateTime $to): string
    {
        $plugin = $this->modelManager->getRepository(Plugin::class)
            ->findOneBy(['name' => 'VanWittlaerTaxdooConnector']);
        $shop = $this->modelManager->getRepository(Shop::class)
            ->getActiveDefault();
        $this->configWriter->saveConfigElement($plugin, 'taxdooHistory', false, $shop);

        $from->sub(new DateInterval($this->configuration->get('historyInterval')));
        if ($this->reporter->submit($from, $to) !== 0) {

            return 'History run finished with error - see log for details.';
        }

        return 'History run successful - see log for details.';
    }
}