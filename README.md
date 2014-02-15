#Mehen [![Build Status](https://travis-ci.org/mageekguy/mehen.png?branch=master)](https://travis-ci.org/mageekguy/mehen)

Mehen is a tool written in PHP to forward ports between two hosts via [SSH](http://fr.wikipedia.org/wiki/Secure_Shell).  
To use it, SSH should be available on the local host and sshd should be running on the remote host.  
Moreover, you should use a PHP version ≥ 5.3.  
For example, to forward the port 3307 from your local host to the port 3306 of a remote host to connect to the corresponding MySQL server, just do:
```php
<?php

namespace how\to;

require '/path/to/mehen/autoloader.php';

use mehen;

$tunnel = new mehen\tunnel('yourSshHost', 'yourSshUser', '127.0.0.1', 3307, '127.0.0.1', 3306);
$tunnel
	->setSshPrivateKeyFile('path/to/your/private/key')
	->open()
;

$mysqlClient = new \mysqli('127.0.0.1', 'yourDatabaseUser', 'yourDatabasePassword', 'yourDatabaseName', 3307);

// Do some awesome stuff in your database
$mysqlClient->query(…);

$tunnel->close();
```
