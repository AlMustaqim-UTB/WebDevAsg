-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 30, 2025 at 07:01 PM
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
('AD00002', 'admin2@email.com', '$2y$10$a83uAK2erVnQRKK8GjhIve3/3VULM0VD1K/EnaqZrewEj.RxX9fmO');

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
('CI001', 'BJK8903', 67, 'Available', 'Focus', 2012, 4, 'Automatic', 'Lorem Ipsum', '../css/images/Ford_Focus.png', 'CM06', 'CT01'),
('CI002', 'BBK282', 150, 'Rented', 'Alphard', 2024, 8, 'Manual', 'null', '../css/images/Toyota_Alphard.png', 'CM02', 'CT02'),
('CI003', 'BG1141', 60, 'Available', 'Vitara', 2017, 5, 'Automatic', 'null', '../css/images/Suzuki_Vitara.png', 'CM03', 'CT03'),
('CI004', 'HJU9834', 80, 'Available', 'Civic', 2015, 4, 'Automatic', 'Lorem Ipsum', '../css/images/Honda_Civic.png', 'CM01', 'CT01'),
('CI005', 'SDA2348', 85, 'Available', 'Sonata', 2020, 4, 'Automatic', 'Lorem Ipsum', '../css/images/Hyundai_Sonata.png', 'CM04', 'CT01'),
('CI006', 'BB101', 230, 'Rented', 'Stinger', 2022, 4, 'Manual', 'Lorem Ipsum', '../css/images/Kia_Stinger.png', 'CM08', 'CT01'),
('CI007', 'PO984', 55, 'Rented', 'Bezza', 2020, 4, 'Manual', 'None', '../css/images/Perodua_Bezza.png', 'CM05', 'CT01'),
('CI009', 'KK768', 59, 'Available', 'Vitara', 2011, 5, 'Manual', 'None', '../css/images/Suzuki_Vitara.png', 'CM08', 'CT03'),
('CI010', 'HJ787', 135, 'Available', 'Model S', 2023, 5, 'Automatic', 'Electric Vehicle', '../css/images/Tesla_ModelS.png', 'CM07', 'CT01'),
('CI011', 'IOP3021', 89, 'Available', 'Vios', 2024, 4, 'Automatic', 'None', '../css/images/Toyota_Vios.png', 'CM02', 'CT01');

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
('CM03', 'Suzuki'),
('CM04', 'Hyundai'),
('CM05', 'Peroduo'),
('CM06', 'Ford'),
('CM07', 'Tesla'),
('CM08', 'Kia');

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
('CU00001', '1234567QWERTY', 'john.doe@email.com', '$2y$10$5t54ISt/EVyJ07dkHr648unQy.we.EA84OfB1tXevMoeGRkXWrzKm'),
('CU00002', '9876543LICENSEnum', 'jane.d@email.com', '$2y$10$nbL6VOJyIMCy/VP3yfKZW.mSO9H6M3I9GsOlS.GX5WALxJCUIad32'),
('CU00003', 'qwerty1234567', 'test.cust@email.com', '$2y$10$4pnfuqTB1G7.hlqPnB1msurkzWbxyj8PXJbw9x4lU3CwiGDCnDzUK');

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
('PI0007', '2025-10-30', 'online', 'Paid'),
('PI0008', '2025-10-31', 'Cash', 'Cancelled'),
('PI0009', NULL, 'cash', 'Pending'),
('PI0010', '2025-10-30', 'online', 'Paid');

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
('RI0001', 'CU00001', 'CI006', '2025-10-31', '2025-12-12', 9660, 'Active', 'PI0007', 'HQ'),
('RI0002', 'CU00002', 'CI003', '2025-10-31', '2025-11-01', 60, 'Cancelled', 'PI0008', 'Gadong'),
('RI0003', 'CU00002', 'CI007', '2025-10-31', '2025-11-04', 220, 'Pending', 'PI0009', 'Gadong'),
('RI0004', 'CU00003', 'CI002', '2025-10-31', '2025-11-01', 150, 'Completed', 'PI0010', 'HQ');

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
('CU00001', 'John', 'Doe', 1234567),
('CU00002', 'Jane', 'Doe', 9876543),
('CU00003', 'test', 'cust', 1234567);

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
