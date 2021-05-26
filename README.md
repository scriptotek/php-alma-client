[![Build Status](https://img.shields.io/travis/scriptotek/php-alma-client.svg)](https://travis-ci.org/scriptotek/php-alma-client)
[![Scrutinizer code quality](https://scrutinizer-ci.com/g/scriptotek/php-alma-client/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/scriptotek/php-alma-client/?branch=master)
[![StyleCI](https://styleci.io/repos/35571779/shield)](https://styleci.io/repos/35571779)
[![Packagist](https://img.shields.io/packagist/v/scriptotek/alma-client.svg)](https://packagist.org/packages/scriptotek/alma-client)

# php-alma-client

Simple PHP package for working with the [Alma REST APIs](https://developers.exlibrisgroup.com/alma/apis).
SOAP APIs will not be supported.
Currently, this package supports Bibs (read/write), Users and Analytics (read only).
It is integrated with [php-marc](https://github.com/scriptotek/php-marc) for editing MARC records.
If the package doesn't fit your needs, you might take a look at the alternative
[php-alma](https://github.com/BCLibraries/php-alma) package.

## Table of Contents

  * [Install using Composer](#install-using-composer)
  * [Initializing a client](#initializing-a-client)
  * [Quick intro](#quick-intro)
  * [Note about lazy-loading and existence-checking](#note-about-lazy-loading-and-existence-checking)
  * [Bibs: Bibliographic records, Holdings, Representations, Portfolios](#bibs-bibliographic-records-holdings-representations-portfolios)
     * [Getting a single record](#getting-a-single-record)
     * [The MARC21 record](#the-marc21-record)
     * [Searching for records](#searching-for-records)
     * [Getting linked record from network zone](#getting-linked-record-from-network-zone)
     * [Editing records](#editing-records)
     * [Holdings and items](#holdings-and-items)
     * [Item by barcode](#item-by-barcode)
     * [Electronic portfolios and collections](#electronic-portfolios-and-collections)
     * [Digital representation and files](#digital-representation-and-files)
  * [Users, loans, fees and requests](#users-loans-fees-and-requests)
     * [Search](#search)
     * [Loans](#loans)
     * [Fees](#fees)
     * [Requests](#requests)
  * [Analytics reports](#analytics-reports)
     * [Column names](#column-names)
     * [Filters](#filters)
  * [Task lists](#task-lists)
     * [Lending requests](#lending-requests)
     * [Requested resources (pick from shelf)](#requested-resources-pick-from-shelf)
  * [Jobs](#jobs)
     * [Listing jobs](#listing-jobs)
     * [Retrieving information about a specific job](#retrieving-information-about-a-specific-job)
     * [Submitting a job](#submitting-a-job)
  * [Code Tables](#code-tables)
     * [Getting a single code table](#getting-a-codetable)
  * [Automatic retries on errors](#automatic-retries-on-errors)
  * [Laravel integration](#laravel-integration)
     * [Customizing the HTTP client stack](#customizing-the-http-client-stack)
  * [Future plans](#future-plans)

Created by [gh-md-toc](https://github.com/ekalinin/github-markdown-toc)

## Install using Composer

Use [Composer](https://getcomposer.org) to install sru-client with a HTTP library such as Guzzle:

```bash
composer require scriptotek/alma-client php-http/guzzle6-adapter http-interop/http-factory-guzzle
```

We use [HTTP discovery](https://github.com/http-interop/http-factory-discovery) to discover
[HTTP client](https://packagist.org/providers/psr/http-client-implementation) and
[HTTP factory](https://packagist.org/providers/psr/http-factory-implementation) implementations,
so Guzzle can be swapped with any other
[PSR-17](https://www.php-fig.org/psr/psr-17/)/[PSR-18](https://www.php-fig.org/psr/psr-18/)-compatible library.

## Initializing a client

Start by initiating a new client with the API key you get at [Ex Libris Developer Network](https://developers.exlibrisgroup.com/):

```php
require_once('vendor/autoload.php');
use Scriptotek\Alma\Client as AlmaClient;
use Scriptotek\Sru\Client as SruClient;

$alma = new AlmaClient('MY_SECRET_API_KEY', 'eu');
```

where `'eu'` is the region code for Europe (use `'na'` for North America or `'ap'` for Asia Pacific).

If your Alma instance is connected to a network zone and you want to work
with bib records there, you can also add an API key for the network zone:

```php
$alma->nz->setKey('MY_SECRET_NETWORK_ZONE_API_KEY');
```

If you want search support, connect [an SRU client](https://github.com/scriptotek/php-sru-client):

```php
$alma->setSruClient(new SruClient(
    'https://bibsys-k.alma.exlibrisgroup.com/view/sru/47BIBSYS_UBO',
    ['version' => '1.2']
));
```

You can also connect an SRU client to the network zone SRU service:

```php
$alma->nz->setSruClient(new SruClient(
    'https://bibsys-k.alma.exlibrisgroup.com/view/sru/47BIBSYS_NETWORK',
    ['version' => '1.2']
));
```

## Quick intro

The library provides access to Bibs, Holdings and Items
through `$alma->bibs`, Users (`$alma->users`), Analytics reports
(`$alma->analytics`), Libraries (`$alma->libraries`), Task Lists (`$alma->taskLists`) and Jobs (`$alma->jobs`).

To fetch a Bib record:

```php
$bib = $alma->bibs->get('990114012304702204');
```

## Note about lazy-loading and existence-checking

Lazy loading: Note that the client uses lazy loading to reduce the number of HTTP
requests. Requests are not made when you instantiate objects, but when you request
data from them. So in this case, a HTTP request for the holding record is first
made when you call `getRecord()`. The response is then cached on the object for
re-use. In the same way, no HTTP request is made for the Bib record in this case,
since we don't request any data from that.


In general the library delays fetching data until it's actually needed, a practice
called lazy-loading. This means you can for instance initialize a `Bib` object and echo
back the MMS id without any network requests taking place, since there is no need to
fetch any data yet:

```php
$bib = $alma->bibs->get('9901140123047044111');
echo $bib->mms_id;
```

If you request anything else on the Bib object, like the title, the data will automatically
be fetched and populated on the object:

```php
echo $bib->title;
```

If the resource did not exist, a `ResourceNotFound` exception would be thrown at this point.
So you could go ahead and handle that case like so:

```php

$bib = $alma->bibs->get('9901140123047044111');
try {
    echo $bib->title;
} catch (\Scriptotek\Alma\Exception\ResourceNotFound $exc) {
    // Handle the case when the record doesn't exist
}
```

But you can also use the `exists()` method, which is often more convenient:

```php
$bib = $alma->bibs->get('9901140123047044111');
if (!$bib->exists()) {
    // Handle the case when the record doesn't exist
}
```

## Bibs: Bibliographic records, Holdings, Representations, Portfolios

### Getting a single record

A bibliographic record can be fetched either by MMS ID:

```php
$bib = $alma->bibs->get('990114012304702204');
```

or by barcode:

```php
$bib = $alma->bibs->fromBarcode('92nf02526');
```

Note: This methods returns null if the barcode is not found, as does the two methods below.

There are also two lookup methods that use SRU search and require that you have
an SRU client attached. The first lets you use a generic CQL query:

```php
$bib = $alma->bibs->findOne('alma.all_for_ui="9788299308922"');
```

The second is a shorthand for looking up records from ISBN:

```php
$bib = $alma->bibs->fromIsbn('9788299308922');
```

All the methods above returns either a single `Bib` record or `null` if not found.

### The MARC21 record

The MARC21 record is available as `$bib->record` in the form of a
[php-marc](https://github.com/scriptotek/php-marc/blob/master/src/Record.php) `Record` object
that extends `File_MARC_Record` from [File_MARC](https://github.com/pear/File_MARC),
meaning you can use all File_MARC methods in addition to the convenience methods from php-marc.

### Searching for records

If you have connected an SRU client (described above), you can search using
CQL search syntax like so:

```php
foreach ($alma->bibs->search('alma.dewey_decimal_class_number=530.12') as $bib) {
	$marcRecord = $bib->record;
	echo "$marcRecord->id: $marcRecord->title\n";
}
```

### Getting linked record from network zone

If you have configured a network zone API key (see above), you can easily
get the network zone record connected to the institution zone record like so:

```php
$nzBib = $bib->getNzRecord();
```

### Editing records

The MARC21 record can be edited using the `File_MARC_Record` interface
(see [File_MARC](https://github.com/pear/File_MARC) for documentation),
and then saved back to Alma using the `save()` method on the `Bib` object.
In the example below we delete a subject heading and add another:

```php
$bib = $alma->bibs->get('990114012304702204');

foreach ($bib->record->getSubjects() as $subject) {
    if ($subject->vocabulary == 'noubomn' && (string) $subject == 'Boating with dogs') {
        $subject->delete();
    }
}

$bib->record->appendField(new File_MARC_Data_Field('650', [
    new File_MARC_Subfield('a', 'Boating with cats'),
    new File_MARC_Subfield('2', 'noubomn'),
], null, '0'));

$bib->save();
```

### Holdings and items

The `Bib` object provides easy access to holdings through the `holdings` iterator:

```php
$bib = $alma->bibs->get('990310361044702204');
foreach ($bib->holdings as $holding) {
    echo "{$holding->holding_id} {$holding->call_number}";
}
```

As with the `Bib` object, the MARC record for the `Holding` object is available
either from the `getRecord()` method or the `record` property:

```php
$holding = $alma->bibs['990310361044702204']->holdings['22102913020002204'];
$marcRecord = $holding->record;
```

**Note:** Editing holding records is not yet supported. Will be added in the future.

Items can be listed from holdings in the same manner as holdings can be fetched from bibs.
Here's an example:

```php
$bib = $alma->bibs->get('990310361044702204');
foreach ($bib->holdings as $holding) {
    foreach ($holding->items as $item) {
        echo "{$item->holding_id} {$item->pid} {$item->barcode} : ";
        echo "{$item->location->desc} {$item->call_number} : ";
        echo "{$item->base_status->desc} {$item->process_type->desc}";
        echo "\n";
    }
}
```

In this case, the client makes one request to fetch the list of holdings, and
then one request per holding to fetch items.

### Item by barcode

There is a special entrypoint to retrieve an item by barcode:

```php
$item = $alma->items->fromBarcode('92nf02526');
```

### Electronic portfolios and collections

Electronic portfolios and collections are available on the `Bib` object in the same way as holdings.

```php
$bib = $alma->bibs->get('990310361044702204');
foreach ($bib->portfolios as $portfolio) {
    echo "{$portfolio->portfolio_id} {$portfolio->electronic_collection->service->link} ";
    echo "{$portfolio->electronic_collection->public_name}\n";
}
```

```php
$bib = $alma->bibs->get('990310361044702204');
foreach ($bib->electronic_collections as $collection) {
    echo "{$collection->public_name}";
}
```

### Digital representation and files

```php
$bib = $alma->bibs->get('990310361044702204');
foreach ($bib->representations as $rep) {
    echo "{$rep->representation_id} {$rep->label}\n";
    foreach ($rep->files as $rep_file) {
        echo "{$rep_file->label} {$rep_file->thumbnail_url}\n";
    }
}
```

## Users, loans, fees and requests

**Note**: Editing is not fully implemented.

### Search

Example:

```php
foreach ($alma->users->search('last_name~Heggø AND first_name~Dan') as $user) {
    echo "{$user->first_name} {$user->last_name} ({$user->primary_id})\n";
}
```

### Contact Info Updates

#### Update an address

Example:

```php
$uerr = $alma->users->get('EXTID_123456');
$user->contactInfo->unsetPreferredAddress();
$addresses = $user->contactInfo->getAddresses();
$campusBox = 'Box 876';
$changed = false;
foreach ($addresses as $address) {
	if ($address->address_type[0]->value === 'school') {
		$address->line1 = $campusBox;
		$address->preferred = true;
		$changed = true;
		break;
	}
}
if (!$changed) {
	$new = json_decode('{ "preferred": true, "segment_type": "Internal", "line1": "My University", "line2": ".$campusBox.", "city": "Scottsdale", "state_province": "AZ", "postal_code": "85054", "country": { "value": "USA" }, "address_note": "string", "start_date": "2020-07-20", "end_date": "2021-07-20", "address_type": [ { "value": "school" } ] }');
	$user->contactInfo->addAddress($new);
}
$user->save();
```

#### Add an SMS number

Example:

```php
$user = $alma->users->get('EXTID_123456');
$user->contactInfo->setSmsSNumber('18005882300');
$user->save();
```

#### Change an email

Example:

```php
$user = $alma->users->get('EXTID_123456');
$user->contactInfo->removeEmail('bounced@email.org');
$user->contactInfo->addEmail('confirmed@email.net');
$user->save();
```

### Loans

Example:

```php

foreach ($user->loans as $loan) {
    echo "{$loan->due_date} {$loan->title}\n";
}
```

Note that `$user->loans` is an iterator. To get an array instead,
you can use `iterator_to_array($user->loans)`.

### Fees

Example:

```php
printf('Total: %s %s', $user->fees->total_sum, $user->fees->currency);

foreach ($user->fees as $fee) {
    echo "{$fee->type->value}\t{$fee->balance}\t{$fee->title}\n";
}
```

### Requests

Example:

```php
foreach ($user->requests as $request) {
    echo json_encode($request, JSON_PRETTY_PRINT);
}
```

Requests can also be retrieved from a `Bib` object or an `Item` object.

## Analytics reports

To retrieve the results from a single report:

```php
$report = $alma->analytics->get('/shared/Alma/Item Historical Events/Reports/Items went into temporary location');
foreach ($report as $row) {
    echo implode("\t", $row) . "\n";
}
```

The rows are returned using a generator that takes care of fetching more rows until
the result set is depleted, so you don't have to think about continuation. If you only
want a subset of the rows, you must take care of breaking out of the loop yourself.

### Column names

The column names can be accessed through `headers`:

```php
$report = $alma->analytics->get('/shared/Alma/Item Historical Events/Reports/Items went into temporary location');
foreach ($report->headers as $header) {
    echo "$header\n";
}
```

The column names can be accessed through can also be used to access columns for a given row:

```php
foreach ($report as $row) {
    echo $row['Title'] . "\n";
}
```

If you would rather use other column names than the ones defined in Analytics
it is also possible to override them by passing in an array of names that must
follow the same order as the columns in the report:

```php
$report = $alma->analytics->get(
    '/shared/Alma/Item Historical Events/Reports/Items went into temporary location',
    [
        'mms_id',
        'receiving_date',
    ]
);
foreach ($report->rows as $row) {
    echo $row->mms_id . ": " . $row->receiving_date . "\n";
}
```


### Filters

The method also accepts a filter.

```php
$report = $alma->analytics->get(
    '/shared/Alma/Item Historical Events/Reports/Items went into temporary location',
    [
        'mms_id',
        'receiving_date',
    ],
    '<sawx:expr op="greaterOrEqual" xsi:type="sawx:comparison"
                   xmlns:sawx="com.siebel.analytics.web/expression/v1.1"
                   xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
       <sawx:expr xsi:type="sawx:sqlExpression">"Physical Item Details"."Receiving   Date"</sawx:expr>
       <sawx:expr xsi:type="sawx:sqlExpression">TIMESTAMPADD(SQL_TSI_DAY, -1, CURRENT_DATE)</sawx:expr>
    </sawx:expr>',
);
foreach ($report->rows as $row) {
    echo $row->mms_id . ": " . $row->receiving_date . "\n";
}
```

There isn't much official documentation on filters, and error responses from the
API are generally not useful, but there's some helpful hints in
[this blog post](https://developers.exlibrisgroup.com/blog/Working-with-Analytics-REST-APIs).

My experience so far is that including a "is prompted" filter in the report is
really needed. Otherwise the query returns a `400 No more rows to fetch`
(after a looong wait).

Furthermore, I've *not* been successful with pure SQL filters. In OBI you can
convert a filter to SQL. The results looks like this:

```xml
<sawx:expr xmlns:saw="com.siebel.analytics.web/report/v1.1" xmlns:sawx="com.siebel.analytics.web/expression/v1.1" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:type="sawx:sawx:sql">"Physical Item Details"."Receiving   Date" &gt;=  TIMESTAMPADD(SQL_TSI_DAY, -1, CURRENT_DATE)</sawx:expr>
```

But used with the API, the response is the same as if you forget to include an
"is prompted" filter: a loong wait follow by a "400 No more rows to fetch".

## Task lists

### Lending requests

````php
$library = $alma->conf->libraries['LIBRARY_CODE'];
$requests = $alma->taskLists->getLendingRequests($library, [
    'printed' => 'N',
    'status' => 'REQUEST_CREATED_LEND',
]);
foreach ($requests as $request) {
    echo "- {$request->request_id} {$request->status}\n";
}
````

Note: As of 2018-10-13, there is a bug preventing you from retrieving more than 100 lending requests.

### Requested resources (pick from shelf)

```php
$library = $alma->libraries['LIBRARY_CODE'];
$requests = $alma->taskLists->getRequestedResources($library, 'DEFAULT_CIRC_DESK', [
    'printed' => 'N',
]);
foreach ($requests as $request) {
    echo "- {$request->resource_metadata->title} {$request->resource_metadata->mms_id->value}\n";
}
```

## Jobs

### Listing jobs

To list all jobs and their instances:

```php
foreach ($alma->jobs as $job) {
    echo "[{$job->id}] {$job->name} / {$job->description}\n";
    foreach ($job->instances as $instance) {
        echo "    [{$instance->id}] Status: {$instance->status->desc}\n";
    }
}
```

This is a generator, so you can start processing results before the full list has been retrieved (it fetches jobs in batches of 10).

### Retrieving information about a specific job

```php
$job = $alma->jobs['M43'];
```

### Submitting a job

```php
$instance = $alma->jobs['M43']->submit();
```

## Code Tables

### Getting a Code Table

To fetch a code table

```php
$ct = $alma->codetables->get('systemJobStatus');
echo "Rows for ".$ct->sub_system->value."'s ".$ct->name."\n";
foreach ($ct->row as $row) {
	echo "code: ".$row->code.", description: ".$row->description."\n";
}
```

## Automatic retries on errors

If the client receives a 429 (rate limiting) response from Alma, it will sleep for a short time (0.5 seconds by default)
and retry the request a configurable number of times (10 by default), before giving up and throwing a
`Scriptotek\Alma\Exception\MaxNumberOfAttemptsExhausted`.
Both the max number of attempts and the sleep time can be configured:

```php
$client->maxAttempts = 5;
$client->sleepTimeOnRetry = 3;  // seconds
```

If the client receives a 5XX server error, it will by default not retry the request, but this can be configured.
This can be useful when retrieving large Analytics reports, which have a tendency to fail intermittently.

```php
$client->maxAttemptsOnServerError = 10;
$client->sleepTimeOnServerError = 10;  // seconds
```

When the number of retries have been exhausted, a `Scriptotek\Alma\Exception\RequestFailed` exception is thrown.

## Laravel integration

This project ships with an auto-discoverable service provider and facade. Run

    $ php artisan vendor:publish --provider="Scriptotek\Alma\Laravel\ServiceProvider"

to create the `config/alma.php` configuration file.

If you are on Laravel 5.4 or older, you must manually add the service provider to the
`$providers` array in your `config/app.php`:

    Scriptotek\Alma\Laravel\ServiceProvider::class,

And the facade:

    'Alma' => Scriptotek\Alma\Laravel\Facade::class,

### Customizing the HTTP client stack

If the Laravel [Service Container](https://laravel.com/docs/master/providers)
contains a PSR-18 HTTP Client bound to `Psr\Http\Client\ClientInterface`,
Alma Client will use that implementation instead of instantiating its own HTTP
client.

Here's an example service provider that registers a HTTP client with some middleware
from [php-http/client-common](http://docs.php-http.org/en/latest/plugins/introduction.html#install):

```php
<?php

namespace App\Providers;

use Http\Client\Common\Plugin\ContentLengthPlugin;
use Http\Client\Common\Plugin\ErrorPlugin;
use Http\Client\Common\Plugin\RetryPlugin;
use Http\Client\Common\PluginClient;
use Http\Factory\Discovery\HttpClient;
use Illuminate\Support\ServiceProvider;
use Psr\Http\Client\ClientInterface;

class HttpServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(ClientInterface::class, function($app) {
            return new PluginClient(
                HttpClient::client(),
                [
                    new ContentLengthPlugin(),
                    new RetryPlugin([
                        'retries' => 10,
                    ]),
                    new ErrorPlugin(),
                ]
            );
        });
    }
}
```

## Future plans

- Better support for editing, perhaps better integration with the php-marc package to provide a fluent editing interface:

```php
$bib = $alma->bibs->get('990114012304702204');  // a Bib object
$bib->record->subjects->add([
	'term' => 'Boating with cats',
	'vocabulary' => noubomn'
]);
$bib->save()
```

- Support for creating records and users:

```php
$bib = new Bib();
$alma->bibs->store($bib);
```

