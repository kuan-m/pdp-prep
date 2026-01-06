<?php
/**
 * Тест производительности: I/O операции
 * 
 * Этот тест проверяет производительность различных операций ввода-вывода:
 * - Чтение/запись файлов
 * - Работа с большими файлами
 * - Множественные операции I/O
 */

// Отключаем вывод ошибок
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');

$startTime = microtime(true);
$startMemory = memory_get_usage();

// Параметры теста
$fileSize = isset($_GET['size']) ? (int)$_GET['size'] : 1024; // KB
$iterations = isset($_GET['iterations']) ? (int)$_GET['iterations'] : 10;
$testDir = sys_get_temp_dir() . '/pdp-prep-io-test';

if ($fileSize < 1 || $fileSize > 10240) {
    $fileSize = 1024;
}
if ($iterations < 1 || $iterations > 100) {
    $iterations = 10;
}

// Создаем тестовую директорию
if (!is_dir($testDir)) {
    mkdir($testDir, 0777, true);
}

$results = [];

// Тест 1: Запись файлов
$writeStart = microtime(true);
$writeFiles = [];
for ($i = 0; $i < $iterations; $i++) {
    $filename = $testDir . '/test_write_' . $i . '.txt';
    $content = str_repeat('A', $fileSize * 1024); // Генерируем контент
    file_put_contents($filename, $content);
    $writeFiles[] = $filename;
}
$writeTime = (microtime(true) - $writeStart) * 1000;

$results['write'] = [
    'files_count' => $iterations,
    'file_size_kb' => $fileSize,
    'total_size_mb' => round(($fileSize * $iterations) / 1024, 2),
    'time_ms' => round($writeTime, 4),
    'time_per_file_ms' => round($writeTime / $iterations, 4),
    'throughput_mb_per_sec' => round((($fileSize * $iterations) / 1024) / ($writeTime / 1000), 2)
];

// Тест 2: Чтение файлов
$readStart = microtime(true);
$totalBytesRead = 0;
for ($i = 0; $i < $iterations; $i++) {
    $content = file_get_contents($writeFiles[$i]);
    $totalBytesRead += strlen($content);
}
$readTime = (microtime(true) - $readStart) * 1000;

$results['read'] = [
    'files_count' => $iterations,
    'total_bytes_read' => $totalBytesRead,
    'total_size_mb' => round($totalBytesRead / 1024 / 1024, 2),
    'time_ms' => round($readTime, 4),
    'time_per_file_ms' => round($readTime / $iterations, 4),
    'throughput_mb_per_sec' => round(($totalBytesRead / 1024 / 1024) / ($readTime / 1000), 2)
];

// Тест 3: Множественные операции чтения/записи
$mixedStart = microtime(true);
$mixedFiles = [];
for ($i = 0; $i < $iterations; $i++) {
    $filename = $testDir . '/test_mixed_' . $i . '.txt';
    $content = str_repeat('B', ($fileSize * 1024) / 2);
    
    // Запись
    file_put_contents($filename, $content);
    
    // Чтение
    $readContent = file_get_contents($filename);
    
    // Модификация
    $readContent .= str_repeat('C', ($fileSize * 1024) / 2);
    file_put_contents($filename, $readContent);
    
    $mixedFiles[] = $filename;
}
$mixedTime = (microtime(true) - $mixedStart) * 1000;

$results['mixed'] = [
    'operations_count' => $iterations * 3, // write + read + write
    'time_ms' => round($mixedTime, 4),
    'time_per_operation_ms' => round($mixedTime / ($iterations * 3), 4)
];

// Тест 4: Работа с большим файлом
$largeFileSize = $fileSize * 10; // 10x больше
$largeFilename = $testDir . '/test_large.txt';
$largeContent = str_repeat('D', $largeFileSize * 1024);

$largeWriteStart = microtime(true);
file_put_contents($largeFilename, $largeContent);
$largeWriteTime = (microtime(true) - $largeWriteStart) * 1000;

$largeReadStart = microtime(true);
$largeReadContent = file_get_contents($largeFilename);
$largeReadTime = (microtime(true) - $largeReadStart) * 1000;

$results['large_file'] = [
    'file_size_kb' => $largeFileSize,
    'file_size_mb' => round($largeFileSize / 1024, 2),
    'write_time_ms' => round($largeWriteTime, 4),
    'read_time_ms' => round($largeReadTime, 4),
    'write_throughput_mb_per_sec' => round(($largeFileSize / 1024) / ($largeWriteTime / 1000), 2),
    'read_throughput_mb_per_sec' => round(($largeFileSize / 1024) / ($largeReadTime / 1000), 2)
];

// Очистка
foreach (array_merge($writeFiles, $mixedFiles, [$largeFilename]) as $file) {
    if (file_exists($file)) {
        unlink($file);
    }
}
if (is_dir($testDir)) {
    rmdir($testDir);
}

$endTime = microtime(true);
$endMemory = memory_get_usage();

$response = [
    'test' => 'File I/O Operations',
    'parameters' => [
        'file_size_kb' => $fileSize,
        'iterations' => $iterations
    ],
    'results' => $results,
    'performance' => [
        'total_time_ms' => round(($endTime - $startTime) * 1000, 4),
        'memory_used_bytes' => $endMemory - $startMemory,
        'memory_used_mb' => round(($endMemory - $startMemory) / 1024 / 1024, 4)
    ],
    'php_config' => [
        'opcache_enabled' => (bool)ini_get('opcache.enable'),
        'opcache_enable_cli' => (bool)ini_get('opcache.enable_cli'),
        'opcache_memory_consumption' => ini_get('opcache.memory_consumption'),
        'opcache_max_accelerated_files' => ini_get('opcache.max_accelerated_files'),
        'opcache_validate_timestamps' => (bool)ini_get('opcache.validate_timestamps'),
        'jit_enabled' => ini_get('opcache.jit') && ini_get('opcache.jit') !== 'disable',
        'jit_buffer_size' => ini_get('opcache.jit_buffer_size'),
        'jit_mode' => ini_get('opcache.jit'),
        'php_version' => PHP_VERSION,
        'opcache_status' => function_exists('opcache_get_status') ? @json_decode(json_encode(@opcache_get_status(false)), true) : null,
        'config_summary' => [
            'opcache' => (bool)ini_get('opcache.enable') ? '✅ Включен' : '❌ Отключен',
            'jit' => (ini_get('opcache.jit') && ini_get('opcache.jit') !== 'disable') ? '✅ Включен (' . ini_get('opcache.jit') . ')' : '❌ Отключен',
            'jit_buffer' => ini_get('opcache.jit_buffer_size'),
            'opcache_memory' => ini_get('opcache.memory_consumption') . ' MB',
            'php_version' => PHP_VERSION
        ]
    ]
];

// Выводим JSON
echo json_encode($response, JSON_PRETTY_PRINT);

