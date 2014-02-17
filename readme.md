# Monga [![Build Status](https://secure.travis-ci.org/league/monga.png?branch=master)](https://travis-ci.org/league/monga)

A simple and swift MongoDB abstraction layer for PHP5.3+

[Find Monga on Packagist/Composer](https://packagist.org/packages/league/monga)

## What's this all about?

* An easy API to get connections, databases and collection.
* A filter builder that doesn't make your mind go nuts.
* All sorts of handy update functions.
* An abstraction for sorting single results.
* GridFS support for a Mongo filesystem.
* Easy aggregation and distinct values.

## Vision

Monga was created with the acknowledgement of the MongoDB PHP package already being pretty awesome. That's why in a lot of cases Monga is just a simple wrapper around the MongoDB classes.
It provides some helpers and helps you set up queries using a query builder. Which you cal also choose not to use! All will still works accordingly.
During the development a lot of planning has gone into creating a nice streamlined API that closely follows the MongoDB base classes, while complementing existing query builder for SQL-like database.

## Examples

```php

use League\Monga;

// Get a connection
$connection = Monga::connection($dns, $connectionOptions);

// Get the database
$database = $connection->database('db_name');

// Drop the database
$database->drop();

// Get a collection
$collection = $database->collection('collection_name');

// Drop the collection
$collection->drop();

// Truncate the collection
$collection->truncate();

// Insert some values into the collection
$insertIds = $collection->insert(array(
	array(
		'name' => 'John',
		'surname' => 'Doe',
		'nick' => 'The Unknown Man',
		'age' => 20,
	),
	array(
		'name' => 'Frank',
		'surname' => 'de Jonge',
		'nick' => 'Unknown',
		'nik' => 'No Man',
		'age' => 23,
	),
));

// Update a collection
$collection->update(function($query){
	$query->increment('age')
		->remove('nik')
		->set('nick', 'FrenkyNet');
});

// Find Frank
$frank = $collection->findOne(function($query){
	$query->where('name', 'Frank')
		->whereLike('surname', '%e Jo%');
});

// Or find him using normal array syntax
$frank = $collection->find(array('name' => 'Frank', 'surname' => new MongoRegex('/e Jo/imxsu')));

$frank['age']++;

$collection->save($frank);

// Also supports nested queries
$users = $collection->find(function($query){
	$query->where(function($query){
		$query->where('name', 'Josh')
			->orWhere('surname', 'Doe');
	})->orWhere(function(){
		$query->where('name', 'Frank')
			->where('surname', 'de Jonge');
	});
});

// get the users as an array
$arr = $users->toArray();
```

## Aggregation

A big part of the newly released MongoDB pecl package is aggregation support. Which is super easy to do with Monga:

```php
$collection->aggregate(function($a){
	$a->project(array(
		'name' => 1,
		'surname' => -1,
		'tags' => 1,
	))->unwind('tags');

	// But also more advanced groups/projections
	$a->project(function($p){
		$p->select('field')
			->select('scores')
			->exclude('other_field');
	})->group(function($g){
		$g->by(array('$name', '$surname'))
			->sum('scores');
	});
});
```

If you need any help, come find me in the IRC channels (#php-loep by the nick of: FrenkyNet)

Enjoy!
