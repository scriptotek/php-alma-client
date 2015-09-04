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

## Tutorial

First initiate a new client object:

```php
require_once('vendor/autoload.php');
use Scriptotek\Alma\Client as AlmaClient;

$apiKey = 'MYSECRETKEY';

$alma = new AlmaClient($apiKey);
```

To fetch and update a bibliographic record:

```php
$bib = $alma->bibs['990114012304702204'];  // a Bib object

$record = $bib->record;  // a File_MARC_Record object
$newSubject = new File_MARC_Data_Field('650', array(
    new File_MARC_Subfield('a', 'Boating with cats'),
    new File_MARC_Subfield('2', 'noubomn'),
), null, '0');
$record->appendField($newSubject);

$bib->record = $record;
$bib->save()
```

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

