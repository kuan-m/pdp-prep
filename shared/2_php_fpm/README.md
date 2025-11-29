
## Тема
2.  PHP-FPM

## Практика
2.  Настроить PHP-FPM локально:

    -   изменить параметры `pm`, `max_children`, `max_requests`,
    -   замерить, как меняется производительность при нагрузке (через ab
        или wrk).

## Ресурсы
2. [Demystifying Nginx and PHP-FPM for PHP Developers](https://medium.com/@mgonzalezbaile/demystifying-nginx-and-php-fpm-for-php-developers-bba548dd38f9)


### Войти в контейнер
```bash
docker exec -it pdp-prep-tools sh
```

### Запустить отправку запросов
```bash
npx autocannon -c 100 -d 30 http://pdp-nginx/2_php_fpm/test.php
```