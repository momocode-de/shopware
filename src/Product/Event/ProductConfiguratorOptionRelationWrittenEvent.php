<?php declare(strict_types=1);

namespace Shopware\Product\Event;

use Shopware\Framework\Event\NestedEvent;
use Shopware\Framework\Event\NestedEventCollection;

class ProductConfiguratorOptionRelationWrittenEvent extends NestedEvent
{
    const NAME = 'product_configurator_option_relation.written';

    /**
     * @var string[]
     */
    private $productConfiguratorOptionRelationUuids;

    /**
     * @var NestedEventCollection
     */
    private $events;

    /**
     * @var array
     */
    private $errors;

    public function __construct(array $productConfiguratorOptionRelationUuids, array $errors = [])
    {
        $this->productConfiguratorOptionRelationUuids = $productConfiguratorOptionRelationUuids;
        $this->events = new NestedEventCollection();
        $this->errors = $errors;
    }

    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * @return string[]
     */
    public function getProductConfiguratorOptionRelationUuids(): array
    {
        return $this->productConfiguratorOptionRelationUuids;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function hasErrors(): bool
    {
        return count($this->errors) > 0;
    }

    public function addEvent(NestedEvent $event): void
    {
        $this->events->add($event);
    }

    public function getEvents(): NestedEventCollection
    {
        return $this->events;
    }
}
