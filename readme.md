# Spectrumero Interactive Bot

Este BOT se considera todavía una prueba de concepto, para nada terminado, y de hecho, necesita más bromas en los comentarios del código, en todo caso, parece funcionar adecuadamente.

### Instalación

Todas las credenciales necesarias para el funcionamiento del BOT (Telegram), como así también los accesos a los servicios de terceros (Youtube API, etc) deben estar en la carpeta `/credentials`, en un archivo `global.php` similar a este:


```php
<?php

//The direct URL to your webhook, HTTPS
$webhook_url = "URL_TO_YOUR_WEBHOOK";

$bot_api_key  = 'TELEGRAM_BOT_API_KEY';
$bot_username = 'BOT_USERNAME';
$yt_api_key = "YT_API_KEY";

// Canales de Youtube que serán inspeccionados por el comando /quever
$yt_channels = array(
	"Spectrumero Javi Ortiz" => "UCSaVwN8v8iRnys6aZqEVQqA",
	"Darío Ruellan" => "UCVAMJjuVMVPZydNVBAFphwQ"
);

// Canales en los cuales el bot puede interactuar
$allowed_chans = array( "-1001133699410", "-263275991" );

// We use it to normalize the ammount of lines the bot pushes to Telegram
define("OUTPUTLINES", 4);

```

De necesitar otras credenciales como Google Services, el Json de configuración también puede ser agregado a dicha carpeta.

El resto de las dependencias se pueden instalar mediante **Composer**:

```
composer install
```

### Dependencias

El bot está desarrollado empleando la librería de Armando Lüscher. Documentación en https://github.com/php-telegram-bot/core y un ejemplo en: https://github.com/php-telegram-bot/example-bot

### Soporte

Tenemos un canal de desarrollo y soporte en **Telegram**: https://t.me/joinchat/GF2QNg-xRdd9cQFRjoDXNQ Están invitados a hacer preguntas y comentarios.