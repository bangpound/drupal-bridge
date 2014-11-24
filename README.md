Drupal Bridge
=============

Composer
--------

In your composer.json, define the Drupal root relative to the composer.json file.
`drupal-root` is the only option required for the Composer integration.

```json
{
    "extra": {
        "drupal-root": "docroot/"
    }
}
```

The Composer integration works well with [`composer/installers`][] but this is not an
explicit dependency. When `composer/installers` is available, you can install Drupal
modules, themes, profiles and Drush plugins.

The snippet below is an example based on the `composer/installers` documentation and
depends on `drupal-root` being set to `docroot/` (as above).

```json
{
    "extra": {
        "installer-paths": {
            "docroot/profiles/special/modules/custom/amazing": [
                "somebody/amazing-module"
            ],
            "docroot/profiles/special/": [
                "somebody/special-profile"
            ],
            "docroot/sites/all/modules/{$name}/": [
                "type:drupal-module"
            ],
            "docroot/sites/all/themes/{$name}/": [
                "type:drupal-theme"
            ],
            "docroot/sites/all/libraries/{$name}/": [
                "type:drupal-library"
            ],
            "docroot/profiles/{$name}/": [
                "type:drupal-profile"
            ],
            "docroot/sites/all/drush/{$name}/": [
                "type:drupal-drush"
            ]
        }
    }
}
```

The name of the directory where a profile is installed apparently must be named precisely
for the same as its info file.

[`composer/installers`]: http://composer.github.io/installers/

### Drupal install

```json
{
    "repositories": [
        {
            "type": "package",
            "package": {
                "name": "drupal/drupal",
                "version": "7.x-dev",
                "dist": {
                    "type": "tar",
                    "url": "http://ftp.drupal.org/files/projects/drupal-7.x-dev.tar.gz"
                },
                "source": {
                    "type": "git",
                    "url": "git://git.drupal.org/project/drupal.git",
                    "reference": "7.x"
                },
                "bin": [
                    "scripts/drupal.sh",
                    "scripts/password-hash.sh"
                ],
                "require": {
                    "php": ">=5.2.4",
                    "ext-date": "*",
                    "ext-dom": "*",
                    "ext-filter": "*",
                    "ext-gd": "*",
                    "ext-hash": "*",
                    "ext-json": "*",
                    "ext-pcre": "*",
                    "ext-PDO": "*",
                    "ext-session": "*",
                    "ext-SimpleXML": "*",
                    "ext-SPL": "*",
                    "ext-xml": "*"
                }
            }
        }
    ],
    "require": {
        "drupal/drupal": "~7.0@dev",
        "drufony/drupal-bridge": "~1.0@dev"
    },
    "extra": {
        "drupal-install": {
            "symlink": true,
            "relative": true
        }
    },
    "scripts": {
        "create-sites-dir": "Bangpound\\Bridge\\Drupal\\Composer\\ScriptHandler::createSitesDir",
        "post-install-cmd": [
            "Bangpound\\Bridge\\Drupal\\Composer\\ScriptHandler::prepareDrupalRoot"
        ],
        "post-update-cmd": [
            "Bangpound\\Bridge\\Drupal\\Composer\\ScriptHandler::prepareDrupalRoot"
        ]
    }
}
```

When you define `drupal/drupal` as a dependency, the `post-install-cmd` and
`post-update-cmd` scripts will (by default) create a relative symlink of the `includes`,
`misc`, `modules`, and `themes` directory. The `profiles` directory is left out so that
these can be managed by the `composer/installers` plugin for Drupal profile projects.

### Core profiles

The core profile for `standard` and `minimal` installs can be obtained from the
[Drufony][] package repository. Just merge this snippet into your composer.json file. Also
configure your `installer-paths` as described above so that the profiles are installed
into the Drupal root's `profiles` directory.

```json
{
    "repositories": [
        {
            "type": "composer",
            "url": "http://drufony.github.io/packages.json"
        }
    ],
    "require": {
        "drupal/minimal": "7.*@dev",
        "drupal/standard": "7.*@dev"
    }
}
```

[Drufony]: http://drufony.github.io

### Drush command

```json
{
    "require": {
        "drush/drush": "dev-master"
    },
    "scripts": {
        "drush": "Bangpound\\Bridge\\Drupal\\Drush\\Composer\\ScriptHandler::drushCommand"
    }
}
```

```bash
composer drush updatedb
```
