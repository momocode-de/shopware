<?php declare(strict_types=1);

namespace Shopware\Framework\Event;

class ArticlesTranslationsWrittenEvent extends NestedEvent
{
    const NAME = 'articles_translations.written';

    /**
     * @var string[]
     */
    private $articlesTranslationsUuids;

    /**
     * @var NestedEventCollection
     */
    private $events;

    /**
     * @var array
     */
    private $errors;

    public function __construct(array $articlesTranslationsUuids, array $errors = [])
    {
        $this->articlesTranslationsUuids = $articlesTranslationsUuids;
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
    public function getArticlesTranslationsUuids(): array
    {
        return $this->articlesTranslationsUuids;
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
