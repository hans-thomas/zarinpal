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
and you are good to go (legacy config still works)
now you can access the zarinpal lib like this:
```php
use Zarinpal\Laravel\Facade\Zarinpal;

$results = Zarinpal::request(
    "example.com/testVerify.php",          //required
    1000,                                  //required
    'testing',                             //required
    'me@example.com',                      //optional
    '09000000000',                         //optional
    [                          //optional
        "Wages" => [
            "zp.1.1" => [
                "Amount" => 120,
                "Description" => "part 1"
            ],
            "zp.2.5" => [
                "Amount" => 60,
                "Description" => "part 2"
            ]
        ]
    ]
);
// save $results['Authority'] for verifying step
Zarinpal::redirect(); // redirect user to zarinpal

// after that verify transaction by that $results['Authority']
Zarinpal::verify('OK',1000,$results['Authority']);
```

