<?php

declare(strict_types=1);

namespace Shopsys\ShopBundle\Model\Order\Item;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Order\Item\Exception\WrongItemTypeException;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem as BaseOrderItem;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData as BaseOrderItemData;
use Shopsys\FrameworkBundle\Model\Order\Order as BaseOrder;
use Shopsys\FrameworkBundle\Model\Pricing\Price;

/**
 * @ORM\Table(name="order_items")
 * @ORM\Entity
 */
class OrderItem extends BaseOrderItem
{
    public const TYPE_TIP = 'tip';

    /**
     * @var bool|null
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $someBoolean;

    /**
     * @param \Shopsys\ShopBundle\Model\Order\Order $order
     * @param string $name
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $price
     * @param string $vatPercent
     * @param int $quantity
     * @param string $type
     * @param null|string $unitName
     * @param null|string $catnum
     */
    public function __construct(
        BaseOrder $order,
        string $name,
        Price $price,
        string $vatPercent,
        int $quantity,
        string $type,
        ?string $unitName,
        ?string $catnum
    ) {
        parent::__construct(
            $order,
            $name,
            $price,
            $vatPercent,
            $quantity,
            $type,
            $unitName,
            $catnum
        );
    }

    /**
     * @param \Shopsys\ShopBundle\Model\Order\Item\OrderItemData $orderItemData
     */
    public function edit(BaseOrderItemData $orderItemData)
    {
        parent::edit($orderItemData);

        if ($this->isTypeTip()) {
            $this->someBoolean = $orderItemData->someBoolean;
        }
    }

    /**
     * @return bool
     */
    public function isTypeTip(): bool
    {
        return $this->type === self::TYPE_TIP;
    }

    protected function checkTypeTip(): void
    {
        if (!$this->isTypeTip()) {
            throw new WrongItemTypeException(self::TYPE_TIP, $this->type);
        }
    }

    /**
     * @return bool|null
     */
    public function isSomeBoolean(): ?bool
    {
        return $this->someBoolean;
    }

    /**
     * @param bool $someBoolean
     */
    public function setSomeBoolean(bool $someBoolean): void
    {
        $this->someBoolean = $someBoolean;
    }
}
