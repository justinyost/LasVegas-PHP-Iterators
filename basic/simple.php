<?php
include 'SimpleIterator.php';

$filePath = 'data.csv';
$iterator = new SimpleIterator($filePath);

foreach ($iterator as $key => $row) {
	// What's happening under the hood in the foreach (...) parens:
	// `do { $iterator->next(); } while (!$iterator->accept()); $row = $iterator->current();`

	echo $key . ": ID: " . $row['0'] . " Name: " . $row[1] . "\n";
}