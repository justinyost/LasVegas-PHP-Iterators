<?php

$file = file('../data.csv');
$csvArray = array_map('str_getcsv', $file);

if (
	is_array($argv)
	&& array_key_exists(1, $argv)
	&& $argv[1] === "print"
) {
	print_r($csvArray);
	exit();
}

$arrayObject = new ArrayObject( $csvArray );
$arrayIterator = $arrayObject->getIterator();

foreach ($arrayIterator as $key => $row) {
	echo $key . ": ID: " . $row['0'] . " Name: " . $row[1] . "\n";
}