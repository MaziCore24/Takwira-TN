<?php
// Connect to the database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "takwira_tn_2";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reserve_field'])) {
    $team1_id = intval($_POST['team1_id']);
    $team2_id = intval($_POST['team2_id']);
    $reservation_datetime = $_POST['reservation_datetime'];

    // Validate datetime
    $current_time = new DateTime();
    $reservation_time = DateTime::createFromFormat('Y-m-d\TH:i', $reservation_datetime);

    if ($reservation_time === false || $reservation_time <= $current_time) {
        echo "Invalid or past datetime.";
        exit;
    }

    // Check for existing reservations
    $stmt = $conn->prepare("SELECT * FROM reservations WHERE (team_id = ? OR team_id = ?) AND reservation_datetime = ?");
    if ($stmt) {
        $stmt->bind_param("iis", $team1_id, $team2_id, $reservation_datetime);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "One or both teams already have a reservation at this time.";
        } else {
            // Insert reservation
            $insert_stmt = $conn->prepare("INSERT INTO reservations (team_id, reservation_datetime) VALUES (?, ?)");
            if ($insert_stmt) {
                $insert_stmt->bind_param("is", $team1_id, $reservation_datetime);
                $insert_stmt->execute();
                echo "Reservation made successfully.";
            } else {
                echo "Error preparing reservation statement: " . $conn->error;
            }
        }
    } else {
        echo "Error preparing select statement: " . $conn->error;
    }
}

$conn->close();
?>
