<?php
class Movie {
    private $conn;
    private $movie_id;
    private $title;
    private $description;
    private $release_date;
    private $avg_rating;

    public function __construct($conn, $movie_id) {
        $this->conn = $conn;
        $this->movie_id = $movie_id;
        $this->loadMovie();
    }

    private function loadMovie() {
        $stmt = $this->conn->prepare("SELECT * FROM movies WHERE movie_id = ?");
        $stmt->execute([$this->movie_id]);
        $movie = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($movie) {
            $this->title = $movie['title'];
            $this->description = $movie['description'];
            $this->release_date = $movie['release_date'];
            $this->avg_rating = $movie['avg_rating'];
        } else {
            throw new Exception("Movie not found");
        }
    }

    public function getMovieDetails() {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'release_date' => $this->release_date,
            'avg_rating' => $this->avg_rating
        ];
    }

    public function getReviews() {
        $stmt = $this->conn->prepare("
            SELECT r.review_text, r.rating, r.created_at, u.username 
            FROM reviews r
            JOIN users u ON r.user_id = u.user_id
            WHERE r.movie_id = ?
            ORDER BY r.created_at DESC
        ");
        $stmt->execute([$this->movie_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addReview($user_id, $rating, $review_text) {
        $stmt = $this->conn->prepare("INSERT INTO reviews (movie_id, user_id, rating, review_text) VALUES (?, ?, ?, ?)");
        $stmt->execute([$this->movie_id, $user_id, $rating, $review_text]);

        $this->updateAverageRating();
    }

    private function updateAverageRating() {
        $stmt = $this->conn->prepare("SELECT AVG(rating) AS avg_rating FROM reviews WHERE movie_id = ?");
        $stmt->execute([$this->movie_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->avg_rating = $result['avg_rating'];

        $stmt = $this->conn->prepare("UPDATE movies SET avg_rating = ? WHERE movie_id = ?");
        $stmt->execute([$this->avg_rating, $this->movie_id]);
    }
}
