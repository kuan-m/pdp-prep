# PDP Prep

Практическое руководство по углубленному изучению PHP, архитектуры и системного дизайна.

Этот репозиторий является реализацией PDP, составленного для меня старшим разработчиком. Проект также даст возможность «пощупать» сравнительные тесты производительности и наглядно взглянуть на них. И поможет превратиться из "пользователя языка" в инженера, понимающего, как работает код на низком уровне: от жизненного цикла запроса до оптимизации FPM и работы JIT-компилятора.

## Как пользоваться репозиторием?

Для работы с проектом используется Kubernetes (Minikube) и Tilt для удобной разработки.

1.  Установите [**Minikube**](https://minikube.sigs.k8s.io/docs/start/?arch=%2Fwindows%2Fx86-64%2Fstable%2F.exe+download).
2.  Запустите кластер:
    ```bash
    minikube start
    ```
3.  Установите [**Tilt**](https://docs.tilt.dev/install.html).
4.  Поднимите окружение:
    ```bash
    tilt up
    ```
5.  Откройте интерфейс Tilt (ссылка появится в терминале) и нажмите "Open port" для доступа к сервисам.

---

## 1. PHP Lifecycle

Разбираем, что происходит при запуске скрипта: системные вызовы, инициализация и завершение.

**Запуск:**
```bash
make lifecycle
```

- [Теория](docs/1_2_3_lifecycle_php_cgi_fpm_opcache/README.md)
- [Практика](shared/php/1_lifecycle/README.md)

---

## 2. Мониторинг и сравнения PHP-FPM

Изучаем метрики, настройку пулов FPM и поведение под нагрузкой.

**Действия:**
1.  Откройте Графану через Tilt.
2.  Перейдите в дешборд **Monitoring**.
3.  Запустите нагрузочные тесты из корня проекта:

```bash
make test-high   # Высокая нагрузка (для проверки max_children)
make test-mid    # Средняя нагрузка
make test-go     # Сравнение с Go сервисом
```

- [Теория](docs/1_2_3_lifecycle_php_cgi_fpm_opcache/README.md#2-разница-php-cgi-и-php-fpm)
- [Практика](shared/php/2_php_fpm/README.md)

---

## 3. JIT и Opcache

Сравниваем производительность: чистый PHP vs Opcache vs JIT.

**Действия:**
1.  В интерфейсе Tilt найдите микросервис `web`.
2.  В открывшемся UI запустите тесты для 3 микросервисов, чтобы увидеть разницу в скорости обработки запросов.

- [Теория](docs/1_2_3_lifecycle_php_cgi_fpm_opcache/README.md)
- [Практика](shared/php/3_jit_opcache/README.md)

---

## 4. Autoload

Разбираем механизмы автозагрузки классов (PSR-4, Composer) и их влияние на производительность.

**Команды:**

```bash
make simple-autoload        # Запуск простого примера автозагрузки
make laravel-autoload       # Подсчет загруженных классов в Laravel
make laravel-autoload-http  # Запрос к Laravel через HTTP для анализа
```

---

## 5. Архитектура PHP-приложения

Разбираем полную цепочку обработки запроса: Nginx → PHP-FPM → OPcache → приложение → Redis → DB.

- [Теория](docs/5_php_app_architecture/README.md)

---

## 6. Как масштабировать Laravel API под 10k RPS? (TODO)

---

## 7. System Design Instagram (TODO)

---
`Ниже находится детальный план, составленный старшим разработчиком`
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

1.  PHP lifecycle

2.  PHP-FPM

3.  JIT, OPCache

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

6.  Спроектировать маленький System Design-кейс: 
    -   "Как масштабировать Laravel API под 10k RPS?"

7.  System Design Instagram

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
