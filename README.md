# PHP URL Shortener and Expander
Url shortener/expander library.

## Benefits

- Produces short codes depending on the (integer) id of the record 
- Supports legacy or custom short urls. So you can move your old system without changing short urls or short codes
- Uses low server resources
- Reports errors in json format via responseJson static function
- Can count redirects ( on Mysql )
- Supports Doctrine Cache Clients ( example includes redis usage via predis )
- Uses alphanumeric characters and supports all browsers
- Uses PDO in order to prevent SQL injection hacks
- Supports URL check before shorten and after expanded ( checks for http 200 code )
- Redirection after expand is optional ( redirects with 301 for SEO compliance )
- You can change allowed characters used in shortened urls code. This may reduce count of possible shortened url code.

## Installation

### Requirements:
   * PHP
   * Mysql DB or Maria DB
   * PDO Extension must be enabled
   * Credentials will be needed to connect and run SQL queries on database server
   * Sql files under sql directory must be applied on database server
   * Composer
### Install ( via Composer )

```bash
$ composer require emrahtoy/url-shortener
```
## Using URL shortener service
```php
$urlShortener = new \UrlCompressor\Shortener($connection, $config);

// returns true or false depending on success and error.
$result = $urlShortener->shorten($url); 

// responseJson is a tool to return json with proper headers. This function also redirect with 301 code if you send true as secondary parameter.
// You can send "donotredirect" in order to prevent redirection even it is set "true"
\UrlCompressor\Common::responseJson($urlShortener->getResult(), false);
```

## Using URL expander service
```php
$urlExpander = new \UrlCompressor\Expander($connection, $config);

// returns true or false depending on success and error.
$result = $urlExpander->expand($shortened_code); 

// responseJson is a tool to return json with proper headers. This function also redirect with 301 code if you send true as secondary parameter.
// You can send "donotredirect" in order to prevent redirection even it is set "true" 
\UrlCompressor\Common::responseJson($urlExpander->getResult(),(isset($_REQUEST['donotredirect']))?false:true);
```

### Available configuration options
```php
$config = [
    'CheckUrl' => false, // check url before shortening or after expand
    'ShortUrlTableName' => 'shortenedurls', // Database table name where shortened url codes are stored
    'CustomShortUrlTableName' => 'customshortenedurls', // Database table name where your legacy shortened codes are stored
    'TrackTableName' => 'track', // Database table name where the visit/redirect counts are stored
    'DefaultLocation' => '', // where to redirect if expanded url could not find
    'Track'=>false // Determines if visit/redirects will be stored
];
```

### Using cache

You can use any cache library supported by [Doctrine Cache](https://github.com/doctrine/cache).

This example uses [Predis Client](https://github.com/nrk/predis) with [Redis](https://redis.io)

```php
$urlShortener = new \UrlCompressor\Shortener($connection, $config);
$urlExpander = new \UrlCompressor\Expander($connection, $config);

// using Predis Client with Doctrine Cache
try{
    $cacheConfig = [
        'server' => array(
            'host' => '127.0.0.1',
            'port' => 6379
        ),
        'password' => 'supersecretauthentication' // if you have set authentication on redis
    ];
    
    //lets create redis client
    $cacheClient = new Predis\Client($cacheConfig);
    // and try to connect
    $cacheClient->connect();
    
    // we can encapsulate cache cilent with doctrine cache if any exception not fired 
    $cache = new \Doctrine\Common\Cache\PredisCache($cacheClient);

    // set cache provider for url shortener service 
    $urlShortener->setCache($cache);
    
    // set cache provider for url expander service 
    $urlExpander->setCache($cache);
    
} catch(Exception $e){
    $result=new \UrlCompressor\Result();
    $result->error($e->getMessage());
    \UrlCompressor\Common::responseJson($result->result());
    die();
}
```