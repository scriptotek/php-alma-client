[![Build Status](https://img.shields.io/travis/scriptotek/php-alma-client.svg)](https://travis-ci.org/scriptotek/php-alma-client)
[![Scrutinizer code quality](https://scrutinizer-ci.com/g/scriptotek/php-alma-client/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/scriptotek/php-alma-client/?branch=master)
[![StyleCI](https://styleci.io/repos/35571779/shield)](https://styleci.io/repos/35571779)
[![Packagist](https://img.shields.io/packagist/v/scriptotek/alma-client.svg)](https://packagist.org/packages/scriptotek/alma-client)

# php-alma-client

Simple PHP package for working with the [Alma REST APIs](https://developers.exlibrisgroup.com/alma/apis).
SOAP APIs will not be supported.
Currently, this package only supports the "Bibs" API, but it provides read/write support.
It is tightly integrated with the excellent File_MARC package for editing MARC records.
If the package doesn't fit your needs, you might take a look at the alternative
[php-alma](https://github.com/BCLibraries/php-alma) package.

## Table of Contents

   * [php-alma-client](#php-alma-client)
      * [Table of Contents](#table-of-contents)
      * [Install using Composer](#install-using-composer)
      * [Initializing a client](#initializing-a-client)
      * [Bibliographic records](#bibliographic-records)
         * [Getting a single record](#getting-a-single-record)
         * [The MARC21 record](#the-marc21-record)
         * [Searching for records](#searching-for-records)
         * [Getting linked record from network zone](#getting-linked-record-from-network-zone)
         * [Editing records](#editing-records)
         * [Holdings and items](#holdings-and-items)
      * [Analytics reports](#analytics-reports)
         * [Column names](#column-names)
         * [Filters](#filters)
      * [Users](#users)
      * [Laravel 5 integration](#laravel-5-integration)
      * [Future plans](#future-plans)

*Created by [gh-md-toc](https://github.com/ekalinin/github-markdown-toc)*

## Install using Composer

Make sure you have [Composer](https://getcomposer.org) installed, then run

```bash
composer require scriptotek/alma-client
```

in your project directory to get the latest stable version of the package.

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


## Bibliographic records

### Getting a single record

A bibliographic record can be fetched either by MMS ID:

```php
$bib = $alma->bibs->get('990114012304702204');
```

or by barcode:

```php
$bib = $alma->bibs->fromBarcode('92nf02526');
```

or by ISBN (this requires that you have connected an SRU client):

```php
$bib = $alma->bibs->fromIsbn('9788299308922');
```

### The MARC21 record

The MARC21 record is available as `$bib->marc` in the form of a
[php-marc](https://github.com/scriptotek/php-marc/blob/master/src/Record.php) `Record` object
that extends `File_MARC_Record` from [File_MARC](https://github.com/pear/File_MARC),
meaning you can use all File_MARC methods in addition to the convenience methods from php-marc.

### Searching for records

If you have connected an SRU client (described above), you can search using
the CQL search syntax.

```php
foreach ($alma->bibs->search('alma.dewey_decimal_class_number=530.12') as $bib) {
	$rec = $bib->marc;
	echo "$rec->id: $rec->title\n";
}
```

### Getting linked record from network zone

If you have configured a network zone API key (see above), you can easily
get the network zone record connected to the institution zone record like so:

```php
$nzBib = $bib->getNzRecord();
```

### Editing records

The MARC21 record can easily be edited using the `File_MARC_Record` interface
(see [File_MARC](https://github.com/pear/File_MARC) for documentation):

```php
$record = $bib->marc;

$newSubject = new File_MARC_Data_Field('650', array(
    new File_MARC_Subfield('a', 'Boating with cats'),
    new File_MARC_Subfield('2', 'noubomn'),
), null, '0');
$record->appendField($newSubject);

$bib->save($record);
```

### Holdings and items

To get a MARC21 holding record:

```php
$bib = $alma->bibs->get('990310361044702204');
$holding = $bib->getHolding('22102913020002204');
$marc = $holding->getMarc();
```

Lazy loading: Note that the client uses lazy loading to reduce the number of HTTP
requests. Requests are not made when you instantiate objects, but when you request
data from them. So in this case, a HTTP request for the holding record is first
made when you call `getMarc()`. The response is then cached on the object for
re-use. In the same way, no HTTP request is made for the Bib record in this case,
since we don't request any data from that.

To loop over holdings and items:

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
then one request per holding.

If you're only interested in item info for a single holding, and know the
holding ID, you can get away with a single HTTP request using

```
foreach ($alma->bibs->get('990310361044702204')->getHolding('22102913020002204')->items as $item) {
    // Do stuff
}
```

## Analytics reports

To retrieve the results from a single report:

```php
$report = $alma->analytics->get('UIO,Universitetsbiblioteket/Reports/RSS/Nyhetslister : Fysikk');
foreach ($report->rows as $row) {
    echo $row[0] . ": " . $row[1] . "\n";
}
```

The rows are returned using a generator that takes care of fetching more rows until
the result set is depleted, so you don't have to think about continuation. If you only
want a subset of the rows, you must take care of breaking out of the loop yourself.

### Column names

Unfortunately, the Analytics API doesn't provide column names (an inherent
limitation in OBI according to a comment
[here](https://developers.exlibrisgroup.com/blog/Working-with-Analytics-REST-APIs)),
but as a workaround you can pass a list of column names into the get method to
create a manual mapping (which will of course break if someone decides to re-order
the columns inside OBI... there's just no way to safeguard against that).

```php
$report = $alma->analytics->get(
    'UIO,Universitetsbiblioteket/Reports/RSS/Nyhetslister : Fysikk',
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
    'UIO,Universitetsbiblioteket/Reports/RSS/Nyhetslister : Fysikk',
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

## Users

```php
foreach ($alma->users->search('last_name~Heggø AND first_name~Dan') as $user) {
    echo "$user->first_name $user->last_name ($user->primary_id)\n";
}
```

## Laravel 5 integration

This project ships with a service provider that you can add to the
`$providers` array in your `config/app.php` if you like:

    Scriptotek\Alma\Providers\AlmaServiceProvider::class,

There's also a facade you can add to the `$aliases` array:

    'Alma' => Scriptotek\Alma\Facades\Alma::class,

Run

    $ php artisan vendor:publish --provider="Scriptotek\Alma\Providers\AlmaServiceProvider"

to create the `config/alma.php` configuration file.

## Future plans

In the future, the package might add more abstraction, so you
do, say,


```php
$bib = $alma->bibs->get('990114012304702204');  // a Bib object
$bib->marc->subjects->add([
	'term' => 'Boating with cats',
	'vocabulary' => noubomn'
]);
$bib->save()
```

but that's not supported yet.

Adding a new record: (not tested)

```php
$bib = new Bib();
$alma->bibs->store($bib);
```

Getting holdings and items:

```php
$bib = $alma->bibs->get('990114012304702204');
foreach ($bib->holdings() as $holding) {
    foreach ($holding->items() as $item) {
        echo $item->id;
    }
}
```

