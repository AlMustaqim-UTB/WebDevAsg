<?php include 'navbar.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - Car Rental</title>
  <link rel="stylesheet" href="css/admin.css">
</head>
<body>
  <div class="overlay">
    <main>
      <h1>Admin Dashboard</h1>
      <p>Welcome to the Car Rental Management System Dashboard.</p>
      <div class="table-container">
        <table>
          <thead>
            <tr><th>Section</th><th>Description</th></tr>
          </thead>
          <tbody>
            <tr><td><a href="customers.php">Customers</a></td><td>Manage customer accounts</td></tr>
            <tr><td><a href="cars.php">Cars</a></td><td>Manage available cars</td></tr>
            <tr><td><a href="rentals.php">Rentals</a></td><td>View and update rental records</td></tr>
          </tbody>
        </table>
      </div>
    </main>
  </div>
</body>
</html>
