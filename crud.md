---
layout: default
permalink: /crud/
title: CRUD Operations
---

# CRUD Operations

In the previous [Connecting](/connecting) section, we established a connection
to Mongo and chosen a database to work with, available in the `$database`
variable that we've set. Now we are ready to run some CRUD operations on
collections. First, we will want to choose a collection to operate on:

~~~ php
$collection = $database->collection('people');
~~~

## Insert (Create)

Monga makes inserting documents into a collection a snap. `Collection::insert()`
can insert a single document by passing an array of key/value pairs. If you want
to insert multiple documents, you can simply pass an array containing the
key/value arrays of documents. The following is an example of both:

~~~ php
// Insert one document
$collection->insert([
    'name' => 'Bryan',
    'email => 'bryan@bryan-crowe.com',
    'age' => 26
]);

// Insert multiple documents
$collection->insert([
    [
        'name' => 'Bryan',
        'email => 'bryan@bryan-crowe.com',
        'age' => 26
    ],
    [
        'name' => 'Frank',
        'email => 'info@frenky.net',
        'age' => 26
    ],
    [
        'name' => 'Palmer',
        'email' => 'palmer@example.com',
        'age' => 20
    ]
]);
~~~

## Find (Read)

Finding documents with Monga can be done either through the array syntax
or by providing an anonymous function that utilizes the `Query` class. Using an
anonymous function and `Query` object will allow you to do nested queries.
First let's find a single document with the `findOne()` method where the
person's name is Bryan -- once with the array syntax and once using an anonymous
function and the `Query` object:

~~~ php
$person = $collection->findOne([
    'name' => 'Bryan'
]);

$person = $collection->findOne(function ($query) {
    $query->where('name', 'Bryan')
});
~~~

The two finds above are equivalent to one another. Next, we'll do search with
`find()` method that yields multiple results:

~~~ php
$person = $collection->find([
    'age' => 26
]);

$person = $collection->find(function ($query) {
    $query->where('age', 26)
});
~~~

These two finds are also equivalent.

### Nested Queries

Monga also supports nesting queries. The following is an example of nesting
queries to find people named Bryan or Palmer, or anyone who has an age greater
than 21:

~~~ php
$people = $collection->find(function ($query) {
    $query->where(function ($query) {
        $query->where('name', 'Bryan')
            ->orWhere('name', 'Palmer');
    })->orWhere(function () {
        $query->whereGte('age', 21);
    });
});
~~~

## Update

Updating documents can be done using the `update()` method, or by finding a
document, then using the `save()` method after modifying it.

~~~ php
// Increment everyone's age
$collection->update(function ($query) {
    $query->increment('age');
});
~~~

The previous example would increment everyone's age by one. If we only wanted to
increment a single person's age, we could look up the particular document,
increment the age in PHP, then `save()` the document:

~~~ php
// Find the first person with the name "Palmer"
$person = $collection->findOne(function ($query) {
    $query->where('name', 'Palmer');
});

// Increment the age by modifying the key's value
$person['age']++;

// Save the document
$collection->save($person);
~~~

## Delete

Deleting documents works similar to finding and updating documents. In which,
the `remove()` method also can take an array of conditions or a closure using
the `Query` option to remove documents which match. For example:

~~~ php
$collection->remove([
    'age' => 26
]);

$collection->remove(function ($query) {
    $query->where('age', 26);
});
~~~
