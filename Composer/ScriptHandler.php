<?php

namespace Bangpound\Bridge\Drupal\Composer;

use Bangpound\Bridge\Drupal\Autoload\ClassMapGenerator;
use Composer\Script\Event;
use Composer\Script\CommandEvent;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * Class ScriptHandler
 * @package Bangpound\Bridge\Drupal\Composer
 */
class ScriptHandler
{
    /**
     * Installs Drupal under the web root directory.
     *
     * @param $event CommandEvent A instance
     * @deprecated use prepareDrupalRoot instead.
     */
    public static function installDrupal(CommandEvent $event)
    {
        self::prepareDrupalRoot($event);
        self::createSitesDir($event);
    }

    /**
     * Links Drupal core, modules, themes and assets into Drupal root.
     *
     * @param CommandEvent $event
     */
    public static function prepareDrupalRoot(CommandEvent $event)
    {
        $options = self::getOptions($event);
        $composer = $event->getComposer();
        $filesystem = new Filesystem();

        $originDir = realpath($composer->getConfig()->get('vendor-dir') . '/drupal/drupal');
        $targetDir = realpath($options['drupal-root']);

        if (is_dir($originDir)) {

            if ($options['drupal-install']['relative']) {
                $originDir = rtrim($filesystem->makePathRelative($originDir, $targetDir), '/');
            }

            $directories = array(
                'includes',
                'misc',
                'modules',
                'themes',
            );

            foreach ($directories as $directory) {
                $filesystem->remove($targetDir .'/'. $directory);

                if ($options['drupal-install']['symlink']) {
                    $event->getIO()->write(sprintf('Creating symlink for Drupal\'s \'%s\' directory', $directory));
                    $filesystem->symlink($originDir .'/'. $directory, $targetDir .'/'. $directory);
                } else {
                    $event->getIO()->write(sprintf('Copying Drupal\'s \'%s\' directory to web root', $directory));
                    $filesystem->mkdir($targetDir .'/'. $directory, 0777);
                    // We use a custom iterator to ignore VCS files
                    $filesystem->mirror($originDir .'/'. $directory, $targetDir .'/'. $directory, Finder::create()->ignoreDotFiles(false)->in($originDir));
                }
            }
        }
    }

    /**
     * Create the Drupal site directory.
     *
     * This script checks for an existing sites directory, but it also
     * overwrites existing files. It should only be called on user demand
     * just to be safe.
     *
     * @param Event $event
     */
    public static function createSitesDir(Event $event)
    {
        $options = self::getOptions($event);
        $composer = $event->getComposer();
        $filesystem = new Filesystem();

        $originDir = realpath($composer->getConfig()->get('vendor-dir') . '/drupal/drupal');
        $targetDir = realpath($options['drupal-root']);

        if (is_dir($originDir)) {
            if (!$filesystem->exists($targetDir . '/sites')) {
                if ($event->getIO()->askConfirmation('Create a new sites directory? ', false)) {
                    $event->getIO()->write(sprintf('Creating new sites directory.'));
                    $filesystem->mirror($originDir . '/sites', $targetDir . '/sites', null, array('override' => true));
                }
            } else {
                $event->getIO()->write(sprintf('Sites directory already exists.'));
            }
        } else {
            $event->getIO()->write(sprintf('Drupal is not in the vendor directory.'));
        }
    }

    protected static function getOptions(Event $event)
    {
        $options = array_merge(
            array(
                'drupal-install' => array(
                    'symlink' => true,
                    'relative' => true,
                ),
                'drupal-root' => '',
            ),
            $event->getComposer()->getPackage()->getExtra()
        );

        $options['drupal-install'] = getenv('COMPOSER_DRUPAL_INSTALL') ?: $options['drupal-install'];

        return $options;
    }

    /**
     * Paths in Drupal root to scan for classes.
     *
     * This should never include profiles and sites, because those are scanned to
     * generate separate classmaps.
     *
     * @var array
     */
    protected static $root_paths = array(
        'misc', 'modules', 'scripts', 'themes',

        // None of these files actually contain PHP classes, but the are
        // scanned anyway.
        'authorize.php', 'cron.php', 'index.php', 'install.php',
        'update.php', 'xmlrpc.php',
    );

    /**
     * Paths in subdirectories (profiles and sites) to scan for classes.
     *
     * @var array
     */
    protected static $subdir_paths = array(
        'modules', 'themes', 'plugins',
    );

    /**
     * @param CommandEvent $event
     */
    public static function dumpAutoload(CommandEvent $event)
    {
        $cwd = getcwd();
        $io = $event->getIO();

        $options = self::getOptions($event);
        if (!empty($options['drupal-root'])) {
            chdir($options['drupal-root']);
        }

        $generator = new ClassMapGenerator();
        $dirs = array_filter(self::$root_paths, 'file_exists');
        $io->write('Dumping classmap for <info>DRUPAL_ROOT</info>');
        $generator->dump($dirs, 'classmap.php');

        $finder = Finder::create()
            ->directories()
            ->depth(0)
            ->followLinks()
            ->in(array('profiles', 'sites'))
        ;

        foreach ($finder as $file) {
            /** @var \Symfony\Component\Finder\SplFileInfo $file */
            chdir($file->getPathInfo() .'/'. $file->getRelativePathname());
            $io->write(sprintf('Dumping classmap for <info>%s</info>', $file->getPathInfo() .'/'. $file->getRelativePathname()));
            $dirs = array_filter(self::$subdir_paths, 'file_exists');
            $generator->dump($dirs, 'classmap.php');
            chdir($cwd);
            if (!empty($options['drupal-root'])) {
                chdir($options['drupal-root']);
            }
        }
        chdir($cwd);
    }
}
