# AwesomeFileSharing
**AwesomeFileSharing (AFS)** — простой файлообменник на PHP и JavaScript.

## Возможности
- Загрузка фото, видео, аудио, текстовых файлов и архивов.
- Удобный интерфейс.
- Гибкая настройка сайта.
- Понятный код для кастомизации (наверное).

## Установка основного сервера
### Этап 1. Подготовка
1. Клонируйте репозиторий.
2. Установите [Composer](https://getcomposer.org/download/).
3. Выполните в папке `src/api`:
   ```bash
   composer require phpmailer/phpmailer
   ```
4. Переименуйте файл `src/api/config.ex.php` в `src/api/config.php`.
5. Тоже самое с файлом `src/api/config.ex.json`, но только с расширением `.json`.

### Этап 2. Установка базы данных
1. Откройте phpMyAdmin.
2. Создайте базу данных.
3. Импортируйте файл `structure.sql`.
4. Заполните данные доступа к БД в `src/api/config.php`.

### Этап 3. Настройка hCaptcha
1. Авторизуйтесь на [hCaptcha](https://hcaptcha.com/).
2. Перейдите во вкладку [Sites](https://dashboard.hcaptcha.com/sites) и добавьте новый сайт.
3. Скопируйте сгенерированный `sitekey` и вставьте его в `src/api/config.php` и `src/api/config.json`.
5. Получите `secret` на странице [настроек аккаунта](https://dashboard.hcaptcha.com/settings/secrets) и вставьте его в `src/api/config.php`.

### Этап 4. Настройка почты (необязательно)
1. Получите данные для SMTP (сервер, логин, пароль).
2. Укажите следующие параметры в `src/api/config.php`:
   - `smtp_host` — адрес SMTP-сервера.
   - `smtp_username` — логин.
   - `smtp_password` — пароль.
   - `from_email` — адрес отправителя.
   - `from_name` — имя отправителя.

### Этап 5. Лимиты и структура файлов
1. Укажите максимальный размер загружаемого файла (`maxSize`) в `src/api/config.php`.
2. Создайте папку `uploads` в корневом каталоге или измените путь в `src/api/config.php` (`src`).
3. Укажите максимальный объём хранилища (`storageLimit`) в гигабайтах.
4. Укажите домены основного сайта и коротких ссылок (`baseUrl`, `shortBaseUrl`).

### Этап 6. Настройка аккаунтов
1. Если включена email-верификация, установите `emailVerification` в `true`.
2. Настройте длину логина и пароля:
   - `maxUsernameLength` — максимальная длина логина.
   - `minUsernameLength` — минимальная длина логина.
   - `minPasswordLength` — минимальная длина пароля.
3. Укажите параметры проверки паролей (`passwordRequired`).

### Этап 7. Запуск
1. Загрузите сайт на любой хостинг с поддержкой PHP и загрузки файлов.
2. Для локального запуска выполните в папке `src`:
   ```bash
   php -S localhost:8000
   ```
   Сайт будет доступен по адресу `http://localhost:8000`.

## Установка AFSLink
### Этап 1. Подготовка
1. Убедитесь, что основной сервер настроен.
2. Загрузите содержимое папки `afslink` на хост.

### Этап 2. Настройка
1. Скопируйте настройки из `src/api/config.php` в `afslink/config.php`.

### Этап 3. Запуск
Запустите сайт аналогично 7-му этапу установки основного сервера.

## Что использовалось для разработки
- **PHP:** Версии 8.2.
- **MySQL:** Версии 8.4.3.
- **Composer:** Версии 2.8.2.

## Поддержка
Если у вас есть вопросы, обратитесь через [Telegram](https://t.me/kararasenokk) или [GitHub Issues](https://github.com/kararasenok-gd/awesomefilesharing/issues).

Для материальной поддержки воспользуйтесь [этой ссылкой](https://t.me/krrsnkbio/26).

## Авторы
- [@kararasenok-gd](https://github.com/kararasenok-gd) — основной разработчик.
- [@KailUser](https://github.com/KailUser) — дизайнер макета сайта.

---

P.S. Я это через ChatGPT перефразировал ибо изначально это было непонятным :3