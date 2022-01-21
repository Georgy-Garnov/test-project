### Тестовое задание.

#### Техническое задание

[Исходная задача - ТЗ](./requirements.md)

#### База данных

В качестве базы данных используется MySQL. 

[Структура БД](./database.md)

#### Среда выполнения проекта

В качестве среды выполнения проекта используется Docker.
Для компоновки контейнеров используется Docker Compose.
Контейнеры расположены в папке **docker**

В составе проекта 3 контейнера:

* Контейнер database - сервер БД mysql:5.7.34
  Внутри папки контейнера находиться папка **data** в ней располагаются файлы с базами данных
* Контейнер app - сервер приложения php-fpm версия php 8.1
  Директория с исполняемыми PHP файлами зеркалирована в папку /src/public
* Контейнер nginx - веб сервер Nginx

#### Развертывание проекта

* [ ] Установить Docker
* [ ] Установить Docker-compose
* [ ] Перейти в папку /docker
* [ ] Выполнить команду ```docker-compose up -d```
* [ ] Перейти по адресу http://localhost:83
* [ ] Если загружается стартовая страница Symfony значит все работает
* [ ] Остановка контейнера docker-compose stop

#### Обновление курсов валют

Для обновления курсов валют используется сервис http://currate.ru/account
Необходимо получить ключ для запросов по API. 
Для этого:

* [ ] Перейти по адресу сервиса
* [ ] Запросить получение ключа указав свой адрес электронной почти
* [ ] Проверить почту - получить ключ
* [ ] Внести полученный ключ в настройки приложения - файл src/.env - настройка - RATES_KEY
* [ ] перейти в папку /docker
* [ ] выполнить команду 
  ```docker-compose exec app php /var/www/bin/console app:refresh-rates```

#### Описание REST сервисов проекта