<?php
require_once __DIR__ . '/header.php';

$categoryId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Load the chosen category name
$catStmt = getDB()->prepare("SELECT id, name FROM category WHERE id = ?");
$catStmt->execute([$categoryId]);
$category = $catStmt->fetch();

if (!$category) {
    echo '<main><p>Category not found.</p>';
    require_once __DIR__ . '/footer.php';
    exit;
}

$pageTitle = $category['name'];

// Fetch all active auctions in this category
$stmt = getDB()->prepare("
    SELECT a.id, a.title, a.description, a.endDate, a.image,
           u.name AS userName,
           (SELECT MAX(bid) FROM bid WHERE auctionId = a.id) AS topBid
    FROM auction a
    JOIN user u ON u.id = a.userId
    WHERE a.categoryId = ?
      AND a.endDate > NOW()
    ORDER BY a.endDate ASC
");
$stmt->execute([$categoryId]);
$auctions = $stmt->fetchAll();
?>

<main>
    <h1><?= e($category['name']) ?> Cars</h1>

    <?php if (empty($auctions)): ?>
        <p>No <?= e($category['name']) ?> auctions at the moment.</p>
    <?php else: ?>
        <ul class="carList">
            <?php foreach ($auctions as $auction): ?>
                <li>
                    <?php if ($auction['image'] && file_exists(__DIR__ . '/images/auctions/' . $auction['image'])): ?>
                        <img src="/images/auctions/<?= e($auction['image']) ?>" alt="<?= e($auction['title']) ?>">
                    <?php else: ?>
                        <img src="/car.png" alt="<?= e($auction['title']) ?>">
                    <?php endif; ?>

                    <article>
                        <h2><?= e($auction['title']) ?></h2>
                        <h3><?= e($category['name']) ?></h3>
                        <p><?= e(substr($auction['description'], 0, 300)) ?>...</p>

                        <p class="price">
                            <?php if ($auction['topBid']): ?>
                                Current bid: £<?= number_format($auction['topBid'], 2) ?>
                            <?php else: ?>
                                No bids yet
                            <?php endif; ?>
                        </p>

                        <p style="text-align:right; color:#666; font-size:0.9em;">
                            Ends in: <?= timeRemaining($auction['endDate']) ?>
                        </p>

                        <a class="more auctionLink" href="/auction.php?id=<?= $auction['id'] ?>">
                            More &gt;&gt;
                        </a>
                    </article>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

<?php require_once __DIR__ . '/footer.php'; ?>
