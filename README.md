# PHP-JS-CSS web toolkit
Composer repository - MissKittin@GitHub

### Adding the repository - method 1
```
composer init

# add repository
composer config repositories.misskittin composer "https://raw.githubusercontent.com/MissKittin/php-js-css-web-toolkit-pkg/repo/github"

# allow required plugin
composer config allow-plugins.misskittin/php-js-css-web-toolkit-pkg true

# remove GPL libraries (optional)
composer config --json extra.php-js-css-web-toolkit-remove-gpl true

# disable php_polyfill component (optional)
composer config --json extra.php-js-css-web-toolkit-disable-php-polyfill true

# install packages
composer require misskittin/php-js-css-web-toolkit
composer require misskittin/php-js-css-web-toolkit-extras
```

### Adding the repository - method 2
Create a `composer.json` in the project root directory:
```
{
    "name": "vendor/name",
    "description": "Example description",
    "license": "MIT",
    "autoload": {
        "psr-4": {
            "Vendor\\Name\\": "src/"
        }
    },
    "authors": [
        {
            "name": "Example author"
        }
    ],
    "repositories": {
        "misskittin": {
            "type": "composer",
            "url": "https://raw.githubusercontent.com/MissKittin/php-js-css-web-toolkit-pkg/repo/github"
        }
    },
    "require": {
        "misskittin/php-js-css-web-toolkit": "^1.0",
        "misskittin/php-js-css-web-toolkit-extras": "^1.0"
    },
    "extra": {
        "php-js-css-web-toolkit-remove-gpl": false,
        "php-js-css-web-toolkit-disable-php-polyfill": false
    },
    "config": {
        "allow-plugins": {
            "misskittin/php-js-css-web-toolkit-pkg": true
        },
        "optimize-autoloader": true,
        "preferred-install": "dist",
        "sort-packages": true
    }
}

```

### Packages
* `misskittin/php-js-css-web-toolkit`
	[1.0](https://github.com/MissKittin/php-js-css-web-toolkit/tree/v1.0)
	[dev-master](https://github.com/MissKittin/php-js-css-web-toolkit)
* `misskittin/php-js-css-web-toolkit-extras`
	[1.0](https://github.com/MissKittin/php-js-css-web-toolkit-extras/tree/v1.0)
	[dev-master](https://github.com/MissKittin/php-js-css-web-toolkit-extras)
* `misskittin/php-js-css-web-toolkit-pkg`
	[1.0](https://github.com/MissKittin/php-js-css-web-toolkit-pkg/tree/v1.0)
	[dev-master](https://github.com/MissKittin/php-js-css-web-toolkit-pkg)
