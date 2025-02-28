<?php
require_once __DIR__ . '/config.php';

// Connect to database
try {
    $db = connectDatabase($hostname, $database, $username, $password);
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Step 1: Read CSV file
$csvFile = __DIR__ . '/nobel_data/nobel_v5_CHEM.csv'; // Adjust path as needed
$rows = [];

if (!file_exists($csvFile)) {
    die("CSV file not found: $csvFile");
}
if (($handle = fopen($csvFile, 'r')) === false) {
    die("Failed to open CSV file: $csvFile");
}

while (($data = fgetcsv($handle, 1000, ',')) !== false) {
    if (count($data) < 9) {
        error_log("Skipping malformed row: " . implode(',', $data));
        continue;
    }
    $rows[] = $data;
}
fclose($handle);

if (empty($rows)) {
    die("CSV file is empty or unreadable");
}
array_shift($rows); // Skip header

// Step 2: Extract Unique Countries
$all_countries = [];
foreach ($rows as $row) {
    $countries = array_map('trim', explode(',', $row[6]));
    $all_countries = array_merge($all_countries, $countries);
}
$all_countries = array_unique($all_countries);

$stmt = $db->query("SELECT country_name FROM countries");
$existing_countries = $stmt->fetchAll(PDO::FETCH_COLUMN);
$new_countries = array_diff($all_countries, $existing_countries);

if (!empty($new_countries)) {
    $insertStmt = $db->prepare("INSERT INTO countries (country_name) VALUES (:name)");
    foreach ($new_countries as $country) {
        try {
            $insertStmt->execute(['name' => $country]);
        } catch (PDOException $e) {
            error_log("Failed to insert country '$country': " . $e->getMessage());
        }
    }
}

$country_map = [];
$stmt = $db->query("SELECT id, country_name FROM countries");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $country_map[$row['country_name']] = $row['id'];
}

// Step 3: Insert Laureates & Link to Countries
$laureate_map = [];
foreach ($rows as $row) {
    $fullname = trim($row[1] . ' ' . $row[2]);

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

    // Check if prize already exists
    $stmt = $db->prepare("SELECT id FROM prizes WHERE year = :year AND category = 'chemistry' AND contrib_sk = :contrib_sk AND contrib_en = :contrib_en");
    $stmt->execute([
        'year' => $year,
        'contrib_sk' => $contrib_sk,
        'contrib_en' => $contrib_en,
    ]);
    $prize_id = $stmt->fetchColumn();

    if (!$prize_id) {
        $stmt = $db->prepare("INSERT INTO prizes (year, category, contrib_sk, contrib_en) VALUES (:year, 'chemistry', :contrib_sk, :contrib_en)");
        $stmt->execute([
            'year' => $year,
            'contrib_sk' => $contrib_sk,
            'contrib_en' => $contrib_en,
        ]);
        $prize_id = $db->lastInsertId();
    }

    foreach ($group as $row) {
        $fullname = trim($row[1] . ' ' . $row[2]);
        $laureate_id = $laureate_map[$fullname];

        $stmt = $db->prepare("INSERT IGNORE INTO laureates_prizes (laureate_id, prize_id) VALUES (:lid, :pid)");
        $stmt->execute(['lid' => $laureate_id, 'pid' => $prize_id]);
    }
}

echo "Chemistry import completed successfully!\n";