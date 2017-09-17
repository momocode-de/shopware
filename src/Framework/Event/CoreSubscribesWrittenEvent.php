<?php declare(strict_types=1);

namespace Shopware\Framework\Event;

class CoreSubscribesWrittenEvent extends NestedEvent
{
    const NAME = 'core_subscribes.written';

    /**
     * @var string[]
     */
    private $coreSubscribesUuids;

    /**
     * @var NestedEventCollection
     */
    private $events;

    /**
     * @var array
     */
    private $errors;

    public function __construct(array $coreSubscribesUuids, array $errors = [])
    {
        $this->coreSubscribesUuids = $coreSubscribesUuids;
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
    public function getCoreSubscribesUuids(): array
    {
        return $this->coreSubscribesUuids;
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
