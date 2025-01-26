# Nova 4 Multiple DatePicker

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ghanem/multiple-date-picker.svg?style=flat-square)](https://packagist.org/packages/ghanem/multiple-date-picker)
[![Total Downloads](https://img.shields.io/packagist/dt/ghanem/multiple-date-picker.svg?style=flat-square)](https://packagist.org/packages/ghanem/multiple-date-picker)




This [Laravel Nova](https://nova.laravel.com/) package adds the ability to set a Multiple DatePicker in Nova 4

## Requirements

- `php: ^7.3|^8.0`
- `laravel/nova: ^4.0`

## Features

- Select Multiple Dates

## Installation

Install the package in to a Laravel app that uses [Nova](https://nova.laravel.com) via composer:

```bash
composer require ghanem/multiple-date-picker
```

## Usage

### Configuration Options

In Any Nova Resource

```php
// In Any Nova Resource

use Ghanem\MultipleDatePicker\MultipleDatePicker;
.....

MultipleDatePicker::make('Feild Name', 'feild_name')->hideFromIndex(),
```

### Known Issues
- Internal links will result in a full page load rather than following an Inertia Link


## License

Nova Multiple DatePicker is open-sourced software licensed under the [MIT license](LICENSE.md).

## Sponsor

[💚️ Become a Sponsor](https://github.com/sponsors/AbdullahGhanem)
