
<?php
include('vendor/autoload.php');
define('BOT_TOKEN', '321826553:AAHa6cgTEk5rcByssenEffpnxPM73umTXsk');

use Telegram\Bot\Api;

function enviar_mensaje_asistencia($mensaje, $chat_id){
	try{
		$telegram = new Api('BOT TOKEN');

		$keyboard = [
		    ['Sí', 'No']
		];

		$reply_markup = $telegram->replyKeyboardMarkup([
			'keyboard' => $keyboard,
			'resize_keyboard' => true,
			'one_time_keyboard' => true
		]);


		$response = $telegram->sendMessage([
			'chat_id' => $chat_id,
			'text' => $mensaje,
			'reply_markup' => $reply_markup
		]);

		$messageId = $response->getMessageId();
	}catch(\Exception $e){
		echo $e;
	}
}

enviar_mensaje_asistencia("Hola", "10079511");
