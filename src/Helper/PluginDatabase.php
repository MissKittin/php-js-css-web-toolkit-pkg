<?php
	namespace MissKittin\PhpJsCssWebToolkitPkg\Helper;

	use Exception;

	class PluginDatabaseException extends Exception {}
	class PluginDatabase
	{
		/*
		 * Abstraction for easy management of settings
		 */

		public static $initialDatabase=null;

		private $database_file;
		private $database;
		private $originalDatabase;

		public function __construct($database_file)
		{
			$this->database_file=$database_file;

			if(file_exists($database_file))
			{
				$this->database=json_decode(
					file_get_contents($database_file),
					true
				);
				$this->originalDatabase=$this->database;

				if($this->database !== null)
					return;
			}

			$this->database=self::$initialDatabase;
			$this->originalDatabase=$this->database;
		}

		public function set_database_file($database_file)
		{
			$this->__construct($database_file);
		}
		public function get($parameter)
		{
			if(isset($this->database[$parameter]))
				return $this->database[$parameter];
		}
		public function set($parameter, $value)
		{
			$this->database[$parameter]=$value;
		}
		public function save()
		{
			$databaseAltered=false;

			if(file_exists(dirname($this->database_file)))
				foreach($this->database as $key=>$value)
					if($this->originalDatabase[$key] !== $value)
					{
						$databaseAltered=true;
						break;
					}

			if($databaseAltered)
			{
				$jsonThrowOnError=0;

				if(defined('JSON_THROW_ON_ERROR'))
					$jsonThrowOnError=JSON_THROW_ON_ERROR;

				$jsonData=json_encode(
					$this->database,
					$jsonThrowOnError
				);

				if($jsonData === false)
					throw new PluginDatabaseException('JSON encoding error');

				if(file_put_contents(
					$this->database_file,
					$jsonData
				) === false)
					throw new PluginDatabaseException('Unable to save database ('.$this->database_file.')');
			}

			$this->originalDatabase=$this->database;
		}
	}
?>