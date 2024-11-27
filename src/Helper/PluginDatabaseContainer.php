<?php
	namespace MissKittin\PhpJsCssWebToolkitPkg\Helper;

	class PluginDatabaseContainer
	{
		/*
		 * A container that groups single databases into sections
		 */

		private $databases;

		public function __construct($databases)
		{
			$this->databases=$databases;
		}

		public function set_database_file($database, $database_file)
		{
			$this
			->	databases[$database]
			->	set_database_file($database_file);

			return $this;
		}
		public function get($database, $parameter)
		{
			return $this
			->	databases[$database]
			->	get($parameter);
		}
		public function set($database, $parameter, $value)
		{
			$this
			->	databases[$database]
			->	set($parameter, $value);

			return $this;
		}
		public function save()
		{
			foreach($this->databases as $database)
				$database->save();
		}
	}
?>