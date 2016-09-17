<?php

namespace Hmaus\Drafter;

use Composer\Package\Package;
use Composer\Script\Event;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class Installer
{
    /**
     * Install Drafter
     *
     * @param Event $event
     */
    public static function installDrafter(Event $event)
    {
        $io = $event->getIO();
        $composer = $event->getComposer();
        $config = $composer->getConfig();

        $extra = $composer->getPackage()->getExtra();

        if (!isset($extra['drafter-installer-tag'])) {
            $io->writeError('  <error>Please configure drafter-installer in your composer.json:</error>');
            $io->writeError('
  "extra": {
      "drafter-installer-tag": "v3.1.1"
  }
            ');

            return;
        }

        $tag = $extra['drafter-installer-tag'];
        $binDir = $config->get('bin-dir');
        $targetDir = $config->get('vendor-dir') . '/apiaryio/drafter';
        $binSource = $binDir . '/drafter';
        $binTarget = $targetDir . '/bin/drafter';

        $fs = new Filesystem();
        if ($fs->exists($binTarget) && $fs->exists($binSource)) {
            $io->write('  - Installing <info>drafter</info> (<comment>'.$tag.'</comment>)');
            $io->write(sprintf('    Loading from cache', $targetDir));

            return;
        }

        $downloadManager = $composer->getDownloadManager();

        try {
            $package = self::createComposerInMemoryPackage($targetDir, $tag);
            $downloadManager->download($package, $targetDir);

            if (self::getMake() === null) {
                $io->writeError('No knowledge on how to build for your OS');

                return;
            }

            $io->write('    Fetching submodules');
            $process = new Process('git submodule update --init --checkout --recursive');
            $process->setWorkingDirectory($targetDir);
            $process->setTimeout(null);
            $process->run(function ($type, $buffer) use ($io) {
                if (!$io->isVerbose()) {
                    return;
                }
                echo $buffer;
            });

            $io->write('    Compiling drafter (grab a coffee | -v for output)');
            $process = new Process(self::getMake());
            $process->setWorkingDirectory($targetDir);
            $process->setTimeout(null);
            $process->run(function ($type, $buffer) use ($io) {
                if (!$io->isVerbose()) {
                    return;
                }
                echo $buffer;
            });

            $fs->symlink($binTarget, $binSource);

            $process = new Process('chmod +x drafter');
            $process->setWorkingDirectory($binDir);
            $process->run(function ($type, $buffer) use ($io) {
                if (!$io->isVerbose()) {
                    return;
                }
                echo $buffer;
            });

            $io->write('');
        } catch (\Exception $e) {
            $io->writeError($e->getMessage());
        }
    }

    /**
     * Create composer in memory package
     *
     * @param string $targetDir Download target dir
     * @param string $tag       Tag to install, e.g. v3.1.1
     * @return Package
     */
    public static function createComposerInMemoryPackage($targetDir, $tag)
    {
        $package = new Package('apiaryio/drafter', 'drafter', $tag);
        $package->setTargetDir($targetDir);
        $package->setInstallationSource('source');
        $package->setSourceUrl('https://github.com/apiaryio/drafter');
        $package->setSourceType('git');
        $package->setSourceReference($tag);

        return $package;
    }

    /**
     * Returns the Operating System.
     *
     * @return string OS, e.g. macosx, windows, freebsd, linux.
     */
    public static function getOS()
    {
        $uname = strtolower(php_uname());

        if (strpos($uname, "darwin") !== false) {
            return 'macosx';
        } elseif (strpos($uname, "win") !== false) {
            return 'windows';
        } elseif (strpos($uname, "linux") !== false) {
            return 'linux';
        } elseif (strpos($uname, "freebsd") !== false) {
            return 'freebsd';
        } else {
            return 'unknown';
        }
    }

    /**
     * Get gnu make command to use
     *
     * @return string|null
     */
    public static function getMake()
    {
        $os = self::getOS();

        $map = [
            'macosx'  => 'make',
            'windows' => null,
            'linux'   => 'make',
            'freebsd' => 'gmake',
            'unknown' => null,
        ];

        return $map[$os];
    }
}
