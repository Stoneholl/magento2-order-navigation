<?php

declare(strict_types=1);

namespace Stoneholl\OrderNavigation\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Psr\Log\LoggerInterface;

/** Request-local presentation state. No order data is persisted or logged. */
class Navigation
{
    /** @var ScopeConfigInterface */
    private $config;
    /** @var AuthorizationInterface */
    private $authorization;
    /** @var StoreRepositoryInterface */
    private $stores;
    /** @var NeighborReader */
    private $reader;
    /** @var LoggerInterface */
    private $logger;
    /** @var array */
    private $states = [];

    public function __construct(
        ScopeConfigInterface $config,
        AuthorizationInterface $authorization,
        StoreRepositoryInterface $stores,
        NeighborReader $reader,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->authorization = $authorization;
        $this->stores = $stores;
        $this->reader = $reader;
        $this->logger = $logger;
    }

    public function isAllowed(): bool
    {
        return $this->config->isSetFlag('stoneholl_order_navigation/general/enabled')
            && $this->authorization->isAllowed('Magento_Sales::actions_view')
            && $this->authorization->isAllowed('Stoneholl_OrderNavigation::view');
    }

    /**
     * Return hidden, store_unavailable, unavailable or ready state for the native loaded order.
     *
     * @param OrderInterface $order
     * @return array
     */
    public function getState(OrderInterface $order): array
    {
        // Recheck permission even when a result has been memoized during this request.
        if (!$this->isAllowed()) {
            return ['status' => 'hidden'];
        }
        $orderId = (int) $order->getEntityId();
        $storeId = (int) $order->getStoreId();
        if ($storeId <= 0) {
            return ['status' => 'store_unavailable'];
        }
        if ($orderId <= 0) {
            return ['status' => 'unavailable'];
        }
        $key = $storeId . ':' . $orderId;
        if (isset($this->states[$key])) {
            return $this->states[$key];
        }
        try {
            $this->stores->getById($storeId);
        } catch (NoSuchEntityException $exception) {
            return $this->states[$key] = ['status' => 'store_unavailable'];
        } catch (\Exception $exception) {
            return $this->states[$key] = $this->unavailable();
        }
        try {
            return $this->states[$key] = array_merge(
                ['status' => 'ready'],
                $this->reader->getNeighbors($orderId, $storeId)
            );
        } catch (\Exception $exception) {
            return $this->states[$key] = $this->unavailable();
        }
    }

    private function unavailable(): array
    {
        // Exception text/trace can contain SQL parameters and customer data; never attach it.
        $this->logger->warning('STONEHOLL_ORDER_NAVIGATION_READ_FAILED');
        return ['status' => 'unavailable'];
    }
}
