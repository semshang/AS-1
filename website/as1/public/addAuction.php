<?php
require_once __DIR__ . '/init.php';
session_start();

// Must be logged in — redirect before any output
if (!isLoggedIn()) {
    header('Location: /login.php');
    exit;
}

$db    = getDB();
$error = '';

$categories = $db->query("SELECT id, name FROM category ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title']       ?? '');
    $description = trim($_POST['description'] ?? '');
    $categoryId  = (int) ($_POST['category']  ?? 0);
    $endDate     = trim($_POST['endDate']      ?? '');

    if (empty($title) || empty($description) || !$categoryId || empty($endDate)) {
        $error = 'All fields are required.';
    } elseif (strtotime($endDate) <= time()) {
        $error = 'The auction end date must be in the future.';
    } else {
        $imageName = null;

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
                    $imageName = null;
                }
            }
        }

        if (!$error) {
            $ins = $db->prepare("
                INSERT INTO auction (title, description, categoryId, userId, endDate, image)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $ins->execute([$title, $description, $categoryId, $_SESSION['userId'], $endDate, $imageName]);

            header('Location: /auction.php?id=' . $db->lastInsertId());
            exit;
        }
    }
}

$pageTitle = 'List a Car';
require_once __DIR__ . '/header.php';
?>

<main>
    <h1>List Your Car for Auction</h1>

    <?php if ($error): ?>
        <p class="flash-error"><?= e($error) ?></p>
    <?php endif; ?>

    <form action="/addAuction.php" method="POST" enctype="multipart/form-data">

        <label>Car Title / Name</label>
        <input type="text" name="title"
               value="<?= isset($_POST['title']) ? e($_POST['title']) : '' ?>" />

        <label>Category</label>
        <select name="category" style="flex-grow:1; width:20vw; margin-bottom:1em; margin-right:2vw; margin-left:2vw; font-size:1em; padding:0.3em;">
            <option value="">-- Select a category --</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>"
                    <?= (isset($_POST['category']) && $_POST['category'] == $cat['id']) ? 'selected' : '' ?>>
                    <?= e($cat['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Description</label>
        <textarea name="description"
                  style="width:100%; height:12em; flex-basis:100%; margin-left:2vw; margin-right:2vw;"
        ><?= isset($_POST['description']) ? e($_POST['description']) : '' ?></textarea>

        <label>Auction End Date &amp; Time</label>
        <input type="datetime-local" name="endDate"
               value="<?= isset($_POST['endDate']) ? e($_POST['endDate']) : '' ?>" />

        <label>Car Photo (optional)</label>
        <input type="file" name="image" accept="image/*"
               style="flex-grow:1; width:20vw; margin-bottom:1em; margin-right:2vw; margin-left:2vw;" />

        <input type="submit" value="List Auction" />
    </form>

<?php require_once __DIR__ . '/footer.php'; ?>
