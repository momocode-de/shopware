<?php declare(strict_types=1);

namespace Shopware\Core\Framework\Plugin\Command\Lifecycle;

use Shopware\Core\Framework\Adapter\Console\ShopwareStyle;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Plugin\PluginEntity;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'plugin:checksum:check',
    description: 'Check the integrity of the plugin files',
)]
#[Package('core')]
class PluginChecksumCheckCommand extends Command
{
    /**
     * @internal
     */
    public function __construct(
        private readonly EntityRepository $pluginRepo,
        private readonly PluginFileHashService $pluginFileHasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('plugin', InputArgument::OPTIONAL, 'Plugin name to check');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new ShopwareStyle($input, $output);
        $context = Context::createCLIContext();

        if ($input->getArgument('plugin') === null) {
            $io->info('Checking all plugins');
            $plugins = $this->pluginRepo->search(new Criteria(), $context)->getEntities();
        } else {
            $plugin = $this->getPlugin($input, $context);
            $plugins = new EntityCollection();
            $plugins->add($plugin);
        }

        $success = true;
        /** @var PluginEntity $plugin */
        foreach ($plugins as $plugin) {
            if (!$plugin instanceof PluginEntity) {
                $io->error(\sprintf('Plugin "%s" not found', $input->getArgument('plugin')));

                return self::FAILURE;
            }

            $io->info('Checking plugin: ' . $plugin->getName());

            $checksumFilePath = $this->pluginFileHasher->getChecksumFilePathForPlugin($plugin);
            if (!is_file($checksumFilePath)) {
                $io->info(\sprintf('Plugin "%s" checksum file for not found', $plugin->getName()));

                continue;
            }

            $checksumFileContent = (string) file_get_contents($checksumFilePath);

            try {
                $checksumFileData = json_decode($checksumFileContent, true, 512, \JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                $io->error(\sprintf('Checksum file for plugin "%s" is not valid json', $plugin->getName()));

                return self::FAILURE;
            }

            $extensions = $checksumFileData['extensions'];
            $checksumPluginVersion = $checksumFileData['pluginVersion'];

            if ($plugin->getVersion() !== $checksumPluginVersion) {
                $io->error(\sprintf(
                    'Plugin version is %s but checksum file is for version %s',
                    $plugin->getVersion(),
                    $checksumPluginVersion
                ));

                return self::FAILURE;
            }

            $currentlyHashedFiles = $this->pluginFileHasher->getHashes($plugin, $extensions);
            $previouslyHashedFiles = $checksumFileData['hashes'];

            $detectedChanges = $this->pluginFileHasher->compareChecksum($previouslyHashedFiles, $currentlyHashedFiles);

            if (!empty($detectedChanges['new'])) {
                $success = false;
                $io->warning('New files detected:');
                $io->listing(array_keys($detectedChanges['new']));
            }
            if (!empty($detectedChanges['missing'])) {
                $success = false;
                $io->warning('Missing files detected:');
                $io->listing(array_keys($detectedChanges['missing']));
            }
            if (!empty($detectedChanges['changed'])) {
                $success = false;
                $io->warning('Edited files detected:');
                $io->listing(array_keys($detectedChanges['changed']));
            }

            if ($detectedChanges['new'] === [] && $detectedChanges['missing'] === [] && $detectedChanges['changed'] === []) {
                $io->success(\sprintf('Plugin "%s" are unchanged.', $plugin->getName()));
            } else {
                $io->error(\sprintf('Plugin "%s" has changed code.', $plugin->getName()));
            }
        }

        return $success ? self::SUCCESS : self::FAILURE;
    }

    private function getPlugin(InputInterface $input, Context $context): ?Entity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('name', $input->getArgument('plugin')));

        return $this->pluginRepo->search($criteria, $context)->first();
    }
}
