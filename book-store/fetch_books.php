<?php
require 'db_connect.php';

try {

    $categories = ['app_development', 'mobile_development', 'ai'];
    $queries = ['web+development', 'mobile+development', 'artificial+intelligence'];

    // Prepare the statement once
    $stmt = mysqli_prepare($conn, "INSERT INTO books (title, author, category) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE title=title");
    if (!$stmt) {
        throw new Exception("Prepare error: " . mysqli_error($conn));
    }

    foreach ($categories as $index => $category) {

        $url = "https://www.googleapis.com/books/v1/volumes?q=" . $queries[$index];

        if ($response === false) {
            echo "Failed to fetch data for category: $category<br>";

        }

        foreach ($data['items'] as $item) {
            $title = $item['book']['title'] ?? 'Unknown Title';
            $author = isset($item['book']['authors']) ? implode(", ", $item['book']['authors']) : 'Unknown';

            // Bind parameters
            mysqli_stmt_bind_param($stmt, "sss", $title, $author, $category);

            // Execute the statement
            if (!mysqli_stmt_execute($stmt)) {
                echo "Insert error for book '$title': " . mysqli_stmt_error($stmt) . "<br>";
            }
        }
    }

    mysqli_stmt_close($stmt);
    echo "Books fetched and stored successfully!";
} catch (Exception $e) {
    echo "An error occurred: " . $e->getMessage();
}

mysqli_close($conn);
?>