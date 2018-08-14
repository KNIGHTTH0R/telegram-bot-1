<?php

namespace alexshadie\TelegramBot\MessageDispatcher;


use alexshadie\TelegramBot\Bot\BotApi;
use alexshadie\TelegramBot\Query\Message;

class MessageDispatcher implements MessageDispatcherInterface
{
    /**
     * @var MessageHandler[][]
     */
    private $handlers = [];

    /** @var BotApi */
    private $botApi;

    public function __construct(BotApi $botApi)
    {
        $this->botApi = $botApi;
    }

    protected function setupHandler(MessageHandler $handler): void
    {
        // Perform setup operations for each handler, e.g. dependency injections and so on
    }

    public function addHandler(MessageHandler $handler, int $priority = 100): void
    {
        if (!isset($this->handlers[$priority])) {
            $this->handlers[$priority] = [];
            ksort($this->handlers);
        }
        $this->setupHandler($handler);
        $this->handlers[$priority][] = $handler;
    }

    public function dispatch(Message $message): void
    {
        foreach ($this->handlers as $handlerList) {
            foreach ($handlerList as $handler) {
                if ($handler->isSuitable($message)) {
                    $handler->beforeHandle($message);
                    $handler->handle($message, $this->botApi);
                    if ($handler->isTerminator()) {
                        return;
                    }
                }
            }
        }
    }
}