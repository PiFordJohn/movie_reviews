<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: user.php");
    exit;
}

// Fetch user profile details
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username, email FROM users WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch user reviews
$stmt = $conn->prepare("SELECT m.title, r.rating, r.review_text, r.created_at
                        FROM reviews r
                        JOIN movies m ON r.movie_id = m.movie_id
                        WHERE r.user_id = :user_id");
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
</head>
<body>
    <header>
        <h1>Welcome, <?php echo htmlspecialchars($user['username']); ?></h1>
        <a href="index.php">Back to Movies</a>
        <a href="user.php?action=logout">Logout</a>
    </header>

    <h2>Your Profile</h2>
    <p>Email: <?php echo htmlspecialchars($user['email']); ?></p>
    
    <h3>Your Reviews</h3>
    <?php foreach ($reviews as $review): ?>
        <div class="review">
            <strong><?php echo htmlspecialchars($review['title']); ?></strong>
            <p>Rating: <?php echo htmlspecialchars($review['rating']); ?>/5</p>
            <p><?php echo htmlspecialchars($review['review_text']); ?></p>
            <small>Posted on: <?php echo htmlspecialchars($review['created_at']); ?></small>
        </div>
    <?php endforeach; ?>
</body>
</html>
