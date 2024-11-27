<?php
	namespace MissKittin\PhpJsCssWebToolkitPkg\Helper;

	use Composer\InstalledVersions;

	class PluginHelper
	{
		/*
		 * Common methods used by the Plugin class and commands
		 */

		public const TOOLKIT='misskittin/php-js-css-web-toolkit';
		public const EXTRAS='misskittin/php-js-css-web-toolkit-extras';
		public const PKG='misskittin/php-js-css-web-toolkit-pkg';

		private static $extraValues=null;

		public static function isToolkitInstalled()
		{
			return InstalledVersions::isInstalled(self::TOOLKIT);
		}
		public static function areExtrasInstalled()
		{
			return InstalledVersions::isInstalled(self::EXTRAS);
		}
		public static function getPackages($composer)
		{
			$packageData=[];

			foreach($composer
			->	getRepositoryManager()
			->	getLocalRepository()
			->	getPackages()
			as $package){
				$packageName=$package->getName();

				switch($packageName)
				{
					case self::TOOLKIT:
					case self::EXTRAS:
					case self::PKG:
						$packageData[$packageName]=[
							$package->getFullPrettyVersion(),
							$composer
							->	getInstallationManager()
							->	getInstallPath($package)
						];
				}
			}

			if(!isset($packageData[self::EXTRAS]))
				$packageData[self::EXTRAS]=['0', null];

			return $packageData;
		}
		public static function getPackageDirs($packageData)
		{
			$dirs['pkg_var']=$packageData[self::PKG][1].'/var';

			$dirs['tk']=$packageData[self::TOOLKIT][1];
			$dirs['tk_bin']=$dirs['tk'].'/bin';
			$dirs['tk_com']=$dirs['tk'].'/com';
			$dirs['tk_lib']=$dirs['tk'].'/lib';

			$dirs['tke']=$packageData[self::EXTRAS][1];
			$dirs['tke_bin']=$dirs['tke'].'/bin';
			$dirs['tke_com']=$dirs['tke'].'/com';
			$dirs['tke_lib']=$dirs['tke'].'/lib';

			return $dirs;
		}
		public static function getPackageVersions($packageData)
		{
			return [
				'toolkit'=>$packageData[self::TOOLKIT][0],
				'extras'=>$packageData[self::EXTRAS][0]
			];
		}
		public static function readExtraEntry($composer, $entry)
		{
			if(self::$extraValues === null)
				self::$extraValues=$composer->getPackage()->getExtra();

			if(isset(self::$extraValues[$entry]))
				return self::$extraValues[$entry];

			return false;
		}
		public static function execPluginTool(
			$dirs,
			$component,
			$tool,
			$args,
			$extras=false
		){
			if($extras)
				$extras='e';
			else
				$extras='';

			if($component === null)
			{
				system('"'.PHP_BINARY.'" '
				.	'"'.$dirs['tk'.$extras.'_bin'].'/'.$tool.'.php" '
				.	$args
				);

				return self::class;
			}

			system('"'.PHP_BINARY.'" '
			.	'"'.$dirs['tk'.$extras.'_com'].'/'.$component.'/bin/'.$tool.'.php" '
			.	$args
			);
		}

		public static function findAvailableTools(
			$dirs,
			$areExtrasInstalled,
			$inComponents=false
		){
			if($inComponents)
			{
				$tools=[];
				$components=array_diff(scandir($dirs['tk_com']), ['.', '..']);

				if(
					$areExtrasInstalled &&
					is_dir($dirs['tke_com'])
				){
					$components=array_merge(
						$components,
						array_diff(scandir($dirs['tke_com']), ['.', '..'])
					);
					sort($components);
				}

				foreach($components as $component)
				{
					$componentBinDir=null;

					switch(true)
					{
						case (is_dir($dirs['tk_com'].'/'.$component.'/bin')):
							$componentBinDir=$dirs['tk_com'].'/'.$component.'/bin';
						break;
						case (is_dir($dirs['tke_com'].'/'.$component.'/bin')):
							$componentBinDir=$dirs['tke_com'].'/'.$component.'/bin';
					}

					if($componentBinDir === null)
						continue;

					foreach(
						array_diff(scandir($componentBinDir), ['.', '..'])
						as $tool
					)
						if(substr($tool, -4) === '.php')
							yield $component.' '.pathinfo($tool, PATHINFO_FILENAME);
				}

				return;
			}

			$tools=array_diff(scandir($dirs['tk_bin']), ['.', '..']);

			if($areExtrasInstalled)
			{
				$tools=array_merge(
					$tools,
					array_diff(scandir($dirs['tke_bin']), ['.', '..'])
				);
				sort($tools);
			}

			foreach($tools as $tool)
				if(substr($tool, -4) === '.php')
					yield pathinfo($tool, PATHINFO_FILENAME);
		}
		public static function findTool(
			$dirs,
			$tool,
			$areExtrasInstalled,
			$component=''
		){
			if($component !== '')
				$component='/com/'.$component;

			if(file_exists($dirs['tk'].$component.'/bin/'.$tool.'.php'))
				return $dirs['tk'].$component.'/bin/'.$tool.'.php';

			if(
				$areExtrasInstalled &&
				file_exists($dirs['tke'].$component.'/bin/'.$tool.'.php')
			)
				return $dirs['tke'].$component.'/bin/'.$tool.'.php';

			return false;
		}
		public static function putenvToolkitPaths($dirs)
		{
			putenv('TK_BIN='.$dirs['tk_bin']);
			putenv('TK_COM='.$dirs['tk_com']);
			putenv('TK_LIB='.$dirs['tk_lib']);

			return self::class;
		}
		public static function execCommandTool($path, $args)
		{
			$exitCode=0;
			$argsLen=count($args);

			for($i=0; $i<$argsLen; ++$i)
				$args[$i]=escapeshellarg($args[$i]);

			$args=implode(' ', $args);

			if(isset($args[0]))
				$args=' '.$args;

			system('"'.PHP_BINARY.'" '
			.	'"'.$path.'"'
			.	$args
			,	$exitCode
			);

			return $exitCode;
		}
	}
?>