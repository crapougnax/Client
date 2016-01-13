# xAC Client

xAC Client is a complete PHP semantic client able to perform queries on xAC.

## Configuration
Client configuration is quite simple. Set your API secret key, the API URL and the desired API version.
 
```PHP
use Arrowsphere\Client\xAC as Client;

Client::setApiKey('MySecretApiKeyThatNobodyKnowsAbout');

Client::setBaseUrl('https://xac.arrowsphereconnect.com/api/');

Client::setVersion(2); // 1 and 2 (default) are valid versions
```

## Cursors

### Basic usage
```PHP

$cursor = Client::customers();

// get first customers batch as an array of xAC\Entity() instances
$collection = $cursor->get();

// or walk through collection
$cursor->rewind();
foreach ($cursor->getOne() as $entity) {
	// do something on $entity instance
}

```

### Current methods

```PHP
// get next batch
$data = $cursor->next();

// get previous page
$data = $cursor->prev();
```

### Query filters
```PHP
// add filter on-the-fly
$cursor->filter('name', 'Acme')->get();

```

## Entities

### Basic usage
Entities are elements like customer, subscription or order. Each element can be accessed as an xAC\Entity() instance.

```PHP
// Get a customer instance
$customer = Client::customer('myRef');

// Get a property
echo $customer->company_name;
```
### Entity Cursors
An entity instance can contain collections. To access data, get a cursor and proceed as described in the Cursors section.

```PHP
// Get a cursor for the subscriptions collection
$cursor = $customer->subscriptions();

// get first batch
$batch = $cursor->get();
```

### Create and save a new element (yet to come)
```PHP
// create an empty customer instance
$customer = Client::customer();

// send a create request to the API with customer data
$result = $customer->create([
	'customer_ref'  => 'newRef',
	'customer_name' => 'Acme Limited',
	...
]);
```

