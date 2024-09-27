<?php
	namespace MissKittin\PhpJsCssWebToolkitPkg\Command;

	use Symfony\Component\Console\Input\InputInterface;
	use Symfony\Component\Console\Output\OutputInterface;
	use Symfony\Component\Console\Input\InputArgument;
	use Symfony\Component\Console\Input\InputOption;
	use Composer\Command\BaseCommand;
	use Composer\InstalledVersions;

	class TkcpCommand extends BaseCommand
	{
		protected function configure(): void
		{
			$this
			->	setName('tkcp')
			->	setDescription('Copy library from the PHP JS CSS web toolkit')
			->	setDefinition([
					new InputArgument('library-name', null, InputOption::VALUE_REQUIRED, 'Library file (eg. clock.js)'),
					new InputArgument('output-file', null, InputOption::VALUE_OPTIONAL, 'Output file (eg. assets/clock.js)')
				])
			->	setHelp(''
				.	'Copy library from the PHP JS CSS web toolkit'.PHP_EOL
				.	'Usage: composer tkcp library-name [path/to/output-file]'.PHP_EOL
				.	'Eg: composer tkcp clock.js'.PHP_EOL
				.	'Eg: composer tkcp clock.js assets/clock.js'
				);
		}
		protected function execute(InputInterface $input, OutputInterface $output): int
		{
			if(!InstalledVersions::isInstalled('misskittin/php-js-css-web-toolkit'))
			{
				$output->write(
					'<error>Error: this command does not work without misskittin/php-js-css-web-toolkit library</error>'.PHP_EOL
				);

				return 1;
			}

			$input_file=$input->getArgument('library-name');
			$output_file=$input->getArgument('output-file');

			if($input_file === 'Library file (eg. clock.js)')
			{
				$output->write(
					'<error>Usage: composer tkcp library-name [path/to/output-file]</error>'.PHP_EOL
				);

				return 1;
			}

			if($output_file === 'Output file (eg. assets/clock.js)')
				$output_file='./'.$input_file;

			foreach(
				$this->getComposer()->getRepositoryManager()->getLocalRepository()->getPackages()
				as $package
			)
				switch($package->getName())
				{
					case 'misskittin/php-js-css-web-toolkit':
					case 'misskittin/php-js-css-web-toolkit-extras':
						$package_data[$package->getName()]=$this->getComposer()->getInstallationManager()->getInstallPath($package);
				}

			if(is_file($package_data['misskittin/php-js-css-web-toolkit'].'/lib/'.$input_file))
				$input_file=$package_data['misskittin/php-js-css-web-toolkit'].'/lib/'.$input_file;
			else if(is_file($package_data['misskittin/php-js-css-web-toolkit-extras'].'/lib/'.$input_file))
				$input_file=$package_data['misskittin/php-js-css-web-toolkit-extras'].'/lib/'.$input_file;
			else
			{
				$output->write(
					'<error>Error: '.$input_file.' library not found</error>'.PHP_EOL
				);

				return 1;
			}

			if(copy($input_file, $output_file))
				return 0;

			return 1;
		}
	}
?>