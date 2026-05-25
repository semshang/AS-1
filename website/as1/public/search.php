<?php
$pageTitle = 'Search Results';
require_once __DIR__ . '/header.php';

$query = trim($_GET['q'] ?? '');
$auctions = [];

if ($query !== '') {
    // Search titles and descriptions; use FULLTEXT-style LIKE for simplicity
    // ORDER BY relevance: title match scores higher than description match
    $like = '%' . $query . '%';
    $stmt = getDB()->prepare("
        SELECT a.id, a.title, a.description, a.endDate, a.image,
               c.name AS categoryName,
               u.name AS userName,
               (SELECT MAX(bid) FROM bid WHERE auctionId = a.id) AS topBid,
               (CASE
                    WHEN a.title LIKE ? THEN 2
                    ELSE 1
                END) AS relevance
        FROM auction a
        JOIN category c ON c.id = a.categoryId
        JOIN user     u ON u.id = a.userId
        WHERE (a.title LIKE ? OR a.description LIKE ?)
          AND a.endDate > NOW()
        ORDER BY relevance DESC, a.endDate ASC
    ");
    $stmt->execute([$like, $like, $like]);
    $auctions = $stmt->fetchAll();
}
?>

<main>
    <h1>
        <?php if ($query): ?>
            Search Results for &ldquo;<?= e($query) ?>&rdquo;
        <?php else: ?>
            Search
        <?php endif; ?>
    </h1>

    <?php if ($query && empty($auctions)): ?>
        <p>No auctions matched your search. Try different keywords.</p>
    <?php elseif (!empty($auctions)): ?>
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
                        <h3><?= e($auction['categoryName']) ?></h3>
                        <p><?= e(substr($auction['description'], 0, 300)) ?>...</p>

                        <p class="price">
                            <?php if ($auction['topBid']): ?>
                                Current bid: £<?= number_format($auction['topBid'], 2) ?>
                            <?php else: ?>
                                No bids yet
                            <?php endif; ?>
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
