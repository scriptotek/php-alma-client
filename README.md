[![Build Status](https://img.shields.io/travis/scriptotek/php-alma-client.svg)](https://travis-ci.org/scriptotek/php-alma-client)
[![Scrutinizer code quality](https://scrutinizer-ci.com/g/scriptotek/php-alma-client/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/scriptotek/php-alma-client/?branch=master)
[![StyleCI](https://styleci.io/repos/35571779/shield)](https://styleci.io/repos/35571779)

# php-alma-client

Simple PHP package for working with the [Alma REST APIs](https://developers.exlibrisgroup.com/alma/apis).
SOAP APIs will not be supported.
Currently, this package only supports the "Bibs" API, but it provides read/write support.
It is tightly integrated with the excellent File_MARC package for editing MARC records.
If the package doesn't fit your needs, you might take a look at the alternative
[php-alma](https://github.com/BCLibraries/php-alma) package.

### Install using Composer

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

The MARC21 record is available as `$bib->record` in the form of a
[php-marc](https://github.com/scriptotek/php-marc/blob/master/src/Record.php) `Record` object
that extends `File_MARC_Record` from [File_MARC](https://github.com/pear/File_MARC),
meaning you can use all File_MARC methods in addition to the convenience methods from php-marc.

### Searching for records

If you have connected an SRU client (described above), you can search using
the CQL search syntax.

```php
foreach ($alma->bibs->search('alma.dewey_decimal_class_number=530.12') as $bib) {
	$rec = $bib->record;
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
$record = $bib->record;

$newSubject = new File_MARC_Data_Field('650', array(
    new File_MARC_Subfield('a', 'Boating with cats'),
    new File_MARC_Subfield('2', 'noubomn'),
), null, '0');
$record->appendField($newSubject);

$bib->save($record);
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
$bib->record->subjects->add([
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

