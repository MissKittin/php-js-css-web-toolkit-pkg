<?php
	namespace MissKittin\PhpJsCssWebToolkitPkg\Command;

	use Symfony\Component\Console\Input\InputInterface;
	use Symfony\Component\Console\Output\OutputInterface;
	use Composer\Command\BaseCommand;
	use Composer\InstalledVersions;

	class TkCommand extends BaseCommand
	{
		protected function configure(): void
		{
			$this
			->	setName('tk')
			->	setDescription('Run the tool from the PHP JS CSS web toolkit')
			->	setHelp(''
				.	'Run the tool from the PHP JS CSS web toolkit'.PHP_EOL
				.	'Usage: composer tk tool-name [tool-args]'.PHP_EOL
				.	'Eg: composer tk serve --port 8000'
				)
			->	ignoreValidationErrors();
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

			$exit_code=0;
			$argv=array_slice($GLOBALS['argv'], 2);

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

			if(!isset($argv[0]))
			{
				$output->write('<info>Available tools:</info>'.PHP_EOL);

				$tools=array_diff(scandir($package_data['misskittin/php-js-css-web-toolkit'].'/bin'), ['.', '..']);

				if(isset($package_data['misskittin/php-js-css-web-toolkit-extras']))
				{
					$tools=array_merge(
						$tools,
						array_diff(scandir($package_data['misskittin/php-js-css-web-toolkit-extras'].'/bin'), ['.', '..'])
					);
					sort($tools);
				}

				foreach($tools as $tool)
					if(substr($tool, -4) === '.php')
						$output->write(' '.pathinfo($tool, PATHINFO_FILENAME).PHP_EOL);

				return 1;
			}

			$tool=$argv[0];
			$argv=array_slice($argv, 1);

			if(file_exists($package_data['misskittin/php-js-css-web-toolkit'].'/bin/'.$tool.'.php'))
				$tool=$package_data['misskittin/php-js-css-web-toolkit'].'/bin/'.$tool.'.php';
			else if(
				(isset($package_data['misskittin/php-js-css-web-toolkit-extras'])) &&
				file_exists($package_data['misskittin/php-js-css-web-toolkit-extras'].'/bin/'.$tool.'.php')
			)
				$tool=$package_data['misskittin/php-js-css-web-toolkit-extras'].'/bin/'.$tool.'.php';
			else
			{
				$output->write(
					'<error>Error: tool '.$tool.' not found</error>'.PHP_EOL
				);

				return 1;
			}

			for($i=0; $i<count($argv); ++$i)
				$argv[$i]=escapeshellarg($argv[$i]);

			$argv=implode(' ',$argv);

			if(isset($argv[0]))
				$argv=' '.$argv;

			system('"'.PHP_BINARY.'" '
			.	'"'.$tool.'"'
			.	$argv
			,	$exit_code
			);

			return $exit_code;
		}
	}
?>