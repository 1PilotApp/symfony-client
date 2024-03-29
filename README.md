<p align="center">
  <a href="https://1pilot.io/symfony"><img src="https://1pilot.io/assets/images/repos/1pilot_logo_symfony.png" alt="1Pilot.io - a universal dashboard to effortlessly manage all your sites"></a>
</p>

<p align="center">
<a href="https://packagist.org/packages/1pilotapp/symfony-client"><img alt="Latest Version on Packagist" src="https://img.shields.io/packagist/v/1pilotapp/symfony-client.svg?style=flat-square"></a>
<a href="https://github.com/1PilotApp/symfony-client/blob/master/LICENSE.md"><img alt="Software License" src="https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square"></a>
<a href="https://github.com/1PilotApp/symfony-client/actions"><img alt="Build Status" src="https://img.shields.io/github/actions/workflow/status/1PilotApp/symfony-client/tests.yml?branch=2.x&label=tests&style=flat-square"></a>
<a href="https://packagist.org/packages/1PilotApp/symfony-client"><img alt="Total Downloads" src="https://img.shields.io/packagist/dt/1pilotapp/symfony-client.svg?style=flat-square"></a>
</p>

<p align="center">
  <a href="https://1pilot.io/symfony">Website</a>
  <span> · </span>
  <a href="https://app.1pilot.io/register">Free Trial</a>
  <span> · </span>
  <a href="https://1pilot.io/#pricing">Pricing</a>
  <span> · </span>
  <a href="https://docs.1pilot.io/setup/symfony" target="_blank" >Documentation</a>
  <span> · </span>
  <a href="https://docs.1pilot.io/api/introduction" target="_blank">API</a>
  <span> · </span>
  <a href="mailto:support@1pilot.io" target="_blank">Support</a>
</p><br>

![1Pilot dashboard](https://1pilot.io/assets/images/repos/dashboard_2022.png)

## Everything you need to know in just one dashboard.

- **Uptime monitoring**<br> Get instant notifications about downtime and fix it before everyone else even knows it’s an issue.

- **SSL certificate monitoring**<br> Keep track of certificates across all your applications and set reminders of their expiration dates.
- **Config file and server version monitoring**<br> Be alerted when a config file is edited or when PHP, Database or WEB servers are updated.

- **Composer package management**<br> See installed composer packages across all your applications and track their updates. Know exactly when new versions are available and log a central history of all changes.

- **Robust notification system**<br> Get instant notifications across email, Slack and Discord. Too much? Then create fully customisable alerts and summaries for each function and comms channel at a frequency that suits you.

- **Full-featured 15-day trial**<br> Then $2/site/month with volume discounts available. No setup fees. No long-term contracts.

<p align="center">
<br><a href="https://www.youtube.com/watch?v=-xmmjuPV5Dw" target="_blank">
   <img width="480" src="https://1pilot.io/assets/images/repos/1pilot_github_video_w480.png" alt="Watch the demo">
</a><br><br>
</p>

You have just discovered our advanced monitoring tool for your Symfony applications and all the individual sites that you manage. We have designed it as a central dashboard to harmonise the maintenance of your entire website roster. Because we believe that coders should be out there coding. Let computers monitor computers, so that we humans don’t have to worry about it.

We searched the galaxy for a robust answer to our challenges, and found none. So, our team embarked on our greatest mission yet and 1Pilot was born.

<p align="center">
<a href="https://app.1pilot.io/register"><img src="https://1pilot.io/assets/images/repos/free_trial_2022.jpg" alt="Get your first site onboard in under 3 minutes! Start the 15-day full-feature trial"></a>
</p>

<p align="center">
<a href="https://app.1pilot.io/register">Try it for free</a> without any limitations for 15 days. No credit card required.
</p>

## Install

### Symfony 6, 5 & 4.4

``` bash
composer require 1pilotapp/symfony-client:^2.0
```

1. add a new `config/packages/one_pilot_client.yaml` file with the following content:
    ```
    one_pilot_client:
        private_key: "%env(ONE_PILOT_PRIVATE_KEY)%"
        mail_from_address: "%env(ONE_PILOT_MAIL_FROM_ADDRESS)%"
    ```

2. add to your `.env` file the following parameters:
    ```    
    ONE_PILOT_PRIVATE_KEY=[your key]
    ONE_PILOT_MAIL_FROM_ADDRESS=[mail from address used by verification tool]
    ```
    > `ONE_PILOT_PRIVATE_KEY` can be any random alphanumeric string. If you are not sure what key to use, go to 1Pilot dashboard and open the page to add a new site: a random key will be generated for you, and you can copy / paste it in your file. Of course you are free to create a totally different key, just make sure you have the same key in your `.env` and on the 1Pilot dashboard. 

    > `ONE_PILOT_MAIL_FROM_ADDRESS` email address that you use for send mail from your application. It's used by the email verification tool for ensure emails are properly send by your application. 

3. add to your `config/routes.yaml` the following configuration:
    ```
    one_pilot:
        resource: "@OnePilotClientBundle/Resources/config/routing.xml"
        prefix:   /
    ```

You are now ready to add the site to your [1Pilot dashboard](https://app.1pilot.io/sites/create)!

### Symfony 3

``` bash
composer require 1pilotapp/symfony-client:^1.0
```

1. add to your `app/config/config.yml` file the following configuration keys:
    ```
    one_pilot_client:
        private_key: "%one_pilot_private_key%"
        mail_from_address: "%one_pilot_mail_from_address%"
    ```

2. add to your `app/config/parameters.yml.dist` file the following parameter:
    ```
        one_pilot_private_key: ~
        one_pilot_mail_from_address: ~
    ```
    
   This defines the new required configuration parameter.
    
3. add to your `app/config/parameters.yml` file the following parameter: 

    ```
        one_pilot_private_key: [your key]
        one_pilot_mail_from_address: [mail from address used by verification tool]
    ```
    
    > `one_pilot_private_key` can be any random alphanumeric string. If you are not sure what key to use, go to 1Pilot dashboard and open the page to add a new site: a random key will be generated for you, and you can copy / paste it in your file. Of course you are free to create a totally different key, just make sure you have the same key in your `parameters.yml` and on the 1Pilot dashboard.

    > `one_pilot_email_check_from_address` email address that you use for send mail from your application. It's used by the email verification tool for ensure emails are properly send by your application. 

4. add to your `app/config/routing.yml` the following configuration:
    ```
    one_pilot:
        resource: "@OnePilotClientBundle/Resources/config/routing.xml"
        prefix:   /
    ```

5. add to your `app/AppKernel.php` file, in the `registerBundles` method, the following line:
    ```
    ...
    new OnePilot\ClientBundle\OnePilotClientBundle(),
    ...
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

- [1Pilot.io](https://github.com/1PilotApp)
- [All Contributors](https://github.com/1PilotApp/symfony-client/contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
