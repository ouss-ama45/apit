 <?php
 date_default_timezone_set('Africa/Casablanca');

include "connect.php";

error_reporting(E_ALL);
ini_set('display_errors', '1');

// Check if the request method is GET (for reading data) or POST (for inserting data)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Fetch data from the "tickets" table
    $user = $_GET['id'];
    $sql = "SELECT * FROM tickets WHERE user = '$user' ORDER BY date DESC ";
    $result = mysqli_query($con, $sql);

    // Check if any rows are returned
    if ($result) {
        // Fetch the data and store it in an array
        $data = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }

        // Apply utf8_encode to each string element in the inner arrays
        $utf8EncodedData = array_map(function ($row) {
            return array_map('utf8_encode', $row);
        }, $data);

        // Return the data as JSON
        header('Content-Type: application/json');
        echo json_encode($utf8EncodedData, JSON_PRETTY_PRINT);
    } else {
        // Query failed
        echo json_encode(['status' => 'error', 'message' => 'Failed to fetch tickets']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Assuming you receive data in JSON format
    $requestData = json_decode(file_get_contents('php://input'), true);

    // Check if the required data is present
    if (isset($requestData['ingredients']) && is_array($requestData['ingredients'])) {
        $ingredients = $requestData['ingredients'];
        $user = $requestData['id'];

        // Loop through the ingredients and insert data into the database
        foreach ($ingredients as $ingredient) {
            $name = $ingredient['name'];
            $price = $ingredient['price'];
            $quantity = $ingredient['quantity'];

            // Check if the quantity is greater than 0 before inserting
            if ($quantity > 0) {
                // Use the current date and time
                $date = date('Y-m-d H:i:s');

                $insertSql = "INSERT INTO tickets (name,price, quantity, date,user) VALUES ('$name','$price', $quantity, '$date','$user')";

                if (mysqli_query($con, $insertSql)) {
                    // Data inserted successfully
                    echo json_encode(['status' => 'success', 'message' => 'Data inserted successfully']);
                } else {
                    // Failed to insert data
                    echo json_encode(['status' => 'error', 'message' => 'Failed to insert data']);
                }
            }
        }
    } else {
        // Invalid or missing data
        echo json_encode(['status' => 'error', 'message' => 'Invalid or missing data']);
    }
} else {
    // Invalid request method
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}

// Close the connection
mysqli_close($con);
?>