<?php
	namespace MissKittin\PhpJsCssWebToolkitPkg\Command;

	use Composer\Command\BaseCommand;
	use Symfony\Component\Console\Input\InputInterface;
	use Symfony\Component\Console\Input\InputArgument;
	use Symfony\Component\Console\Input\InputOption;
	use Symfony\Component\Console\Output\OutputInterface;
	use MissKittin\PhpJsCssWebToolkitPkg\Helper\PluginHelper;

	class TkDisablePolyfill extends BaseCommand
	{
		protected function configure(): void
		{
			$this
			->	setName('tk-disable-polyfill')
			->	setDescription('Temporarily disable autoloading of php_polyfill component')
			->	setDefinition([
					new InputArgument('true-or-false', null, InputOption::VALUE_REQUIRED, 'true|false'),
				])
			->	setHelp(''
				.	'Temporarily disable autoloading of php_polyfill component'.PHP_EOL
				.	'Usage: composer tk-disable-polyfill true|false'.PHP_EOL
				.	'Eg: composer tk-disable-polyfill true'.PHP_EOL
				.	'Eg: composer tk-disable-polyfill false'.PHP_EOL
				.	PHP_EOL
				.	'Note:'
				.	' use'
				.	' composer config --json extra.php-js-css-web-toolkit-disable-php-polyfill true'
				.	' to save setting to composer.json'
				);
		}
		protected function execute(InputInterface $input, OutputInterface $output): int
		{
			if(!PluginHelper::isToolkitInstalled())
			{
				$output->write(
					'<error>Error: this command does not work without '.PluginHelper::TOOLKIT.' package</error>'.PHP_EOL
				);

				return 1;
			}

			$dirs=PluginHelper::getPackageDirs(
				PluginHelper::getPackages(
					$this->getComposer()
				)
			);

			switch($input->getArgument('true-or-false'))
			{
				case 'true':
					if(file_exists($dirs['pkg_var'].'/php_polyfill.php.disabled'))
					{
						$output->write(
							'<info>php_polyfill autoloading already disabled</info>'.PHP_EOL
						);

						return 0;
					}

					if(!rename(
						$dirs['pkg_var'].'/php_polyfill.php',
						$dirs['pkg_var'].'/php_polyfill.php.disabled'
					)){
						$output->write(
							'<error>rename() error</error>'.PHP_EOL
						);

						return 1;
					}
				break;
				case 'false':
					if(file_exists($dirs['pkg_var'].'/php_polyfill.php'))
					{
						$output->write(
							'<info>php_polyfill autoloading already enabled</info>'.PHP_EOL
						);

						return 0;
					}

					if(!rename(
						$dirs['pkg_var'].'/php_polyfill.php.disabled',
						$dirs['pkg_var'].'/php_polyfill.php'
					)){
						$output->write(
							'<error>rename() error</error>'.PHP_EOL
						);

						return 1;
					}
				break;
				default:
					$output->write(
						'<error>Usage: composer tk-disable-polyfill true|false</error>'.PHP_EOL
					);

					return 1;
			}

			return 0;
		}
	}
?>