<?php

/**
 * Простой PSR-4 автозагрузчик
 *
 * демонстрирует идею:
 *  - сопоставление namespace-префикса и базового каталога
 *  - преобразование имени класса в путь к файлу
 *  - подключение файла при первом обращении к классу.
 */
class SimplePsr4Autoloader
{
    private array $prefixes = [];

    public function register(): void
    {
        spl_autoload_register([$this, 'loadClass']);
    }

    public function addNamespace(string $prefix, string $baseDir): void
    {
        $prefix = trim($prefix, '\\') . '\\';

        $baseDir = rtrim($baseDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        $this->prefixes[$prefix] ??= [];
        $this->prefixes[$prefix][] = $baseDir;
    }

    public function loadClass(string $class): void
    {
        // проходим по всем известным префиксам
        foreach ($this->prefixes as $prefix => $baseDirs) {
            // класс должен начинаться с этого префикса
            if (str_starts_with($class, $prefix)) {
                // относительное имя класса (без префикса)
                $relativeClass = substr($class, strlen($prefix));

                // меняем namespace-сепараторы на слеши и добавляем .php
                $relativePath = str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';

                foreach ($baseDirs as $baseDir) {
                    $file = $baseDir . $relativePath;
                    if (is_file($file)) {
                        require $file;
                        return;
                    }
                }
            }
        }
    }
}


