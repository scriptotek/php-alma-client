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

If you have [Composer](https://getcomposer.org) installed, just run

```bash
composer require scriptotek/alma-client dev-master
```

in your project directory to get the latest version of the package.

## Tutorial: Editing a bib record

Start by initiating a new client:

```php
require_once('vendor/autoload.php');
use Scriptotek\Alma\Client as AlmaClient;

$alma = new AlmaClient('MY_SECRET_API_KEY');
```

Optionally, connect [an SRU client](https://github.com/scriptotek/php-sru-client)
if you want to do searches:

```
use Scriptotek\Sru\Client as SruClient;
$alma->setSruClient(new SruClient(
    'https://bibsys-k.alma.exlibrisgroup.com/view/sru/47BIBSYS_UBO',
    ['version' => '1.2']
));
```

A bibliographic record can be fetched either by MMS ID:

```php
$bib = $alma->bibs['990114012304702204'];
```

or by barcode:

```php
$bib = $alma->bibs->fromBarcode('92nf02526');
```

or by ISBN (this requires an SRU client):

```php
$bib = $alma->bibs->fromIsbn('9788299308922');
```

The returned `Bib` object can then easily be edited using the `File_MARC_Record` interface:

```php
$record = $bib->record;  // a File_MARC_Record object
$newSubject = new File_MARC_Data_Field('650', array(
    new File_MARC_Subfield('a', 'Boating with cats'),
    new File_MARC_Subfield('2', 'noubomn'),
), null, '0');
$record->appendField($newSubject);

$bib->save()
```

## Network zone

If your Alma instance is connected to a network zone,
you can easily get the network zone record connected to
the institution zone record:

```
$alma->nz->setKey('MY_SECRET_NETWORK_ZONE_API_KEY');
$nzBib = $bib->getNzRecord();
```

Note that the API key for the network zone is not the same
as the one for the institution zone.

## Future plans

In the future, the package might add more abstraction, so you
do, say,


```php
$bib = $alma->bibs['990114012304702204'];  // a Bib object
$bib->record->subjects->add([
	'term' => 'Boating with cats',
	'vocabulary' => noubomn'
]);
$bib->save()
```

but that's not supported yet.

The Alma APIs don't include a search API, but we can use SRU for that:

```php
$sru = new Sru\Client('...');
$ids = [];
foreach ($sru->search('realfagstermer=Undervannsakustikk') as $record) {
	$ids[] = $record->id;
}
echo "Will update " . count($ids) . " records";
foreach ($ids as $id) {
	$bib = $alma->bibs['990114012304702204'];  // a Bib object
	echo "[$bib->mms_id]\n";
	$record = $bib->record;

	// Using File_MARC:
	$subjects = $record->getSubjects('noubomn');
	foreach ($subjects as $s) {
		if ($s == 'Undervannsakustikk') {
			$s->delete();
		}
	}

	$bib->record = $record;
	$bib->save($record)
}
```

Adding a new record: (not tested)

```php
$bib = new Bib();
$alma->bibs->store($bib);
```

Getting holdings and items:

```php
$bib = $alma->bibs['990114012304702204'];
foreach ($bib->holdings() as $holding) {
    foreach ($holding->items() as $item) {
        echo $item->id;
    }
}
```

