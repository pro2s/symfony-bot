<?php

namespace App\Service;

use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Method\SendMessageMethod;
use TgBotApi\BotApiBase\Type\UpdateType;
use App\Console\Application;

class CommandExecutor
{
    private $bot;
    private $application;

    public function __construct(BotApiComplete $bot, Application $application)
    {
        $this->bot = $bot;
        $this->application = $application;
    }
    
    public function isCommand(UpdateType $update): bool
    {
        return $update->message
            && $update->message->text
            && $this->application->isCommand($update->message->text);
    }

    public function runCommand(int $userId, string $command): array
    {
        $empty = 'Fail';

        $command = $this->application->normalizeCommand($command);
        $input = new StringInput($command);
        $output = new BufferedOutput();
                
        try {
            if ($this->application->doRun($input, $output) === 0) {
                $empty = 'Done';
            }
        } catch (\Throwable $th) {
            $empty = 'Something went wrong';
        }
        
        $message = $output->fetch() ?: $empty;
        $this->bot->sendMessage(SendMessageMethod::create($userId, $message));

        return [
            'result' => 'success',
            'input' => $command,
            'output' => $message,
        ];
    }
}
