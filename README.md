# Moon Mining Manager

This application manages moon-mining revenue and invoicing for EVE Online corporations and alliances.

## Requirements

* PHP 7.1+
* MySQL

## Installation instructions

* Run `composer install` to install backend dependencies
* Run `npm install` to install frontend dependencies
* Run `php artisan migrate` to create the database tables
* Rename the `.env.example` file to `.env` and add values for your application ID and secret, chosen prime character (must have director role within the corporation) and alliance, and whitelisted alliances/corporations

## Operation instructions

* Run `php artisan queue:work` to start the job queue process. See the [Laravel documentation on Queues](https://laravel.com/docs/5.5/queues) for more information on how to use Supervisor to manage job queues.
* Have your primary user login to the application. They must have director roles within the corporation that owns your refineries in order to retrieve citadel information.
* Manually add the primary user's ID to the `whitelist` table. They can now log in to view the application and authorise any other users.

### EVE tables

You will need to import the following EVE dump tables into your database. They can be downloaded from [Fuzzworks](https://www.fuzzwork.co.uk/dump/latest/).

* invTypes
* invTypeMaterials
* mapSolarSystems
* mapRegions

## License

This application is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).
