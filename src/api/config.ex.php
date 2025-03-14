<?php

return [
    "database" => [
        "host" => "", // Сервер базы данных
        "user" => "", // Логин
        "password" => "", // Пароль
        "database" => "" // Название базы данных
    ],
    "hcaptcha" => [
        "sitekey" => "", // Ключ сайта
        "secret" => "" // Секретный ключ для проверки капчи
    ],
    "mail" => [
        // Если ты не планируешь использовать верификацию по почте, то оставь это всё пустым и поставь "emailVerification" на false ("emailVerification" => false).
        // Если у вас на домене настроен почтовый сервак, то подставьте его значения тут или перепишите весь бекэнд на mail()
        'smtp_host' => '', // Хост SMTP
        'smtp_auth' => true,
        'smtp_username' => '', // Логин
        'smtp_password' => '', // Пароль
        'smtp_secure' => 'tls', // 'tls' или 'ssl'
        'smtp_port' => 587, // 587 для TLS, 465 для SSL
        'from_email' => '', // Адрес отправителя. Тут может быть что угодно, хоть mail@example.com
        'from_name' => 'AWESOME FILE SHARING!!!' // Имя отправителя. Тут также может быть что угодно
    ],
    "files" => [
        "maxSize" => 10 * 1024 * 1024, // Вместо 10 - размер в мегабайтах. Или замени это математическое выражение на количество байт
        "src" => "../../uploads/",
        "maxFilesInOneTask" => 10, // Сейчас этот параметр не используется
        "baseUrl" => "https://server/file?name=", // Вместо server - ваш домен
        "shortBaseUrl" => "https://server/?code=", // Вместо server - ваш домен для коротких ссылок. Если не используется, то оставь пустым
        "storageLimit" => 0 * 1024 * 1024 * 1024 // Вместо 0 - размер в гигабайтах. Или замени это математическое выражение на количество байт
    ],
    "accounts" => [
        "emailVerification" => false, // true - если хотите верифицировать по почте, false - если не хотите
        "maxUsernameLength" => 64, // Вместо 64 - любое число
        "minUsernameLength" => 3, // Вместо 3 - любое число
        "minPasswordLength" => 6, // Вместо 6 - любое число
        "passwordRequired" => [
            "uppercase" => false, // true - если хотите проверять заглавные буквы, false - если не хотите
            "numbers" => false, // true - если хотите проверять цифры, false - если не хотите
            "symbols" => false // true - если хотите проверять спец символы, false - если не хотите
        ],
        "registration" => true // true - если хотите разрешить регистрацию, false - если нет
    ],
    "meta" => [
        "title" => "AWESOME FILE SHARING", // Вместо AWESOME FILE SHARING - любое название
        "version" => "25.03.07.2" // Тут может быть что угодно. Трогать не советую
    ],
    "short" => [
        "expireTime" => 7 * 24 * 60 * 60 // Вместо 7 - количество дней
    ],
    "admin" => [
        "enabled" => true, // Включает/отключает админ панель (https://.../admin, где ... - домен сайта)
        "allowed_ids" => [1] // Разрешенные user_id для доступа
    ]
];