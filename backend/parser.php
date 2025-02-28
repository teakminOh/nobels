<?php

function parseCSV($filename) {
    $handle = fopen($filename, 'r');
    $data = array();

    while (($row = fgetcsv($handle, 1000, ";")) !== FALSE) { // Fixed 'FLASE' typo
        $data[] = array_filter($row); // Push only non-empty values
    }

    fclose($handle);
    
    if (!empty($data)) {
        unset($data[0]); // Remove header
    }

    return $data;
}

$laureates = parseCSV('./nobel_data/nobel_v5_FYZ.csv');

echo "<pre>";
print_r($laureates);
echo "</pre>";

?>
