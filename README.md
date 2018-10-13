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

  * [Table of Contents](#table-of-contents)
  * [Install using Composer](#install-using-composer)
  * [Initializing a client](#initializing-a-client)
  * [Quick intro](#quick-intro)
  * [Note about lazy-loading and existence-checking](#note-about-lazy-loading-and-existence-checking)
  * [Bibs: Bibliographic records, Holdings, Items](#bibs-bibliographic-records-holdings-items)
     * [Getting a single record](#getting-a-single-record)
     * [The MARC21 record](#the-marc21-record)
     * [Searching for records](#searching-for-records)
     * [Getting linked record from network zone](#getting-linked-record-from-network-zone)
     * [Editing records](#editing-records)
     * [Holdings and items](#holdings-and-items)
  * [Items](#items)
  * [Users, loans, fees and requests](#users-loans-fees-and-requests)
     * [Search](#search)
     * [Loans](#loans)
     * [Fees](#fees)
     * [Requests](#requests)
  * [Analytics reports](#analytics-reports)
     * [Column names](#column-names)
     * [Filters](#filters)
  * [Laravel 5 integration](#laravel-5-integration)
  * [Future plans](#future-plans)

Created by [gh-md-toc](https://github.com/ekalinin/github-markdown-toc)

## Install using Composer

Make sure you have [Composer](https://getcomposer.org) installed, then run

```bash
composer require scriptotek/alma-client
```

in your project directory to get the latest stable version of the package. You
also need a HTTP library. Php-alma-client uses
[HTTPlug discovery](http://php-http.readthedocs.io/en/latest/discovery.html) in
order to not depend on one specific library. If you don't already have a HTTP
library installed, try Guzzle:

```bash
composer require php-http/guzzle6-adapter
```

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

The `$alma` object provides `$alma->bibs` (for Bibs, Holdings, Items), `$alma->users` (for Users),
`$alma->analytics` (for Analytics reports), `$alma->libraries` (for Libraries).

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

## Bibs: Bibliographic records, Holdings, Items

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

## Items

There is a special entrypoint to retrieve an item by barcode:

```php
$item = $alma->items->fromBarcode('92nf02526');
```

## Users, loans, fees and requests

**Note**: Editing is not yet implemented.

### Search

Example:

```php
foreach ($alma->users->search('last_name~Heggø AND first_name~Dan') as $user) {
    echo "{$user->first_name} {$user->last_name} ({$user->primary_id})\n";
}
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

````php
$library = $alma->conf->libraries['LIBRARY_CODE'];
$requests = $alma->taskLists->getRequestedResources($library, 'DEFAULT_CIRC_DESK', [
    'printed' => 'N',
]);
foreach ($requests as $request) {
    echo "- {$request->resource_metadata->title} {$request->resource_metadata->mms_id->value}\n";
}
````

## Laravel 5 integration

This project ships with an auto-discoverable service provider and facade. Run

    $ php artisan vendor:publish --provider="Scriptotek\Alma\Laravel\ServiceProvider"

to create the `config/alma.php` configuration file.

If you are on Laravel 5.4 or older, you must manually add the service provider to the
`$providers` array in your `config/app.php`:

    Scriptotek\Alma\Laravel\ServiceProvider::class,

And the facade:

    'Alma' => Scriptotek\Alma\Laravel\Facade::class,


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

