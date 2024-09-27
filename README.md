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
        "php-js-css-web-toolkit-remove-gpl": false
    },
    "config": {
        "allow-plugins": {
            "misskittin/php-js-css-web-toolkit-pkg": true
        }
    }
}

```

### Packages
* `misskittin/php-js-css-web-toolkit`
	[dev-master](https://github.com/MissKittin/php-js-css-web-toolkit)
* `misskittin/php-js-css-web-toolkit-extras`
	[dev-master](https://github.com/MissKittin/php-js-css-web-toolkit-extras)
* `misskittin/php-js-css-web-toolkit-pkg`
	[dev-master](https://github.com/MissKittin/php-js-css-web-toolkit-pkg)
