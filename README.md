Indiegogo php client
====



## Requirements

* PHP 5.4.0 and up.

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).
Either run
```
php composer.phar require --prefer-dist tiamo/indiegogo "*"
```
or add
```
"tiamo/indiegogo": "*"
```
to the require section of your `composer.json` file.

## Usage

```php
$apiToken = '...';
$client = new \Indiegogo\Client($apiToken);
if ($client->auth($email, $password)) {
    $me = $client->getCredentials();
    ...
}
```

## License

Licensed under the [MIT license](http://opensource.org/licenses/MIT).
