<?php

use App\Entity\Group;
use App\Entity\User;
use Symfony\Component\Dotenv\Dotenv;

require __DIR__.'/vendor/autoload.php';

$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/.env');

// We use the Symfony Console to get the container
// But a simpler way is to use bin/console or just write a small command.
// Let's create a temporary PHP script that uses Doctrine.

echo "Use php bin/console to create data instead of raw PHP to avoid config issues.\n";
