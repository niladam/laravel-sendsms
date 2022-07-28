# Laravel SendSMS integration

[![Latest Version on Packagist](https://img.shields.io/packagist/v/niladam/laravel-sendsms.svg?style=flat-square)](https://packagist.org/packages/niladam/laravel-sendsms)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/niladam/laravel-sendsms/run-tests?label=tests)](https://github.com/niladam/laravel-sendsms/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/niladam/laravel-sendsms/Check%20&%20fix%20styling?label=code%20style)](https://github.com/niladam/laravel-sendsms/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/niladam/laravel-sendsms.svg?style=flat-square)](https://packagist.org/packages/niladam/laravel-sendsms)

Just a small Laravel package that allows you to use [sendsms.ro](https://www.sendsms.ro/ro/) API.

## Installation

You can install the package via composer:

```bash
composer require niladam/laravel-sendsms
```

You can publish the config file with:

```bash
php artisan vendor:publish --provider="Niladam\LaravelSendsms\LaravelSendsmsServiceProvider" --tag=config
```

This is the contents of the published config file:

```php
<?php

/**
 * This is the package main config file.
 *
 */
return [
    /**
     * This is your main username.
     */
    "username" => env("LARAVEL_SENDSMS_USERNAME", null),

    /**
     * This is your main password.
     */
    "password" => env("LARAVEL_SENDSMS_PASSWORD", null),

    /**
     * This is the base URL that the package will use.
     *
     * It has already been filled with a default value.
     *
     */
    "url" => env("LARAVEL_SENDSMS_URL", "https://api.sendsms.ro/json"),

    /**
     * If this package should have debug turned on
     * please set this here.
     *
     */
    "debug" => env("LARAVEL_SENDSMS_DEBUG", false),

    "messages" => [
        "from" => env("LARAVEL_SENDSMS_FROM", null),
        "callback_url" => env("LARAVEL_SENDSMS_CALLBACK", null),
        "charset" => env("LARAVEL_SENDSMS_CHARSET", null),
        "coding" => env("LARAVEL_SENDSMS_CODING", null),
        "class" => env("LARAVEL_SENDSMS_CLASS", -1),
        "auto_detect_encoding" => env(
            "LARAVEL_SENDSMS_AUTODETECT_ENCODING",
            null
        ),
        /**
         * Information on the report mask:
         *
         *  1   Delivered
         *  2   Undelivered
         *  4   Queued at network
         *  8   Sent to network
         *  16  Failed at network
         *
         * So, 19 means:
         *
         * (Delivered + Undelivered + Failed at network)
         * 1 + 2 + 16 = 19
         */
        "report_mask" => env("LARAVEL_SENDSMS_MASK", 19)
    ],

    /**
     * This is basically a mapping of the operations
     * that the API will use.
     *
     */
    "operations" => [
        "balance" => "user_get_balance",
        "ping" => "ping",
        "price" => "route_check_price",
        "info" => "user_get_info",
        "number" => "user_get_phone_number",
        "send" => "message_send",
    ],
];
```


## Usage

### Sending a message 

```php
use Niladam\LaravelSendsms\SendSmsMessage;

$message = SendSmsMessage::create();

$message->to('0744123123')
        ->message('Example message here.')
        // You can also use the alias ->text('example text')
        // the following is optional, as it'll use your default settings
        ->from('0744123456')
        ->send();
```

OR

```php
use Niladam\LaravelSendsms\SendSmsMessage;

SendSmsMessage::create()
        ->to('0744123123')
        // You can also use the alias ->text('example text')
        ->message('Example message here.')
        // the following is optional, as it'll use your default settings
        ->from('0744123456')
        ->send();
```

OR

```php
SendSmsMessage::create(to: 0744123123, message: 'Example message here')->send();

// Or by specifying the from.

SendSmsMessage::create(to: '0744123123', message: 'Example message here', from: '0744123456')->send();
```

## Other available operations

### ping (check system)

```php
use Niladam\LaravelSendsms\Facades\LaravelSendsms;

LaravelSendSms::ping();

// returns the following output
[
  "status" => 0,
  "message" => "OK",
]
```

### balance (check balance)
```php
use Niladam\LaravelSendsms\Facades\LaravelSendsms;

LaravelSendSms::balance();

// returns the following output
[
  "status" => 0,
  "message" => "OK",
  "details" => 49.76696,
]
```

### price (check pricing for a number)
```php
use Niladam\LaravelSendsms\Facades\LaravelSendsms;

LaravelSendSms::price('0744123123');

// returns the following output
[
  "status" => 0,
  "message" => "OK",
  "details" => [
    "cost" => 0.035, // this is the cost.
    "status" => 64,
    "reason" => "64: Routed OK",
  ],
]
```

### info (get user info)
```php
use Niladam\LaravelSendsms\Facades\LaravelSendsms;

LaravelSendSms::info();

// returns the following output
[
  "status" => 0,
  "message" => "OK",
  "details" => [
    "balance" => "49.76696",
    "name" => "Some Cool Company Name",
    "phone_number" => "",
    "currency_code" => "EUR",
    "default_prefix" => "40",
    "timezone" => "Europe/Bucharest",
    "last_message_sent_at" => "2022-07-28 12:48:37",
    "currency_symbol" => "€ ",
    "affiliate" => [
      "active" => false,
      "id" => null,
      "auth_ip_list" => null,
      "max_registrations" => null,
      "max_registrations_period" => null,
    ],
  ],
]
```

### number
```php
use Niladam\LaravelSendsms\Facades\LaravelSendsms;

LaravelSendSms::number();

// returns the following output
[
  "status" => 0,
  "message" => "OK",
  "details" => "", // This should contain information related to the user's verified phone number.
]
```

### Command
```shell
# The package also publishes a command with the following signature:
# laravel:sendsms {to?} {message?} {from?}
# so you can use it with your tinker.
#
php artisan laravel:sendsms
#
# You will be asked to provide the required details.

# Or you can easily provide them yourself.
php artisan laravel:sendsms "0744123123" "Example message here." "0744123456"
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

- [Madalin Tache](https://github.com/niladam)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
