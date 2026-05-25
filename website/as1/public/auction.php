<?php
require_once __DIR__ . '/header.php';

$auctionId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$db = getDB();

// Load the auction
$stmt = $db->prepare("
    SELECT a.*, c.name AS categoryName, u.name AS userName, u.id AS userId
    FROM auction a
    JOIN category c ON c.id = a.categoryId
    JOIN user     u ON u.id = a.userId
    WHERE a.id = ?
");
$stmt->execute([$auctionId]);
$auction = $stmt->fetch();

if (!$auction) {
    echo '<main><p>Auction not found.</p>';
    require_once __DIR__ . '/footer.php';
    exit;
}

$pageTitle = $auction['title'];

// --- Handle bid submission ---
$bidError   = '';
$bidSuccess = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bid'])) {
    if (!isLoggedIn()) {
        $bidError = 'You must be logged in to place a bid.';
    } else {
        $amount = (float) $_POST['bid'];
        // Get the current highest bid
        $topBid = $db->prepare("SELECT MAX(bid) FROM bid WHERE auctionId = ?");
        $topBid->execute([$auctionId]);
        $currentTop = (float) $topBid->fetchColumn();

        if ($amount <= 0) {
            $bidError = 'Please enter a valid bid amount.';
        } elseif ($amount <= $currentTop) {
            $bidError = 'Your bid must be higher than the current bid of £' . number_format($currentTop, 2) . '.';
        } else {
            $ins = $db->prepare("INSERT INTO bid (bid, auctionId, userId) VALUES (?, ?, ?)");
            $ins->execute([$amount, $auctionId, $_SESSION['userId']]);
            $bidSuccess = 'Your bid of £' . number_format($amount, 2) . ' was placed successfully!';
        }
    }
}

// --- Handle review submission ---
$reviewError   = '';
$reviewSuccess = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reviewText'])) {
    if (!isLoggedIn()) {
        $reviewError = 'You must be logged in to leave a review.';
    } else {
        $text = trim($_POST['reviewText']);
        if (empty($text)) {
            $reviewError = 'Review text cannot be empty.';
        } else {
            $ins = $db->prepare("
                INSERT INTO review (reviewText, reviewerId, targetId, auctionId)
                VALUES (?, ?, ?, ?)
            ");
            $ins->execute([$text, $_SESSION['userId'], $auction['userId'], $auctionId]);
            $reviewSuccess = 'Your review was posted.';
        }
    }
}

// Fetch current highest bid
$topBidStmt = $db->prepare("SELECT MAX(bid) FROM bid WHERE auctionId = ?");
$topBidStmt->execute([$auctionId]);
$topBid = $topBidStmt->fetchColumn();

// Fetch bid history
$bidsStmt = $db->prepare("
    SELECT b.bid, b.createdAt, u.name AS bidderName
    FROM bid b
    JOIN user u ON u.id = b.userId
    WHERE b.auctionId = ?
    ORDER BY b.bid DESC
");
$bidsStmt->execute([$auctionId]);
$bidHistory = $bidsStmt->fetchAll();

// Fetch reviews for the auction author
$reviewsStmt = $db->prepare("
    SELECT r.reviewText, r.createdAt, u.name AS reviewerName, u.id AS reviewerId
    FROM review r
    JOIN user u ON u.id = r.reviewerId
    WHERE r.targetId = ?
    ORDER BY r.createdAt DESC
");
$reviewsStmt->execute([$auction['userId']]);
$reviews = $reviewsStmt->fetchAll();
?>

<main>
    <!-- Car Page layout from supplied HTML -->
    <article class="car">

        <!-- Car image -->
        <?php if ($auction['image'] && file_exists(__DIR__ . '/images/auctions/' . $auction['image'])): ?>
            <img src="/images/auctions/<?= e($auction['image']) ?>" alt="<?= e($auction['title']) ?>">
        <?php else: ?>
            <img src="/car.png" alt="<?= e($auction['title']) ?>">
        <?php endif; ?>

        <!-- Details panel -->
        <section class="details">
            <h2><?= e($auction['title']) ?></h2>
            <h3><?= e($auction['categoryName']) ?></h3>
            <p>Auction created by <a href="/userReviews.php?id=<?= $auction['userId'] ?>"><?= e($auction['userName']) ?></a></p>

            <p class="price">
                <?php if ($topBid): ?>
                    Current bid: £<?= number_format($topBid, 2) ?>
                <?php else: ?>
                    No bids yet — be the first!
                <?php endif; ?>
            </p>

            <time>Time left: <?= timeRemaining($auction['endDate']) ?></time>

            <!-- Edit / Delete links for auction owner -->
            <?php if (isLoggedIn() && $_SESSION['userId'] == $auction['userId']): ?>
                <p style="margin-top:1em;">
                    <a href="/editAuction.php?id=<?= $auction['id'] ?>">Edit this auction</a>
                </p>
            <?php endif; ?>

            <!-- Bid form -->
            <?php if (!empty($bidError)):   echo '<p class="flash-error">'   . e($bidError)   . '</p>'; endif; ?>
            <?php if (!empty($bidSuccess)): echo '<p class="flash-success">' . e($bidSuccess) . '</p>'; endif; ?>

            <?php if (isLoggedIn() && new DateTime($auction['endDate']) > new DateTime()): ?>
                <form action="/auction.php?id=<?= $auction['id'] ?>" method="POST" class="bid">
                    <input type="text" name="bid" placeholder="Enter bid amount (£)" />
                    <input type="submit" name="submit" value="Place bid" />
                </form>
            <?php elseif (!isLoggedIn()): ?>
                <p><a href="/login.php">Log in</a> to place a bid.</p>
            <?php endif; ?>
        </section>

        <!-- Full description -->
        <section class="description">
            <p><?= nl2br(e($auction['description'])) ?></p>
        </section>

        <!-- Bid history -->
        <?php if (!empty($bidHistory)): ?>
        <section class="reviews" style="margin-top:2em;">
            <h2>Bid History</h2>
            <ul>
                <?php foreach ($bidHistory as $b): ?>
                    <li>
                        <strong><?= e($b['bidderName']) ?></strong> bid
                        £<?= number_format($b['bid'], 2) ?>
                        <em><?= e(date('d/m/Y H:i', strtotime($b['createdAt']))) ?></em>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
        <?php endif; ?>

        <!-- Reviews section (for the auction's author) -->
        <section class="reviews">
            <h2>Reviews of <?= e($auction['userName']) ?></h2>

            <?php if (empty($reviews)): ?>
                <p>No reviews yet for this seller.</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($reviews as $review): ?>
                        <li>
                            <strong>
                                <a href="/userReviews.php?id=<?= $review['reviewerId'] ?>">
                                    <?= e($review['reviewerName']) ?>
                                </a>
                                said
                            </strong>
                            <?= e($review['reviewText']) ?>
                            <em><?= e(date('d/m/Y', strtotime($review['createdAt']))) ?></em>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <!-- Add review form (logged-in users only) -->
            <?php if (isLoggedIn() && $_SESSION['userId'] != $auction['userId']): ?>
                <?php if (!empty($reviewError)):   echo '<p class="flash-error">'   . e($reviewError)   . '</p>'; endif; ?>
                <?php if (!empty($reviewSuccess)): echo '<p class="flash-success">' . e($reviewSuccess) . '</p>'; endif; ?>

                <form action="/auction.php?id=<?= $auction['id'] ?>" method="POST">
                    <label>Add your review</label>
                    <textarea name="reviewText"></textarea>
                    <input type="submit" name="submit" value="Add Review" />
                </form>
            <?php elseif (!isLoggedIn()): ?>
                <p><a href="/login.php">Log in</a> to leave a review.</p>
            <?php endif; ?>
        </section>

    </article>

<?php require_once __DIR__ . '/footer.php'; ?>
