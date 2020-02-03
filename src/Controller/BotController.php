<?php

namespace App\Controller;

use App\Service\WebhookSetter;
use App\Service\CommandExecutor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use TgBotApi\BotApiBase\WebhookFetcher;
use TgBotApi\BotApiBase\BotApiNormalizer;
use Psr\Http\Message\ServerRequestInterface;

class BotController extends AbstractController
{
    /**
     * @Route("/webhook", name="bot_webhook")
     */
    public function webhook(ServerRequestInterface $request, CommandExecutor $commandExecutor)
    {
        try {
            $fetcher = new WebhookFetcher(new BotApiNormalizer());
            $update = $fetcher->fetch($request);
        } catch (\Throwable $th) {
            return $this->json([
                'result' => 'fail',
                'description' => $th->getMessage(),
            ]);
        }
        
        if ($commandExecutor->isCommand($update)) {
            $result = $commandExecutor->runCommand(
                $update->message->from->id,
                $update->message->text
            );
        }

        return $this->json($result);
    }

    /**
     * @Route("/set", name="bot_set")
     */
    public function setWebhook(WebhookSetter $webhookSetter)
    {
        $webhookUrl = $this->generateUrl('bot_webhook');
        $result = $webhookSetter->setWebhook($webhookUrl);
        
        return $this->json($result);
    }
}
