**PACKAGE STILL IN DEVELOPMENT**

# 1Pilot.io connector for Symfony applications

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Total Downloads][ico-downloads]][link-downloads]

[1Pilot.io](1pilot.io) is a central Dashboard to manage your websites. It offers you a simple way to have all your websites and applications monitored on the same place. For Laravel applications you will benefit of the Uptime and Certificate 
monitoring as well as the report of installed package and updates available: a simple way to keep your apps up-to-date and secure.

This bundle is **1Pilot** client app for Symfony.

## Install

``` bash
composer require 1pilotapp/symfony-client:dev-master
```

After installation, you need to setup the bundle. 

**Symfony3:**

1. add to your `app/config/config.yml` file the following configuration keys:
    ```
    one_pilot_client:
        private_key: "%one_pilot_private_key%"
        skip_timestamp_validation: "%one_pilot_skip_timestamp_validation%"
    ```
2. add to your `app/config/parameters.yml` file the following parameters:
    ```    
    one_pilot_private_key: [your key]
    one_pilot_skip_timestamp_validation: [true|false]
    ```
    `one_pilot_private_key` can be any random alphanumeric string. 
    `one_pilot_skip_timestamp_validation` is usually set to `false` for better security, but it can be set to `true` if you experience troubles due to server time and timezone.
3. add to your `app/config/routing.yml` the following configuration:
    ```
    one_pilot:
        resource: "@OnePilotClientBundle/Resources/config/routing.xml"
        prefix:   /
    ```
4. add to your `app/AppKernel.php` file, in the `registerBundles` method, the following line:
    ```
    ...
    new OnePilot\ClientBundle\OnePilotClientBundle(),
    ...
    ```

You should now ready to go!

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please email support@1pilot.io instead of using the issue tracker.

## Credits

- [1Pilot.io][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/1PilotApp/symfony-client.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/1PilotApp/symfony-client/master.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/1pilotapp/symfony-client.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/1pilotapp/symfony-client
[link-downloads]: https://packagist.org/packages/1PilotApp/symfony-client
[link-author]: https://github.com/1PilotApp
[link-contributors]: ../../contributors
