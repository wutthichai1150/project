<?php
include('includes/db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fname = $_POST['mem_fname'];
    $lname = $_POST['mem_lname'];
    $user = $_POST['mem_user'];
    $password = password_hash($_POST['mem_password'], PASSWORD_DEFAULT); // แฮชรหัสผ่าน
    $mail = $_POST['mem_mail'];

    $sql = "INSERT INTO member (mem_fname, mem_lname, mem_user, mem_password, mem_mail) 
            VALUES ('$fname', '$lname', '$user', '$password', '$mail')";

    if ($conn->query($sql) === TRUE) {
        echo "New member added successfully!";
        header("Location: manage_member.php"); // Redirect to manage members page
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Member</title>
</head>
<body>
  <h1>Add New Member</h1>
  <form method="POST">
    <label>First Name:</label>
    <input type="text" name="mem_fname" required><br><br>

    <label>Last Name:</label>
    <input type="text" name="mem_lname" required><br><br>

    <label>Username:</label>
    <input type="text" name="mem_user" required><br><br>

    <label>Password:</label>
    <input type="password" name="mem_password" required><br><br>

    <label>Email:</label>
    <input type="email" name="mem_mail" required><br><br>

    <button type="submit">Add Member</button>
  </form>
</body>
</html>
