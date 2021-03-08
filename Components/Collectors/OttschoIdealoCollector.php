<?php


namespace VanWittlaerTaxdooConnector\Components\Collectors;


use Doctrine\ORM\EntityRepository;
use OttIdealoDirectsale\Entity\IdealoOrderState;
use Shopware\Components\Model\ModelManager;
use stdClass;

class OttschoIdealoCollector implements CollectorInterface
{
    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var EntityRepository
     */
    private $idealOrderStateRepository;

    /**
     * OttschoIdealoCollector constructor.
     * @param ModelManager $modelManager
     */
    public function __construct(ModelManager $modelManager)
    {
        $this->modelManager = $modelManager;
    }

    /**
     * @inheritDoc
     */
    public function collectOrder(stdClass $order): void
    {
        $idealo = $this->getIdealoOrderStateRepository()->findOneBy(['orderId' => $order->id]);
        if (!$idealo instanceof IdealoOrderState) {

            return;
        }

        $order->ottschoIdealo['idealoOrderId'] = $idealo->getIdealoOrderId();
    }

    /**
     * @return EntityRepository
     */
    private function getIdealoOrderStateRepository(): EntityRepository
    {
        if (!$this->idealOrderStateRepository instanceof EntityRepository) {
            $this->idealOrderStateRepository = $this->modelManager->getRepository(IdealoOrderState::class);
        }

        return $this->idealOrderStateRepository;
    }
}