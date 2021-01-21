<?php

namespace VanWittlaerTaxdooConnector\Components\Resource;

use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use PDO;
use Shopware\Components\Model\ModelManager;

class Order
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ModelManager
     */
    private $models;

    /**
     * Order constructor.
     * @param Connection $connection
     * @param ModelManager $models
     */
    public function __construct(Connection $connection, ModelManager $models)
    {
        $this->connection = $connection;
        $this->models = $models;
    }

    /**
     * @param DateTime $from
     * @param DateTime $to
     * @param array $sentStatus
     * @param array $paidStatus
     * @param array $refundStatus
     * @param int $offset
     * @param int|null $limit
     * @return array
     */
    public function getTaxableOrders(
        DateTime $from,
        DateTime $to,
        array $sentStatus,
        array $paidStatus,
        array $refundStatus,
        int $offset = 0,
        ?int $limit = null
    ): array {
        $query = $this->buildTaxableOrdersQuery();
        $query->setParameter(':sentStatus', $sentStatus, Connection::PARAM_INT_ARRAY)
            ->setParameter(':paidStatus', $paidStatus, Connection::PARAM_INT_ARRAY)
            ->setParameter(':refundStatus', $refundStatus, Connection::PARAM_INT_ARRAY)
            ->setParameter(':from', $from->format('Y-m-d H:i:s'))
            ->setParameter(':to', $to->format('Y-m-d H:i:s'));

        if ($limit !== null) {
            $query->setFirstResult($offset)
                ->setMaxResults($limit);
        }
        $orders = $query->execute()->fetchAll(PDO::FETCH_OBJ);

        return array_combine(array_column($orders, 'number'), $orders);
    }

    /**
     * @return QueryBuilder
     */
    protected function buildTaxableOrdersQuery(): QueryBuilder
    {
        $sentQuery = $this->connection->createQueryBuilder();
        $sentQuery->from('s_order_history', 'sent')
            ->select('sent.change_date as sentDate')
            ->where($sentQuery->expr()->in('sent.order_status_id', ':sentStatus'))
            ->andWhere($sentQuery->expr()->eq('sent.orderID', 'ord.id'))
            ->orderBy('sent.change_date', 'ASC')
            ->setMaxResults(1);

        $paidQuery = $this->connection->createQueryBuilder();
        $paidQuery->from('s_order_history', 'paid')
            ->select('paid.change_date as paidDate')
            ->where($paidQuery->expr()->in('paid.payment_status_id', ':paidStatus'))
            ->andWhere($paidQuery->expr()->eq('paid.orderID', 'ord.id'))
            ->orderBy('paid.change_date', 'ASC')
            ->setMaxResults(1);

        $refundQuery = $this->connection->createQueryBuilder();
        $refundQuery->from('s_order_history', 'refund')
            ->select('refund.change_date as refundDate')
            ->where($refundQuery->expr()->in('refund.payment_status_id', ':refundStatus'))
            ->andWhere($refundQuery->expr()->eq('refund.orderID', 'ord.id'))
            ->orderBy('refund.change_date', 'ASC')
            ->setMaxResults(1);

        $query = $this->connection->createQueryBuilder();
        $query->from('s_order', 'ord')
            ->select('ord.ordernumber as number')
            ->addSelect('ord.id as id')
            ->addSelect('IFNULL((' . $sentQuery->getSQL() . '), IF(ord.status IN (:sentStatus), ord.ordertime, NULL)) as sentDate')
            ->addSelect('IFNULL((' . $paidQuery->getSQL() . '), IF(ord.cleared IN (:paidStatus), IFNULL(ord.cleareddate, ord.ordertime), NULL)) as paidDate')
            ->addSelect('(' . $refundQuery->getSQL() . ') as refundDate')
            ->addSelect('((ord.status IN (:sentStatus)) OR NOT (' . $sentQuery->getSQL() . ') IS NULL) as sentState')
            ->addSelect('((ord.cleared IN (:paidStatus)) OR NOT (' . $paidQuery->getSQL() . ') IS NULL) as paidState')
            ->addSelect('((ord.cleared IN (:refundStatus)) OR NOT (' . $refundQuery->getSQL() . ') IS NULL) as refundState')
            ->where('ord.ordernumber > 0')
            ->andWhere($query->expr()->orX(
                $query->expr()->in('ord.status', ':sentStatus'),
                $query->expr()->in('ord.cleared', ':paidStatus'),
                $query->expr()->in('ord.cleared', ':refundStatus')
            ))
            ->having($query->expr()->orX(
                $query->expr()->andX(
                    $query->expr()->gte('sentDate', ':from'),
                    $query->expr()->lt('sentDate', ':to')
                ),
                $query->expr()->andX(
                    $query->expr()->gte('paidDate', ':from'),
                    $query->expr()->lt('paidDate', ':to')
                ),
                $query->expr()->andX(
                    $query->expr()->gte('refundDate', ':from'),
                    $query->expr()->lt('refundDate', ':to')
                )
            ))
            ->orderBy('ord.ordernumber', 'ASC');

        return $query;
    }
}