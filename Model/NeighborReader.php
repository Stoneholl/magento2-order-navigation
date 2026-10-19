<?php

declare(strict_types=1);

namespace Stoneholl\OrderNavigation\Model;

use Magento\Framework\App\ResourceConnection;

/** Internal reader; deployments with row restrictions require a verified integration before enabling. */
class NeighborReader
{
    /** @var ResourceConnection */
    private $resource;

    public function __construct(ResourceConnection $resource)
    {
        $this->resource = $resource;
    }

    /**
     * Read exactly two neighbors by record sequence, independent of dates, status and grid state.
     *
     * @param int $orderId
     * @param int $storeId
     * @return array
     */
    public function getNeighbors(int $orderId, int $storeId): array
    {
        if ($orderId <= 0 || $storeId <= 0) {
            throw new \InvalidArgumentException('A persisted order and storefront are required.');
        }
        $connection = $this->resource->getConnection();
        // MySQL 5.7 can otherwise walk half the store's index backwards before applying the ID bound.
        // Use Magento's existing store index and its implicit InnoDB primary-key suffix; add no index.
        $table = $connection->quoteIdentifier($this->resource->getTableName('sales_order'))
            . ' FORCE INDEX ('
            . $connection->quoteIdentifier($this->resource->getIdxName('sales_order', ['store_id']))
            . ')';
        $result = [];
        foreach (['previous' => ['<', 'DESC'], 'next' => ['>', 'ASC']] as $direction => $order) {
            // Select has no index-hint API. Identifiers are quoted above; both values stay bound below.
            // phpcs:ignore Magento2.SQL.RawQuery.FoundRawSql
            $query = 'SELECT entity_id, increment_id FROM ' . $table
                . ' WHERE store_id = :store_id AND entity_id ' . $order[0] . ' :order_id'
                . ' ORDER BY entity_id ' . $order[1] . ' LIMIT 1';
            $row = $connection->fetchRow($query, ['store_id' => $storeId, 'order_id' => $orderId]);
            $result[$direction] = $row ? [
                'entity_id' => (int) $row['entity_id'],
                'increment_id' => (string) $row['increment_id']
            ] : null;
        }
        return $result;
    }
}
