# 1Pilot.io connector for Symfony applications

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Total Downloads][ico-downloads]][link-downloads]

[1Pilot.io](1pilot.io) is a central Dashboard to manage your websites. It offers you a simple way to have all your websites and applications monitored on the same place. For Symfony applications you will benefit of the Uptime and Certificate 
monitoring as well as the report of installed package and updates available: a simple way to keep your apps up-to-date and secure.

## Install

``` bash
composer require 1pilotapp/symfony-client
```

After installation, you need to setup the bundle. 

### Symfony 3

1. add to your `app/config/config.yml` file the following configuration keys:
    ```
    one_pilot_client:
        private_key: "%one_pilot_private_key%"
    ```

2. add to your `app/config/parameters.yml` file the following parameters:
    ```
        one_pilot_private_key: [your key]
    ```
    
    > `one_pilot_private_key` can be any random alphanumeric string. You choose it and copy it, because you'll have to use it when adding the site to 1Pilot dashboard.

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

You are now ready to add the site to your [1Pilot dashboard](https://app.1pilot.io/sites/create)!

### Symfony 4

1. add a new `config/packages/one_pilot_client.yaml` file with the following content:
    ```
    one_pilot_client:
        private_key: "%env(ONE_PILOT_PRIVATE_KEY)%"
    ```

2. add to your `.env` file the following parameters:
    ```    
    ONE_PILOT_PRIVATE_KEY=[your key]
    ```

    > `ONE_PILOT_PRIVATE_KEY` can be any random alphanumeric string. You choose it and remember it, because you'll have to use it when adding the site to 1Pilot dashboard. 

3. add to your `config/routes.yaml` the following configuration:
    ```
    one_pilot:
        resource: "@OnePilotClientBundle/Resources/config/routing.xml"
        prefix:   /
    ```

You are now ready to add the site to your [1Pilot dashboard](https://app.1pilot.io/sites/create)!

## Advanced configuration
If your server is not at time you can have issue to connect your application to 1Pilot. For solve that edit `app/config/config.yml` and add `skip_timestamp_validation` like bellow.
```
one_pilot_client:
    private_key: "..."
    skip_timestamp_validation: true
```
> Please note that this option will decrease security and that you should as much as possible set your server at the correct time.

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

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
[link-travis]: https://travis-ci.org/1PilotApp/symfony-client
[link-downloads]: https://packagist.org/packages/1PilotApp/symfony-client
[link-author]: https://github.com/1PilotApp
[link-contributors]: ../../contributors
