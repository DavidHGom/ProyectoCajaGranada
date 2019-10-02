<?php
// Load composer
require '/opt/telegram-bot/vendor/autoload.php';
$API_KEY = '244175919:AAGGX9gW0ri_kzMx_dgEyIdrCI6Uuz6t1pc';
$BOT_NAME = 'FundacionCGR';
try {
    // Create Telegram API object
    $telegram = new Longman\TelegramBot\Telegram($API_KEY, $BOT_NAME);
    // Delete webhook
    $result = $telegram->deleteWebhook();
    if ($result->isOk()) {
        echo $result->getDescription();
    }
} catch (Longman\TelegramBot\Exception\TelegramException $e) {
    echo $e;
}
