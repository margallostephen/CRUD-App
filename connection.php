<?php
// Connection for database
$conn = new mysqli("localhost", "root", "password", "crud");

if ($conn->connect_errno)
    echo "Failed to connect to MySQL: " . $conn->connect_error;