<?php

namespace App\Command;

use Symfony\Component\Console\Helper\DescriptorHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

class BotListCommand extends Command
{
    protected static $defaultName = 'bot:list';

    protected function configure()
    {
        $this
            ->setDescription('Show awailable commands list')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->setDecorated(false);
        $io->writeln('Commands List:');
        
        $application = $this->getApplication();
        if (!($application instanceof \App\Console\Application)) {
            $io->note('Use list command instead');
            return 0;
        }

        $commandOutput = new BufferedOutput();
        $helper = new DescriptorHelper();
        $helper->describe($commandOutput, $application, [
            'format' => 'json',
            'namespace' => 'bot',
        ]);

        $data = \json_decode($commandOutput->fetch(), true);
        foreach ($data['commands'] ?? [] as $command) {
            $name = $application->denormalizeCommand($command['name']);
            $io->writeln(sprintf('  %s - %s', $name, $command['description']));
        }
        
        return 0;
    }
}
