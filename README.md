# Arrowsphere API Client

The Arrowsphere API Client Suite is the best way to consume services offered by the Arrowsphere xAC API.

Written in PHP and Framework-agnostic, the Client is available as a composer package.

## The xAC Client

xAC Client is a complete PHP semantic client able to perform queries on the xAC API.

### Configuration
Client configuration is quite simple. Set your API secret key, the API URL and the desired API version.
 
```PHP
use Arrowsphere\Client\xAC as Client;

Client::setApiKey('MySecretApiKeyThatNobodyKnowsAbout');

Client::setBaseUrl('https://xac.arrowsphereconnect.com/api/');

Client::setVersion(2); // 1 and 2 (default) are valid versions
```

### Client useful methods

#### Get Last response
Get the last response from the service with the getLastRespons() static method. The content is an array representing the latest received json payload.

```PHP
var_dump(Client::getLastResponse());

array(6) {
  ["status"]=>
  int(0)
  ["message"]=>
  string(7) "Success"
  ["ACUserID"]=>
  int(1)
  ["ACTransactionID"]=>
  string(36) "7d985f56-011f-436e-bf38-4afec9467cc6"
  ["language"]=>
  string(5) "en_UK"
  ["body"]=>
  array(4) {
    ["parameters"]=>
    array(0) {
    }
    ["data"]=> ...
    
```

#### Get Services
Get an array of available services for the whole API and for the user identified by the API key or for a given Entity() instance.

```PHP

// Get all services for user ahtentified with API key
var_dump(Client::getServices());

array(15) {
  ["transactions"]=>
  array(3) {
    ["type"]=>
    string(10) "collection"
    ["description"]=>
    string(16) "Get Transactions"
    ["endpoint"]=>
    string(12) "transactions"
  }
  ["customers"]=>
  array(3) {
    ["type"]=>
    string(10) "collection"
    ["description"]=>
    string(39) "Get Customers of authenticated reseller"
    ["endpoint"]=>
    string(9) "customers"
  }
  ...
  
var_dump(Client::getServices(Client::customer()));

```

### Cursors

#### Basic usage
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

#### Current methods

```PHP
// get next batch
$data = $cursor->next();

// get previous page
$data = $cursor->prev();
```

#### Query filters
```PHP
// add filter on-the-fly
$cursor->filter('name', 'Acme')->get();

```

### Entities

#### Basic usage
Entities are elements like customer, subscription or order. Each element can be accessed as an xAC\Entity() instance.

```PHP
// Get a customer instance
$customer = Client::customer('myRef');

// Get a property
echo $customer->company_name;
```
#### Entity Cursors
An entity instance can contain collections. To access data, get a cursor and proceed as described in the Cursors section.

```PHP
// Get a cursor for the subscriptions collection
$cursor = $customer->subscriptions();

// get first batch
$batch = $cursor->get();
```

#### Entity Actions

To execute operations on a given Entity() instance, either populated or empty, get an Action() handler instance.

```PHP
// create an empty customer instance
$customer = Client::customer();

// get a create action handler
$action = $customer->create();

// or do the same in one pass
$action = Client::customer()->create();

```

Action() instances can receive a data array with the setData() method.

```PHP
$action->setData(['someKey' => 'someValue']);
```

Action() can then be executed with the execute() method. 

```PHP
// $result will contain a boolean
$result = $action->execute();
```


##### Create and save a new element


```PHP
// create an empty customer instance
$customer = Client::customer();

// get a provision action
$action = $customer->provision();

$action->setData([
	'customer_ref'  => 'newRef',
	'customer_name' => 'Acme Limited',
	...
]);

$res = $action->execute();

// send a create request to the API with customer data
$result = $customer->create([
	'customer_ref'  => 'newRef',
	'customer_name' => 'Acme Limited',
	...
]);
```
