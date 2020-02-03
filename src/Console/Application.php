<?php

namespace App\Console;

use Symfony\Bundle\FrameworkBundle\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Console\Exception\NamespaceNotFoundException;
use Symfony\Component\Console\Exception\CommandNotFoundException;

class Application extends BaseApplication
{
    private const COMMAND_ENCODING = 'UTF-8';
    private const COMMAND_START = '/';
    private const COMMAND_PREFIX = 'bot:';

    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        $command = parent::get($name);

        if ($command instanceof \Symfony\Component\Console\Command\HelpCommand) {
            $helpCommand = $this->get(self::COMMAND_PREFIX . 'help');
            $helpCommand->setCommand($command);

            return $helpCommand;
        }

        return $command;
    }

    /**
     * {@inheritdoc}
     */
    public function __construct(KernelInterface $kernel)
    {
        parent::__construct($kernel);
        
        $inputDefenition = new InputDefinition([
            new InputArgument('command', InputArgument::REQUIRED, 'The command to execute'),
            new InputOption('--help', '-h', InputOption::VALUE_NONE, 'Display this help message'),
            new InputOption('--quiet', '-q', InputOption::VALUE_NONE, 'Do not output any message'),
            new InputOption('--version', '-V', InputOption::VALUE_NONE, 'Display this application version'),
        ]);
        
        $this->setDefinition($inputDefenition);
    }
    
    /**
     * {@inheritdoc}
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $input->setInteractive(false);

        if (true === $input->hasParameterOption(['--quiet', '-q'], true)) {
            $output->setVerbosity(OutputInterface::VERBOSITY_QUIET);
        }

        try {
            return parent::doRun($input, $output);
        } catch (NamespaceNotFoundException $e) {
            $output->write('Bot Commands are not defined');
        } catch (CommandNotFoundException $e) {
            $error = sprintf(
                "Command \"%s\" is not defined.\n",
                $this->denormalizeCommand($this->getCommandName($input))
            );
            
            $alternatives = array_filter(array_map(
                function ($alternative) {
                    return $this->denormalizeCommand($alternative);
                },
                $e->getAlternatives()
            ));

            if ($alternatives) {
                if (1 == \count($alternatives)) {
                    $error .= "Did you mean this?\n    ";
                } else {
                    $error .= "Did you mean one of these?\n    ";
                }
                $error .= implode("\n    ", $alternatives);
            }

            $output->writeln($error);
        }

        return -1;
    }

    public function normalizeCommand(string $command): string
    {
        return self::COMMAND_PREFIX . \mb_strcut($command, 1, null, self::COMMAND_ENCODING);
    }

    public function denormalizeCommand(string $command): string
    {
        if (strpos($command, self::COMMAND_PREFIX) === 0) {
            return self::COMMAND_START . substr($command, strlen(self::COMMAND_PREFIX));
        }
        
        return '';
    }

    public function isCommand(string $command): bool
    {
        return \mb_substr($command, 0, 1, self::COMMAND_ENCODING) === self::COMMAND_START;
    }
}
