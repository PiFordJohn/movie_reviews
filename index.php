<?php
// Include database connection
include 'db.php';

// Start session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: user.php"); // Redirect to login page
    exit;
}

try {
    // Fetch movies along with genres using a join query
    $stmt = $conn->query("
        SELECT m.movie_id, m.title, m.description, m.release_date, m.avg_rating, g.genre_name 
        FROM movies m
        LEFT JOIN genres g ON m.genre_id = g.genre_id
    ");
    $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $movies = [];
    $error_message = $e->getMessage();
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
        <nav>
            <a href="user.php?action=logout">Logout</a>
        </nav>
    </header>
    <section class="movies">
        <?php if (!empty($movies)): ?>
            <?php foreach ($movies as $movie): ?>
                <div class="movie">
                    <h2><?php echo htmlspecialchars($movie['title']); ?></h2>
                    <p><?php echo htmlspecialchars($movie['description']); ?></p>
                    <p>Release Date: <?php echo htmlspecialchars($movie['release_date']); ?></p>
                    <p>Genre: <?php echo htmlspecialchars($movie['genre_name']); ?></p>
                    <p>Average Rating: <?php echo htmlspecialchars($movie['avg_rating']); ?></p>
                    <a href="movie.php?movie_id=<?php echo $movie['movie_id']; ?>">View Details</a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No movies available at the moment.</p>
            <?php if (isset($error_message)): ?>
                <p>Error: <?php echo htmlspecialchars($error_message); ?></p>
            <?php endif; ?>
        <?php endif; ?>
    </section>
</body>
</html>
