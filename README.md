# Bitly Provider for PHP OAuth 2.0 Client

This package provides Bitly OAuth 2.0 support for the PHP League's [OAuth 2.0 Client](https://github.com/thephpleague/oauth2-client). Work with Bitly API (4)

Full documentation, can be see in [Bitly documentation](http://dev.bitly.com/v4_documentation.html).

## Installation

```
composer require bybrand/oauth2-bitly
```

## Usage
This is a instruction base to get the token, and in then, to save in your database to future request.

```
use Bybrand\OAuth2\Client\Provider\Bitly as ProviderBitly;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

$params = $_GET;

$provider = new ProviderBitly([
    'clientId'     => 'key-id',
    'clientSecret' => 'secret-key',
    'redirectUri'  => 'your-url-redirect'
]);

if (!empty($params['error'])) {
    // Got an error, probably user denied access
    $message = 'Got error: ' . htmlspecialchars($params['error'], ENT_QUOTES, 'UTF-8');

    // Return error.
    echo $message;
}
if (!isset($params['code']) or empty($params['code'])) {
    // If we don't have an authorization code then get one
    $authorizationUrl = $provider->getAuthorizationUrl();

    // Get state and store it to the session
    $_SESSION['oauth2state'] = $provider->getState();

    header('Location: '.$authorizationUrl);
    exit;
// Check given state against previously stored one to mitigate CSRF attack
} elseif (empty($params['state']) || ($params['state'] !== $_SESSION['oauth2state'])) {
    unset($_SESSION['oauth2state']);

    // Set error and redirect.
    echo 'Invalid stage';
} else {
    try {
        // Try to get an access token (using the authorization code grant)
        $token = $provider->getAccessToken('authorization_code', [
            'code' => $params['code']
        ]);

        // Retriave a list of Bitly groups.
        // @see http://dev.bitly.com/v4/#operation/getGroups
        $groups = $provider->getResourceOwner($token);
    } catch (IdentityProviderException $e) {
        // Error, HTTP code Status
        // @see http://dev.bitly.com/v4/#section/RESTfulness
    } catch (\Exception $e) {
        // Error, make redirect or message.
    }

    // Use this to interact with an API on the users behalf
    echo $token->getToken();
}
```
Please, for more information see the PHP League's general usage examples.

## Testing

```
bash
$ ./vendor/bin/phpunit
```

or individual method test, by group.

```
bash
$ ./vendor/bin/phpunit --group=Bitly.GetBaseAccessTokenUrl
```


## License

The MIT License (MIT). Please see [License File](https://github.com/bybrand/oauth2-bitly/blob/master/LICENSE) for more information.
