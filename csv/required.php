<?php
include 'CsvFileIterator.php';

$filePath = 'data.csv';
$requiredFields = array(
	'id',
	'name',
	'text',
	'timestamp',
);
$iterator = new CsvFileIterator($filePath, $requiredFields);

foreach ($iterator as $key => $row) {
	echo $key . ": ID: " . $row['id'] . " Name: " . $row['name'] . "\n";

}