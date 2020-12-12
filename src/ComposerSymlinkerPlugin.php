<?php

namespace ThisIsRuddy\ComposerSymlinker;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\ScriptEvents;
use Composer\Util\Filesystem;
use Composer\Util\Platform;
use Composer\Util\ProcessExecutor;

/**
 * Class Plugin
 *
 * @author  Dan Watts <thisisruddy@gmail.com>
 *
 * @package ThisIsRuddy\ComposerSymlinker
 */
class ComposerSymlinkerPlugin implements PluginInterface, EventSubscriberInterface
{
    /**
     * Composer.json extra field name
     */
    const PACKAGE_FIELD = 'symlink-mappings';

    /**
     * @var Composer $composer
     */
    protected $composer;

    /**
     * @var IOInterface $io
     */
    protected $io;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var SymlinkMapping[]
     */
    protected $symlinkMappings = [];

    /**
     * @var string
     */
    protected $mageDir = '';

    /**
     * @var ProcessExecutor
     */
    protected $processExecutor;

    /**
     * @param Composer $composer
     * @param IOInterface $io
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
        $this->filesystem = new Filesystem();
        $this->symlinkMappings = $this->getSymlinkMappingsFromExtra();
    }

    /**
     * @return SymlinkMapping[]
     */
    public function getSymlinkMappingsFromExtra()
    {
        $packageExtra = $this->composer->getPackage()->getExtra();

        if (!array_key_exists(static::PACKAGE_FIELD, $packageExtra)) {
            $this->io->write(sprintf("<info>No %s defined</info>", static::PACKAGE_FIELD));
            return [];
        }

        /**
         * @var SymlinkMapping[]
         */
        $mappings = [];
        foreach ($packageExtra[static::PACKAGE_FIELD] as $mapping) {
            try {
                $obj = new SymlinkMapping($mapping);
                $mappings[] = $obj;
            } catch (\Exception $e) {
                $this->io->write(sprintf("<error>%s<error>", $e->getMessage()));
            }
        }

        return $mappings;
    }

    /**
     * @return ProcessExecutor
     */
    protected function getProcess()
    {
        if (!$this->processExecutor)
            $this->processExecutor = new ProcessExecutor();

        return $this->processExecutor;
    }

    /**
     * @param string $source
     * @param string $dest
     * @param bool $isDir
     * @return bool
     */
    private function createWindowsLink(string $source, string $dest, bool $isDir = true): bool
    {
        $destDir = dirname($dest);
        if (empty(realpath($destDir)))
            mkdir($destDir);

        $cmd = sprintf(
            'mklink %s %s %s',
            $isDir ? '/J' : '/H',
            ProcessExecutor::escape($dest),
            ProcessExecutor::escape($source)
        );
        return $this->getProcess()->execute($cmd, $output) === 0;
    }

    /**
     * @param string $source
     * @param string $dest
     * @return boolean
     */
    private function createLink(string $source, string $dest): bool
    {
        if (Platform::isWindows())
            return $this->createWindowsLink($source, $dest, is_dir($source));
        else
            return $this->filesystem->relativeSymlink($source, $dest);
    }

    public function execute(): void
    {
        if (empty($this->symlinkMappings))
            return;

        /**
         * @var SymlinkMapping
         */
        foreach ($this->symlinkMappings as $mapping) {
            $srcPath = $mapping->getSourcePath();
            $destPath = $mapping->getDestinationPath();

            if (empty(realpath($srcPath))) {
                $this->io->write(sprintf("<error>Link source does not exist: '%s'<error>", $srcPath));
                continue;
            }

            if (!empty(realpath($destPath))) {
                $this->io->write(sprintf("<info>Link destination already exists: '%s', skipped.<info>", $destPath));
                continue;
            }

            if (!$result = $this->createLink($srcPath, $destPath))
                $this->io->write(sprintf("<error>Failed to create link for: '%s' -> '%s'<error>", $srcPath, $destPath));
            else
                $this->io->write(sprintf("<info>Link created for: '%s' -> '%s'<info>", $mapping->getSource(), $mapping->getDestination()));
        }
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ScriptEvents::POST_INSTALL_CMD => [
                ['execute', 0]
            ],
            ScriptEvents::POST_UPDATE_CMD => [
                ['execute', 0]
            ]
        ];
    }

    public function deactivate(Composer $composer, IOInterface $io)
    {
    }

    public function uninstall(Composer $composer, IOInterface $io)
    {
    }
}
