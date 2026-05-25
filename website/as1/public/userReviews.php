<?php
$pageTitle = 'User Reviews';
require_once __DIR__ . '/header.php';

$db     = getDB();
$userId = (int) ($_GET['id'] ?? 0);

// Load the target user
$stmt = $db->prepare("SELECT id, name FROM user WHERE id = ?");
$stmt->execute([$userId]);
$targetUser = $stmt->fetch();

if (!$targetUser) {
    echo '<main><p>User not found.</p>';
    require_once __DIR__ . '/footer.php';
    exit;
}

$pageTitle = 'Reviews for ' . $targetUser['name'];

// Fetch all reviews written about this user
$reviews = $db->prepare("
    SELECT r.reviewText, r.createdAt, u.name AS reviewerName, u.id AS reviewerId
    FROM review r
    JOIN user u ON u.id = r.reviewerId
    WHERE r.targetId = ?
    ORDER BY r.createdAt DESC
");
$reviews->execute([$userId]);
$reviews = $reviews->fetchAll();
?>

<main>
    <h1>Reviews for <?= e($targetUser['name']) ?></h1>

    <?php if (empty($reviews)): ?>
        <p>No reviews yet for this user.</p>
    <?php else: ?>
        <ul class="reviews" style="list-style:none; padding:0;">
            <?php foreach ($reviews as $review): ?>
                <li style="padding:1em 0; border-bottom:1px solid #ddd;">
                    <strong>
                        <a href="/userReviews.php?id=<?= $review['reviewerId'] ?>">
                            <?= e($review['reviewerName']) ?>
                        </a>
                        said:
                    </strong>
                    <?= e($review['reviewText']) ?>
                    <br>
                    <small style="color:#888;"><?= e(date('d/m/Y', strtotime($review['createdAt']))) ?></small>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <p style="margin-top:2em;"><a href="javascript:history.back()">&larr; Back</a></p>

<?php require_once __DIR__ . '/footer.php'; ?>
