<?php


namespace VanWittlaerTaxdooConnector\Components\Collectors;


use Doctrine\DBAL\Connection;
use PDO;
use stdClass;

class MagnalisterCollector implements CollectorInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * MagnalisterCollector constructor.
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @inheritDoc
     */
    public function collectOrder(stdClass $order): void
    {
        $mlData = $this->getMagnalisterOrder($order->id);
        if ($mlData === null) {

            return;
        }
        $data = json_decode($mlData['data'], true);

        $magnalister['channel'] = $mlData['platform'];
        $magnalister['orderId'] = $mlData['special'];

        if ($mlData['platform'] === 'amazon') {
            $magnalister['fulfillmentChannel'] = $data['FulfillmentChannel'] ?? null;
            if ($magnalister['fulfillmentChannel'] === 'AFN') {
                $magnalister['fulfillmentCenterId'] = $data['FulfillmentCenterId'] ?? null;
            }
        }

        $order->magnalister = $magnalister;
    }

    /**
     * @param string $orderId
     * @return array|null
     */
    private function getMagnalisterOrder(string $orderId): ?array
    {
        $query = $this->connection->createQueryBuilder();
        $query->from('magnalister_orders', 'orders')
            ->select('*')
            ->where($query->expr()->eq('current_orders_id', ':id'))
            ->setMaxResults(1)
            ->setParameter(':id', $orderId);

        $data = $query->execute()->fetch(PDO::FETCH_ASSOC);
        if ($data === false) {

            return null;
        }

        return $data;
    }
}