<?php declare(strict_types=1);

namespace Shopware\Order\Repository;

use Shopware\Api\Read\EntityReaderInterface;
use Shopware\Api\RepositoryInterface;
use Shopware\Api\Search\AggregationResult;
use Shopware\Api\Search\Criteria;
use Shopware\Api\Search\EntityAggregatorInterface;
use Shopware\Api\Search\EntitySearcherInterface;
use Shopware\Api\Search\UuidSearchResult;
use Shopware\Api\Write\EntityWriterInterface;
use Shopware\Api\Write\GenericWrittenEvent;
use Shopware\Api\Write\WriteContext;
use Shopware\Context\Struct\TranslationContext;
use Shopware\Order\Collection\OrderStateTranslationBasicCollection;
use Shopware\Order\Collection\OrderStateTranslationDetailCollection;
use Shopware\Order\Definition\OrderStateTranslationDefinition;
use Shopware\Order\Event\OrderStateTranslation\OrderStateTranslationAggregationResultLoadedEvent;
use Shopware\Order\Event\OrderStateTranslation\OrderStateTranslationBasicLoadedEvent;
use Shopware\Order\Event\OrderStateTranslation\OrderStateTranslationDetailLoadedEvent;
use Shopware\Order\Event\OrderStateTranslation\OrderStateTranslationSearchResultLoadedEvent;
use Shopware\Order\Event\OrderStateTranslation\OrderStateTranslationUuidSearchResultLoadedEvent;
use Shopware\Order\Struct\OrderStateTranslationSearchResult;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class OrderStateTranslationRepository implements RepositoryInterface
{
    /**
     * @var EntityReaderInterface
     */
    private $reader;

    /**
     * @var EntityWriterInterface
     */
    private $writer;

    /**
     * @var EntitySearcherInterface
     */
    private $searcher;

    /**
     * @var EntityAggregatorInterface
     */
    private $aggregator;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(
        EntityReaderInterface $reader,
        EntityWriterInterface $writer,
        EntitySearcherInterface $searcher,
        EntityAggregatorInterface $aggregator,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->reader = $reader;
        $this->writer = $writer;
        $this->searcher = $searcher;
        $this->aggregator = $aggregator;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function search(Criteria $criteria, TranslationContext $context): OrderStateTranslationSearchResult
    {
        $uuids = $this->searchUuids($criteria, $context);

        $entities = $this->readBasic($uuids->getUuids(), $context);

        $aggregations = null;
        if ($criteria->getAggregations()) {
            $aggregations = $this->aggregate($criteria, $context);
        }

        $result = OrderStateTranslationSearchResult::createFromResults($uuids, $entities, $aggregations);

        $event = new OrderStateTranslationSearchResultLoadedEvent($result);
        $this->eventDispatcher->dispatch($event->getName(), $event);

        return $result;
    }

    public function aggregate(Criteria $criteria, TranslationContext $context): AggregationResult
    {
        $result = $this->aggregator->aggregate(OrderStateTranslationDefinition::class, $criteria, $context);

        $event = new OrderStateTranslationAggregationResultLoadedEvent($result);
        $this->eventDispatcher->dispatch($event->getName(), $event);

        return $result;
    }

    public function searchUuids(Criteria $criteria, TranslationContext $context): UuidSearchResult
    {
        $result = $this->searcher->search(OrderStateTranslationDefinition::class, $criteria, $context);

        $event = new OrderStateTranslationUuidSearchResultLoadedEvent($result);
        $this->eventDispatcher->dispatch($event->getName(), $event);

        return $result;
    }

    public function readBasic(array $uuids, TranslationContext $context): OrderStateTranslationBasicCollection
    {
        /** @var OrderStateTranslationBasicCollection $entities */
        $entities = $this->reader->readBasic(OrderStateTranslationDefinition::class, $uuids, $context);

        $event = new OrderStateTranslationBasicLoadedEvent($entities, $context);
        $this->eventDispatcher->dispatch($event->getName(), $event);

        return $entities;
    }

    public function readDetail(array $uuids, TranslationContext $context): OrderStateTranslationDetailCollection
    {
        /** @var OrderStateTranslationDetailCollection $entities */
        $entities = $this->reader->readDetail(OrderStateTranslationDefinition::class, $uuids, $context);

        $event = new OrderStateTranslationDetailLoadedEvent($entities, $context);
        $this->eventDispatcher->dispatch($event->getName(), $event);

        return $entities;
    }

    public function update(array $data, TranslationContext $context): GenericWrittenEvent
    {
        $affected = $this->writer->update(OrderStateTranslationDefinition::class, $data, WriteContext::createFromTranslationContext($context));
        $event = GenericWrittenEvent::createFromWriterResult($affected, $context, []);
        $this->eventDispatcher->dispatch(GenericWrittenEvent::NAME, $event);

        return $event;
    }

    public function upsert(array $data, TranslationContext $context): GenericWrittenEvent
    {
        $affected = $this->writer->upsert(OrderStateTranslationDefinition::class, $data, WriteContext::createFromTranslationContext($context));
        $event = GenericWrittenEvent::createFromWriterResult($affected, $context, []);
        $this->eventDispatcher->dispatch(GenericWrittenEvent::NAME, $event);

        return $event;
    }

    public function create(array $data, TranslationContext $context): GenericWrittenEvent
    {
        $affected = $this->writer->insert(OrderStateTranslationDefinition::class, $data, WriteContext::createFromTranslationContext($context));
        $event = GenericWrittenEvent::createFromWriterResult($affected, $context, []);
        $this->eventDispatcher->dispatch(GenericWrittenEvent::NAME, $event);

        return $event;
    }
}
