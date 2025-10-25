-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: db:3306
-- Generation Time: Oct 22, 2025 at 03:38 PM
-- Server version: 8.3.0
-- PHP Version: 8.2.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `webdev_asg_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `adminID` varchar(10) NOT NULL,
  `adminEmail` varchar(30) NOT NULL,
  `adminPassword` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`adminID`, `adminEmail`, `adminPassword`) VALUES
('AD00001', 'fulan.f@admin.com', 'null');

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
  `year` int NOT NULL,
  `capacity` int NOT NULL,
  `transmission` text NOT NULL,
  `description` varchar(300) NOT NULL,
  `imageURL` varchar(100) NOT NULL,
  `makeID` varchar(6) NOT NULL,
  `categoryID` varchar(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `car`
--

INSERT INTO `car` (`carID`, `plateNo`, `ratePerDay`, `status`, `carModel`, `year`, `capacity`, `transmission`, `description`, `imageURL`, `makeID`, `categoryID`) VALUES
('CI001', 'BBG1201', 80, 'Available', 'Civic', 2022, 5, 'Automatic', 'null', 'null', 'CM01', 'CT01'),
('CI002', 'BBK282', 150, 'Rented', 'Alphard', 2024, 8, 'Manual', 'null', 'null', 'CM02', 'CT02'),
('CI003', 'BG1141', 60, 'Rented', 'Vitara', 2017, 5, 'Automatic', 'null', 'null', 'CM03', 'CT03');

-- --------------------------------------------------------

--
-- Table structure for table `carCategory`
--

CREATE TABLE `carCategory` (
  `categoryID` varchar(6) NOT NULL,
  `categoryName` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `carCategory`
--

INSERT INTO `carCategory` (`categoryID`, `categoryName`) VALUES
('CT01', 'Sedan'),
('CT02', 'MPV'),
('CT03', 'SUV');

-- --------------------------------------------------------

--
-- Table structure for table `carMake`
--

CREATE TABLE `carMake` (
  `makeID` varchar(6) NOT NULL,
  `makeName` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `carMake`
--

INSERT INTO `carMake` (`makeID`, `makeName`) VALUES
('CM01', 'Honda'),
('CM02', 'Toyota'),
('CM03', 'Suzuki');

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `customerID` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `licenseNo` varchar(20) NOT NULL,
  `email` varchar(30) NOT NULL,
  `password` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`customerID`, `licenseNo`, `email`, `password`) VALUES
('CU00001', 'B01-123456F24/2023', 'wafi123@gmail.com', 'null'),
('CU00002', 'B01-654321F24/2023', 'hafiy456@gmail.com', 'null'),
('CU00003', 'B01-456123F24/2023', 'john.d@email.com', 'null');

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `paymentID` varchar(8) NOT NULL,
  `paymentDate` date NOT NULL,
  `paymentMethod` varchar(15) NOT NULL,
  `paymentStatus` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`paymentID`, `paymentDate`, `paymentMethod`, `paymentStatus`) VALUES
('PI001', '2025-10-21', 'Cash', 'Paid'),
('PI002', '2025-10-31', 'Card', 'Unpaid'),
('PI003', '2025-10-08', 'Cash', 'Paid');

-- --------------------------------------------------------

--
-- Table structure for table `rentalID`
--

CREATE TABLE `rentalID` (
  `rentalID` varchar(10) NOT NULL,
  `customerID` varchar(10) NOT NULL,
  `carID` varchar(10) NOT NULL,
  `startDate` date NOT NULL,
  `endDate` date NOT NULL,
  `totalPrice` double NOT NULL,
  `rentalStatus` varchar(15) NOT NULL,
  `paymentID` varchar(8) NOT NULL,
  `deliveryLocation` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `rentalID`
--

INSERT INTO `rentalID` (`rentalID`, `customerID`, `carID`, `startDate`, `endDate`, `totalPrice`, `rentalStatus`, `paymentID`, `deliveryLocation`) VALUES
('PI003', 'CU00003', 'CI001', '2025-10-08', '2025-10-12', 300, 'Completed', 'PI003', 'HQ'),
('RI001', 'CU00001', 'CI003', '2025-10-21', '2025-10-24', 180, 'Active', 'PI001', 'HQ'),
('RI002', 'CU00002', 'CI002', '2025-10-30', '2025-11-06', 0, 'Pending', 'PI002', 'HQ');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `userID` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `firstName` text NOT NULL,
  `lastName` text NOT NULL,
  `phoneNo` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`userID`, `firstName`, `lastName`, `phoneNo`) VALUES
('AD00001', 'Fulan', 'Fulani', 9999990),
('CU00001', 'Wafi', 'Halkim', 7151801),
('CU00002', 'Hafiy', 'Mukim', 4567890),
('CU00003', 'John', 'Doe', 7654321);

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
-- Indexes for table `carCategory`
--
ALTER TABLE `carCategory`
  ADD PRIMARY KEY (`categoryID`);

--
-- Indexes for table `carMake`
--
ALTER TABLE `carMake`
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
-- Indexes for table `rentalID`
--
ALTER TABLE `rentalID`
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
  ADD CONSTRAINT `car_ibfk_1` FOREIGN KEY (`makeID`) REFERENCES `carMake` (`makeID`),
  ADD CONSTRAINT `car_ibfk_2` FOREIGN KEY (`categoryID`) REFERENCES `carCategory` (`categoryID`);

--
-- Constraints for table `customer`
--
ALTER TABLE `customer`
  ADD CONSTRAINT `customer_ibfk_1` FOREIGN KEY (`customerID`) REFERENCES `user` (`userID`);

--
-- Constraints for table `rentalID`
--
ALTER TABLE `rentalID`
  ADD CONSTRAINT `rentalID_ibfk_1` FOREIGN KEY (`customerID`) REFERENCES `customer` (`customerID`),
  ADD CONSTRAINT `rentalID_ibfk_2` FOREIGN KEY (`carID`) REFERENCES `car` (`carID`),
  ADD CONSTRAINT `rentalID_ibfk_3` FOREIGN KEY (`paymentID`) REFERENCES `payment` (`paymentID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
