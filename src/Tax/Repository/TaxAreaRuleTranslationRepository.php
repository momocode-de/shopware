<?php declare(strict_types=1);

namespace Shopware\Tax\Repository;

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
use Shopware\Tax\Collection\TaxAreaRuleTranslationBasicCollection;
use Shopware\Tax\Collection\TaxAreaRuleTranslationDetailCollection;
use Shopware\Tax\Definition\TaxAreaRuleTranslationDefinition;
use Shopware\Tax\Event\TaxAreaRuleTranslation\TaxAreaRuleTranslationAggregationResultLoadedEvent;
use Shopware\Tax\Event\TaxAreaRuleTranslation\TaxAreaRuleTranslationBasicLoadedEvent;
use Shopware\Tax\Event\TaxAreaRuleTranslation\TaxAreaRuleTranslationDetailLoadedEvent;
use Shopware\Tax\Event\TaxAreaRuleTranslation\TaxAreaRuleTranslationSearchResultLoadedEvent;
use Shopware\Tax\Event\TaxAreaRuleTranslation\TaxAreaRuleTranslationUuidSearchResultLoadedEvent;
use Shopware\Tax\Struct\TaxAreaRuleTranslationSearchResult;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class TaxAreaRuleTranslationRepository implements RepositoryInterface
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

    public function search(Criteria $criteria, TranslationContext $context): TaxAreaRuleTranslationSearchResult
    {
        $uuids = $this->searchUuids($criteria, $context);

        $entities = $this->readBasic($uuids->getUuids(), $context);

        $aggregations = null;
        if ($criteria->getAggregations()) {
            $aggregations = $this->aggregate($criteria, $context);
        }

        $result = TaxAreaRuleTranslationSearchResult::createFromResults($uuids, $entities, $aggregations);

        $event = new TaxAreaRuleTranslationSearchResultLoadedEvent($result);
        $this->eventDispatcher->dispatch($event->getName(), $event);

        return $result;
    }

    public function aggregate(Criteria $criteria, TranslationContext $context): AggregationResult
    {
        $result = $this->aggregator->aggregate(TaxAreaRuleTranslationDefinition::class, $criteria, $context);

        $event = new TaxAreaRuleTranslationAggregationResultLoadedEvent($result);
        $this->eventDispatcher->dispatch($event->getName(), $event);

        return $result;
    }

    public function searchUuids(Criteria $criteria, TranslationContext $context): UuidSearchResult
    {
        $result = $this->searcher->search(TaxAreaRuleTranslationDefinition::class, $criteria, $context);

        $event = new TaxAreaRuleTranslationUuidSearchResultLoadedEvent($result);
        $this->eventDispatcher->dispatch($event->getName(), $event);

        return $result;
    }

    public function readBasic(array $uuids, TranslationContext $context): TaxAreaRuleTranslationBasicCollection
    {
        /** @var TaxAreaRuleTranslationBasicCollection $entities */
        $entities = $this->reader->readBasic(TaxAreaRuleTranslationDefinition::class, $uuids, $context);

        $event = new TaxAreaRuleTranslationBasicLoadedEvent($entities, $context);
        $this->eventDispatcher->dispatch($event->getName(), $event);

        return $entities;
    }

    public function readDetail(array $uuids, TranslationContext $context): TaxAreaRuleTranslationDetailCollection
    {
        /** @var TaxAreaRuleTranslationDetailCollection $entities */
        $entities = $this->reader->readDetail(TaxAreaRuleTranslationDefinition::class, $uuids, $context);

        $event = new TaxAreaRuleTranslationDetailLoadedEvent($entities, $context);
        $this->eventDispatcher->dispatch($event->getName(), $event);

        return $entities;
    }

    public function update(array $data, TranslationContext $context): GenericWrittenEvent
    {
        $affected = $this->writer->update(TaxAreaRuleTranslationDefinition::class, $data, WriteContext::createFromTranslationContext($context));
        $event = GenericWrittenEvent::createFromWriterResult($affected, $context, []);
        $this->eventDispatcher->dispatch(GenericWrittenEvent::NAME, $event);

        return $event;
    }

    public function upsert(array $data, TranslationContext $context): GenericWrittenEvent
    {
        $affected = $this->writer->upsert(TaxAreaRuleTranslationDefinition::class, $data, WriteContext::createFromTranslationContext($context));
        $event = GenericWrittenEvent::createFromWriterResult($affected, $context, []);
        $this->eventDispatcher->dispatch(GenericWrittenEvent::NAME, $event);

        return $event;
    }

    public function create(array $data, TranslationContext $context): GenericWrittenEvent
    {
        $affected = $this->writer->insert(TaxAreaRuleTranslationDefinition::class, $data, WriteContext::createFromTranslationContext($context));
        $event = GenericWrittenEvent::createFromWriterResult($affected, $context, []);
        $this->eventDispatcher->dispatch(GenericWrittenEvent::NAME, $event);

        return $event;
    }
}
