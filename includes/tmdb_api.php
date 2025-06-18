<?php
/**
 * MoodifyMe - TMDB API Functions
 * Contains functions for interacting with The Movie Database API
 */

/**
 * Get movie recommendations from TMDB based on emotion
 * @param string $emotion Source emotion
 * @param string $targetEmotion Target emotion
 * @param int $limit Maximum number of movies to return
 * @return array Movie recommendations
 */
function getTMDBMovieRecommendations($emotion, $targetEmotion = 'happy', $limit = 5) {
    // Map emotions to appropriate genres
    $genreMap = [
        'happy' => [35, 10751], // Comedy, Family
        'sad' => [18, 10749], // Drama, Romance
        'angry' => [28, 80], // Action, Crime
        'anxious' => [27, 53], // Horror, Thriller
        'calm' => [99, 36], // Documentary, History
        'excited' => [12, 14], // Adventure, Fantasy
        'bored' => [878, 10752], // Science Fiction, War
        'tired' => [16, 10402], // Animation, Music
        'stressed' => [35, 10751], // Comedy, Family (to relieve stress)
        'neutral' => [18, 36], // Drama, History
        'energetic' => [28, 12], // Action, Adventure
        'focused' => [9648, 878], // Mystery, Science Fiction
        'inspired' => [18, 36], // Drama, History
        'relaxed' => [10749, 35] // Romance, Comedy
    ];

    // Map emotion transitions to appropriate genres
    $transitionMap = [
        'happy_happy' => [35, 10751], // Comedy, Family
        'sad_happy' => [35, 10751], // Comedy, Family
        'angry_calm' => [99, 36], // Documentary, History
        'anxious_calm' => [99, 36], // Documentary, History
        'bored_excited' => [12, 14], // Adventure, Fantasy
        'tired_energetic' => [28, 12], // Action, Adventure
        'stressed_relaxed' => [10749, 35], // Romance, Comedy
        // Add more specific transitions as needed
    ];

    // Check if there's a specific transition mapping
    $transitionKey = strtolower($emotion) . '_' . strtolower($targetEmotion);
    if (isset($transitionMap[$transitionKey])) {
        $genres = $transitionMap[$transitionKey];
    } else {
        // Default to target emotion genres
        $genres = isset($genreMap[strtolower($targetEmotion)]) ? $genreMap[strtolower($targetEmotion)] : $genreMap['happy'];
    }

    // Get primary genre
    $primaryGenre = $genres[0];

    // Build API URL
    $apiKey = TMDB_API_KEY;
    $url = "https://api.themoviedb.org/3/discover/movie?api_key={$apiKey}&with_genres={$primaryGenre}&sort_by=popularity.desc&page=1&vote_count.gte=100";

    // Make API request
    $response = makeAPIRequest($url);

    // Process results
    $movies = [];
    if (isset($response['results'])) {
        $count = 0;
        foreach ($response['results'] as $movie) {
            if ($count >= $limit) break;

            // Skip movies with no poster
            if (empty($movie['poster_path'])) continue;

            $movies[] = [
                'title' => $movie['title'],
                'description' => $movie['overview'],
                'type' => 'movies',
                'source_emotion' => $emotion,
                'target_emotion' => $targetEmotion,
                'content' => "Released: " . substr($movie['release_date'], 0, 4) . ", Rating: " . round($movie['vote_average'], 1) . "/10",
                'image_url' => "https://image.tmdb.org/t/p/w500" . $movie['poster_path'],
                'link' => "https://www.themoviedb.org/movie/" . $movie['id']
            ];

            $count++;
        }
    }

    return $movies;
}

/**
 * Search for movies by title
 * @param string $query Search query
 * @param int $limit Maximum number of results
 * @return array Search results
 */
function searchTMDBMovies($query, $limit = 5) {
    $apiKey = TMDB_API_KEY;
    $query = urlencode($query);
    $url = "https://api.themoviedb.org/3/search/movie?api_key={$apiKey}&query={$query}&page=1";

    $response = makeAPIRequest($url);

    $results = [];
    if (isset($response['results'])) {
        $count = 0;
        foreach ($response['results'] as $movie) {
            if ($count >= $limit) break;

            // Skip movies with no poster
            if (empty($movie['poster_path'])) continue;

            $results[] = [
                'id' => $movie['id'],
                'title' => $movie['title'],
                'description' => $movie['overview'],
                'image_url' => "https://image.tmdb.org/t/p/w500" . $movie['poster_path'],
                'release_date' => $movie['release_date'],
                'rating' => $movie['vote_average']
            ];

            $count++;
        }
    }

    return $results;
}

/**
 * Get movie details by ID
 * @param int $movieId Movie ID
 * @return array|null Movie details or null if not found
 */
function getTMDBMovieDetails($movieId) {
    $apiKey = TMDB_API_KEY;
    $url = "https://api.themoviedb.org/3/movie/{$movieId}?api_key={$apiKey}&append_to_response=credits,videos,recommendations";

    $response = makeAPIRequest($url);

    if (!isset($response['id'])) {
        return null;
    }

    // Process cast
    $cast = [];
    if (isset($response['credits']['cast'])) {
        foreach (array_slice($response['credits']['cast'], 0, 5) as $actor) {
            $cast[] = [
                'name' => $actor['name'],
                'character' => $actor['character'],
                'profile_path' => $actor['profile_path'] ? "https://image.tmdb.org/t/p/w185" . $actor['profile_path'] : null
            ];
        }
    }

    // Process crew (director, writer)
    $director = null;
    $writers = [];
    if (isset($response['credits']['crew'])) {
        foreach ($response['credits']['crew'] as $crew) {
            if ($crew['job'] === 'Director') {
                $director = $crew['name'];
            }
            if ($crew['department'] === 'Writing') {
                $writers[] = $crew['name'];
            }
        }
    }

    // Process videos (trailers)
    $trailer = null;
    if (isset($response['videos']['results'])) {
        foreach ($response['videos']['results'] as $video) {
            if ($video['type'] === 'Trailer' && $video['site'] === 'YouTube') {
                $trailer = "https://www.youtube.com/watch?v=" . $video['key'];
                break;
            }
        }
    }

    // Process recommendations
    $recommendations = [];
    if (isset($response['recommendations']['results'])) {
        foreach (array_slice($response['recommendations']['results'], 0, 3) as $rec) {
            if (empty($rec['poster_path'])) continue;

            $recommendations[] = [
                'id' => $rec['id'],
                'title' => $rec['title'],
                'image_url' => "https://image.tmdb.org/t/p/w185" . $rec['poster_path']
            ];
        }
    }

    // Build result
    $result = [
        'id' => $response['id'],
        'title' => $response['title'],
        'tagline' => $response['tagline'],
        'overview' => $response['overview'],
        'poster_url' => $response['poster_path'] ? "https://image.tmdb.org/t/p/w500" . $response['poster_path'] : null,
        'backdrop_url' => $response['backdrop_path'] ? "https://image.tmdb.org/t/p/w1280" . $response['backdrop_path'] : null,
        'release_date' => $response['release_date'],
        'runtime' => $response['runtime'],
        'genres' => array_map(function($genre) { return $genre['name']; }, $response['genres']),
        'rating' => $response['vote_average'],
        'vote_count' => $response['vote_count'],
        'director' => $director,
        'writers' => $writers,
        'cast' => $cast,
        'trailer' => $trailer,
        'recommendations' => $recommendations
    ];

    return $result;
}

/**
 * Get popular movies
 * @param int $limit Maximum number of movies
 * @return array Popular movies
 */
function getTMDBPopularMovies($limit = 10) {
    $apiKey = TMDB_API_KEY;
    $url = "https://api.themoviedb.org/3/movie/popular?api_key={$apiKey}&page=1";

    $response = makeAPIRequest($url);

    $movies = [];
    if (isset($response['results'])) {
        $count = 0;
        foreach ($response['results'] as $movie) {
            if ($count >= $limit) break;

            // Skip movies with no poster
            if (empty($movie['poster_path'])) continue;

            $movies[] = [
                'id' => $movie['id'],
                'title' => $movie['title'],
                'description' => $movie['overview'],
                'image_url' => "https://image.tmdb.org/t/p/w500" . $movie['poster_path'],
                'release_date' => $movie['release_date'],
                'rating' => $movie['vote_average']
            ];

            $count++;
        }
    }

    return $movies;
}

/**
 * Make API request to TMDB
 * @param string $url API URL
 * @return array Response data
 */
function makeAPIRequest($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

/**
 * Save TMDB movie recommendations to database
 * @param array $movies Movie recommendations
 * @return bool Success status
 */
/**
 * Get movies by specific genre ID
 * @param int $genreId The genre ID from TMDB
 * @param int $limit Maximum number of movies to return
 * @return array Movies in the specified genre
 */
function getTMDBMoviesByGenre($genreId, $limit = 12) {
    $apiKey = TMDB_API_KEY;
    $url = "https://api.themoviedb.org/3/discover/movie?api_key={$apiKey}&with_genres={$genreId}&sort_by=popularity.desc&page=1&vote_count.gte=100";

    // Make API request
    $response = makeAPIRequest($url);

    // Process results
    $movies = [];
    if (isset($response['results'])) {
        $count = 0;
        foreach ($response['results'] as $movie) {
            if ($count >= $limit) break;

            // Skip movies with no poster
            if (empty($movie['poster_path'])) continue;

            // Get source and target emotions from parameters
            $sourceEmotion = isset($_GET['source']) ? $_GET['source'] : 'neutral';
            $targetEmotion = isset($_GET['target']) ? $_GET['target'] : 'happy';

            // Make sure they're safe strings
            $sourceEmotion = preg_replace('/[^a-zA-Z0-9_]/', '', $sourceEmotion);
            $targetEmotion = preg_replace('/[^a-zA-Z0-9_]/', '', $targetEmotion);

            $movies[] = [
                'title' => $movie['title'],
                'description' => $movie['overview'],
                'type' => 'movies',
                'source_emotion' => $sourceEmotion,
                'target_emotion' => $targetEmotion,
                'content' => "Released: " . substr($movie['release_date'], 0, 4) . ", Rating: " . round($movie['vote_average'], 1) . "/10",
                'image_url' => "https://image.tmdb.org/t/p/w500" . $movie['poster_path'],
                'link' => "https://www.themoviedb.org/movie/" . $movie['id']
            ];

            $count++;
        }
    }

    return $movies;
}

/**
 * Save TMDB movie recommendations to database
 * @param array $movies Movie recommendations
 * @return bool Success status
 */
function saveTMDBMovieRecommendations($movies) {
    global $conn;

    if (empty($movies)) {
        return false;
    }

    $success = true;

    foreach ($movies as $movie) {
        $stmt = $conn->prepare("INSERT INTO recommendations (title, description, type, source_emotion, target_emotion, content, image_url, link, created_at)
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");

        $stmt->bind_param("ssssssss",
            $movie['title'],
            $movie['description'],
            $movie['type'],
            $movie['source_emotion'],
            $movie['target_emotion'],
            $movie['content'],
            $movie['image_url'],
            $movie['link']
        );

        if (!$stmt->execute()) {
            $success = false;
        }
    }

    return $success;
}
