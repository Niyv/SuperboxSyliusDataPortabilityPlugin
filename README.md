<p align="center">
    <a href="https://sylius.com" target="_blank">
        <img src="https://demo.sylius.com/assets/shop/img/logo.png" />
    </a>
</p>

<h1 align="center">Data Portability Plugin</h1>

<p align="center">Out-of-the-box Plugin Solution for GDPR Compliance.</p>

## Installation

1. Run `composer require superbox/sylius-data-portability-plugin

## Usage

This is an out-of-the-box basic plugin for Sylius to comply with the  EU GDPR in regards to data portability.
Users can navigate to "your-sylius-website/data-portability" to access the plugins functionality.

On entering their e-mail address, the plugin will gather all related data to that e-mail in the existing database and 
send it as an e-mail attachment to the supplied address.

The following data is gathered and send out:

Tables: 

    sylius_customer;
    sylius_address;
    sylius_shop_user;
    sylius_order;
    sylius_address_log_entry;
    sylius_payment;
    sylius_product_review;

Fields:


    'email', 'firstName', 'lastName', 'birthday', 'gender', 'phoneNumber', 'company', 'countryCode', 'street', 'city', 'postcode', 'username', 'title', 'rating',  'comment',

### Running plugin tests

  - Behat (non-JS scenarios)

    ```bash
    $ bin/behat --tags="~@javascript"
    ```

  - Behat (JS scenarios)
 
    1. Download [Chromedriver](https://sites.google.com/a/chromium.org/chromedriver/)
    
    2. Run Selenium server with previously downloaded Chromedriver:
    
        ```bash
        $ bin/selenium-server-standalone -Dwebdriver.chrome.driver=chromedriver
        ```
    3. Run test application's webserver on `localhost:8080`:
    
        ```bash
        $ (cd tests/Application && bin/console server:run 127.0.0.1:8080 -d web -e test)
        ```
    
    4. Run Behat:
    
        ```bash
        $ bin/behat --tags="@javascript"
        ```