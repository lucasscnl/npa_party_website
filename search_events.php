<?php
require_once 'config.php';

$query = isset($_GET['q']) ? trim($_GET['q']) : '';

try {
    if ($query === '') {
        $stmt = $pdo->query("SELECT * FROM event_cards ORDER BY event_date ASC");
    } else {
        $stmt = $pdo->prepare("SELECT * FROM event_cards WHERE title LIKE :query OR category LIKE :query ORDER BY event_date ASC");
        $stmt->execute(['query' => '%' . $query . '%']);
    }
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur lors de la recherche des événements : " . $e->getMessage());
}

if (count($events) === 0) {
    echo '<p>Aucun événement trouvé.</p>';
} else {
    foreach ($events as $event): ?>
        <div class="event-card">
            <span class="event-category"><?= htmlspecialchars($event['category']); ?></span>
            <h1 class="event-title"><?= htmlspecialchars($event['title']); ?></h1>
            <div class="event-img">
                <img src="<?= htmlspecialchars($event['image_url']); ?>" alt="">
            </div>
            <div class="event-info">
                <p><i class="fa-solid fa-calendar-days"></i> 
                    <?= date('l j F Y à H:i', strtotime($event['event_date'])); ?></p>
                <p><i class="fa-solid fa-location-dot"></i> 
                    <?= htmlspecialchars($event['location']); ?></p>
            </div>
        </div>
    <?php endforeach;
}
?>
