<?php declare(strict_types=1);

namespace Shopware\Core\Framework\Plugin\Command\Lifecycle;

use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Plugin\PluginEntity;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Finder\Finder;

#[Package('core')]
class PluginFileHashService
{
    public function __construct(
        private readonly string $rootDir,
    ) {
    }

    public function getChecksumFilePathForPlugin(PluginEntity $plugin): string
    {
        return $this->rootDir . '/' . $plugin->getPath() . '/checksums.json';
    }

    /**
     * @param array<string, string> $savedHashes
     * @param array<string, string> $currentHashes
     *
     * @return array{'new': array<string, string>, 'missing': array<string, string>, 'changed': array<string, string>}
     */
    public function compareChecksum(array $savedHashes, array $currentHashes): array
    {
        $newFiles = array_diff_key($currentHashes, $savedHashes);
        $missingFiles = array_diff_key($savedHashes, $currentHashes);
        $manipulatedFiles = array_diff(array_diff_key($savedHashes, $missingFiles, $newFiles), $currentHashes);

        return [
            'new' => $newFiles,
            'missing' => $missingFiles,
            'changed' => $manipulatedFiles,
        ];
    }

    /**
     * TODO: Validate the file extensions
     */
    /**
     * @return string[]
     */
    public function getExtensions(InputInterface $input): array
    {
        $extensions = \explode(',', $input->getOption('file-extensions'));

        foreach ($extensions as $key => $extension) {
            $extensions[$key] = '*.' . $extension;
        }

        return $extensions;
    }

    /**
     * @param string[] $extensions
     *
     * @return array<string, string>
     */
    public function getHashes(PluginEntity $plugin, array $extensions): array
    {
        $pluginPath = $plugin->getPath();
        if ($pluginPath === null) {
            // TODO: Throw an exception
            return [];
        }

        $directories = $this->getDirectories($plugin);

        $finder = new Finder();
        $finder->in($directories)->files()->name($extensions);

        $hashes = [];
        foreach ($finder as $file) {
            $absoluteFilePath = $file->getRealPath();
            if (!\is_string($absoluteFilePath) || !$absoluteFilePath) {
                // TODO: log or throw error
                continue;
            }

            $relativePath = (string) str_replace($this->rootDir . '/' . $pluginPath, '', $absoluteFilePath);

            $hash = \hash_file('sha256', $absoluteFilePath);
            if (!\is_string($hash) || !$hash) {
                // TODO: log or throw error
                continue;
            }

            $hashes[$relativePath] = $hash;
        }

        return $hashes;
    }

    /**
     * @return string[]
     */
    public function getDirectories(PluginEntity $plugin): array
    {
        $directories = [];

        $autoload = $plugin->getAutoload();
        $psr4 = $autoload['psr-4'] ?? [];
        foreach ($psr4 as $path) {
            if (\is_string($path) && $path !== '') {
                $directories[] = "{$this->rootDir}/{$plugin->getPath()}{$path}";
            }
        }

        return array_unique($directories);
    }
}
