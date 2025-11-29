<?php
/**
 * Тест производительности: Обработка строк
 * 
 * Этот тест проверяет производительность различных операций со строками:
 * - Подсчет вхождений подстрок (без strpos, через циклы)
 * - Замена символов в цикле
 * - Парсинг и обработка текста
 * - Генерация и обработка больших строк
 * 
 * Эти операции нагружают CPU множеством циклов по символам строк.
 */

// Отключаем вывод ошибок
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');

$startTime = microtime(true);
$startMemory = memory_get_usage();

// Параметры теста
$textSize = isset($_GET['size']) ? (int)$_GET['size'] : 10000; // Длина текста в символах
$iterations = isset($_GET['iterations']) ? (int)$_GET['iterations'] : 100;

// Ограничиваем параметры
if ($textSize < 100 || $textSize > 100000) {
    $textSize = min(max($textSize, 100), 100000);
}
if ($iterations < 1 || $iterations > 1000) {
    $iterations = min(max($iterations, 1), 1000);
}

/**
 * Генерация случайного текста
 */
function generateText($size) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789 ';
    $text = '';
    $charsLen = strlen($chars);
    for ($i = 0; $i < $size; $i++) {
        $text .= $chars[rand(0, $charsLen - 1)];
    }
    return $text;
}

/**
 * Подсчет вхождений подстроки (без использования strpos)
 */
function countSubstring($text, $substring) {
    $count = 0;
    $textLen = strlen($text);
    $subLen = strlen($substring);
    
    for ($i = 0; $i <= $textLen - $subLen; $i++) {
        $match = true;
        for ($j = 0; $j < $subLen; $j++) {
            if ($text[$i + $j] !== $substring[$j]) {
                $match = false;
                break;
            }
        }
        if ($match) {
            $count++;
        }
    }
    
    return $count;
}

/**
 * Замена символов в цикле
 */
function replaceChars($text, $from, $to) {
    $result = '';
    $len = strlen($text);
    for ($i = 0; $i < $len; $i++) {
        if ($text[$i] === $from) {
            $result .= $to;
        } else {
            $result .= $text[$i];
        }
    }
    return $result;
}

/**
 * Подсчет слов (разделитель - пробел)
 */
function countWords($text) {
    $count = 0;
    $len = strlen($text);
    $inWord = false;
    
    for ($i = 0; $i < $len; $i++) {
        if ($text[$i] === ' ') {
            $inWord = false;
        } else {
            if (!$inWord) {
                $count++;
                $inWord = true;
            }
        }
    }
    
    return $count;
}

/**
 * Реверс строки (без strrev)
 */
function reverseString($text) {
    $result = '';
    $len = strlen($text);
    for ($i = $len - 1; $i >= 0; $i--) {
        $result .= $text[$i];
    }
    return $result;
}

/**
 * Удаление дубликатов символов
 */
function removeDuplicates($text) {
    $result = '';
    $seen = [];
    $len = strlen($text);
    
    for ($i = 0; $i < $len; $i++) {
        $char = $text[$i];
        if (!isset($seen[$char])) {
            $result .= $char;
            $seen[$char] = true;
        }
    }
    
    return $result;
}

/**
 * Подсчет частоты символов
 */
function countCharFrequency($text) {
    $freq = [];
    $len = strlen($text);
    
    for ($i = 0; $i < $len; $i++) {
        $char = $text[$i];
        if (!isset($freq[$char])) {
            $freq[$char] = 0;
        }
        $freq[$char]++;
    }
    
    return $freq;
}

$results = [];

// Генерируем тестовый текст
$testText = generateText($textSize);
$substring = 'abc'; // Подстрока для поиска

// Тест 1: Подсчет вхождений подстроки
$countStart = microtime(true);
$totalCount = 0;
for ($i = 0; $i < $iterations; $i++) {
    $totalCount += countSubstring($testText, $substring);
}
$countTime = (microtime(true) - $countStart) * 1000;

$results['substring_count'] = [
    'text_size' => $textSize,
    'iterations' => $iterations,
    'total_matches' => $totalCount,
    'time_ms' => round($countTime, 4),
    'time_per_iteration_ms' => round($countTime / $iterations, 4),
    'operations_per_second' => round($iterations / ($countTime / 1000), 2)
];

// Тест 2: Замена символов
$replaceStart = microtime(true);
for ($i = 0; $i < $iterations; $i++) {
    $replaced = replaceChars($testText, 'a', 'X');
}
$replaceTime = (microtime(true) - $replaceStart) * 1000;

$results['char_replace'] = [
    'text_size' => $textSize,
    'iterations' => $iterations,
    'time_ms' => round($replaceTime, 4),
    'time_per_iteration_ms' => round($replaceTime / $iterations, 4),
    'operations_per_second' => round($iterations / ($replaceTime / 1000), 2)
];

// Тест 3: Подсчет слов
$wordsStart = microtime(true);
$totalWords = 0;
for ($i = 0; $i < $iterations * 10; $i++) {
    $totalWords += countWords($testText);
}
$wordsTime = (microtime(true) - $wordsStart) * 1000;

$results['word_count'] = [
    'text_size' => $textSize,
    'iterations' => $iterations * 10,
    'total_words' => $totalWords,
    'time_ms' => round($wordsTime, 4),
    'time_per_iteration_ms' => round($wordsTime / ($iterations * 10), 4),
    'operations_per_second' => round(($iterations * 10) / ($wordsTime / 1000), 2)
];

// Тест 4: Реверс строки
$reverseStart = microtime(true);
for ($i = 0; $i < $iterations; $i++) {
    $reversed = reverseString($testText);
}
$reverseTime = (microtime(true) - $reverseStart) * 1000;

$results['string_reverse'] = [
    'text_size' => $textSize,
    'iterations' => $iterations,
    'time_ms' => round($reverseTime, 4),
    'time_per_iteration_ms' => round($reverseTime / $iterations, 4),
    'operations_per_second' => round($iterations / ($reverseTime / 1000), 2)
];

// Тест 5: Удаление дубликатов
$dedupStart = microtime(true);
for ($i = 0; $i < $iterations / 10; $i++) {
    $deduplicated = removeDuplicates($testText);
}
$dedupTime = (microtime(true) - $dedupStart) * 1000;

$results['remove_duplicates'] = [
    'text_size' => $textSize,
    'iterations' => max(1, $iterations / 10),
    'time_ms' => round($dedupTime, 4),
    'time_per_iteration_ms' => round($dedupTime / max(1, $iterations / 10), 4),
    'operations_per_second' => round(max(1, $iterations / 10) / ($dedupTime / 1000), 2)
];

// Тест 6: Подсчет частоты символов
$freqStart = microtime(true);
for ($i = 0; $i < $iterations; $i++) {
    $frequency = countCharFrequency($testText);
}
$freqTime = (microtime(true) - $freqStart) * 1000;

$results['char_frequency'] = [
    'text_size' => $textSize,
    'iterations' => $iterations,
    'unique_chars' => count($frequency),
    'time_ms' => round($freqTime, 4),
    'time_per_iteration_ms' => round($freqTime / $iterations, 4),
    'operations_per_second' => round($iterations / ($freqTime / 1000), 2)
];

$endTime = microtime(true);
$endMemory = memory_get_usage();

$response = [
    'test' => 'String Processing',
    'parameters' => [
        'text_size' => $textSize,
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
try {
    $json = json_encode($response, JSON_PRETTY_PRINT);
    if ($json === false) {
        http_response_code(500);
        echo json_encode([
            'error' => 'Failed to encode JSON',
            'json_error' => json_last_error_msg(),
            'memory_used' => round(memory_get_usage() / 1024 / 1024, 2) . ' MB'
        ], JSON_PRETTY_PRINT);
    } else {
        echo $json;
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to output JSON',
        'message' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}

