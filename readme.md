# MatomoTracker

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]

## About

The `alfrasc\laravel-matomo-tracker` Laravel package is a wrapper for the `piwik\piwik-php-tracker`. The Piwik php tracker allows serverside tracking.

## Features

 * MatomoTracker Facade
 * Queued tracking requests in Laravel queue

Feel free to suggest new features.

## Installation

Via Composer

Require the `alfrasc\laravel-matomo-tracker` package in your `composer.json` and update your dependencies:
``` bash
$ composer require alfrasc/laravel-matomo-tracker
```

Publish the config file (optional)

Run `php artisan vendor:publish` to publish the config file if needed.
``` bash
$ php artisan vendor:publish
```

Update your `.env`

Add these variables to your `.env` file and configure it to fit your environment.
``` bash
[...]
MATOMO_URL="https://your.matomo-install.com"
MATOMO_SITE_ID=1
MATOMO_AUTH_TOKEN="00112233445566778899aabbccddeeff"
MATOMO_QUEUE="matomotracker"
[...]
```

That's it!

## Usage

You can use the facade to track.

``` bash
MatomoTracker::doTrackPageView('Page Title')
```

### Basic functionality

Please see the https://developer.matomo.org/api-reference/PHP-Piwik-Tracker page for basic method documentation.

Additionally there are some Methods to simplyfy the usage:
``` bash
// instead of using 
MatomoTracker::doTrackAction($actionUrl, 'download') // or
MatomoTracker::doTrackAction($actionUrl, 'link')

// you can use this
MatomoTracker::doTrackDownload($actionUrl);
MatomoTracker::doTrackOutlink($actionUrl);
```

### Queues

For queuing you can use these functions.
``` bash
MatomoTracker::queuePageView(string $documentTitle)
MatomoTracker::queueEvent(string $category, string $action, $name = false, $value = false)
MatomoTracker::queueContentImpression(string $contentName, string $contentPiece = 'Unknown', $contentTarget = false)
MatomoTracker::queueContentInteraction(string $interaction, string $contentName, string $contentPiece = 'Unknown', $contentTarget = false)
MatomoTracker::queueSiteSearch(string $keyword, string $category = '',  $countResults = false)
MatomoTracker::queueGoal($idGoal, $revencue = 0.0)
MatomoTracker::queueDownload(string $actionUrl)
MatomoTracker::queueOutlink(string $actionUrl)
MatomoTracker::queueEcommerceCartUpdate(float $grandTotal)
MatomoTracker::queueEcommerceOrder(float $orderId, float $grandTotal, float $subTotal = 0.0, float $tax = 0.0, float $shipping = 0.0,  float $discount = 0.0)
MatomoTracker::queueBulkTrack()
```

For setting up queues, find the documentation on https://laravel.com/docs/6.x/queues.

## Change log

Please see the [changelog](changelog.md) for more information on what has changed recently.


## Contributing

Please see [contributing.md](contributing.md) for details and a todolist.

## Security

If you discover any security related issues, please email alexander.schmidhuber@gmail.com instead of using the issue tracker.

## Credits

- [Alexander Schmidhuber][link-author]
- [All Contributors][link-contributors]

## License

BSD-3-Clause. Please see the [license file](license.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/alfrasc/laravel-matomo-tracker.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/alfrasc/laravel-matomo-tracker.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/alfrasc/laravel-matomo-tracker
[link-downloads]: https://packagist.org/packages/alfrasc/laravel-matomo-tracker
[link-author]: https://github.com/alfrasc
[link-contributors]: ../../contributors
