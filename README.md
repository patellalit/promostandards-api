# Install

This is a PHP client for the Promostandards API.

## Installation

Install the package via Composer:

```bash
composer require "shera/promostandards-api"
```
## Usage

Here's a basic example of how to use the client:

```bash
<?php
$productEndPoint = "https://example.com/endpoint-url";
$userId = "XXXX";
$password = "XXXX";
$productId = "XXXX";

$productVersion = "2.0.0";
$localizationCountry = "US";
$localizationLanguage = "en";

$extraParams = [];

$client = new PromostandardClient($userId, $password);
$productData = $client->getProductDataV2($productEndPoint, $productVersion, $productId, $localizationCountry, $localizationLanguage, $extraParams);
?>
```

Replace "XXXX" with your actual user ID, password, and product ID. Replace "https://example.com/endpoint-url" with the actual endpoint URL of the Promostandards API.

License
This project is licensed under the MIT License.

## Support

If you're feeling generous and want to show some extra appreciation:

:coffee: [Buy me a coffee](https://www.buymeacoffee.com/patellalit)

Remember, every cup of coffee counts when it comes to coding! :wink:
