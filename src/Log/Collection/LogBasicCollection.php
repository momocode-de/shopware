<?php declare(strict_types=1);

namespace Shopware\Log\Collection;

use Shopware\Api\Entity\EntityCollection;
use Shopware\Log\Struct\LogBasicStruct;

class LogBasicCollection extends EntityCollection
{
    /**
     * @var LogBasicStruct[]
     */
    protected $elements = [];

    public function get(string $uuid): ? LogBasicStruct
    {
        return parent::get($uuid);
    }

    public function current(): LogBasicStruct
    {
        return parent::current();
    }

    protected function getExpectedClass(): string
    {
        return LogBasicStruct::class;
    }
}
