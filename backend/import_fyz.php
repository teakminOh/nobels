<?php
require_once("config.php");

// Connect to database
$db = connectDatabase($hostname, $database, $username, $password);

// Step 1: Read CSV file
$csvFile = './nobel_data/nobel_v5_FYZ.csv'; // Adjust path
$rows = [];

if (($handle = fopen($csvFile, 'r')) !== false) {
    while (($data = fgetcsv($handle, 1000, ',')) !== false) {
        $rows[] = $data;
    }
    fclose($handle);
}

// Skip header
array_shift($rows);

// Step 2: Extract Unique Countries
$all_countries = [];
foreach ($rows as $row) {
    $countries = array_map('trim', explode(',', $row[6])); // Normalize country names
    $all_countries = array_merge($all_countries, $countries);
}
$all_countries = array_unique($all_countries);

// Fetch existing country names
$stmt = $db->query("SELECT country_name FROM countries");
$existing_countries = $stmt->fetchAll(PDO::FETCH_COLUMN);
$new_countries = array_diff($all_countries, $existing_countries);

// Insert new countries
if (!empty($new_countries)) {
    $insertStmt = $db->prepare("INSERT INTO countries (country_name) VALUES (:name)");
    foreach ($new_countries as $country) {
        $insertStmt->execute(['name' => $country]);
    }
}

// Build country ID map
$country_map = [];
$stmt = $db->query("SELECT id, country_name FROM countries");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $country_map[$row['country_name']] = $row['id'];
}

// Step 3: Insert Laureates & Link to Countries
$laureate_map = [];
foreach ($rows as $row) {
    $fullname = trim($row[1] . ' ' . $row[2]);

    // Check if laureate exists
    if (!isset($laureate_map[$fullname])) {
        $stmt = $db->prepare("SELECT id FROM laureates WHERE fullname = :name");
        $stmt->execute(['name' => $fullname]);
        $laureate_id = $stmt->fetchColumn();

        if (!$laureate_id) {
            $stmt = $db->prepare("INSERT INTO laureates (fullname, sex, birth_year, death_year) VALUES (:name, :sex, :birth, :death)");
            $stmt->execute([
                'name' => $fullname,
                'sex' => $row[3],
                'birth' => $row[4],
                'death' => $row[5] ?: null,
            ]);
            $laureate_id = $db->lastInsertId();
        }
        $laureate_map[$fullname] = $laureate_id;
    }

    // Link Laureate to Countries
    $countries = array_map('trim', explode(',', $row[6]));
    foreach ($countries as $country) {
        $country_id = $country_map[$country] ?? null;
        if ($country_id) {
            $stmt = $db->prepare("INSERT IGNORE INTO laureates_countries (laureate_id, country_id) VALUES (:lid, :cid)");
            $stmt->execute(['lid' => $laureate_id, 'cid' => $country_id]);
        }
    }
}

// Step 4: Insert Prizes and Link Laureates
$prize_groups = [];
foreach ($rows as $row) {
    $key = $row[0] . '|' . $row[7] . '|' . $row[8]; // year|contrib_sk|contrib_en
    $prize_groups[$key][] = $row;
}

foreach ($prize_groups as $key => $group) {
    list($year, $contrib_sk, $contrib_en) = explode('|', $key);

    // Insert prize
    $stmt = $db->prepare("INSERT INTO prizes (year, category, contrib_sk, contrib_en) VALUES (:year, 'physics', :contrib_sk, :contrib_en)");
    $stmt->execute([
        'year' => $year,
        'contrib_sk' => $contrib_sk,
        'contrib_en' => $contrib_en,
    ]);
    $prize_id = $db->lastInsertId();

    // Link Laureates to Prize
    foreach ($group as $row) {
        $fullname = trim($row[1] . ' ' . $row[2]);
        $laureate_id = $laureate_map[$fullname];

        $stmt = $db->prepare("INSERT IGNORE INTO laureates_prizes (laureate_id, prize_id) VALUES (:lid, :pid)");
        $stmt->execute(['lid' => $laureate_id, 'pid' => $prize_id]);
    }
}

