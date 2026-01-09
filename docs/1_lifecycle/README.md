
1.  Написать PHP-скрипт и отследить его полный lifecycle:

    -   запустить с `strace -f php script.php`,
    -   понять, какие системные вызовы делает PHP при запуске.

PHP-CGI выполняет шаги 1–5 (запуск среды) (загрузка бинарника, парсинг конфигов, инициализация расширений) на каждый запрос. PHP-FPM делает это один раз при старте master-процесса. Рабочие процессы (workers) уже имеют всё необходимое в памяти и сразу переходят к шагу 6, что ускоряет выполнение в разы.

```mermaid
sequenceDiagram
    autonumber
    participant OS as Operating System
    participant Engine as Zend VM / PHP
    participant OPC as OPcache (SHM)
    participant JIT as JIT Buffer

    Note over OS, Engine: Инициализация (только при старте FPM)
    OS->>Engine: Загрузка бинарника и libc.so.6
    Engine->>Engine: Чтение php.ini и загрузка расширений
    Engine->>OPC: Выделение Shared Memory (shmget/mmap)

    Note over OS, Engine: Обработка запроса
    Engine->>OPC: Есть ли байт-код для script.php?
    
    alt Cache Miss
        Engine->>OS: openat() & read() файла
        Engine->>Engine: Лексический анализ (Tokens)
        Engine->>Engine: Парсинг в AST (Abstract Syntax Tree)
        Engine->>Engine: Компиляция в Opcodes (байт-код)
        Engine->>OPC: Сохранение байт-кода
    else Cache Hit
        OPC-->>Engine: Возврат Opcodes
    end

    loop Исполнение (Execution)
        Engine->>Engine: Выполнение байт-кода (Интерпретация)
        
        opt Если включен JIT (Hot Code)
            Engine->>JIT: Компиляция Opcodes в Native Machine Code
            JIT-->>OS: Выполнение напрямую процессором (CPU)
        end
    end

    Note over Engine: Завершение (Shutdown)
    Engine->>Engine: RSHUTDOWN (очистка памяти запроса)
    Engine->>Engine: Освобождение объектов и деструкторы
```