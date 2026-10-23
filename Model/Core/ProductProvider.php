<?php

declare(strict_types=1);

namespace Stoneholl\OrderNavigation\Model\Core;

use Stoneholl\Core\Api\ProductProviderInterface;
use Stoneholl\Core\Model\Data\ProductDescriptor;

class ProductProvider implements ProductProviderInterface
{
    public function getProducts(): array
    {
        return [new ProductDescriptor(
            'order-navigation',
            'Stoneholl Previous / Next Order Navigation',
            ['Stoneholl_OrderNavigation'],
            ['stoneholl/magento2-order-navigation']
        )];
    }
}
