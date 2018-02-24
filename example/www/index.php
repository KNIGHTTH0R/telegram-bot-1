<?php
error_reporting(E_ALL);

require __DIR__ . '/../../vendor/autoload.php';

$config = require __DIR__ . '/../auth.php';
list($name, $token, $endpoint) = $config;

$logger = new \Monolog\Logger("telegram-bot");

$webHookBotApi = new \alexshadie\TelegramBot\Bot\BotApi($name, $token, $logger);

$messageDispatcher = new \alexshadie\TelegramBot\MessageDispatcher\MessageDispatcher($webHookBotApi);
$messageDispatcher->addHandler(
    new \alexshadie\TelegramBot\MessageDispatcher\EchoMessageHandler()
);

$bot = new \alexshadie\TelegramBot\Bot\WebHookBot(
    $endpoint,
    realpath(__DIR__ . "/../cert/cert.pem")
);

$bot->setBotApi($webHookBotApi)
    ->setLogger($logger)
    ->setMessageDispatcher($messageDispatcher);

$app = new Silex\Application();
//$app['debug'] = true;
$app->get('/', function () use ($app) {
    return "Telegram bot api test";
});


$app->post("/{$token}", function (\Symfony\Component\HttpFoundation\Request $request) use ($app, $bot) {
    $content = $request->getContent();
    $update = \alexshadie\TelegramBot\Query\Update::createFromObject(json_decode($content));
    $bot->handleUpdate($update);
    return "";
});

$app->run();