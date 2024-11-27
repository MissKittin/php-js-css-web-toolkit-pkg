<?php
	namespace MissKittin\PhpJsCssWebToolkitPkg\Command;

	use Composer\Command\BaseCommand;
	use Symfony\Component\Console\Input\InputInterface;
	use Symfony\Component\Console\Output\OutputInterface;
	use MissKittin\PhpJsCssWebToolkitPkg\Helper\PluginHelper;

	class Tkc extends BaseCommand
	{
		protected function configure(): void
		{
			$this
			->	setName('tkc')
			->	setDescription('Run the tool from the PHP JS CSS web toolkit component')
			->	setHelp(''
				.	'Run the tool from the PHP JS CSS web toolkit'.PHP_EOL
				.	'Usage: composer tkc component-name tool-name [tool-args]'.PHP_EOL
				.	'Eg: composer tkc php_polyfill mkcache --out cache.php'
				)
			->	ignoreValidationErrors();
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

			$packageData=PluginHelper::getPackages($this->getComposer());
			$dirs=PluginHelper::getPackageDirs($packageData);
			$areExtrasInstalled=PluginHelper::areExtrasInstalled();

			if(!isset($GLOBALS['argv'][3]))
			{
				$output->write('<info>Available tools:</info>'.PHP_EOL);

				foreach(PluginHelper::findAvailableTools(
					$dirs,
					$areExtrasInstalled,
					true
				) as $tool)
					$output->write(' '.$tool.PHP_EOL);

				return 1;
			}

			$foundTool=PluginHelper::findTool(
				$dirs,
				$GLOBALS['argv'][3],
				$areExtrasInstalled,
				$GLOBALS['argv'][2]
			);

			if($foundTool === false)
			{
				$output->write(
					'<error>Error: tool '.$GLOBALS['argv'][3].' from '.$GLOBALS['argv'][2].' component not found</error>'.PHP_EOL
				);

				return 1;
			}

			return PluginHelper
			::	putenvToolkitPaths($dirs)
			::	execCommandTool(
					$foundTool,
					array_slice($GLOBALS['argv'], 4)
				);
		}
	}
?>