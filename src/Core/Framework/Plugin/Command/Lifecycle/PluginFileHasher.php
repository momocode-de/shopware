<?php declare(strict_types=1);

namespace Shopware\Core\Framework\Plugin\Command\Lifecycle;

use Shopware\Core\Framework\Plugin\PluginEntity;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Finder\Finder;

class PluginFileHasher
{
    public function __construct(
        private readonly string           $rootDir,
    )
    {
    }

    public function getChecksumFilePathForPlugin(PluginEntity $plugin): string
    {
        return $this->rootDir . '/' . $plugin->getPath() . '/checksums.json';
    }

    public function getDirectories(PluginEntity $plugin): array
    {
        $directories = [];

        $autoload = $plugin->getAutoload();
        $psr4 = $autoload['psr-4'] ?? [];
        foreach ($psr4 as $path) {
            $directories[] = "{$this->rootDir}/{$plugin->getPath()}{$path}";
        }

        return array_unique(array_filter($directories));
    }


    /**
     * TODO: Validate the file extensions
     */
    public function getExtensions(InputInterface $input): array
    {
        $extensions = \explode(',', $input->getOption('file-extensions'));

        foreach ($extensions as $key => $extension) {
            $extensions[$key] = '*.' . $extension;
        }

        return $extensions;
    }

    public function getHashes(PluginEntity $plugin, InputInterface $input): array
    {
        $pluginPath = $plugin->getPath();
        $directories = $this->getDirectories($plugin);
        $extensions = $this->getExtensions($input);

        $finder = new Finder();
        $finder->in($directories)->files()->name($extensions);

        $hashes = [];
        foreach ($finder as $file) {
            $absoluteFilePath = $file->getRealPath();
            $relativePath = str_replace($this->rootDir . '/' . $pluginPath, '', $absoluteFilePath);

            $hashes[$relativePath] = \hash_file('sha256', $absoluteFilePath);
        }

        return $hashes;
    }
}
