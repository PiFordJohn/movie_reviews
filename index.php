<?php
include 'db.php';
include 'classes/Movies.php';  // Include the Movie class to use its methods
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: user.php");
    exit;
}

// Initialize movies as an empty array to avoid errors when using count()
$movies = [];
$recommended_movies = [];
$trending_movies = [];
$search_results = [];

// Handle search functionality
$search_query = '';
if (isset($_GET['search'])) {
    $search_query = $_GET['search'];
}

try {
    // If there's a search query, fetch matching movies
    if ($search_query != '') {
        $stmt = $conn->prepare("
            SELECT m.movie_id, m.title, m.description, m.release_date, m.avg_rating, g.genre_name
            FROM movies m
            LEFT JOIN genres g ON m.genre_id = g.genre_id
            WHERE m.title LIKE ? OR g.genre_name LIKE ?
        ");
        $stmt->execute(['%' . $search_query . '%', '%' . $search_query . '%']);
        $search_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // If no search query, fetch all movies
        $stmt = $conn->prepare("
            SELECT m.movie_id, m.title, m.description, m.release_date, m.avg_rating, g.genre_name
            FROM movies m
            LEFT JOIN genres g ON m.genre_id = g.genre_id
        ");
        $stmt->execute();
        $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Fetch recommended movies based on a sample movie (e.g., the first movie's genre)
    if (!empty($movies)) {
        $movie = new Movie($conn, $movies[0]['movie_id']); // Use the first movie to get recommendations
        $recommended_movies = $movie->getRecommendedMovies();
    }

    // Fetch trending movies based on the number of reviews
    $movie = new Movie($conn, $movies[0]['movie_id']); // Use the first movie to get trending movies
    $trending_movies = $movie->getTrendingMovies();

} catch (Exception $e) {
    // If there was an error with the query, log it or display a message
    $error_message = "Error fetching movies: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movie Review Platform</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Welcome to the Movie Review Platform</h1>
        <?php if (isset($_SESSION['username'])): ?>
            <p>Hello, <?php echo htmlspecialchars($_SESSION['username']); ?>! <a href="user.php?action=logout">Logout</a></p>
        <?php else: ?>
            <p>Hello, Guest! <a href="user.php">Login</a></p>
        <?php endif; ?>
        
        <!-- Search Form -->
        <form action="index.php" method="GET">
            <input type="text" name="search" placeholder="Search by title or genre" value="<?php echo htmlspecialchars($search_query); ?>" required>
            <button type="submit">Search</button>
        </form>
    </header>

    <!-- Search Results Section -->
    <section class="movies">
        <h2>Available Movies</h2>
        <?php if (!empty($search_results)): ?>
            <h3>Search Results for "<?php echo htmlspecialchars($search_query); ?>"</h3>
            <?php foreach ($search_results as $movie): ?>
                <div class="movie">
                    <h3><?php echo htmlspecialchars($movie['title']); ?></h3>
                    <p><?php echo htmlspecialchars($movie['description']); ?></p>
                    <p>Release Date: <?php echo htmlspecialchars($movie['release_date']); ?></p>
                    <p>Genre: <?php echo htmlspecialchars($movie['genre_name'] ?? 'Unknown'); ?></p>
                    <p>Average Rating: <?php echo htmlspecialchars($movie['avg_rating'] ?? 'No ratings yet'); ?></p>
                    <a href="movie.php?movie_id=<?php echo $movie['movie_id']; ?>">View Details</a>
                </div>
            <?php endforeach; ?>
        <?php elseif (!empty($movies)): ?>
            <?php foreach ($movies as $movie): ?>
                <div class="movie">
                    <h3><?php echo htmlspecialchars($movie['title']); ?></h3>
                    <p><?php echo htmlspecialchars($movie['description']); ?></p>
                    <p>Release Date: <?php echo htmlspecialchars($movie['release_date']); ?></p>
                    <p>Genre: <?php echo htmlspecialchars($movie['genre_name'] ?? 'Unknown'); ?></p>
                    <p>Average Rating: <?php echo htmlspecialchars($movie['avg_rating'] ?? 'No ratings yet'); ?></p>
                    <a href="movie.php?movie_id=<?php echo $movie['movie_id']; ?>">View Details</a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No movies available at the moment.</p>
        <?php endif; ?>
    </section>

    <!-- Recommended Movies Section -->
    <section class="recommended-movies">
        <h2>Recommended Movies</h2>
        <?php if (!empty($recommended_movies)): ?>
            <?php foreach ($recommended_movies as $rec_movie): ?>
                <div class="movie">
                    <h3><?php echo htmlspecialchars($rec_movie['title']); ?></h3>
                    <p>Genre: <?php echo htmlspecialchars($rec_movie['genre_name']); ?></p>
                    <a href="movie.php?movie_id=<?php echo $rec_movie['movie_id']; ?>">View Details</a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No recommendations available at the moment.</p>
        <?php endif; ?>
    </section>

    <!-- Trending Movies Section -->
    <section class="trending-movies">
        <h2>Trending Movies</h2>
        <?php if (!empty($trending_movies)): ?>
            <?php foreach ($trending_movies as $trend_movie): ?>
                <div class="movie">
                    <h3><?php echo htmlspecialchars($trend_movie['title']); ?></h3>
                    <p>Reviews: <?php echo htmlspecialchars($trend_movie['review_count']); ?> reviews</p>
                    <a href="movie.php?movie_id=<?php echo $trend_movie['movie_id']; ?>">View Details</a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No trending movies at the moment.</p>
        <?php endif; ?>
    </section>

</body>
</html>
