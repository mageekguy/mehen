<?php

namespace how\to;

use mehen;

require __DIR__ . '/autoloader.php';

$sshHost = ''; // Set your SSH host here, for example my.ssh.server.org
$sshUser = ''; // Set your SSH user here, for example darkvador
$localHost = ''; // Set your local host here, for example 127.0.0.1
$localPort = ''; // Set your local port here, for example 3307
$remoteHost = ''; // Set your remote host here, for example 127.0.0.1
$remotePort = ''; // Set your remote host here, for example 3306
$databaseUser = ''; // Set your database user here, for example anakin
$databasePassword = ''; // Set your database password here, for example skywalker
$databaseName = ''; // Set your database name here, for example tatouine
$sshPrivateKeyFile = null;

$tunnel = new mehen\tunnel($sshHost, $sshUser, $localHost, $localPort, $remoteHost, $remotePort);
$tunnel
	->setSshPrivateKeyFile($sshPrivateKeyFile)
	->open()
;

$mysqlClient = new \mysqli($remoteHost, $databaseUser, $databasePassword, $databaseName, $localPort);

$tunnel->close();
