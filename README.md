# PHP-JS-CSS web toolkit
Composer integration plugin

### Repositories
How to use repository: see branch
* `repo/github` - [use GitHub as package source](https://github.com/MissKittin)

### Note
This plugin will not work without `misskittin/php-js-css-web-toolkit` library  
Also there is no need for manual installation - the plugin is a dependency of the `misskittin/php-js-css-web-toolkit` library

### Features
* removes GPL libraries depending on configuration
* removes documentation, tests and reduces the size of php files
* creates a cache for the `php_polyfill` component
* creates autoloader using `autoloader-generator.php` tool
* automatically includes the `php_polyfill` component

### Commands
* `composer tk tool-name [tool-args]`  
	run the tool from tookit
* `composer tkc component-name tool-name [tool-args]`  
	run tool from toolkit component
* `composer tkcp library-file.ext [path/to/output-file]`  
	copy library from toolkit

### Remove GPL libraries
Add an option to the `extra` section in `composer.json`:
```
"extra": {
    "php-js-css-web-toolkit-remove-gpl": true
}
```

### Loading functions
```
<?php
	// include composer autoloader
	require './vendor/autoload.php';

	// load rand_str.php library
	load_function('rand_str');

	echo rand_str(10);
?>
```
