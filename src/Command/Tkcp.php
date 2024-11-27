<?php
	namespace MissKittin\PhpJsCssWebToolkitPkg\Command;

	use Composer\Command\BaseCommand;
	use Symfony\Component\Console\Input\InputInterface;
	use Symfony\Component\Console\Input\InputArgument;
	use Symfony\Component\Console\Input\InputOption;
	use Symfony\Component\Console\Output\OutputInterface;
	use MissKittin\PhpJsCssWebToolkitPkg\Helper\PluginHelper;

	class Tkcp extends BaseCommand
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
			if(!PluginHelper::isToolkitInstalled())
			{
				$output->write(
					'<error>Error: this command does not work without '.PluginHelper::TOOLKIT.' package</error>'.PHP_EOL
				);

				return 1;
			}

			$inputFile=$input->getArgument('library-name');
			$outputFile=$input->getArgument('output-file');

			if($inputFile === 'Library file (eg. clock.js)')
			{
				$output->write(
					'<error>Usage: composer tkcp library-name [path/to/output-file]</error>'.PHP_EOL
				);

				return 1;
			}

			if($outputFile === 'Output file (eg. assets/clock.js)')
				$outputFile='./'.$inputFile;

			$dirs=PluginHelper::getPackageDirs(
				PluginHelper::getPackages(
					$this->getComposer()
				)
			);

			if(is_file($dirs['tk_lib'].'/'.$inputFile))
				$inputFile=$dirs['tk_lib'].'/'.$inputFile;
			else if(is_file($dirs['tke_lib'].'/'.$inputFile))
				$inputFile=$dirs['tke_lib'].'/'.$inputFile;
			else
			{
				$output->write(
					'<error>Error: '.$inputFile.' library not found</error>'.PHP_EOL
				);

				return 1;
			}

			if(copy($inputFile, $outputFile))
				return 0;

			return 1;
		}
	}
?>