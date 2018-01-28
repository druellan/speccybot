# Spectrumero Interactive Bot

Este dódigo se considera todavía una prueba de concepto, para nada terminado, y de hecho, necesita más bromas insertas como comentarios del mismo, en todo caso, funciona o hace algo muy similar a lo esperado.

### Instalación

El **Webhook** del bot, se encuentra en la carpeta raiz, y se considera el root del proyecto.

Las credenciales de acceso para los diferentes servicios (Telegram, Youtube API, MySQL, etc) deben estar en la carpeta **/credentials** en un archivo **global.php** similar a este:

```php
<?php

$bot_api_key  = 'TELEGRAM_BOT_API_KEY';
$bot_username = 'BOT_USERNAME';
$yt_api_key = "YT_API_KEY";

// Canales que serán inspeccionados por el comando /quever
$yt_channels = array(
	"Spectrumero Javi Ortiz" => "UCSaVwN8v8iRnys6aZqEVQqA",
	"Darío Ruellan" => "UCVAMJjuVMVPZydNVBAFphwQ"
);

// Canales en los cuales el bot puede interactuar
$allowed_chans = array( "-1001133699410", "-263275991" );
```

De necesitar otras credenciales como Google Services, el Json de configuración también puede ser agregado a dicha carpeta.

El resto de las dependencias se pueden instalar mediante **Composer**:

```
composer install
```

### Dependencias

El bot está construido empleando la librería de Armando Lüscher. Documentacipon en https://github.com/php-telegram-bot/core y en ejemplo en: https://github.com/php-telegram-bot/example-bot

### Soporte

Tenemos un canal de desarrollo y soporte en **Telegram**: https://t.me/joinchat/GF2QNg-xRdd9cQFRjoDXNQ Están invitados a hacer preguntas y comentarios.