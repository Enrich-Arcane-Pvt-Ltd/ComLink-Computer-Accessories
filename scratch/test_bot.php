<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$ctrl = app(App\Http\Controllers\Public\ChatBotController::class);
$req = Illuminate\Http\Request::create('/chatbot/query', 'POST', [
    'message' => 'oyt puluvn d mata guide karanna , kohom d me web ekata register vela log venne kiyala',
]);
$res = $ctrl->query($req);
echo $res->getContent() . PHP_EOL;

