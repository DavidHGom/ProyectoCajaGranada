<?php

require '../slim/vendor/autoload.php';

#$app = new \Slim\App;

$app = new \Slim\App([
    'settings' => [
        'displayErrorDetails' => true
    ]
]);

require 'libs/connect.php';
require 'routes/api.php';

$app->run();

?>
