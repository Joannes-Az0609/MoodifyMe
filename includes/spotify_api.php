<?php
/**
 * MoodifyMe Spotify API Integration
 *
 * A simple integration with Spotify API to get music recommendations based on emotions.
 */

/**
 * Get Spotify access token using Client Credentials flow
 * @return string|null Access token or null on failure
 */
function getSpotifyAccessToken() {
    // Get credentials from config
    $clientId = SPOTIFY_CLIENT_ID;
    $clientSecret = SPOTIFY_CLIENT_SECRET;

    // Prepare request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://accounts.spotify.com/api/token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Basic ' . base64_encode($clientId . ':' . $clientSecret),
        'Content-Type: application/x-www-form-urlencoded'
    ]);

    // Execute request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Check for errors
    if ($httpCode != 200) {
        return null;
    }

    // Parse response
    $data = json_decode($response, true);
    if (!isset($data['access_token'])) {
        return null;
    }

    return $data['access_token'];
}

/**
 * Get music recommendations from Spotify based on emotion
 * @param string $sourceEmotion Source emotion
 * @param string $targetEmotion Target emotion
 * @param int $limit Maximum number of tracks to return
 * @return array Track recommendations
 */
function getSpotifyMusicRecommendations($sourceEmotion, $targetEmotion, $limit = 10) {
    // Get access token
    $accessToken = getSpotifyAccessToken();
    if (!$accessToken) {
        return [];
    }

    // Map emotions to search terms
    $searchTerms = [
        'happy' => 'happy upbeat cheerful',
        'sad' => 'sad melancholy emotional',
        'angry' => 'angry intense powerful',
        'anxious' => 'calm relaxing peaceful',
        'calm' => 'calm ambient peaceful',
        'excited' => 'energetic upbeat dance',
        'bored' => 'interesting upbeat catchy',
        'tired' => 'relaxing soft gentle',
        'energetic' => 'energetic workout pump',
        'focused' => 'focus concentration study',
        'inspired' => 'inspiring motivational epic',
        'relaxed' => 'relaxing chill lofi',
        'stressed' => 'meditation relaxing calm'
    ];

    // Get search term based on target emotion
    $searchTerm = isset($searchTerms[strtolower($targetEmotion)])
        ? $searchTerms[strtolower($targetEmotion)]
        : 'happy music';

    // Build URL for search API
    $url = 'https://api.spotify.com/v1/search?q=' . urlencode($searchTerm) . '&type=track&limit=' . $limit;

    // Make API request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken
    ]);

    // Execute request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Check for errors
    if ($httpCode != 200) {
        return [];
    }

    // Parse response
    $data = json_decode($response, true);
    if (!isset($data['tracks']) || !isset($data['tracks']['items']) || empty($data['tracks']['items'])) {
        return [];
    }

    // Format tracks for our application
    $tracks = [];
    foreach ($data['tracks']['items'] as $track) {
        // Get artists
        $artists = [];
        foreach ($track['artists'] as $artist) {
            $artists[] = $artist['name'];
        }

        // Format track data
        $tracks[] = [
            'title' => $track['name'],
            'description' => 'By ' . implode(', ', $artists),
            'type' => 'music',
            'source_emotion' => $sourceEmotion,
            'target_emotion' => $targetEmotion,
            'content' => 'Album: ' . $track['album']['name'] . ', Released: ' . substr($track['album']['release_date'], 0, 4),
            'image_url' => !empty($track['album']['images']) ? $track['album']['images'][0]['url'] : '',
            'link' => $track['external_urls']['spotify'],
            'preview_url' => $track['preview_url'],
            'artist' => implode(', ', $artists),
            'album' => $track['album']['name']
        ];
    }

    return $tracks;
}

/**
 * Save Spotify music recommendations to database
 * @param array $tracks Track recommendations
 * @return bool Success status
 */
function saveSpotifyMusicRecommendations($tracks) {
    global $conn;

    if (empty($tracks)) {
        return false;
    }

    $success = true;

    foreach ($tracks as $track) {
        // Check if preview_url is in the track data
        $previewUrl = isset($track['preview_url']) ? $track['preview_url'] : null;

        // Prepare additional data as JSON
        $additionalData = json_encode([
            'preview_url' => $previewUrl,
            'artist' => $track['artist'],
            'album' => $track['album']
        ]);

        $stmt = $conn->prepare("INSERT INTO recommendations (title, description, type, source_emotion, target_emotion, content, image_url, link, additional_data, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

        $stmt->bind_param("sssssssss",
            $track['title'],
            $track['description'],
            $track['type'],
            $track['source_emotion'],
            $track['target_emotion'],
            $track['content'],
            $track['image_url'],
            $track['link'],
            $additionalData
        );

        if (!$stmt->execute()) {
            $success = false;
        }
    }

    return $success;
}