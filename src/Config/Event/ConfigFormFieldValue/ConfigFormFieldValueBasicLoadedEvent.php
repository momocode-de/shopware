<?php declare(strict_types=1);

namespace Shopware\Config\Event\ConfigFormFieldValue;

use Shopware\Config\Collection\ConfigFormFieldValueBasicCollection;
use Shopware\Context\Struct\TranslationContext;
use Shopware\Framework\Event\NestedEvent;

class ConfigFormFieldValueBasicLoadedEvent extends NestedEvent
{
    const NAME = 'config_form_field_value.basic.loaded';

    /**
     * @var TranslationContext
     */
    protected $context;

    /**
     * @var ConfigFormFieldValueBasicCollection
     */
    protected $configFormFieldValues;

    public function __construct(ConfigFormFieldValueBasicCollection $configFormFieldValues, TranslationContext $context)
    {
        $this->context = $context;
        $this->configFormFieldValues = $configFormFieldValues;
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function getContext(): TranslationContext
    {
        return $this->context;
    }

    public function getConfigFormFieldValues(): ConfigFormFieldValueBasicCollection
    {
        return $this->configFormFieldValues;
    }
}
