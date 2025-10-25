<?php
require_once '../config/db.php';
include 'navbar.php';

// Delete a customer
if (isset($_GET['delete'])) {
  $id = $_GET['delete'];
  $conn->query("DELETE FROM customer WHERE customerID = '$id'");
  header("Location: customers.php");
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin - Customers</title>
  <link rel="stylesheet" href="css/admin.css">
</head>
<body>
  <div class="overlay">
    <main>
      <h1>Customers</h1>
      <p>View and manage customer accounts below.</p>

      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>Customer ID</th>
              <th>License No</th>
              <th>Email</th>
              <th>Password</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $result = $conn->query("SELECT * FROM customer");
            while ($row = $result->fetch_assoc()):
            ?>
            <tr>
              <td><?= $row['customerID'] ?></td>
              <td><?= $row['licenseNo'] ?></td>
              <td><?= $row['email'] ?></td>
              <td><?= $row['password'] ?></td>
              <td>
                <a href="customers.php?delete=<?= $row['customerID'] ?>" class="delete-btn" onclick="return confirm('Delete this customer?')">Delete</a>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </main>
  </div>
</body>
</html>
