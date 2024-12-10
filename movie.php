<?php
// Include necessary files
include 'db.php';
include 'Movies.php';

// Start session for user authentication
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: user.php");
    exit;
}

try {
    // Check if movie_id is provided in the query string
    if (isset($_GET['movie_id'])) {
        $movie = new Movie($conn, $_GET['movie_id']);
        $movie_details = $movie->getMovieDetails();
        $reviews = $movie->getReviews();
    } else {
        $movie_details = null;
        $reviews = [];
    }
} catch (Exception $e) {
    $movie_details = null;
    $reviews = [];
    $error_message = $e->getMessage();
}

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['rating'])) {
    $user_id = $_SESSION['user_id'];

    if ($user_id) {
        $movie->addReview($user_id, $_POST['rating'], $_POST['review_text']);
        header("Location: movie.php?movie_id=" . $_GET['movie_id']);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($movie_details) ? htmlspecialchars($movie_details['title']) : 'Movie Not Found'; ?> - Details</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1><?php echo isset($movie_details) ? htmlspecialchars($movie_details['title']) : 'Movie Not Found'; ?></h1>
        <nav>
            <a href="user.php?action=logout">Logout</a>
        </nav>
    </header>
    <section class="movie-details">
        <?php if ($movie_details): ?>
            <p><?php echo htmlspecialchars($movie_details['description']); ?></p>
            <p>Release Date: <?php echo htmlspecialchars($movie_details['release_date']); ?></p>
            <p>Average Rating: <?php echo htmlspecialchars($movie_details['avg_rating']); ?></p>
        <?php else: ?>
            <p><?php echo isset($error_message) ? htmlspecialchars($error_message) : 'No movie details available.'; ?></p>
        <?php endif; ?>
    </section>
    <section class="reviews">
        <h3>Reviews:</h3>
        <?php if ($reviews): ?>
            <?php foreach ($reviews as $review): ?>
                <div class="review">
                    <strong><?php echo htmlspecialchars($review['username']); ?></strong> 
                    rated: <?php echo htmlspecialchars($review['rating']); ?>/5
                    <p><?php echo htmlspecialchars($review['review_text']); ?></p>
                    <small>Posted on: <?php echo htmlspecialchars($review['created_at']); ?></small>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No reviews yet. Be the first to review!</p>
        <?php endif; ?>

        <!-- Review Form -->
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
