<?php
include 'db.php';
include 'classes/Movie.php';
session_start();

// Redirect to login if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: user.php");
    exit;
}

// Fetch movie details
if (isset($_GET['movie_id'])) {
    $movie_id = $_GET['movie_id'];
    $movie = new Movie($conn, $movie_id);
    $movie_details = $movie->getMovieDetails();
    $reviews = $movie->getReviews();
} else {
    echo "Movie not found.";
    exit;
}

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rating'], $_POST['review_text'])) {
    $user_id = $_SESSION['user_id'];
    $rating = $_POST['rating'];
    $review_text = $_POST['review_text'];

    try {
        $movie->addReview($user_id, $rating, $review_text);
        header("Location: movie.php?movie_id=" . $movie_id);
        exit;
    } catch (Exception $e) {
        $error_message = "Failed to add review: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($movie_details['title']); ?> - Reviews</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1><?php echo htmlspecialchars($movie_details['title']); ?></h1>
        <p><a href="index.php">Back to Movies</a></p>
        <p><a href="user.php?action=logout">Logout</a></p>
    </header>
    <section class="movie-details">
        <p><?php echo htmlspecialchars($movie_details['description']); ?></p>
        <p>Release Date: <?php echo htmlspecialchars($movie_details['release_date']); ?></p>
        <p>Average Rating: <?php echo htmlspecialchars($movie_details['avg_rating']); ?></p>
    </section>
    <section class="reviews">
        <h2>Reviews</h2>
        <?php if (!empty($reviews)): ?>
            <?php foreach ($reviews as $review): ?>
                <div class="review">
                    <strong><?php echo htmlspecialchars($review['username']); ?></strong>
                    rated <?php echo htmlspecialchars($review['rating']); ?>/5
                    <p><?php echo htmlspecialchars($review['review_text']); ?></p>
                    <small>Posted on: <?php echo htmlspecialchars($review['created_at']); ?></small>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No reviews yet. Be the first to review!</p>
        <?php endif; ?>

        <h3>Add Your Review</h3>
        <?php if (isset($error_message)): ?>
            <p style="color: red;"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>
        <form method="POST">
            <label for="rating">Rating (1-5):</label>
            <input type="number" name="rating" min="1" max="5" required>
            <label for="review_text">Review:</label>
            <textarea name="review_text" required></textarea>
            <button type="submit">Submit Review</button>
        </form>
    </section>
</body>
</html>
