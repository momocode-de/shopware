<?php declare(strict_types=1);

namespace Shopware\Framework\Event;

class CoreSessionsBackendWrittenEvent extends NestedEvent
{
    const NAME = 'core_sessions_backend.written';

    /**
     * @var string[]
     */
    private $coreSessionsBackendUuids;

    /**
     * @var NestedEventCollection
     */
    private $events;

    /**
     * @var array
     */
    private $errors;

    public function __construct(array $coreSessionsBackendUuids, array $errors = [])
    {
        $this->coreSessionsBackendUuids = $coreSessionsBackendUuids;
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
    public function getCoreSessionsBackendUuids(): array
    {
        return $this->coreSessionsBackendUuids;
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
