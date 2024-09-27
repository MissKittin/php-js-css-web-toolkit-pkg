<?php
	namespace MissKittin\PhpJsCssWebToolkitPkg\Command;

	use Composer\Plugin\Capability\CommandProvider as CommandProviderCapability;

	class CommandProvider implements CommandProviderCapability
	{
		public function getCommands()
		{
			return [
				new TkCommand(),
				new TkcCommand(),
				new TkcpCommand()
			];
		}
	}
?>