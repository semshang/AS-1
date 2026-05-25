<?php
require_once __DIR__ . '/init.php';
session_start();

if (!isLoggedIn()) {
    header('Location: /login.php');
    exit;
}

$db        = getDB();
$auctionId = (int) ($_GET['id'] ?? 0);
$error     = '';

// Load auction — must belong to this user
$stmt = $db->prepare("SELECT * FROM auction WHERE id = ? AND userId = ?");
$stmt->execute([$auctionId, $_SESSION['userId']]);
$auction = $stmt->fetch();

if (!$auction) {
    header('Location: /');
    exit;
}

$categories = $db->query("SELECT id, name FROM category ORDER BY name ASC")->fetchAll();

// --- Handle delete ---
if (isset($_POST['delete'])) {
    $db->prepare("DELETE FROM bid    WHERE auctionId = ?")->execute([$auctionId]);
    $db->prepare("DELETE FROM review WHERE auctionId = ?")->execute([$auctionId]);
    $db->prepare("DELETE FROM auction WHERE id = ?")->execute([$auctionId]);
    header('Location: /');
    exit;
}

// --- Handle edit ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title']       ?? '');
    $description = trim($_POST['description'] ?? '');
    $categoryId  = (int) ($_POST['category']  ?? 0);
    $endDate     = trim($_POST['endDate']      ?? '');

    if (empty($title) || empty($description) || !$categoryId || empty($endDate)) {
        $error = 'All fields are required.';
    } else {
        $imageName = $auction['image'];

        if (!empty($_FILES['image']['name'])) {
            $allowed  = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $mimeType = mime_content_type($_FILES['image']['tmp_name']);

            if (!in_array($mimeType, $allowed)) {
                $error = 'Only JPEG, PNG, GIF and WebP images are allowed.';
            } else {
                $ext       = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $imageName = uniqid('car_', true) . '.' . $ext;
                $dest      = __DIR__ . '/images/auctions/' . $imageName;

                if (!move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                    $error     = 'Image upload failed. Please try again.';
                    $imageName = $auction['image'];
                }
            }
        }

        if (!$error) {
            $db->prepare("
                UPDATE auction
                SET title = ?, description = ?, categoryId = ?, endDate = ?, image = ?
                WHERE id = ?
            ")->execute([$title, $description, $categoryId, $endDate, $imageName, $auctionId]);

            header('Location: /auction.php?id=' . $auctionId);
            exit;
        }
    }

    // Keep posted values on validation error
    $auction['title']       = $title;
    $auction['description'] = $description;
    $auction['categoryId']  = $categoryId;
    $auction['endDate']     = $endDate;
}

$endDateForInput = date('Y-m-d\TH:i', strtotime($auction['endDate']));

$pageTitle = 'Edit Auction';
require_once __DIR__ . '/header.php';
?>

<main>
    <h1>Edit Auction</h1>

    <?php if ($error): ?>
        <p class="flash-error"><?= e($error) ?></p>
    <?php endif; ?>

    <form action="/editAuction.php?id=<?= $auctionId ?>" method="POST" enctype="multipart/form-data">

        <label>Car Title / Name</label>
        <input type="text" name="title" value="<?= e($auction['title']) ?>" />

        <label>Category</label>
        <select name="category" style="flex-grow:1; width:20vw; margin-bottom:1em; margin-right:2vw; margin-left:2vw; font-size:1em; padding:0.3em;">
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>"
                    <?= $cat['id'] == $auction['categoryId'] ? 'selected' : '' ?>>
                    <?= e($cat['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Description</label>
        <textarea name="description"
                  style="width:100%; height:12em; flex-basis:100%; margin-left:2vw; margin-right:2vw;"
        ><?= e($auction['description']) ?></textarea>

        <label>Auction End Date &amp; Time</label>
        <input type="datetime-local" name="endDate" value="<?= e($endDateForInput) ?>" />

        <label>Replace Car Photo (optional)</label>
        <input type="file" name="image" accept="image/*"
               style="flex-grow:1; width:20vw; margin-bottom:1em; margin-right:2vw; margin-left:2vw;" />

        <input type="submit" value="Save Changes" />
    </form>

    <form action="/editAuction.php?id=<?= $auctionId ?>" method="POST" style="margin-top:2em;"
          onsubmit="return confirm('Are you sure you want to delete this auction?');">
        <input type="submit" name="delete" value="Delete Auction"
               style="background:#c0392b; color:white; border:0; padding:0.5em 1em; cursor:pointer; font-size:1em;" />
    </form>

<?php require_once __DIR__ . '/footer.php'; ?>
