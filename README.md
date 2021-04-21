# Zarinpal Package for laravel 8.x

transaction request library for zarinpal

## usage
### installation
``composer require hans-thomas/zarinpal``
## laravel ready
define these env variables in .env file
```php
merchantID=XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX
sandbox=true
zarinGate=false
```
then add this to `config/services.php`
```php
'zarinpal' => [
    'merchantID' => env( 'merchantID', 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX' ),
    'zarinGate'  => env( 'zarinGate', false ),
    'sandbox'    => env( 'sandbox', false ),
],
```
now you can access the zarinpal lib like this:
```php
use Zarinpal\Laravel\Facade\Zarinpal;

$results = Zarinpal::request(
    "example.com/call_back",               //call back url is required
    1000,                                  //amount is required
    'testing',                             //description is required also
    'metadata' => [
        'me@example.com',                  // email is optional
        '09000000000',                     //phone number is optional
    ]
    
);
// save $results['authority'] for verifying step
Zarinpal::redirect(); // redirect to zarinpal

// after that verify transaction by that $results['authority']
Zarinpal::verify(1000,$results['authority']);
```

