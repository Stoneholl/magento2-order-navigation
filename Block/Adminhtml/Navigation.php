<?php

declare(strict_types=1);

namespace Stoneholl\OrderNavigation\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Block\Adminhtml\Order\View\Tab\Info;
use Stoneholl\OrderNavigation\Model\Navigation as NavigationModel;

class Navigation extends Template
{
    /** @var NavigationModel */
    private $navigation;

    public function __construct(Context $context, NavigationModel $navigation, array $data = [])
    {
        $this->navigation = $navigation;
        parent::__construct($context, $data);
    }

    public function getNavigationState(): array
    {
        if (!$this->navigation->isAllowed()) {
            return ['status' => 'hidden'];
        }
        // Reuse Magento's authorized order-view block; do not load a request-supplied ID or use Registry.
        $info = $this->getLayout()->getBlock('order_tab_info');
        if (!$info instanceof Info) {
            return ['status' => 'unavailable'];
        }
        try {
            $order = $info->getOrder();
        } catch (\Magento\Framework\Exception\LocalizedException $exception) {
            $this->_logger->warning('STONEHOLL_ORDER_NAVIGATION_CURRENT_ORDER_UNAVAILABLE');
            return ['status' => 'unavailable'];
        }
        return $order instanceof OrderInterface
            ? $this->navigation->getState($order)
            : ['status' => 'unavailable'];
    }

    public function getOutputEscaper(): \Magento\Framework\Escaper
    {
        return $this->_escaper;
    }

    public function getOrderUrl(int $orderId): string
    {
        return $this->getUrl('sales/order/view', ['order_id' => $orderId]);
    }
}
