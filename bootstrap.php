<?php
	if(file_exists(__DIR__.'/var/php_polyfill.php'))
		require __DIR__.'/var/php_polyfill.php';

	if(file_exists(__DIR__.'/var/autoload.php'))
		require __DIR__.'/var/autoload.php';
?>