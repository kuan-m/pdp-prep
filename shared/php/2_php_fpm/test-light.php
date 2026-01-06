<?php
/**
 * Легкий тестовый скрипт для нагрузки PHP-FPM
 * Выполняет простые вычисления для создания небольшой нагрузки
 */

$start_time = microtime(true);

// Простые вычисления для создания небольшой нагрузки
$result = 0;
for ($i = 0; $i < 10000; $i++) {
    $result += sqrt($i) * sin($i) + cos($i);
}

$end_time = microtime(true);
$execution_time = ($end_time - $start_time) * 1000; // в миллисекундах

// Получаем информацию о PHP-FPM процессе
$fpm_status = [
    'pid' => getmypid(),
    'memory_usage' => memory_get_usage(true),
    'memory_peak' => memory_get_peak_usage(true),
    'execution_time_ms' => round($execution_time, 2),
    'timestamp' => date('Y-m-d H:i:s'),
    'load' => 'light',
];

header('Content-Type: application/json');
echo json_encode($fpm_status, JSON_PRETTY_PRINT);

