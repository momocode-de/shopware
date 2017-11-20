<?php declare(strict_types=1);

namespace Shopware\Category\Event\Category;

use Shopware\Category\Struct\CategorySearchResult;
use Shopware\Context\Struct\TranslationContext;
use Shopware\Framework\Event\NestedEvent;

class CategorySearchResultLoadedEvent extends NestedEvent
{
    const NAME = 'category.search.result.loaded';

    /**
     * @var CategorySearchResult
     */
    protected $result;

    public function __construct(CategorySearchResult $result)
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
}
