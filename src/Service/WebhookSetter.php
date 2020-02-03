<?php

namespace App\Service;

use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Method\SetWebhookMethod;

class WebhookSetter
{
    private $bot;

    public function __construct(BotApiComplete $bot)
    {
        $this->bot = $bot;
    }

    public function setWebhook(string $webhookUrl): array
    {
        $result = false;

        try {
            $result = $this->bot->setWebhook(SetWebhookMethod::create($webhookUrl));
            $message = 'Webhook is set sucsesfuly';
        } catch (\Throwable $th) {
            $message = $th->getMessage();
        }

        return [
            'result' => $result ? 'success' : 'fail',
            'url' => $webhookUrl,
            'description' => $message,
        ];
    }
}
