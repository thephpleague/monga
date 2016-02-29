# Changelog

All notable changes to `monga` will be documented in this file.

## 1.2.4 - 2015-02-29

- Updated license year.
- Updatd Update::addToSet() to take mixed values.

## 1.2.3 - 2015-12-08

- Added phpunit in require-dev in composer config.
- Fixed indentation issues.
- Updates query to use direct call to whereNot().

## 1.2.2 - 2015-11-19
- Fixed database existence tests for Mongo 3.
- Removed old Mockery fix.
- Updated CONDUCT.md to 1.3 version.

## 1.2.1 - 2015-10-10
- Added CONDUCT.md
- Added the ability to pass in SSL context options to connections.
- Added the ability to pass query objects into collection methods.
- Updated README.md

## 1.2.0 - 2015-09-22
- Fixed an issue where MongoCollection::find() relied on both $query->where()
and $query->select() to be called in order for the find()s arguments to be in
the correct order.
- Applications that may have code that relied on the mismatched order will want
pin/continue using 1.1.0 or lower.

## 1.1.0 - 2015-02-15
- Drop PHP 5.3 support as it is EOL.
- Bumped copyright years.

## 1.0.6 - 2015-02-11
- Added the ability to set a maximum number of retries for "not master"
MongoCursorExceptions within CRUD operations in the Collection class. Augments
issue #8. Defaults to 1 retry as previously implemented.

## 1.0.5 - 2014-10-28
- Changed Packagist vendor to league from php-loep.

## 1.0.4 - 2015-10-23
- The safe option now injects a w option instead of deprecated safe option.
- Remove usage of deprecated Mongo class in favor of MongoClient.
- Fix PSR2 coding standards.
- General repository cleanup.
