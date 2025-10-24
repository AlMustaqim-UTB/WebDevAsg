<?php
include 'navbar.php'; // navbar
require_once '../config/db.php'; // make sure path is correct

// Delete rental
if (isset($_GET['delete'])) {
  $rentalID = $_GET['delete'];
  $conn->query("DELETE FROM rentalID WHERE rentalID = '$rentalID'");
  header("Location: rentals.php");
  exit();
}

// Update rental status
if (isset($_POST['update_status'])) {
  $rentalID = $_POST['rentalID'];
  $newStatus = $_POST['newStatus'];
  $conn->query("UPDATE rentalID SET rentalStatus = '$newStatus' WHERE rentalID = '$rentalID'");
  header("Location: rentals.php");
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin - Rentals</title>
  <link rel="stylesheet" href="css/admin.css" />
  <style>
    /* Inline fix to make sure dropdowns and buttons align and color properly */
    select[name="newStatus"] {
      min-width: 120px;
      padding: 6px 8px;
      border-radius: 6px;
      font-size: 0.9rem;
      border: 1px solid #ccc;
      color: white;
      text-align: center;
      font-weight: 600;
    }

    /* Color classes */
    .pending { background-color: #f4b400; color: black; }
    .active { background-color: #4285f4; color: white; }
    .completed { background-color: #0f9d58; color: white; }

    .action-buttons {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 6px;
    }

    .change-btn {
      background: #0a9396;
      color: #fff;
      border: none;
      padding: 0.3rem 0.8rem;
      border-radius: 5px;
      font-size: 0.85rem;
      cursor: pointer;
      font-weight: 600;
    }

    .change-btn:hover { background: #078082; }

    .delete-btn {
      background: #c0392b;
      color: #fff;
      border: none;
      padding: 0.3rem 0.8rem;
      border-radius: 5px;
      font-size: 0.85rem;
      cursor: pointer;
      font-weight: 600;
      text-decoration: none;
      transition: background 0.3s ease;
    }

    .delete-btn:hover { background: #a93226; }

    /* Legend styling */
    .status-legend {
      display: flex;
      justify-content: center;
      gap: 1rem;
      margin-bottom: 1rem;
      font-size: 1rem;
    }
    .legend-box {
      display: inline-block;
      width: 16px;
      height: 16px;
      border-radius: 3px;
      margin-right: 6px;
    }
  </style>
</head>

<body>
  <div class="overlay">


    <main>
      <h1>Rentals</h1>
      <p>View and manage all car rental records below.</p>

      <!-- Status Legend -->
      <div class="status-legend">
        <div><span class="legend-box" style="background:#f4b400;"></span> Pending</div>
        <div><span class="legend-box" style="background:#4285f4;"></span> Active</div>
        <div><span class="legend-box" style="background:#0f9d58;"></span> Completed</div>
      </div>

      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>Rental ID</th>
              <th>Customer ID</th>
              <th>Car ID</th>
              <th>Start Date</th>
              <th>End Date</th>
              <th>Total Price</th>
              <th>Status</th>
              <th>Location</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $query = "SELECT * FROM rentalID";
            $result = $conn->query($query);

            while ($row = $result->fetch_assoc()):
            ?>
            <tr>
              <td><?= htmlspecialchars($row['rentalID']) ?></td>
              <td><?= htmlspecialchars($row['customerID']) ?></td>
              <td><?= htmlspecialchars($row['carID']) ?></td>
              <td><?= htmlspecialchars($row['startDate']) ?></td>
              <td><?= htmlspecialchars($row['endDate']) ?></td>
              <td><?= htmlspecialchars($row['totalPrice']) ?></td>
              <td><?= htmlspecialchars($row['rentalStatus']) ?></td>
              <td><?= htmlspecialchars($row['deliveryLocation']) ?></td>
              <td>
                <div class="action-buttons">
                  <form method="POST">
                    <input type="hidden" name="rentalID" value="<?= $row['rentalID'] ?>">
                    <select name="newStatus" class="<?= strtolower($row['rentalStatus']) ?>">
                      <option value="Pending" <?= ($row['rentalStatus'] == 'Pending') ? 'selected' : '' ?>>Pending</option>
                      <option value="Active" <?= ($row['rentalStatus'] == 'Active') ? 'selected' : '' ?>>Active</option>
                      <option value="Completed" <?= ($row['rentalStatus'] == 'Completed') ? 'selected' : '' ?>>Completed</option>
                    </select>
                    <button type="submit" name="update_status" class="change-btn">Change</button>
                  </form>
                  <a href="rentals.php?delete=<?= $row['rentalID'] ?>" class="delete-btn" onclick="return confirm('Delete this rental?')">Delete</a>
                </div>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </main>
  </div>

  <!-- JavaScript to recolor dropdowns when changed -->
  <script>
  document.addEventListener("DOMContentLoaded", () => {
    const dropdowns = document.querySelectorAll('select[name="newStatus"]');

    function updateColors() {
      dropdowns.forEach(select => {
        select.classList.remove('pending', 'active', 'completed');
        const val = select.value.toLowerCase();
        select.classList.add(val);
      });
    }

    updateColors();
    dropdowns.forEach(select => select.addEventListener('change', updateColors));
  });
  </script>
</body>
</html>
