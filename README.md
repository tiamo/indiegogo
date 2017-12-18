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

Initialize client
```php
$client = new \Indiegogo\Client();
if ($client->auth($email, $password)) {
    $me = $client->getCredentials();
    // ...
}
```

Export Contributions
```php
$res = $client->contributionExport($campaignId);
$job = $client->jobStatuses($res['job_id']);
if ($job['status'] == 'completed') {
    echo $job['download_url'];
}
```

Import Contributions
```php
$res = $client->contributionImport($campaignId, [
    'file' => curl_file_create($filePath)
]);
$job = $client->jobStatuses($res['job_id']);
if ($job['status'] == 'completed') {
   // ...
}
```

## License

Licensed under the [MIT license](http://opensource.org/licenses/MIT).
