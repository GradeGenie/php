<?php
// $rubrics = [];
// foreach (glob("rubrics/*.json") as $file) {
//     $rubrics[] = json_decode(file_get_contents($file), true);
// }
// echo json_encode($rubrics);
include("c.php");

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT rid, name FROM rubrics";
$result = $conn->query($sql);

$rubrics = array();

if ($result->num_rows > 0) {
    // Output data of each row
    while($row = $result->fetch_assoc()) {
        $rubrics[] = $row;
    }
}

$conn->close();

echo json_encode($rubrics);
?>

