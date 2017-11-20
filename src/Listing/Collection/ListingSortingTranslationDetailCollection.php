<?php declare(strict_types=1);

namespace Shopware\Listing\Collection;

use Shopware\Listing\Struct\ListingSortingTranslationDetailStruct;
use Shopware\Shop\Collection\ShopBasicCollection;

class ListingSortingTranslationDetailCollection extends ListingSortingTranslationBasicCollection
{
    /**
     * @var ListingSortingTranslationDetailStruct[]
     */
    protected $elements = [];

    public function getListingSortings(): ListingSortingBasicCollection
    {
        return new ListingSortingBasicCollection(
            $this->fmap(function (ListingSortingTranslationDetailStruct $listingSortingTranslation) {
                return $listingSortingTranslation->getListingSorting();
            })
        );
    }

    public function getLanguages(): ShopBasicCollection
    {
        return new ShopBasicCollection(
            $this->fmap(function (ListingSortingTranslationDetailStruct $listingSortingTranslation) {
                return $listingSortingTranslation->getLanguage();
            })
        );
    }

    protected function getExpectedClass(): string
    {
        return ListingSortingTranslationDetailStruct::class;
    }
}
