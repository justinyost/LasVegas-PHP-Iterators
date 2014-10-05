<?php
include './CsvFileIterator.php';

$filePath = '../data.csv';
$iterator = new CsvFileIterator($filePath);

foreach ($iterator as $key => $row) {
	echo $key . ": ID: " . $row['id'] . " Name: " . $row['name'] . "\n";
}