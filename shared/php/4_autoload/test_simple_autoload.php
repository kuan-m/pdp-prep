<?php

require __DIR__ . '/psr4_autoloader.php';

$loader = new SimplePsr4Autoloader();

// Маппинг в стиле PSR-4: префикс `App\` → каталог `src/App`
$loader->addNamespace('App', __DIR__ . '/src/App');
$loader->register();

// Класс ещё не загружен, но мы к нему обращаемся — сработает автозагрузка
$service = new \App\Services\ExampleService();

echo $service->sayHello('PSR-4'), PHP_EOL;


