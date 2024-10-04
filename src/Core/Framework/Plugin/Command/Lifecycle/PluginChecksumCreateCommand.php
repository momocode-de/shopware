<?php declare(strict_types=1);

namespace Shopware\Core\Framework\Plugin\Command\Lifecycle;

use Shopware\Core\Framework\Adapter\Console\ShopwareStyle;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Plugin\PluginEntity;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'plugin:checksum:create',
    description: 'Creates a list of files and their checksum for a plugin',
)]
#[Package('core')]
class PluginChecksumCreateCommand extends Command
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
        $this->addArgument('plugin', InputArgument::REQUIRED, 'Plugin to create a checksum list for');
        $this->addOption('file-extensions', null, InputOption::VALUE_OPTIONAL, 'Comma-separated list of file extensions to include in the checksum', 'php,twig');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new ShopwareStyle($input, $output);
        $context = Context::createCLIContext();

        $plugin = $this->getPlugin($input, $context);

        if (!$plugin instanceof PluginEntity) {
            $io->error(\sprintf('Plugin "%s" not found', $input->getArgument('plugin')));

            return self::FAILURE;
        }

        $extensions = $this->pluginFileHasher->getExtensions($input);
        if ($extensions === []) {
            $io->error('No valid file extensions provided');

            return self::FAILURE;
        }

        $checksumFilePath = $this->pluginFileHasher->getChecksumFilePathForPlugin($plugin);
        if (!$checksumFilePath) {
            $io->error(\sprintf('Plugin "%s" checksum file path could not be identified', $input->getArgument('plugin')));

            return self::FAILURE;
        }

        $checksumData = $this->pluginFileHasher->getChecksumData($plugin, $extensions);

        $io->info(\sprintf('Writing checksum for %s file(s) of plugin "%s" to %s', \count($checksumData['hashes']), $plugin->getName(), $checksumFilePath));

        file_put_contents($checksumFilePath, \json_encode($checksumData, \JSON_THROW_ON_ERROR));

        return self::SUCCESS;
    }

    private function getPlugin(InputInterface $input, Context $context): ?Entity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('name', $input->getArgument('plugin')));

        return $this->pluginRepo->search($criteria, $context)->first();
    }
}
