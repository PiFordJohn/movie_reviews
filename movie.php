<?php
include 'db.php';
include 'classes/Movies.php';
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
    $avg_rating = $movie->getAverageRating(); // Get the average rating
    $recommended_movies = $movie->getRecommendedMovies(); // Get recommended movies
    $trending_movies = $movie->getTrendingMovies(); // Get trending movies
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
        <p>Average Rating: <?php echo htmlspecialchars($avg_rating); ?>/5</p>
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

    <section class="recommendations">
        <h3>Recommended Movies</h3>
        <?php if (!empty($recommended_movies)): ?>
            <div class="movies">
                <?php foreach ($recommended_movies as $recommendation): ?>
                    <div class="movie">
                        <h4><?php echo htmlspecialchars($recommendation['title']); ?></h4>
                        <p>Genre: <?php echo htmlspecialchars($recommendation['genre_name']); ?></p>
                        <a href="movie.php?movie_id=<?php echo $recommendation['movie_id']; ?>">View Details</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No recommendations available.</p>
        <?php endif; ?>
    </section>

    <section class="trending">
        <h3>Trending Movies</h3>
        <?php if (!empty($trending_movies)): ?>
            <div class="movies">
                <?php foreach ($trending_movies as $trending_movie): ?>
                    <div class="movie">
                        <h4><?php echo htmlspecialchars($trending_movie['title']); ?></h4>
                        <p>Number of Reviews: <?php echo htmlspecialchars($trending_movie['review_count']); ?></p>
                        <a href="movie.php?movie_id=<?php echo $trending_movie['movie_id']; ?>">View Details</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No trending movies at the moment.</p>
        <?php endif; ?>
    </section>
</body>
</html>
