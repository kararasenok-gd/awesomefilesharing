# AwesomeFileSharing
 AwesomeFileSharing (AFS) - простенький файлообменник на PHP и JavaScript

## Возможности
- Возможность загружать Фото, Видео, Аудио, Текстовые файлы и Архивы
- Удобный интерфейс
- Гибкая настройка своего сайта
- Довольно понятный код

## Установка - Основной сервер
### Этап 1 - Подготовка
1. Клонируйте репозиторий
2. Установите [Composer](https://getcomposer.org/download/)
3. В папке `src/api` выполните 
```bash
composer install phpmailer/phpmailer
```
4. Переименуйте файл `src/api/config.ex.php` в `src/api/config.php`

### Этап 2 - Установка БД
1. Зайдите в phpMyAdmin
2. Создайте базу данных
3. Импортируйте `structure.sql` в базу данных
4. Скопируйте и вставьте данные для доступка к БД в файле `src/api/config.php`

### Этап 3 - hCaptcha
1. Зайдите на [hCaptcha](https://hcaptcha.com/) и авторизуйтесь
2. Дальше идите во вкладку [Sites](https://dashboard.hcaptcha.com/sites) и нажмите `Add Site`
3. Создайте ключ сайта и вставьте его в файл `src/api/config.php` (`sitekey`)
4. Замените `05a2ac4e-50ac-489c-afe4-cfec231200c6` в файлах `src/login/index.html` и `src/templates/upload.html` на ваш ключ
5. Идём в [Настройки аккаунта](https://dashboard.hcaptcha.com/settings/secrets) и получаем `secret`
6. Вставляем его в `src/api/config.php` (`secret`)

### Этап 4 - Почта (не обязательно)
1. Подготовте данные от почтового сервиса (SMTP)
2. Вставьте SMTP-сервер в файл `src/api/config.php` (`smtp_host`)
3. Вставьте логин в файл `src/api/config.php` (`smtp_username`)
4. Вставьте пароль в файл `src/api/config.php` (`smtp_password`)
5. Напишите адрес отправителя в файл `src/api/config.php` (`from_email`)
6. Напишите имя отправителя в файл `src/api/config.php` (`from_name`)

### Этап 5 - Файлы и лимиты
1. Укажите вместо `00` в файле `src/api/config.php` максимальный размер одного файла в мегабайтах (`maxSize`)
2. Создайте папку `uploads` в корневом каталоге. Имя может быть любое, но придётся менять в файле `src/api/config.php` (`src`)
3. Укажите вместо `0` в файле `src/api/config.php` максимальный размер хранилища в гигабайтах (`storageLimit`)
4. Укажите вместо `server` в файле `src/api/config.php` домены основного сайта и коротких ссылок (`baseUrl`, `shortBaseUrl`)

### Этап 6 - Аккаунты
1. Если вы не пропускали Этап 4, то замените `emailVerification` в файле `src/api/config.php` на `true`
2. Укажите вместо `64` в файле `src/api/config.php` максимальную длину логина (`maxUsernameLength`)
3. Укажите вместо `3` в файле `src/api/config.php` минимальную длину логина (`minUsernameLength`)
4. Укажите вместо `6` в файле `src/api/config.php` минимальную длину пароля (`minPasswordLength`)
5. В `passwordRequired` выберите что хотите проверять в пароле и укажите `true` вместо `false`

### Этап 7 - Запуск
1. На этом моменте уже можно размещать сайт на любом хостинге, который поддерживает PHP и перемещение файлов.
2. Если желаете запустить на локальном сервере, то запустите в папке `src` команду 
```bash
php -S localhost:8000
```
Локальный сервер будет работать на порту `8000`.

## Установка - AFSLink
### Этап 1 - Подготовка
1. Сначала надо настроить основной сервер
2. Загрузите содержимое папки `afslink` на хост

### Этап 2 - Настройка
### Настройка - Вариант 1
Если основной сервер и AFSLink на одном сервере, то:
1. Зайдите в файл `afslink/index.php` и замените `../../htdocs/api/config.php` на путь к вашему файлу `config.php` на основном сервере (18 строка)

### Настройка - Вариант 2
Если основной сервер и AFSLink на разных серверах, то:
1. Скопируйте `src/api/config.php` в папку `afslink`
2. Зайдите в файл `afslink/index.php` и замените `../htdocs/api/config.php` на путь к вашему файлу `config.php` (18 строка)

### Этап 3 - Запуск
1. Тут также как и в 7 этапе установки основного сервера

## Поддержка
На случай если есть вопросы, то можете написать в [Telegram](https://t.me/kararasenokk) или в [GitHub](https://github.com/kararasenok-gd/awesomefilesharing/issues).

Если хотите поддержать материально, то [тут](https://t.me/krrsnkbio/26) указаны все способы поддержки.

## Авторы
- [@kararasenok-gd](https://github.com/kararasenok-gd) - Скодил все это
- [@KailUser](https://github.com/KailUser) - Создал макет сайта