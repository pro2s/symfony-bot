<?php

namespace App\Command;

use App\Service\CommandExecutor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use TgBotApi\BotApiBase\Method\GetUpdatesMethod;
use TgBotApi\BotApiBase\BotApiComplete;

class GetUpdatesCommand extends Command
{
    protected static $defaultName = 'app:get-updates';
    
    private $bot;
    private $commandExecutor;

    public function __construct(BotApiComplete $bot, CommandExecutor $commandExecutor)
    {
        $this->bot = $bot;
        $this->commandExecutor = $commandExecutor;

        parent::__construct();
    }
    
    protected function configure()
    {
        $this
            ->setDescription('Get updates from telegram')
        ;
    }

    protected function getOffsetFile(): string
    {
        return sys_get_temp_dir() . '/' . 'bot_offset';
    }

    protected function saveOffset(int $offset)
    {
        $filesystem = new Filesystem();

        try {
            $filesystem->dumpFile($this->getOffsetFile(), (string) $offset);
        } catch (IOExceptionInterface $exception) {
            echo "An error occurred while creating your directory at " . $exception->getPath();
        }
    }

    protected function getOffset(): int
    {
        $filesystem = new Filesystem();
        
        try {
            if ($filesystem->exists($this->getOffsetFile())) {
                return (int) file_get_contents($this->getOffsetFile());
            }
        } catch (IOExceptionInterface $exception) {
            echo "An error occurred while creating your directory at " . $exception->getPath();
        }

        return 0;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $count = 0;
        $offset = $this->getOffset();
        $updates = $this->bot->getUpdates(GetUpdatesMethod::create(['offset' => $offset + 1]));

        foreach ($updates as $update) {
            if ($this->commandExecutor->isCommand($update)) {
                $this->commandExecutor->runCommand(
                    $update->message->from->id,
                    $update->message->text
                );
                ++$count;
            }
            $offset = $update->updateId;
        }

        $this->saveOffset($offset);

        $io->success(\sprintf('%d updates are processed.', $count));

        return 0;
    }
}
