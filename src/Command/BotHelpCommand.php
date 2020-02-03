<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Helper\DescriptorHelper;

class BotHelpCommand extends Command
{
    private $command;
    protected static $defaultName = 'bot:help';

    protected function configure()
    {
        $this
            ->setDescription('Displays help for a bot command')
            ->addArgument('command_name', InputArgument::OPTIONAL, 'Argument description')
        ;
    }

    public function setCommand(Command $command)
    {
        $this->command = $command;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $application = $this->getApplication();
        if (!($application instanceof \App\Console\Application)) {
            $io->note('Use help command instead');
            return 0;
        }

        if (null === $this->command) {
            $this->command = $application->find($input->getArgument('command_name'));
        }

        $commandOutput = new BufferedOutput();
        $helper = new DescriptorHelper();
        $helper->describe($commandOutput, $this->command, ['format' => 'json']);
        $data = \json_decode($commandOutput->fetch(), true);
        $io->writeln('Description:');
        $io->writeln('  ' . $data['description']);
        $io->writeln('Usage:');
        foreach ($data['usage'] as $use) {
            $io->writeln('  ' . $application->denormalizeCommand($use));
        }
        $io->writeln('Arguments:');
        foreach ($data['definition']['arguments'] ?? [] as $argument) {
            $required = $argument['is_required'] ? '*' : ' ';
            $io->writeln(sprintf('  %s %s - %s', $required, $argument['name'], $argument['description']));
        }
        $io->writeln('Options:');
        foreach ($data['definition']['options'] ?? [] as $option) {
            $io->writeln(sprintf('  %s, %s - %s', $option['shortcut'], $option['name'], $option['description']));
        }

        return 0;
    }
}
