# Moon Mining Manager

This application manages moon-mining revenue and invoicing for EVE Online corporations and alliances.

## Requirements

* PHP 7.1+
* MySQL

## Installation instructions

* Run `composer install` to install dependencies
* Run `php artisan migrate` to create the database tables
* Add entries to `.env` for your chosen character (with Accountant roles) and alliance

## Operation instructions

* Run `php artisan queue:work` to start the job queue process

### EVE tables

* invTypes
* invTypeMaterials
* mapSolarSystems

## License

This application is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).
