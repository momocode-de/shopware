<?php declare(strict_types=1);

namespace Shopware\Api\Read;

use Shopware\Api\Entity\EntityCollection;
use Shopware\Context\Struct\TranslationContext;

interface EntityReaderInterface
{
    public function readDetail(string $definition, array $uuids, TranslationContext $context): EntityCollection;

    public function readBasic(string $definition, array $uuids, TranslationContext $context): EntityCollection;
}
