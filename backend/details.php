<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:3000');

require_once __DIR__ . '/config.php';

try {
    $db = connectDatabase($hostname, $database, $username, $password);
    $id = isset($_GET['id']) ? (int)$_GET['id'] : null;

    if (!$id) {
        throw new Exception('No ID provided');
    }

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
        WHERE l.id = :id
        GROUP BY l.id
    ";

    $stmt = $db->prepare($query);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $laureate = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$laureate) {
        throw new Exception('Laureate not found');
    }

    $laureate['prizes'] = json_decode($laureate['prizes'], true);

    echo json_encode(['laureate' => $laureate]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}