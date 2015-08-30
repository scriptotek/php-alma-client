[![Build Status](https://img.shields.io/travis/scriptotek/php-alma-client.svg)](https://travis-ci.org/scriptotek/php-alma-client)
[![Scrutinizer code quality](https://scrutinizer-ci.com/g/scriptotek/php-alma-client/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/scriptotek/php-alma-client/?branch=master)
[![StyleCI](https://styleci.io/repos/35571779/shield)](https://styleci.io/repos/35571779)

# php-alma-client

Simple PHP package for working with the [Alma REST APIs](https://developers.exlibrisgroup.com/alma/apis).
SOAP APIs will not be supported.
Currently, this package only supports the "Bibs" API, but it provides read/write support.
If the package doesn't fit your needs, you might take a look at [php-alma](https://github.com/BCLibraries/php-alma).

### Install using Composer

If you have [Composer](https://getcomposer.org) installed, just run

```bash
composer require scriptotek/alma-client dev-master
```

in your project directory to get the latest version of the package.

## Tutorial

First initiate a new client object:

```php
require_once('vendor/autoload.php');
use Scriptotek\Alma\Client as AlmaClient;

$apiKey = 'MYSECRETKEY';

$alma = new AlmaClient($apiKey);
```

To update a bibliographic record:

```php
$bib = $alma->bibs['990114012304702204'];  // a Bib object
echo $bib->mms_id
$record =  $bib->record; 
$record->getField('650')->getSubField('a')->setValue('Yo!');
$bib->record = $record;
$bib->save()
```

Batch update:

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
	$record =  $bib->record;

	$subjects = $record->getSubjects('noubomn');
	foreach ($subjects as $s) {
		if ($s == 'Undervannsakustikk') {
			$s->delete();
		}
	}
	$record->add
	$subjects->remove('Undervannsakustikk');
	$subjects->add('Hydroakustikk');

	$bib->save($record)
}
```


New entry: (are we allowed to do this??)

```php
$bib = new Bib();
$alma->bibs->store($bib);
```

Holdings and items:

```php
$bib = $alma->bibs['990114012304702204'];
foreach ($bib->holdings() as $holding) {
    foreach ($holding->items() as $item) {
        echo $item->id;
    }
}
```


Note that bibliographic data is only loaded when needed,
so the following holdings request would not cause bibliographic
data to be loaded:

```php
$holdings = $alma->bibs['990114012304702204']->holdings
$items = $holdings[0]->items()
```

