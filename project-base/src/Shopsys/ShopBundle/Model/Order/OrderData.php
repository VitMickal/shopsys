<?php

namespace Shopsys\ShopBundle\Model\Order;

use Shopsys\FrameworkBundle\Model\Order\OrderData as BaseOrderData;

class OrderData extends BaseOrderData
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData|null
     */
    public $orderTip;

    public function __construct()
    {
        parent::__construct();
    }
}
