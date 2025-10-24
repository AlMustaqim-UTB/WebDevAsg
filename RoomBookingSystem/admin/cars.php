<?php
require_once '../config/db.php';
include 'navbar.php';

// Delete a car
if (isset($_GET['delete'])) {
  $id = $_GET['delete'];
  $conn->query("DELETE FROM car WHERE carID = '$id'");
  header("Location: cars.php");
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin - Cars</title>
  <link rel="stylesheet" href="css/admin.css">
</head>
<body>
  <div class="overlay">
    <main>
      <h1>Cars</h1>
      <p>View, add, or delete cars below.</p>

      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>Car ID</th>
              <th>Plate No</th>
              <th>Model</th>
              <th>Make</th>
              <th>Year</th>
              <th>Rate/Day</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $query = "SELECT car.carID, car.plateNo, car.carModel, carMake.makeName, car.year, car.ratePerDay, car.status 
                      FROM car 
                      JOIN carMake ON car.makeID = carMake.makeID";
            $result = $conn->query($query);
            while ($row = $result->fetch_assoc()):
            ?>
            <tr>
              <td><?= $row['carID'] ?></td>
              <td><?= $row['plateNo'] ?></td>
              <td><?= $row['carModel'] ?></td>
              <td><?= $row['makeName'] ?></td>
              <td><?= $row['year'] ?></td>
              <td><?= $row['ratePerDay'] ?></td>
              <td><?= $row['status'] ?></td>
              <td>
                <a href="cars.php?delete=<?= $row['carID'] ?>" class="delete-btn" onclick="return confirm('Delete this car?')">Delete</a>
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
