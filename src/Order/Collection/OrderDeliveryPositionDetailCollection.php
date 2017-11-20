<?php declare(strict_types=1);

namespace Shopware\Order\Collection;

use Shopware\Order\Struct\OrderDeliveryPositionDetailStruct;

class OrderDeliveryPositionDetailCollection extends OrderDeliveryPositionBasicCollection
{
    /**
     * @var OrderDeliveryPositionDetailStruct[]
     */
    protected $elements = [];

    public function getOrderDeliveries(): OrderDeliveryBasicCollection
    {
        return new OrderDeliveryBasicCollection(
            $this->fmap(function (OrderDeliveryPositionDetailStruct $orderDeliveryPosition) {
                return $orderDeliveryPosition->getOrderDelivery();
            })
        );
    }

    protected function getExpectedClass(): string
    {
        return OrderDeliveryPositionDetailStruct::class;
    }
}
