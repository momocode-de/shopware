<?php declare(strict_types=1);

namespace Shopware\Schema\Event\SchemaVersion;

use Shopware\Api\Search\AggregationResult;
use Shopware\Context\Struct\TranslationContext;
use Shopware\Framework\Event\NestedEvent;

class SchemaVersionAggregationResultLoadedEvent extends NestedEvent
{
    const NAME = 'schema_version.aggregation.result.loaded';

    /**
     * @var AggregationResult
     */
    protected $result;

    public function __construct(AggregationResult $result)
    {
        $this->result = $result;
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function getContext(): TranslationContext
    {
        return $this->result->getContext();
    }

    public function getResult(): AggregationResult
    {
        return $this->result;
    }
}
