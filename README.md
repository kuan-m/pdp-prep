# План развития

## Что улучшить?

Архитектурное/Инжинерное мышление

## Цель

Превратиться из "пользователя языка" в инженера.

Понимать что происходит при каждом запросе: парсинг, компиляция,
байткод, OPcache.

Понимание архитектуры PHP-приложения и умеение спроектировать
архитектуру с нуля

## Темы для изучения

1.  [PHP lifecycle](docs/1_2_3_lifecycle_php_cgi_fpm_opcache/README.md)

2.  PHP-FPM
    - [Теория](docs/1_2_3_lifecycle_php_cgi_fpm_opcache/README.md#2-разница-php-cgi-и-php-fpm)
    - [Практика](shared/php/2_php_fpm/README.md)

3.  JIT, OPCache
    - [Теория](docs/1_2_3_lifecycle_php_cgi_fpm_opcache/README.md)
    - [Практика](shared/php/3_jit_opcache/README.md)

4.  Autoload/Composer

5.  Роадмап по System Design

## Задачи

1.  Написать PHP-скрипт и отследить его полный lifecycle:

    -   запустить с `strace -f php script.php`,
    -   понять, какие системные вызовы делает PHP при запуске.

2.  Настроить PHP-FPM локально:

    -   изменить параметры `pm`, `max_children`, `max_requests`,
    -   замерить, как меняется производительность при нагрузке (через ab
        или wrk).

3.  Сравнить производительность с/без OPcache и JIT:

    -   включить/отключить JIT, измерить время выполнения нескольких скриптов (арифметика, I/O, JSON).
    -   записать результаты в таблицу.

4.  Разобрать работу autoload:

    -   создать собственный PSR-4 autoloader и сравнить с Composer.
    -   посмотреть, сколько классов реально грузится при старте Laravel.

5.  Нарисовать архитектуру PHP-приложения:

    -   показать цепочку: Nginx → PHP-FPM → OPcache → приложение → Redis → DB.
    -   объяснить, где узкие места и как добавить кэш/очередь.

6.  Спроектировать маленький System Design-кейс: -"Как масштабировать
    Laravel API под 10k RPS?"

7.  System Design мини аналог Instagram

## Ресурсы

1.  https://dev.to/jamir_hossain_8800f85fdd5/how-php-works-behind-the-scene-1eac

2.  https://medium.com/@mgonzalezbaile/demystifying-nginx-and-php-fpm-for-php-developers-bba548dd38f9

3.  https://www.php.net/manual/en/book.opcache.php

4.  https://php.watch/articles/jit-in-depth

5.  https://getcomposer.org/doc/04-schema.md#autoload

6.  https://jinoantony.com/blog/how-composer-autoloads-php-files

7.  https://tideways.com/profiler/blog/autoloading-performance-avoid-these-5-mistakes

8.  https://www.php-fig.org/psr/psr-4/

9.  [System Design Roadmap](https://roadmap.sh/system-design)

10. https://tsh.io/blog/system-design-for-php-developers/
