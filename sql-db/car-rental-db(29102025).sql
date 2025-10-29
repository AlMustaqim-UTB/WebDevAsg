-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 29, 2025 at 11:23 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `car-rental-db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `adminID` varchar(10) NOT NULL,
  `adminEmail` varchar(30) NOT NULL,
  `adminPassword` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`adminID`, `adminEmail`, `adminPassword`) VALUES
('AD00001', 'admin@email.com', '$2y$10$EiLw5rHPEkEEri6FtfUNSuIOukDsw5dtRUdIE4.iIW.x1Lcw1iOei'),
('AD00002', 'admin2@email', '$2y$10$a83uAK2erVnQRKK8GjhIve3/3VULM0VD1K/EnaqZrewEj.RxX9fmO');

-- --------------------------------------------------------

--
-- Table structure for table `car`
--

CREATE TABLE `car` (
  `carID` varchar(10) NOT NULL,
  `plateNo` varchar(10) NOT NULL,
  `ratePerDay` double NOT NULL,
  `status` text NOT NULL,
  `carModel` varchar(15) NOT NULL,
  `year` int(11) NOT NULL,
  `capacity` int(11) NOT NULL,
  `transmission` text NOT NULL,
  `description` varchar(300) NOT NULL,
  `imageURL` varchar(100) DEFAULT NULL,
  `makeID` varchar(6) NOT NULL,
  `categoryID` varchar(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `car`
--

INSERT INTO `car` (`carID`, `plateNo`, `ratePerDay`, `status`, `carModel`, `year`, `capacity`, `transmission`, `description`, `imageURL`, `makeID`, `categoryID`) VALUES
('CI001', 'BBG1201', 80, 'Rented', 'Civic', 2022, 5, 'Automatic', 'null', '../css/images/car2.jpg', 'CM01', 'CT01'),
('CI002', 'BBK282', 150, 'Available', 'Alphard', 2024, 8, 'Manual', 'null', '../css/images/car3.jpg', 'CM02', 'CT02'),
('CI003', 'BG1141', 60, 'Available', 'Vitara', 2017, 5, 'Automatic', 'null', '../css/images/car6.jpeg', 'CM03', 'CT03'),
('CI666', 'BOJSdf', 500, 'Rented', 'None', 2001, 10, 'Automatic', 'no thanks', '../css/images/car5.jpg', 'CM01', 'CT02'),
('CI999', 'naksdm', 123123, 'Available', 'asknmd', 2000, 22, 'Automatic', 'test', '../css/images/car4.jpg', 'CM01', 'CT02');

-- --------------------------------------------------------

--
-- Table structure for table `carcategory`
--

CREATE TABLE `carcategory` (
  `categoryID` varchar(6) NOT NULL,
  `categoryName` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `carcategory`
--

INSERT INTO `carcategory` (`categoryID`, `categoryName`) VALUES
('CT01', 'Sedan'),
('CT02', 'MPV'),
('CT03', 'SUV');

-- --------------------------------------------------------

--
-- Table structure for table `carmake`
--

CREATE TABLE `carmake` (
  `makeID` varchar(6) NOT NULL,
  `makeName` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `carmake`
--

INSERT INTO `carmake` (`makeID`, `makeName`) VALUES
('CM01', 'Honda'),
('CM02', 'Toyota'),
('CM03', 'Suzuki');

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `customerID` varchar(10) NOT NULL,
  `licenseNo` varchar(20) NOT NULL,
  `email` varchar(30) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`customerID`, `licenseNo`, `email`, `password`) VALUES
('CU00001', 'B01-123456F24/2023', 'wafi123@gmail.com', 'null'),
('CU00002', 'B01-654321F24/2023', 'hafiy456@gmail.com', 'null'),
('CU00003', 'B01-456123F24/2023', 'john.d@email.com', 'null'),
('CU00004', 'BE1201201', 'wafri@email.com', '$2y$10$vnGJevhyFxejcFlDg3W1xek/uY3QyzgVqgiyGe2/0PA4VD16W0IoO');

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `paymentID` varchar(8) NOT NULL,
  `paymentDate` date DEFAULT NULL,
  `paymentMethod` varchar(15) NOT NULL,
  `paymentStatus` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`paymentID`, `paymentDate`, `paymentMethod`, `paymentStatus`) VALUES
('PI0004', '2025-10-29', 'online', 'Paid'),
('PI001', '2025-10-21', 'Cash', 'Paid'),
('PI002', '2025-10-31', 'Card', 'Unpaid'),
('PI003', '2025-10-08', 'Cash', 'Paid');

-- --------------------------------------------------------

--
-- Table structure for table `rental`
--

CREATE TABLE `rental` (
  `rentalID` varchar(10) NOT NULL,
  `customerID` varchar(10) NOT NULL,
  `carID` varchar(10) NOT NULL,
  `startDate` date NOT NULL,
  `endDate` date NOT NULL,
  `totalPrice` double NOT NULL,
  `rentalStatus` varchar(15) NOT NULL,
  `paymentID` varchar(8) NOT NULL,
  `deliveryLocation` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rental`
--

INSERT INTO `rental` (`rentalID`, `customerID`, `carID`, `startDate`, `endDate`, `totalPrice`, `rentalStatus`, `paymentID`, `deliveryLocation`) VALUES
('PI003', 'CU00003', 'CI001', '2025-10-08', '2025-10-12', 300, 'Completed', 'PI003', 'HQ'),
('RI0003', 'CU00004', 'CI001', '2025-10-29', '2025-10-30', 80, 'Pending', 'PI0004', 'HQ'),
('RI001', 'CU00001', 'CI003', '2025-10-21', '2025-10-24', 180, 'Active', 'PI001', 'HQ'),
('RI002', 'CU00002', 'CI002', '2025-10-30', '2025-11-06', 101, 'Active', 'PI002', 'HQ');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `userID` varchar(10) NOT NULL,
  `firstName` text NOT NULL,
  `lastName` text NOT NULL,
  `phoneNo` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`userID`, `firstName`, `lastName`, `phoneNo`) VALUES
('AD00001', 'Fulan', 'Fulani', 9999990),
('AD00002', 'Admin', 'Test', 111),
('CU00001', 'Wafi', 'Halkim', 7151801),
('CU00002', 'Hafiy', 'Mukim', 4567890),
('CU00003', 'John', 'Doe', 7654321),
('CU00004', 'wafri', 'Wakim', 8777222);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD UNIQUE KEY `adminID_2` (`adminID`),
  ADD KEY `adminID` (`adminID`);

--
-- Indexes for table `car`
--
ALTER TABLE `car`
  ADD PRIMARY KEY (`carID`),
  ADD KEY `makeID` (`makeID`),
  ADD KEY `categoryID` (`categoryID`);

--
-- Indexes for table `carcategory`
--
ALTER TABLE `carcategory`
  ADD PRIMARY KEY (`categoryID`);

--
-- Indexes for table `carmake`
--
ALTER TABLE `carmake`
  ADD PRIMARY KEY (`makeID`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD UNIQUE KEY `customerID_2` (`customerID`),
  ADD KEY `customerID` (`customerID`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`paymentID`);

--
-- Indexes for table `rental`
--
ALTER TABLE `rental`
  ADD PRIMARY KEY (`rentalID`),
  ADD KEY `customerID` (`customerID`),
  ADD KEY `paymentID` (`paymentID`),
  ADD KEY `carID` (`carID`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`userID`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin`
--
ALTER TABLE `admin`
  ADD CONSTRAINT `admin_ibfk_1` FOREIGN KEY (`adminID`) REFERENCES `user` (`userID`);

--
-- Constraints for table `car`
--
ALTER TABLE `car`
  ADD CONSTRAINT `car_ibfk_1` FOREIGN KEY (`makeID`) REFERENCES `carmake` (`makeID`),
  ADD CONSTRAINT `car_ibfk_2` FOREIGN KEY (`categoryID`) REFERENCES `carcategory` (`categoryID`);

--
-- Constraints for table `customer`
--
ALTER TABLE `customer`
  ADD CONSTRAINT `customer_ibfk_1` FOREIGN KEY (`customerID`) REFERENCES `user` (`userID`);

--
-- Constraints for table `rental`
--
ALTER TABLE `rental`
  ADD CONSTRAINT `rental_ibfk_1` FOREIGN KEY (`customerID`) REFERENCES `customer` (`customerID`),
  ADD CONSTRAINT `rental_ibfk_2` FOREIGN KEY (`carID`) REFERENCES `car` (`carID`),
  ADD CONSTRAINT `rental_ibfk_3` FOREIGN KEY (`paymentID`) REFERENCES `payment` (`paymentID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
