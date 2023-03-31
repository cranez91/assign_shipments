<?php

function autoloader()
{
    include 'classes/ShipmentHandler.php';
}

spl_autoload_register('autoloader');

if ($argc != 3) {
    echo "Usage: php assign_shipments.php <destinations_file> <drivers_file>\n";
    exit(1);
}

// Loading the list of drivers and destinations from input files
$destinations = file($argv[1], FILE_IGNORE_NEW_LINES);
$drivers = file($argv[2], FILE_IGNORE_NEW_LINES);

$handler = new ShipmentHandler($destinations, $drivers);
$handler->processShipments();