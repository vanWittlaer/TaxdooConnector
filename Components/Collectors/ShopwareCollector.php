<?php


namespace VanWittlaerTaxdooConnector\Components\Collectors;


use Doctrine\ORM\AbstractQuery;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Order\Order as OrderModel;
use stdClass;

class ShopwareCollector implements CollectorInterface
{
    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * ShopwareCollector constructor.
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
        $query = $this->modelManager->getRepository(OrderModel::class)
            ->getOrdersQuery([['property' => 'orders.id', 'value' => $order->id]], []);
        $query->setCacheable(false);

        $order->shopware = $query->getOneOrNullResult(AbstractQuery::HYDRATE_OBJECT);
    }
}