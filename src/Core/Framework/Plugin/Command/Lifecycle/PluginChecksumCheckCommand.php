<?php declare(strict_types=1);

namespace Shopware\Core\Framework\Plugin\Command\Lifecycle;

use Shopware\Core\Framework\Adapter\Console\ShopwareStyle;
use Shopware\Core\Framework\Context;
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
    public function __construct(
        private readonly EntityRepository $pluginRepo,
        private readonly PluginFileHashService $pluginFileHasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('plugin', InputArgument::REQUIRED, 'Plugin name to check');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new ShopwareStyle($input, $output);
        $context = Context::createCLIContext();

        $pluginName = $input->getArgument('plugin');

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('name', $pluginName));
        $plugin = $this->pluginRepo->search($criteria, $context)->first();

        if (!$plugin instanceof PluginEntity) {
            $io->error(\sprintf('Plugin "%s" not found', $pluginName));

            return self::FAILURE;
        }

        $checksumFilePath = $this->pluginFileHasher->getChecksumFilePathForPlugin($plugin);
        if (!is_file($checksumFilePath)) {
            $io->error(\sprintf('Plugin "%s" checksum file for not found', $pluginName));

            return self::FAILURE;
        }

        $checksumFileContent = (string) file_get_contents($checksumFilePath);

        try {
            $checksumFileData = json_decode($checksumFileContent, true, 512, \JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            $io->error(\sprintf('Checksum file for plugin "%s" is not valid json', $pluginName));

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
            $io->warning('New files detected:');
            $io->listing(array_keys($detectedChanges['new']));
        }
        if (!empty($detectedChanges['missing'])) {
            $io->warning('Missing files detected:');
            $io->listing(array_keys($detectedChanges['missing']));
        }
        if (!empty($detectedChanges['changed'])) {
            $io->warning('Edited files detected:');
            $io->listing(array_keys($detectedChanges['changed']));
        }

        return self::SUCCESS;
    }
}
