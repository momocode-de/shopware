<?php declare(strict_types=1);

namespace Shopware\Framework\Event;

class CampaignsHtmlWrittenEvent extends NestedEvent
{
    const NAME = 'campaigns_html.written';

    /**
     * @var string[]
     */
    private $campaignsHtmlUuids;

    /**
     * @var NestedEventCollection
     */
    private $events;

    /**
     * @var array
     */
    private $errors;

    public function __construct(array $campaignsHtmlUuids, array $errors = [])
    {
        $this->campaignsHtmlUuids = $campaignsHtmlUuids;
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
    public function getCampaignsHtmlUuids(): array
    {
        return $this->campaignsHtmlUuids;
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
