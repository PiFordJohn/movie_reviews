<?php
include 'db.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: user.php");
    exit;
}

// Initialize movies as an empty array to avoid errors when using count()
$movies = [];

try {
    // Fetch movies and their genres
    $stmt = $conn->prepare("
        SELECT m.movie_id, m.title, m.description, m.release_date, m.avg_rating, g.genre_name
        FROM movies m
        LEFT JOIN genres g ON m.genre_id = g.genre_id
    ");
    $stmt->execute();
    $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        <p>Hello, <?php echo htmlspecialchars($_SESSION['username']); ?>! <a href="user.php?action=logout">Logout</a></p>
    </header>
    <h2>Available Movies</h2>
    <section class="movies">
        
        <?php if (!empty($movies)): ?>
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
</body>
</html>
