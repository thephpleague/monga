<?php

include './vendor/autoload.php';

// Remove when fix is merged.
Mockery::getConfiguration()->setInternalClassMethodParamMap('MongoCollection', "aggregate", array('$pipeline', '$op = NULL', '$third = NULL'));