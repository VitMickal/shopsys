<?php

namespace Shopsys\ShopBundle\Model\Order\Item;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemFactory as BaseOrderItemFactory;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Pricing\Price;

class OrderItemFactory extends BaseOrderItemFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(EntityNameResolver $entityNameResolver)
    {
        parent::__construct($entityNameResolver);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @param string $name
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $price
     * @param string $vatPercent
     * @param int $quantity
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem
     */
    public function createTip(
        Order $order,
        string $name,
        Price $price,
        string $vatPercent,
        int $quantity
    ): OrderItem {
        $classData = $this->entityNameResolver->resolve(OrderItem::class);

        $orderTip = new $classData(
            $order,
            $name,
            $price,
            $vatPercent,
            $quantity,
            OrderItem::TYPE_TIP,
            null,
            null
        );

        return $orderTip;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem|\Shopsys\ShopBundle\Model\Order\Item\OrderItem
     */
    public function createDefaultTip(Order $order): OrderItem
    {
        $price = new Price(10, 10);

        return $this->createTip(
            $order,
            t('Tip'),
            $price,
            '0',
            1
        );
    }
}
