<?php

declare(strict_types=1);

namespace Stoneholl\OrderNavigation\Model\Config;

use Magento\Framework\App\Config\Value;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;

class Enabled extends Value
{
    public function beforeSave()
    {
        if ($this->getScope() !== ScopeConfigInterface::SCOPE_TYPE_DEFAULT || (int) $this->getScopeId() !== 0) {
            throw new LocalizedException(__('Order navigation can only be configured at Default scope.'));
        }
        if (!in_array($this->getValue(), [0, 1, '0', '1'], true)) {
            throw new LocalizedException(__('Select Yes or No for order navigation.'));
        }
        return parent::beforeSave();
    }
}
