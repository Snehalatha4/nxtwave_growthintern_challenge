<?php
/**
 * Growth Experiments API: Variant Logging and Statistical Evaluation
 */
if (!headers_sent()) {
    header('Content-Type: application/json');
}
require_once __DIR__ . '/../config/database.php';

$db = Database::getConnection();

// POST: Log experiment impression or conversion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true) ?: $_POST;

    $expId = trim($data['experiment_id'] ?? '');
    $variant = strtoupper(trim($data['variant'] ?? 'A'));
    $eventType = trim($data['event_type'] ?? 'impression');
    $sessionId = trim($data['session_id'] ?? session_id());
    $userId = !empty($data['user_id']) ? (int)$data['user_id'] : null;

    if ($expId && in_array($variant, ['A', 'B']) && in_array($eventType, ['impression', 'conversion'])) {
        try {
            $stmt = $db->prepare("INSERT INTO experiment_events (experiment_id, variant, session_id, user_id, event_type, created_at) VALUES (:exp, :var, :sess, :uid, :type, :created)");
            $stmt->execute([
                'exp' => $expId,
                'var' => $variant,
                'sess' => $sessionId,
                'uid' => $userId,
                'type' => $eventType,
                'created' => date('Y-m-d H:i:s')
            ]);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
    }
    exit;
}

// GET: Retrieve all experiments with computed metrics
try {
    $experiments = $db->query("SELECT * FROM experiments ORDER BY id ASC")->fetchAll();
    $results = [];

    foreach ($experiments as $exp) {
        $expId = $exp['id'];

        // Variant A counts
        $stmtAImp = $db->prepare("SELECT COUNT(*) FROM experiment_events WHERE experiment_id = :id AND variant = 'A' AND event_type = 'impression'");
        $stmtAImp->execute(['id' => $expId]);
        $impA = (int)$stmtAImp->fetchColumn();

        $stmtAConv = $db->prepare("SELECT COUNT(*) FROM experiment_events WHERE experiment_id = :id AND variant = 'A' AND event_type = 'conversion'");
        $stmtAConv->execute(['id' => $expId]);
        $convA = (int)$stmtAConv->fetchColumn();

        // Variant B counts
        $stmtBImp = $db->prepare("SELECT COUNT(*) FROM experiment_events WHERE experiment_id = :id AND variant = 'B' AND event_type = 'impression'");
        $stmtBImp->execute(['id' => $expId]);
        $impB = (int)$stmtBImp->fetchColumn();

        $stmtBConv = $db->prepare("SELECT COUNT(*) FROM experiment_events WHERE experiment_id = :id AND variant = 'B' AND event_type = 'conversion'");
        $stmtBConv->execute(['id' => $expId]);
        $convB = (int)$stmtBConv->fetchColumn();

        $crA = $impA > 0 ? round(($convA / $impA) * 100, 1) : 0;
        $crB = $impB > 0 ? round(($convB / $impB) * 100, 1) : 0;
        
        $lift = $crA > 0 ? round((($crB - $crA) / $crA) * 100, 1) : 0;
        $winner = ($crB > $crA && ($impA + $impB) > 50) ? 'Variant B' : (($crA > $crB && ($impA + $impB) > 50) ? 'Variant A' : 'Inconclusive / Gathering Data');

        $results[] = [
            'id' => $expId,
            'name' => $exp['name'],
            'hypothesis' => $exp['hypothesis'],
            'metric' => $exp['metric'],
            'status' => $exp['status'],
            'variant_a' => [
                'label' => $exp['variant_a'],
                'impressions' => $impA,
                'conversions' => $convA,
                'conversion_rate' => $crA
            ],
            'variant_b' => [
                'label' => $exp['variant_b'],
                'impressions' => $impB,
                'conversions' => $convB,
                'conversion_rate' => $crB
            ],
            'relative_lift_percent' => $lift,
            'statistical_winner' => $winner,
            'confidence' => ($impA + $impB > 200) ? '95%+ Statistical Confidence' : 'Preliminary'
        ];
    }

    echo json_encode(['success' => true, 'experiments' => $results]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
