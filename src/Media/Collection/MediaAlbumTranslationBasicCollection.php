<?php declare(strict_types=1);

namespace Shopware\Media\Collection;

use Shopware\Api\Entity\EntityCollection;
use Shopware\Media\Struct\MediaAlbumTranslationBasicStruct;

class MediaAlbumTranslationBasicCollection extends EntityCollection
{
    /**
     * @var MediaAlbumTranslationBasicStruct[]
     */
    protected $elements = [];

    public function get(string $uuid): ? MediaAlbumTranslationBasicStruct
    {
        return parent::get($uuid);
    }

    public function current(): MediaAlbumTranslationBasicStruct
    {
        return parent::current();
    }

    public function getMediaAlbumUuids(): array
    {
        return $this->fmap(function (MediaAlbumTranslationBasicStruct $mediaAlbumTranslation) {
            return $mediaAlbumTranslation->getMediaAlbumUuid();
        });
    }

    public function filterByMediaAlbumUuid(string $uuid): MediaAlbumTranslationBasicCollection
    {
        return $this->filter(function (MediaAlbumTranslationBasicStruct $mediaAlbumTranslation) use ($uuid) {
            return $mediaAlbumTranslation->getMediaAlbumUuid() === $uuid;
        });
    }

    public function getLanguageUuids(): array
    {
        return $this->fmap(function (MediaAlbumTranslationBasicStruct $mediaAlbumTranslation) {
            return $mediaAlbumTranslation->getLanguageUuid();
        });
    }

    public function filterByLanguageUuid(string $uuid): MediaAlbumTranslationBasicCollection
    {
        return $this->filter(function (MediaAlbumTranslationBasicStruct $mediaAlbumTranslation) use ($uuid) {
            return $mediaAlbumTranslation->getLanguageUuid() === $uuid;
        });
    }

    protected function getExpectedClass(): string
    {
        return MediaAlbumTranslationBasicStruct::class;
    }
}
