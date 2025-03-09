<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:3000'); // Exact origin
header('Access-Control-Allow-Methods: GET, POST, OPTIONS'); // Allow GET (and others if needed)
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true'); // Must be true for credentials

require_once __DIR__ . '/config.php';

try {
    $db = connectDatabase($hostname, $database, $username, $password);

    // Query parameters
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $perPage = isset($_GET['perPage']) ? ($_GET['perPage'] === 'all' ? 'all' : (int)$_GET['perPage']) : 10;
    $year = isset($_GET['year']) && $_GET['year'] !== '' ? $_GET['year'] : null;
    $category = isset($_GET['category']) && $_GET['category'] !== '' ? $_GET['category'] : null;
    $type = isset($_GET['type']) && in_array($_GET['type'], ['all', 'individuals', 'organizations']) ? $_GET['type'] : 'all';
    $sort = isset($_GET['sort']) && in_array($_GET['sort'], ['surname', 'year', 'category']) ? $_GET['sort'] : 'year';
    $order = isset($_GET['order']) && in_array(strtoupper($_GET['order']), ['ASC', 'DESC']) ? strtoupper($_GET['order']) : 'ASC';

    // Base query with subquery for prizes
    $query = "
    SELECT 
        l.id, 
        l.fullname,
        l.organisation,
        l.sex,
        l.birth_year,
        l.death_year,
        GROUP_CONCAT(DISTINCT c.country_name ORDER BY c.country_name ASC) AS countries,
        (
            SELECT JSON_ARRAYAGG(
                JSON_OBJECT(
                    'year', p2.year,
                    'category', p2.category,
                    'contrib_en', p2.contrib_en,
                    'contrib_sk', p2.contrib_sk
                )
            )
            FROM prizes p2
            INNER JOIN laureates_prizes lp2 ON p2.id = lp2.prize_id
            WHERE lp2.laureate_id = l.id
        ) AS prizes
    FROM laureates l
    LEFT JOIN laureates_countries lc ON l.id = lc.laureate_id
    LEFT JOIN countries c ON lc.country_id = c.id
    INNER JOIN laureates_prizes lp ON l.id = lp.laureate_id
    INNER JOIN prizes p ON lp.prize_id = p.id
    ";

    // Filters
    $where = [];
    $params = [];
    if ($year) {
        $where[] = "p.year = :year";
        $params[':year'] = $year;
    }
    if ($category) {
        $where[] = "p.category = :category";
        $params[':category'] = $category;
    }
    if ($type === 'individuals') {
        $where[] = "l.fullname IS NOT NULL AND l.fullname != ''";
    } elseif ($type === 'organizations') {
        $where[] = "l.organisation IS NOT NULL AND l.organisation != ''";
    }

    // Apply filters
    if (!empty($where)) {
        $query .= " WHERE " . implode(" AND ", $where);
    }

    $query .= " GROUP BY l.id";

    // Sorting - Simplified since no duplicates
    if ($sort === 'surname') {
        $sortField = "
            CASE 
                WHEN l.fullname IS NOT NULL AND l.fullname != '' 
                THEN SUBSTRING_INDEX(l.fullname, ' ', -1)
                ELSE l.organisation
            END";
    } elseif ($sort === 'year') {
        $sortField = "MIN(p.year)"; // Sort by earliest prize year
    } else { // category
        $sortField = "MIN(p.category)"; // Sort by earliest category alphabetically
    }
    $query .= " ORDER BY $sortField $order";

    // Pagination
    if ($perPage !== 'all') {
        $offset = ($page - 1) * $perPage;
        $query .= " LIMIT :offset, :perPage";
    }

    // Execute main query
    $stmt = $db->prepare($query);
    if ($perPage !== 'all') {
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->bindValue(':perPage', (int)$perPage, PDO::PARAM_INT);
    }
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $laureates = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Decode the JSON prizes field
    foreach ($laureates as &$laureate) {
        $laureate['prizes'] = json_decode($laureate['prizes'], true);
        // Sort prizes by year within each laureate for consistent display
        usort($laureate['prizes'], function($a, $b) {
            return $a['year'] <=> $b['year'];
        });
    }
    unset($laureate);

    // Count query
    $countQuery = "
    SELECT COUNT(DISTINCT l.id)
    FROM laureates l
    INNER JOIN laureates_prizes lp ON l.id = lp.laureate_id
    INNER JOIN prizes p ON lp.prize_id = p.id
    " . (!empty($where) ? " WHERE " . implode(" AND ", $where) : "");

    // Execute count query
    $countStmt = $db->prepare($countQuery);
    $countStmt->execute($params);
    $total = $countStmt->fetchColumn();

    // Fetch years and categories
    $years = $db->query("SELECT DISTINCT year FROM prizes ORDER BY year ASC")->fetchAll(PDO::FETCH_COLUMN);
    $categories = $db->query("SELECT DISTINCT category FROM prizes ORDER BY category ASC")->fetchAll(PDO::FETCH_COLUMN);

    // Return JSON response
    echo json_encode([
        'laureates' => $laureates,
        'total' => $total,
        'years' => $years,
        'categories' => $categories,
        'page' => $page,
        'perPage' => $perPage
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}