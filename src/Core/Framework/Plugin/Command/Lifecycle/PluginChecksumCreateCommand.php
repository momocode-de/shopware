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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'plugin:checksum:create',
    description: 'Creates a list of files and their checksum for a plugin',
)]
#[Package('core')]
class PluginChecksumCreateCommand extends Command
{
    public function __construct(
        private readonly EntityRepository $pluginRepo,
        private readonly PluginFileHashService $pluginFileHasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('plugin', InputArgument::REQUIRED, 'Plugin to create a checksum list for');
        $this->addOption('file-extensions', null, InputOption::VALUE_OPTIONAL, 'Comma-separated list of file extensions to include in the checksum', 'php');
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

        $extensions = $this->pluginFileHasher->getExtensions($input);
        $hashes = $this->pluginFileHasher->getHashes($plugin, $extensions);

        $checksumData = [
            'pluginVersion' => $plugin->getVersion(),
            'extensions' => $extensions,
            'hashes' => $hashes,
        ];

        file_put_contents(
            $this->pluginFileHasher->getChecksumFilePathForPlugin($plugin),
            \json_encode($checksumData, \JSON_THROW_ON_ERROR)
        );

        return self::SUCCESS;
    }
}
