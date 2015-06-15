---
layout: default
permalink: /connecting/
title: Connecting
---

# Connecting

First, we will want to connect to MongoDB. `Monga::connection()` is a simple
wrapper around the `MongoClient` constructor. Its first parameter can accept
either a `MongoClient` object or a DNS string.

~~~ php
use League\Monga;

// Connects mongodb://localhost:27017
$connection = Monga::connection();

// Connect with a username/password and different port:
$connection = Monga::connection('mongodb://beakman:p4ssw0rd@localhost:1337');

// Optionally set some driver options
$connection = Monga::connection('mongodb://localhost:27017', [
	'username' => 'beak:man',
	'password' => 'p@ssword'
]);
~~~

You can find more details around `MongoClient`'s constructor [here](http://php.net/manual/en/mongoclient.construct.php).

Now that we've connected to MongoDB, we'll want to specify our database:

~~~ php
// Get the database
$database = $connection->database('db_name');
~~~

Now that we've succesfully connected to Mongo and have chosen a database to work
on, we can move on to performing CRUD operations with collections in the
[CRUD Operations](/crud) section.
