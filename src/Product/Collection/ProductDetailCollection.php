<?php declare(strict_types=1);

namespace Shopware\Product\Collection;

use Shopware\Category\Collection\CategoryBasicCollection;
use Shopware\Product\Struct\ProductDetailStruct;

class ProductDetailCollection extends ProductBasicCollection
{
    /**
     * @var ProductDetailStruct[]
     */
    protected $elements = [];

    public function getMediaUuids(): array
    {
        $uuids = [];
        foreach ($this->elements as $element) {
            foreach ($element->getMedia()->getUuids() as $uuid) {
                $uuids[] = $uuid;
            }
        }

        return $uuids;
    }

    public function getMedia(): ProductMediaBasicCollection
    {
        $collection = new ProductMediaBasicCollection();
        foreach ($this->elements as $element) {
            $collection->fill($element->getMedia()->getElements());
        }

        return $collection;
    }

    public function getTranslationUuids(): array
    {
        $uuids = [];
        foreach ($this->elements as $element) {
            foreach ($element->getTranslations()->getUuids() as $uuid) {
                $uuids[] = $uuid;
            }
        }

        return $uuids;
    }

    public function getTranslations(): ProductTranslationBasicCollection
    {
        $collection = new ProductTranslationBasicCollection();
        foreach ($this->elements as $element) {
            $collection->fill($element->getTranslations()->getElements());
        }

        return $collection;
    }

    public function getAllCategoryUuids(): array
    {
        $uuids = [];
        foreach ($this->elements as $element) {
            foreach ($element->getCategoryUuids() as $uuid) {
                $uuids[] = $uuid;
            }
        }

        return $uuids;
    }

    public function getAllCategories(): CategoryBasicCollection
    {
        $collection = new CategoryBasicCollection();
        foreach ($this->elements as $element) {
            $collection->fill($element->getCategories()->getElements());
        }

        return $collection;
    }

    public function getAllCategoryTreeUuids(): array
    {
        $uuids = [];
        foreach ($this->elements as $element) {
            foreach ($element->getCategoryTreeUuids() as $uuid) {
                $uuids[] = $uuid;
            }
        }

        return $uuids;
    }

    public function getAllCategoryTree(): CategoryBasicCollection
    {
        $collection = new CategoryBasicCollection();
        foreach ($this->elements as $element) {
            $collection->fill($element->getCategoryTree()->getElements());
        }

        return $collection;
    }

    public function getAllSeoCategoryUuids(): array
    {
        $uuids = [];
        foreach ($this->elements as $element) {
            foreach ($element->getSeoCategoryUuids() as $uuid) {
                $uuids[] = $uuid;
            }
        }

        return $uuids;
    }

    public function getAllSeoCategories(): CategoryBasicCollection
    {
        $collection = new CategoryBasicCollection();
        foreach ($this->elements as $element) {
            $collection->fill($element->getSeoCategories()->getElements());
        }

        return $collection;
    }

    public function getAllTabUuids(): array
    {
        $uuids = [];
        foreach ($this->elements as $element) {
            foreach ($element->getTabUuids() as $uuid) {
                $uuids[] = $uuid;
            }
        }

        return $uuids;
    }

    public function getAllTabs(): ProductStreamBasicCollection
    {
        $collection = new ProductStreamBasicCollection();
        foreach ($this->elements as $element) {
            $collection->fill($element->getTabs()->getElements());
        }

        return $collection;
    }

    public function getAllStreamUuids(): array
    {
        $uuids = [];
        foreach ($this->elements as $element) {
            foreach ($element->getStreamUuids() as $uuid) {
                $uuids[] = $uuid;
            }
        }

        return $uuids;
    }

    public function getAllStreams(): ProductStreamBasicCollection
    {
        $collection = new ProductStreamBasicCollection();
        foreach ($this->elements as $element) {
            $collection->fill($element->getStreams()->getElements());
        }

        return $collection;
    }

    protected function getExpectedClass(): string
    {
        return ProductDetailStruct::class;
    }
}
