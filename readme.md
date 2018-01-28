# Spectrumero Interactive Bot

Este dódigo se considera todavía una prueba de concepto, para nada terminado, y de hecho, necesita más bromas insertas como comentarios del mismo, en todo caso, funciona o hace algo muy similar a lo esperado.

### Instalación

El **Webhook** del bot, se encuentra en la carpeta **/roboter**, y se considera el root del proyecto.

Las credenciales de acceso para los diferentes servicios (Telegram, Youtube API, MySQL, etc) deben estar en la carpeta **/roboter/credentials** en un archivo **global.php** similar a este:

```php
<?php

$bot_api_key  = 'TELEGRAM_BOT_API_KEY';
$bot_username = 'BOT_USERNAME';
$yt_api_key = "YT_API_KEY";

// Enter your MySQL database credentials
//$mysql_credentials = [
//    'host'     => 'localhost',
//    'user'     => 'dbuser',
//    'password' => 'dbpass',
//    'database' => 'dbname',
//];

// Define all IDs of admin users in this array (leave as empty array if not used)

$admin_users = [
	"Druellan"
];
```

De necesitar otras credenciales como Google Services, el Json de configuración también puede ser agregado a dicha carpeta.

El resto de las dependencias se pueden instalar mediante **Composer**:

```
composer install
```

### Dependencias

El bot está construido empleando la librería de Armando Lüscher. Documentacipon en https://github.com/php-telegram-bot/core y en ejemplo en: https://github.com/php-telegram-bot/example-bot





