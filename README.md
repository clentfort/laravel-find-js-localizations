# Find Localizations in Laravel JavaScript Assets

A tools that helps finding untranslated strings in Laravel JavaScript assets for
projects using [rmariuzzo/Laravel-JS-Localization][rmariuzzo] and
[andywer/laravel-js-localization][andywer]. Inspired by
[potsky/laravel-localization-helpers][potsky].

[rmariuzzo]: https://github.com/rmariuzzo/Laravel-JS-Localization
[andywer]:  https://github.com/andywer/laravel-js-localization
[potsky]: https://github.com/potsky/laravel-localization-helpers

# Installation

The package consists out of two parts; a PHP part, available through composer,
and JavaScript part available through npm. First install both parts by running
the following:

> WARNING: At this point the package is not stable yet! If you want to use it
> set the minimum stability of your project to "dev" by adding
> `"minimum-stability": "dev"` to your `composer.json`.


```bash
composer require --dev clentfort/laravel-find-js-localizations
npm install laravel-find-js-localizations
php artisan vendor:publish
```

After the installation of the packages load the service-provider in your app.
You can do so by adding the following line to the `providers`-array in
`config/app.php`.

```php
clentfort\LaravelFindJsLocalizations\ArtisanServiceProvider::class,
```

Now all that is left to do is to publish the configuration to you app, this can
be achieved by running:

```bash
php artisan vendor:publish
```

Verify the package was installed successfully by running `php artisan list`, it
should now include the command `js-localization:missing`.

# Configuration

The command can be configured through the configuration-file in
`config/laravel-find-js-localizations.php`.

| Option | Description |
| --- | --- |
| `node_executable` | The name or the path to the Node.js-exectuable |
| `extension` | The extension of your JavaScript-files |
| `directory` | The directory your JavaScript-assets are stored |


# Usage

Simply run `php artisan js-localization:missing`. 

> The command will write the files in the array-dot notation!

# License

MIT

# Contributing

If you want to contribute feel free to send a PR.

* If you are improving on the JavaScript please make sure you run
  [prettier][prettier] with the `--single-quote` flag before creating the PR.
* If you are improving on the PHP please make sure you do not brake the 80 chars
  per line limit and make sure to run [php-cs-fixer][php-cs-fixer] before you
  create a PR. 

[prettier]: https://github.com/jlongster/prettier
[php-cs-fixer]: https://github.com/FriendsOfPHP/PHP-CS-Fixer

Thank you.
