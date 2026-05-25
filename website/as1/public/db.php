<?php
// Database connection using PDO
// Connects to MySQL via docker-compose service name "mysql"

function getDB() {
    static $pdo = null;

    if ($pdo === null) {
        try {
            $pdo = new PDO(
                'mysql:host=mysql;dbname=assignment1;charset=utf8',
                'root',
                'root'
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Fallback to root if the regular user account has no access to the database.
            if (strpos($e->getMessage(), 'Access denied') !== false) {
                try {
                    $pdo = new PDO(
                        'mysql:host=mysql;dbname=assignment1;charset=utf8',
                        'root',
                        'root'
                    );
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                } catch (PDOException $e) {
                    die('Database connection failed: ' . $e->getMessage());
                }
            } else {
                die('Database connection failed: ' . $e->getMessage());
            }
        }
    }

    return $pdo;
}

// Create the schema and tables if they don't exist yet
function initDB() {
    $pdo = getDB();

    // Users table — stores both regular users and admins
    $pdo->exec("CREATE TABLE IF NOT EXISTS user (
        id       INT AUTO_INCREMENT PRIMARY KEY,
        name     VARCHAR(100) NOT NULL,
        email    VARCHAR(200) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        isAdmin  TINYINT(1)   NOT NULL DEFAULT 0
    )");

    // Car categories managed by admin
    $pdo->exec("CREATE TABLE IF NOT EXISTS category (
        id   INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL
    )");

    // Auctions posted by users
    $pdo->exec("CREATE TABLE IF NOT EXISTS auction (
        id          INT AUTO_INCREMENT PRIMARY KEY,
        title       VARCHAR(200) NOT NULL,
        description TEXT         NOT NULL,
        categoryId  INT          NOT NULL,
        userId      INT          NOT NULL,
        endDate     DATETIME     NOT NULL,
        image       VARCHAR(255) DEFAULT NULL,
        createdAt   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (categoryId) REFERENCES category(id),
        FOREIGN KEY (userId)     REFERENCES user(id)
    )");

    // Reviews left for a user (not a specific auction)
    $pdo->exec("CREATE TABLE IF NOT EXISTS review (
        id         INT AUTO_INCREMENT PRIMARY KEY,
        reviewText TEXT     NOT NULL,
        reviewerId INT      NOT NULL,
        targetId   INT      NOT NULL,
        auctionId  INT      NOT NULL,
        createdAt  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (reviewerId) REFERENCES user(id),
        FOREIGN KEY (targetId)   REFERENCES user(id),
        FOREIGN KEY (auctionId)  REFERENCES auction(id)
    )");

    // Bids placed on auctions
    $pdo->exec("CREATE TABLE IF NOT EXISTS bid (
        id        INT AUTO_INCREMENT PRIMARY KEY,
        bid       DECIMAL(10,2) NOT NULL,
        auctionId INT           NOT NULL,
        userId    INT           NOT NULL,
        createdAt DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (auctionId) REFERENCES auction(id),
        FOREIGN KEY (userId)    REFERENCES user(id)
    )");

    // Seed a default admin account if none exists
    $stmt = $pdo->query("SELECT COUNT(*) FROM user WHERE isAdmin = 1");
    if ($stmt->fetchColumn() == 0) {
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO user (name, email, password, isAdmin) VALUES (?, ?, ?, 1)")
            ->execute(['Admin', 'admin@carbuy.com', $hash]);
    }

    // Seed some default categories if table is empty
    $stmt = $pdo->query("SELECT COUNT(*) FROM category");
    if ($stmt->fetchColumn() == 0) {
        $defaults = ['Estate', 'Electric', 'Coupe', 'Saloon', '4x4', 'Sports', 'Hybrid'];
        $ins = $pdo->prepare("INSERT INTO category (name) VALUES (?)");
        foreach ($defaults as $cat) {
            $ins->execute([$cat]);
        }
    }
}

// Run init on every request (cheap — just checks table existence)
initDB();
