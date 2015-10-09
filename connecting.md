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

// Optionally set some connection and driver options
$connectionOptions = [
	'username' => 'beak:man',
	'password' => 'p@ssword',
	'ssl' => true
];

$driverOptions = []; // Context options for SSL

$connection = Monga::connection('mongodb://localhost:27017', $connectionOptions, $driverOptions);
~~~

You can find more details around `MongoClient`'s constructor [here](http://php.net/manual/en/mongoclient.construct.php).
For more details about setting context options for the `$driverOptions` variable
above, see [Connecting over SSL](http://php.net/manual/en/mongo.connecting.ssl.php#mongo.connecting.context.ssl).

Now that we've connected to MongoDB, we'll want to specify our database:

~~~ php
// Get the database
$database = $connection->database('db_name');
~~~

Now that we've successfully connected to Mongo and have chosen a database to work
on, we can move on to performing CRUD operations with collections in the
[CRUD Operations](/crud) section.
