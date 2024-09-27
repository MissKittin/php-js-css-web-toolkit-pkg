<?php
	namespace MissKittin\PhpJsCssWebToolkitPkg;

	use Composer\Composer;
	use Composer\InstalledVersions;
	use Composer\IO\IOInterface;
	use Composer\Plugin\PluginInterface;
	use Composer\EventDispatcher\EventSubscriberInterface;
	use Composer\Plugin\Capable;

	class Plugin implements PluginInterface, EventSubscriberInterface, Capable
	{
		private $composer;
		private $io;

		public static function getSubscribedEvents()
		{
			return [
				'post-install-cmd'=>'post_install',
				'post-update-cmd'=>'post_install'
			];
		}

		private function remove_gpl(&$meta, $event, $dirs)
		{
			if(
				(!$meta['toolkit']['gpl_removed']) &&
				isset($event->getComposer()->getPackage()->getExtra()['php-js-css-web-toolkit-remove-gpl']) &&
				($event->getComposer()->getPackage()->getExtra()['php-js-css-web-toolkit-remove-gpl'] === true)
			){
				system('"'.PHP_BINARY.'" "'.$dirs['tk_bin'].'/remove-gpl.php" '
				.	'--yes'
				);

				$meta['toolkit']['gpl_removed']=true;
			}
		}

		public function activate(Composer $composer, IOInterface $io)
		{
			if(!InstalledVersions::isInstalled('misskittin/php-js-css-web-toolkit'))
			{
				$io->write(
					'<warning>Warning: misskittin/php-js-css-web-toolkit-pkg composer plugin is installed but without misskittin/php-js-css-web-toolkit library it will not work</warning>'.PHP_EOL
				);
			}

			$this->composer=$composer;
			$this->io=$io;
		}
		public function deactivate(Composer $composer, IOInterface $io) {}
		public function uninstall(Composer $composer, IOInterface $io) {}
		public function getCapabilities()
		{
			return [
				'Composer\Plugin\Capability\CommandProvider'=>'MissKittin\PhpJsCssWebToolkitPkg\Command\CommandProvider'
			];
		}

		public function post_install($event)
		{
			if(!InstalledVersions::isInstalled('misskittin/php-js-css-web-toolkit'))
				return;

			$database_meta=['', ''];
			$extras_installed=true;
			$meta=[
				'toolkit'=>[
					'version'=>'0',
					'gpl_removed'=>false,
					'stripped'=>false
				],
				'extras'=>[
					'version'=>0,
					'stripped'=>false
				]
			];

			foreach(
				$this->composer->getRepositoryManager()->getLocalRepository()->getPackages()
				as $package
			)
				switch($package->getName())
				{
					case 'misskittin/php-js-css-web-toolkit':
					case 'misskittin/php-js-css-web-toolkit-extras':
					case 'misskittin/php-js-css-web-toolkit-pkg':
						$package_data[$package->getName()]=[
							$package->getFullPrettyVersion(),
							$this->composer->getInstallationManager()->getInstallPath($package)
						];
				}

			if(!isset($package_data['misskittin/php-js-css-web-toolkit-extras']))
			{
				$extras_installed=false;
				$package_data['misskittin/php-js-css-web-toolkit-extras']=['0', null];
			}

			$dirs['pkg_var']=$package_data['misskittin/php-js-css-web-toolkit-pkg'][1].'/var';
			$dirs['tk']=$package_data['misskittin/php-js-css-web-toolkit'][1];
			$dirs['tk_bin']=$dirs['tk'].'/bin';
			$dirs['tk_com']=$dirs['tk'].'/com';
			$dirs['tk_lib']=$dirs['tk'].'/lib';
			$dirs['tke']=$package_data['misskittin/php-js-css-web-toolkit-extras'][1];
			$dirs['tke_lib']=$dirs['tke'].'/lib';

			@mkdir($dirs['pkg_var']);

			if(file_exists($dirs['pkg_var'].'/meta.json'))
			{
				$database_meta[0]=file_get_contents($dirs['pkg_var'].'/meta.json');
				$meta=json_decode(
					$database_meta[0],
					true
				);
			}

			$this->remove_gpl($meta, $event, $dirs);

			if(
				($meta['toolkit']['version'] === $package_data['misskittin/php-js-css-web-toolkit'][0]) &&
				($meta['extras']['version'] === $package_data['misskittin/php-js-css-web-toolkit-extras'][0])
			){
				$database_meta[1]=json_encode($meta);

				if($database_meta[0] !== $database_meta[1])
					file_put_contents($dirs['pkg_var'].'/meta.json', $database_meta[1]);

				return;
			}

			$meta['toolkit']['gpl_removed']=false;
			$meta['toolkit']['stripped']=false;
			$meta['extras']['stripped']=false;

			$this->remove_gpl($meta, $event, $dirs);

			if(!$meta['toolkit']['stripped'])
			{
				system('"'.PHP_BINARY.'" "'.$dirs['tk_bin'].'/strip-php-files.php" '
				.	'"'.$dirs['tk'].'" '
				.	'--remove-tests '
				.	'--remove-md'
				);

				system('"'.PHP_BINARY.'" "'.$dirs['tk_com'].'/php_polyfill/bin/mkcache.php" '
				.	'--out "'.$dirs['pkg_var'].'/php_polyfill.php"'
				);

				$meta['toolkit']['stripped']=true;
			}

			if(
				$extras_installed &&
				(!$meta['extras']['stripped'])
			){
				system('"'.PHP_BINARY.'" "'.$dirs['tk_bin'].'/strip-php-files.php" '
				.	'"'.$dirs['tke'].'" '
				.	'--remove-tests '
				.	'--remove-md'
				);

				$meta['extras']['stripped']=true;
			}

			$autoloader_tke_in='';

			if($extras_installed)
				$tke_in='--in "'.$dirs['tke_lib'].'" ';

			system('"'.PHP_BINARY.'" "'.$dirs['tk_bin'].'/autoloader-generator.php" '
			.	'--in "'.$dirs['tk_com'].'" '
			.	'--in "'.$dirs['tk_lib'].'" '
			.	$autoloader_tke_in
			.	'--ignore bin/ '
			.	'--out "'.$dirs['pkg_var'].'/autoload.php"'
			);

			$meta['toolkit']['version']=$package_data['misskittin/php-js-css-web-toolkit'][0];
			$meta['extras']['version']=$package_data['misskittin/php-js-css-web-toolkit-extras'][0];

			file_put_contents($dirs['pkg_var'].'/meta.json', json_encode($meta));
		}
	}
?>