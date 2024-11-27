<?php
	namespace MissKittin\PhpJsCssWebToolkitPkg\Command;

	use Composer\Command\BaseCommand;
	use Symfony\Component\Console\Input\InputInterface;
	use Symfony\Component\Console\Output\OutputInterface;

	class Tkmk extends BaseCommand
	{
		protected function println($output, $line)
		{
			if($line === '')
			{
				$output->write(PHP_EOL);
				usleep(250000);

				return;
			}

			$output->write('<info>');

			foreach(str_split($line) as $char)
			{
				$output->write($char);
				usleep(35000);
			}

			$output->write('</info>'.PHP_EOL);
			usleep(250000);
		}

		protected function configure(): void
		{
			$this->setName('tkmk');
		}
		protected function execute(InputInterface $input, OutputInterface $output): int
		{
			foreach([
				'Let\'s go to the rendez-vouz',
				'Of the past, me and you',
				'DJ plays deja-vu',
				'As we were in 82',
				'',
				'I don\'t want a tainted love',
				'I want something from above',
				'Imagine you\'re dancing',
				'You\'re a robot, man machine',
				'',
				'I just can\'t get enough',
				'',
				'I don\'t want a tainted love',
				'I want something from above',
				'But don\'t go',
				'Just play me Moscow Discow'
			] as $line)
				$this->println($output, $line);

			return 0;
		}
	}
?>