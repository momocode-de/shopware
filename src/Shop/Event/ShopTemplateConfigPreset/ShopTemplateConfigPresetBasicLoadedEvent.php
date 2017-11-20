<?php declare(strict_types=1);

namespace Shopware\Shop\Event\ShopTemplateConfigPreset;

use Shopware\Context\Struct\TranslationContext;
use Shopware\Framework\Event\NestedEvent;
use Shopware\Shop\Collection\ShopTemplateConfigPresetBasicCollection;

class ShopTemplateConfigPresetBasicLoadedEvent extends NestedEvent
{
    const NAME = 'shop_template_config_preset.basic.loaded';

    /**
     * @var TranslationContext
     */
    protected $context;

    /**
     * @var ShopTemplateConfigPresetBasicCollection
     */
    protected $shopTemplateConfigPresets;

    public function __construct(ShopTemplateConfigPresetBasicCollection $shopTemplateConfigPresets, TranslationContext $context)
    {
        $this->context = $context;
        $this->shopTemplateConfigPresets = $shopTemplateConfigPresets;
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function getContext(): TranslationContext
    {
        return $this->context;
    }

    public function getShopTemplateConfigPresets(): ShopTemplateConfigPresetBasicCollection
    {
        return $this->shopTemplateConfigPresets;
    }
}
