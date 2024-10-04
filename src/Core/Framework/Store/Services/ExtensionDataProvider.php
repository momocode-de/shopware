<?php declare(strict_types=1);

namespace Shopware\Core\Framework\Store\Services;

use Shopware\Core\Framework\App\AppCollection;
use Shopware\Core\Framework\App\AppEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Plugin\Command\Lifecycle\PluginFileHashService;
use Shopware\Core\Framework\Plugin\Exception\DecorationPatternException;
use Shopware\Core\Framework\Plugin\PluginCollection;
use Shopware\Core\Framework\Store\StoreException;
use Shopware\Core\Framework\Store\Struct\ExtensionCollection;
use Shopware\Core\Framework\Store\Struct\ExtensionStruct;

/**
 * @internal
 */
#[Package('checkout')]
class ExtensionDataProvider extends AbstractExtensionDataProvider
{
    final public const HEADER_NAME_TOTAL_COUNT = 'SW-Meta-Total';

    public function __construct(
        private readonly ExtensionLoader $extensionLoader,
        private readonly EntityRepository $appRepository,
        private readonly EntityRepository $pluginRepository,
        private readonly ExtensionListingLoader $extensionListingLoader,
        private readonly PluginFileHashService $pluginFileHashService,
    ) {
    }

    public function getInstalledExtensions(Context $context, bool $loadCloudExtensions = true, ?Criteria $searchCriteria = null): ExtensionCollection
    {
        $criteria = $searchCriteria ?: new Criteria();
        $criteria->addAssociation('translations');

        /** @var AppCollection $installedApps */
        $installedApps = $this->appRepository->search($criteria, $context)->getEntities();

        /** @var PluginCollection $installedPlugins */
        $installedPlugins = $this->pluginRepository->search($criteria, $context)->getEntities();
        $pluginCollection = $this->extensionLoader->loadFromPluginCollection($context, $installedPlugins);

        $localExtensions = $this->extensionLoader->loadFromAppCollection($context, $installedApps)->merge($pluginCollection);


        /** @var ExtensionStruct $extension */
        foreach ($localExtensions as $extension) {
            $criteria = new Criteria();
            $criteria->addFilter(new EqualsFilter('name', $extension->getName()));
            $plugin = $this->pluginRepository->search($criteria, $context)->first();

            $checksumFilePath = $this->pluginFileHashService->getChecksumFilePathForPlugin($plugin);
            if (!is_file($checksumFilePath)) {
                continue;
            }

            $checksumFileContent = (string) file_get_contents($checksumFilePath);
            $checksumFileData = json_decode($checksumFileContent, true, 512, \JSON_THROW_ON_ERROR);
            $extensionss = $checksumFileData['extensions'];
            $currentlyHashedFiles = $this->pluginFileHashService->getHashes($plugin, $extensionss);
            $previouslyHashedFiles = $checksumFileData['hashes'];
            $detectedChanges = $this->pluginFileHashService->compareChecksum($previouslyHashedFiles, $currentlyHashedFiles);

            $extension->setNew(array_keys($detectedChanges['new']));
            $extension->setMissing(array_keys($detectedChanges['missing']));
            $extension->setChanged(array_keys($detectedChanges['changed']));
        }

        if ($loadCloudExtensions) {
            return $this->extensionListingLoader->load($localExtensions, $context);
        }

        return $localExtensions;
    }

    public function getAppEntityFromTechnicalName(string $technicalName, Context $context): AppEntity
    {
        $criteria = (new Criteria())->addFilter(new EqualsFilter('name', $technicalName));
        $app = $this->appRepository->search($criteria, $context)->getEntities()->first();

        if (!$app instanceof AppEntity) {
            throw StoreException::extensionNotFoundFromTechnicalName($technicalName);
        }

        return $app;
    }

    public function getAppEntityFromId(string $id, Context $context): AppEntity
    {
        $criteria = new Criteria([$id]);
        $app = $this->appRepository->search($criteria, $context)->getEntities()->first();

        if (!$app instanceof AppEntity) {
            throw StoreException::extensionNotFoundFromId($id);
        }

        return $app;
    }

    protected function getDecorated(): AbstractExtensionDataProvider
    {
        throw new DecorationPatternException(self::class);
    }
}
