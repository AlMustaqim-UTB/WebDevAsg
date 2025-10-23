<?php include '../config/db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - Car Rental System</title>
  <link rel="stylesheet" href="css/admin.css">
</head>
<body>
  <div class="overlay">
    <nav>
      <ul>
        <li><img src="../logo.png" alt="Logo"></li>
        <li><a href="customers.php">Customers</a></li>
        <li><a href="cars.php">Cars</a></li>
        <li><a href="rentals.php">Rentals</a></li>
      </ul>
    </nav>

    <main>
      <h1>Admin Dashboard</h1>
      <p>Welcome to the Car Rental System Admin Panel.</p>
      <div class="table-container">
        <table>
          <thead>
            <tr><th>Section</th><th>Description</th></tr>
          </thead>
          <tbody>
            <tr><td><a href="customers.php">Customers</a></td><td>View and manage customer accounts.</td></tr>
            <tr><td><a href="cars.php">Cars</a></td><td>View, add, or delete cars from the system.</td></tr>
            <tr><td><a href="rentals.php">Rentals</a></td><td>Monitor all ongoing and past car rentals.</td></tr>
          </tbody>
        </table>
      </div>
    </main>
  </div>
</body>
</html>
