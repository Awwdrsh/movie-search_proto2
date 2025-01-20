<?php
$serverName = "localhost";
$userName = "root";
$password = "";
$conn = mysqli_connect($serverName, $userName, $password);

if($conn){
    // echo "Connection Successful <br>";
}
else{
    echo "Failed to connect".mysqli_connect_error();
}
$createDatabase = "CREATE DATABASE IF NOT EXISTS Movies";
if (mysqli_query($conn, $createDatabase)) {
    // echo "Database Created or already Exists <br>";
} else {
    echo "Failed to create database <br>" . mysqli_connect_error();
}
// Select the created database
mysqli_select_db($conn, 'Movies');
// Ensure the table exists
$createTable = "CREATE TABLE IF NOT EXISTS Movie (  
    Movie_Name VARCHAR(255) PRIMARY KEY,
    R_Year VARCHAR(10),
    Poster VARCHAR(255),
    Genre VARCHAR(255),
    Director VARCHAR(255),
    Actors VARCHAR(255),
    Plot VARCHAR(255)
);";
mysqli_query($conn, $createTable);

// Get the movie name from the query string
$movie = isset($_GET['t']) ? mysqli_real_escape_string($conn, $_GET['t']) : "Viking";

// Log the movie name received from the frontend
error_log('Movie searched: ' . $movie);

// Check if the movie exists in the database
$selectAllData = "SELECT * FROM Movie WHERE Movie_Name LIKE '%$movie%'";
$result = mysqli_query($conn, $selectAllData);

// If no movie found in the database
if (mysqli_num_rows($result) == 0) {
    // Fetch movie data from OMDb API
    $url = "http://www.omdbapi.com/?t=" . urlencode($movie) . "&apikey=9fd19167";
    
    // Use cURL instead of file_get_contents for better reliability
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    curl_close($ch);
    
    // Decode the response
    $data = json_decode($response, true);

    // Log the OMDb API response
    error_log('OMDb API response: ' . print_r($data, true));

    if (isset($data['Title'], $data['Year'], $data['Poster'], $data['Genre'], $data['Director'], $data['Actors'], $data['Plot'])) {
        // Escape data before inserting into the database
        $Title = mysqli_real_escape_string($conn, $data['Title']);
        $Year = mysqli_real_escape_string($conn, $data['Year']);
        $Poster = mysqli_real_escape_string($conn, $data['Poster']);
        $Genre = mysqli_real_escape_string($conn, $data['Genre']);
        $Director = mysqli_real_escape_string($conn, $data['Director']);
        $Actors = mysqli_real_escape_string($conn, $data['Actors']);
        $Plot = mysqli_real_escape_string($conn, $data['Plot']);

        // Insert the movie data into the database
        $insertData = "INSERT INTO Movie (Movie_Name, R_Year, Poster, Genre, Director, Actors, Plot)
                       VALUES ('$Title', '$Year', '$Poster', '$Genre', '$Director', '$Actors', '$Plot')";
        if (!mysqli_query($conn, $insertData)) {
            error_log('Error inserting data: ' . mysqli_error($conn)); // Log any insert error
            echo json_encode(["error" => "Error inserting data into the database."]);
            exit;
        } else {
            error_log('Successfully inserted movie into database: ' . $Title);
        }
    } else {
        // If movie is not found in the OMDb API, return an error
        echo json_encode(["error" => "Movie not found in OMDb API."]);
        exit;
    }

    // Query the database again after inserting
    $result = mysqli_query($conn, $selectAllData);
}

// Fetch all rows from the database and prepare for output
$rows = [];
while ($row = mysqli_fetch_assoc($result)) {
    $rows[] = $row;
}

// Return the data as a JSON response
header('Content-Type: application/json');
echo json_encode($rows);

// Close the database connection
mysqli_close($conn);
?>
