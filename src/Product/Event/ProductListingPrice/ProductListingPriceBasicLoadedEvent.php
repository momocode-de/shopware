<?php declare(strict_types=1);

namespace Shopware\Product\Event\ProductListingPrice;

use Shopware\Context\Struct\TranslationContext;
use Shopware\Customer\Event\CustomerGroup\CustomerGroupBasicLoadedEvent;
use Shopware\Framework\Event\NestedEvent;
use Shopware\Framework\Event\NestedEventCollection;
use Shopware\Product\Collection\ProductListingPriceBasicCollection;

class ProductListingPriceBasicLoadedEvent extends NestedEvent
{
    const NAME = 'product_listing_price.basic.loaded';

    /**
     * @var TranslationContext
     */
    protected $context;

    /**
     * @var ProductListingPriceBasicCollection
     */
    protected $productListingPrices;

    public function __construct(ProductListingPriceBasicCollection $productListingPrices, TranslationContext $context)
    {
        $this->context = $context;
        $this->productListingPrices = $productListingPrices;
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function getContext(): TranslationContext
    {
        return $this->context;
    }

    public function getProductListingPrices(): ProductListingPriceBasicCollection
    {
        return $this->productListingPrices;
    }

    public function getEvents(): ?NestedEventCollection
    {
        $events = [];
        if ($this->productListingPrices->getCustomerGroups()->count() > 0) {
            $events[] = new CustomerGroupBasicLoadedEvent($this->productListingPrices->getCustomerGroups(), $this->context);
        }

        return new NestedEventCollection($events);
    }
}
