<?php
	namespace MissKittin\PhpJsCssWebToolkitPkg;

	use Composer\Composer;
	use Composer\IO\IOInterface;
	use Composer\Plugin\PluginInterface;
	use Composer\Plugin\Capable;
	use Composer\Plugin\Capability\CommandProvider as CommandProviderCapability;
	use Composer\EventDispatcher\EventSubscriberInterface;

	use MissKittin\PhpJsCssWebToolkitPkg\Helper\PluginHelper;
	use MissKittin\PhpJsCssWebToolkitPkg\Helper\PluginDatabase;
	use MissKittin\PhpJsCssWebToolkitPkg\Helper\PluginDatabaseContainer;
	use MissKittin\PhpJsCssWebToolkitPkg\Command\Tk;
	use MissKittin\PhpJsCssWebToolkitPkg\Command\Tkc;
	use MissKittin\PhpJsCssWebToolkitPkg\Command\Tkcp;
	use MissKittin\PhpJsCssWebToolkitPkg\Command\TkDisablePolyfill;
	use MissKittin\PhpJsCssWebToolkitPkg\Command\Tkmk;

	class Plugin
	implements
		PluginInterface, EventSubscriberInterface,
		Capable,
		CommandProviderCapability
	{
		private $composer;
		private $io;
		private $packageData;
		private $dirs;
		private $database=null;
		private $extrasInstalled=null;

		public static function getSubscribedEvents()
		{
			return [
				'post-install-cmd'=>'postInstall',
				'post-update-cmd'=>'postInstall'
			];
		}

		private function openDatabase()
		{
			PluginDatabase::$initialDatabase=[
				'version'=>'0',
				'gpl_removed'=>false,
				'stripped'=>false
			];

			$this->packageData=PluginHelper::getPackages($this->composer);
			$this->dirs=PluginHelper::getPackageDirs($this->packageData);

			if($this->database === null)
			{
				$this->database=new PluginDatabaseContainer([
					'toolkit'=>new PluginDatabase($this->dirs['tk'].'/pkg.json'),
					'extras'=>new PluginDatabase($this->dirs['tke'].'/pkg.json')
				]);

				return;
			}

			$this->database
			->	set_database_file('toolkit', $this->dirs['tk'].'/pkg.json')
			->	set_database_file('extras', $this->dirs['tke'].'/pkg.json');
		}
		private function removeGpl(
			$database,
			$composer,
			$dirs
		){
			if(PluginHelper::readExtraEntry(
				$composer,
				'php-js-css-web-toolkit-remove-gpl'
			) === true)
				foreach([
					'toolkit'=>$dirs['tk_bin'],
					'extras'=>$dirs['tke_bin']
				] as $databaseName=>$binDir)
					if(
						(!$database->get($databaseName, 'gpl_removed')) &&
						file_exists($binDir.'/remove-gpl.php')
					){
						$extras=false;

						if($databaseName === 'extras')
							$extras=true;

						PluginHelper::execPluginTool(
							$dirs, null,
							'remove-gpl', '--yes',
							$extras
						);
						$database->set($databaseName, 'gpl_removed', true);
					}
		}

		public function postInstall($event)
		{
			if(!PluginHelper::isToolkitInstalled())
				return;

			$packageVersions=PluginHelper::getPackageVersions($this->packageData);
			$extrasInstalled=PluginHelper::areExtrasInstalled();

			if(
				($this->database === null) ||
				(!file_exists($this->dirs['tk'].'/pkg.json')) ||
				(
					$extrasInstalled &&
					(!file_exists($this->dirs['tke'].'/pkg.json'))
				)
			)
				$this->openDatabase();

			$this->removeGpl(
				$this->database,
				$event->getComposer(),
				$this->dirs
			);

			if(
				($this->database->get('toolkit', 'version') === $packageVersions['toolkit']) &&
				($this->database->get('extras', 'version') === $packageVersions['extras'])
			){
				$this->database->save();
				return;
			}

			if($this->database->get('toolkit', 'version') !== $packageVersions['toolkit'])
				$this->database
				->	set('toolkit', 'gpl_removed', false)
				->	set('toolkit', 'stripped', false);

			if($this->database->get('extras', 'version') !== $packageVersions['extras'])
				$this->database
				->	set('extras', 'gpl_removed', false)
				->	set('extras', 'stripped', false);

			$this->removeGpl(
				$this->database,
				$event->getComposer(),
				$this->dirs
			);

			@mkdir($this->dirs['pkg_var']);

			if(!$this->database->get('toolkit', 'stripped'))
			{
				PluginHelper::execPluginTool($this->dirs, null, 'strip-php-files', ''
				.	'"'.$this->dirs['tk'].'" '
				.	'--remove-tests '
				.	'--remove-md'
				);

				if(file_exists($this->dirs['tk_com'].'/php_polyfill'))
				{
					@unlink($this->dirs['pkg_var'].'/php_polyfill.php');
					@unlink($this->dirs['pkg_var'].'/php_polyfill.php.disabled');

					PluginHelper::execPluginTool($this->dirs, 'php_polyfill', 'mkcache', ''
					.	'--out "'.$this->dirs['pkg_var'].'/php_polyfill.php"'
					);

					if(!function_exists('rmdir_recursive'))
						require $this->dirs['tk_lib'].'/rmdir_recursive.php';

					rmdir_recursive($this->dirs['tk_com'].'/php_polyfill');
				}

				if(PluginHelper::readExtraEntry(
					$this->composer,
					'php-js-css-web-toolkit-disable-php-polyfill'
				) === true)
					rename(
						$this->dirs['pkg_var'].'/php_polyfill.php',
						$this->dirs['pkg_var'].'/php_polyfill.php.disabled'
					);

				foreach([
					'run-php-bin-tests',
					'run-php-com-tests',
					'run-php-lib-tests',
					'run-phtml-tests'
				] as $tool)
					if(file_exists($this->dirs['tk_bin'].'/'.$tool.'.php'))
					{
						unlink($this->dirs['tk_bin'].'/'.$tool.'.php');
						$this->io->write('Removed '.$tool.' tool');
					}

				$this->database->set('toolkit', 'stripped', true);
			}

			if(
				$extrasInstalled &&
				(!$this->database->get('extras', 'stripped'))
			){
				PluginHelper::execPluginTool($this->dirs, null, 'strip-php-files', ''
				.	'"'.$this->dirs['tke'].'" '
				.	'--remove-tests '
				.	'--remove-md'
				);

				$this->database->set('extras', 'stripped', true);
			}

			$autoloaderTkeIn='';

			if($extrasInstalled)
			{
				if(is_dir($this->dirs['tke_com']))
					$autoloaderTkeIn='--in "'.$this->dirs['tke_com'].'" ';

				$autoloaderTkeIn.='--in "'.$this->dirs['tke_lib'].'" ';
			}

			PluginHelper::execPluginTool($this->dirs, null, 'autoloader-generator', ''
			.	'--namespace MissKittin '
			.	'--in "'.$this->dirs['tk_com'].'" '
			.	'--in "'.$this->dirs['tk_lib'].'" '
			.	$autoloaderTkeIn
			.	'--ignore bin/ '
			.	'--out "'.$this->dirs['pkg_var'].'/autoload.php"'
			);

			$this->database
			->	set('toolkit', 'version', $packageVersions['toolkit'])
			->	set('extras', 'version', $packageVersions['extras'])
			->	save();
		}

		public function activate(Composer $composer, IOInterface $io)
		{
			$this->composer=$composer;
			$this->io=$io;

			if(!PluginHelper::isToolkitInstalled())
			{
				$io->write(
					'<warning>Warning: '.PluginHelper::PKG.' composer plugin is installed but without '.PluginHelper::TOOLKIT.' package it will not work</warning>'.PHP_EOL
				);

				return;
			}

			$this->openDatabase();
		}
		public function deactivate(Composer $composer, IOInterface $io) {}
		public function uninstall(Composer $composer, IOInterface $io) {}

		public function getCapabilities()
		{
			return [
				CommandProviderCapability::class=>self::class
			];
		}

		public function getCommands()
		{
			return [
				new Tk(),
				new Tkc(),
				new Tkcp(),
				new TkDisablePolyfill(),
				new Tkmk()
			];
		}
	}
?>