<?php

namespace Bangpound\Bridge\Drupal\Composer;

use Bangpound\Bridge\Drupal\Autoload\ClassMapGenerator;
use Composer\Script\CommandEvent;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class ScriptHandler
{
    /**
     * Installs Drupal under the web root directory.
     *
     * @param $event CommandEvent A instance
     */
    public static function installDrupal(CommandEvent $event)
    {
        $options = self::getOptions($event);
        $webDir = $options['symfony-web-dir'];
        $composer = $event->getComposer();
        $filesystem = new Filesystem();

        $packages = $composer->getPackage()->getRequires();
        $drupal_root = $composer->getConfig()->get('vendor-dir') . DIRECTORY_SEPARATOR .
            $packages['drupal/drupal']->getTarget();

        $directories = array(
            'includes',
            'misc',
            'modules',
            'themes',
        );

        foreach ($directories as $directory) {
            $originDir = '../'. $drupal_root .'/'. $directory;
            $targetDir = $webDir.'/'.$directory;
            $event->getIO()->write(sprintf('Creating symlink for Drupal\'s \'%s\' directory', $directory));
            $filesystem->symlink($originDir, $targetDir);
        }

        $directory = 'sites';
        $targetDir = $webDir.'/'.$directory .'/';

        // Check for sites/default because sites/all may exist if composer installs
        // modules or themes.
        if (!$filesystem->exists($targetDir .'/default')) {
            $originDir = $drupal_root .'/'. $directory;
            $event->getIO()->write(sprintf('Creating new sites directory', $directory));
            $filesystem->mirror($originDir, $targetDir, null, array('override' => true));
        }
    }

    protected static function getOptions(CommandEvent $event)
    {
        $options = array_merge(
            array(
                'symfony-web-dir' => 'web',
                'symfony-drupal-install' => 'relative',
                'drupal-root' => '',
            ),
            $event->getComposer()->getPackage()->getExtra()
        );

        $options['symfony-drupal-install'] = getenv('SYMFONY_DRUPAL_INSTALL') ?: $options['symfony-drupal-install'];

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
        'includes', 'misc', 'modules', 'scripts', 'themes',

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
