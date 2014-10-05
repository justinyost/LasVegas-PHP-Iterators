<?php
include './CsvFileIterator.php';
include './FilterCsvFileIterator.php';

$filePath = '../data.csv';
$requiredFields = array(
	'id',
	'name',
	'text',
	'timestamp',
);
$iterator = new CsvFileIterator($filePath, $requiredFields);
$iterator = new FilterCsvFileIterator($iterator);

foreach ($iterator as $key => $row) {
	echo $key . ": ID: " . $row['id'] . " Name: " . $row['name'] . "\n";
}