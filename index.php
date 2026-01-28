<?php 

include __DIR__ . '/vendor/autoload.php';

header('Content-Type: application/json');

use BuildCake\Utils\Utils;


$_POST = json_decode(file_get_contents('php://input'), true);

Utils::loadEnv(__DIR__ . '/.env');
//after put rate limiter


if ($_ENV['APP_ENV'] !== 'development') { // Evitar enviar erros em ambiente de desenvolvimento
    // Set a sampling rate for profiling - this is relative to traces_sample_rate
   \Sentry\init([
           'dsn' => $_ENV['SENTRY_DSN'] ?: null,
           // Specify a fixed sample rate
           'traces_sample_rate' => 1.0,
           // Set a sampling rate for profiling - this is relative to traces_sample_rate
           'profiles_sample_rate' => 1.0,
           'environment' => $_ENV['APP_ENV'] ?: 'development', // Ambiente (opcional)
           'release' => $_ENV['APP_VERSION'] ?: '1.0.0', // Versão do app (opcional)
   ]);
}

set_exception_handler(function (Throwable $exception) {
   if(isset($_ENV['SENTRY_DSN']) && !empty($_ENV['SENTRY_DSN'])) {Sentry\captureException($exception);}

   Utils::sendResponse(500, [], 'Ocorreu um erro no servidor. Nossa equipe foi notificada.', 
   ['error' => $exception->getMessage(), 'file' => $exception->getFile(), 'line' => $exception->getLine()]);
   exit;
});

set_error_handler(function ($errno, $errstr, $errfile, $errline) {
   $errorMessage = "Erro: $errstr em $errfile na linha $errline";

   if(isset($_ENV['SENTRY_DSN']) && !empty($_ENV['SENTRY_DSN'])) {Sentry\captureMessage($errorMessage);}

   Utils::sendResponse(500, [], 'Ocorreu um erro no servidor. Nossa equipe foi notificada.', ['error' => $errorMessage]);
   exit;
});

Utils::includeFileRequest();