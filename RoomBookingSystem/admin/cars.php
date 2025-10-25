<?php
require_once '../config/db.php';
include 'navbar.php';

// Delete car
if (isset($_GET['delete'])) {
  $carID = $_GET['delete'];
  $conn->query("DELETE FROM car WHERE carID = '$carID'");
  header("Location: cars.php");
  exit();
}

// Add new car
if (isset($_POST['add_car'])) {
  $carID = $_POST['carID'];
  $plateNo = $_POST['plateNo'];
  $ratePerDay = $_POST['ratePerDay'];
  $status = $_POST['status'];
  $carModel = $_POST['carModel'];
  $year = $_POST['year'];
  $capacity = $_POST['capacity'];
  $transmission = $_POST['transmission'];
  $makeID = $_POST['makeID'];
  $categoryID = $_POST['categoryID'];

  $stmt = $conn->prepare("INSERT INTO car (carID, plateNo, ratePerDay, status, carModel, year, capacity, transmission, makeID, categoryID, description, imageURL)
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'null', 'null')");
  $stmt->bind_param("ssdssiisss", $carID, $plateNo, $ratePerDay, $status, $carModel, $year, $capacity, $transmission, $makeID, $categoryID);
  $stmt->execute();

  header("Location: cars.php");
  exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin - Cars</title>
  <link rel="stylesheet" href="css/admin.css">
  <style>
    /* Modal overlay */
    .modal {
      display: none;
      position: fixed;
      z-index: 100;
      left: 0; top: 0;
      width: 100%; height: 100%;
      background: rgba(0, 0, 0, 0.7);
      justify-content: center;
      align-items: center;
    }

    .modal-content {
      background: white;
      color: black;
      padding: 2rem;
      border-radius: 10px;
      width: 420px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    }

    .modal-content h2 {
      text-align: center;
      margin-bottom: 1rem;
      color: #004aad;
    }

    .modal-content label {
      display: block;
      margin-top: 0.8rem;
      font-weight: bold;
    }

    .modal-content input,
    .modal-content select {
      width: 100%;
      padding: 0.5rem;
      margin-top: 0.3rem;
      border-radius: 5px;
      border: 1px solid #ccc;
    }

    .modal-content button {
      margin-top: 1rem;
      width: 100%;
      background: #004aad;
      color: white;
      padding: 0.6rem;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-weight: 600;
      transition: background 0.3s ease;
    }

    .modal-content button:hover {
      background: #003b88;
    }

    .close {
      float: right;
      font-size: 1.5rem;
      font-weight: bold;
      color: #333;
      cursor: pointer;
    }

    .add-btn {
      background: #004aad;
      color: #fff;
      border: none;
      padding: 0.7rem 1.4rem;
      border-radius: 6px;
      cursor: pointer;
      font-size: 1rem;
      font-weight: 600;
      transition: background 0.3s ease;
      margin-bottom: 1rem;
    }
    .add-btn:hover {
      background: #003d8f;
    }
  </style>
</head>
<body>
  <main>
    <h1>Cars</h1>
    <p>View, add, or delete cars below.</p>

    <button id="openModal" class="add-btn">+ Add Car</button>

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
          $result = $conn->query("SELECT * FROM car");
          while ($row = $result->fetch_assoc()):
          ?>
          <tr>
            <td><?= htmlspecialchars($row['carID']) ?></td>
            <td><?= htmlspecialchars($row['plateNo']) ?></td>
            <td><?= htmlspecialchars($row['carModel']) ?></td>
            <td><?= htmlspecialchars($row['makeID']) ?></td>
            <td><?= htmlspecialchars($row['year']) ?></td>
            <td><?= htmlspecialchars($row['ratePerDay']) ?></td>
            <td><?= htmlspecialchars($row['status']) ?></td>
            <td>
              <a href="cars.php?delete=<?= $row['carID'] ?>" class="delete-btn" onclick="return confirm('Delete this car?')">Delete</a>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </main>

  <!-- Add Car Modal -->
  <div id="addCarModal" class="modal">
    <div class="modal-content">
      <span class="close" id="closeModal">&times;</span>
      <h2>Add New Car</h2>
      <form method="POST">
        <label>Car ID</label>
        <input type="text" name="carID" required>

        <label>Plate Number</label>
        <input type="text" name="plateNo" required>

        <label>Rate Per Day</label>
        <input type="number" name="ratePerDay" step="0.01" required>

        <label>Status</label>
        <select name="status">
          <option value="Available">Available</option>
          <option value="Rented">Rented</option>
        </select>

        <label>Model</label>
        <input type="text" name="carModel" required>

        <label>Year</label>
        <input type="number" name="year" required>

        <label>Capacity</label>
        <input type="number" name="capacity" required>

        <label>Transmission</label>
        <select name="transmission">
          <option value="Automatic">Automatic</option>
          <option value="Manual">Manual</option>
        </select>

        <label>Make ID</label>
        <input type="text" name="makeID" required>

        <label>Category ID</label>
        <input type="text" name="categoryID" required>

        <button type="submit" name="add_car">Add Car</button>
      </form>
    </div>
  </div>

  <script>
  // Modal functionality
  const modal = document.getElementById("addCarModal");
  const openModal = document.getElementById("openModal");
  const closeModal = document.getElementById("closeModal");

  openModal.onclick = () => { modal.style.display = "flex"; };
  closeModal.onclick = () => { modal.style.display = "none"; };
  window.onclick = (event) => { if (event.target === modal) modal.style.display = "none"; };
  </script>
</body>
</html>
