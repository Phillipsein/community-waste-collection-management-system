-- Community Waste Collection Management System (Group 6)
-- Database schema. Mirrors the ERD in the project report. Run this file
-- first in phpMyAdmin (or the mysql client), then run seed.sql.

SET NAMES utf8mb4;

CREATE TABLE zones (
  zone_id INT AUTO_INCREMENT PRIMARY KEY,
  zone_name VARCHAR(100) NOT NULL,
  location_description VARCHAR(255)
);

CREATE TABLE administrators (
  admin_id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role VARCHAR(50) DEFAULT 'administrator',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE vehicles (
  vehicle_id INT AUTO_INCREMENT PRIMARY KEY,
  registration_number VARCHAR(50) NOT NULL,
  vehicle_type VARCHAR(50),
  capacity_kg INT
);

CREATE TABLE residents (
  resident_id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(100) NOT NULL,
  phone_number VARCHAR(30) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  address VARCHAR(255),
  zone_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (zone_id) REFERENCES zones(zone_id)
);

CREATE TABLE collectors (
  collector_id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(100) NOT NULL,
  phone_number VARCHAR(30) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  zone_id INT NOT NULL,
  vehicle_id INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (zone_id) REFERENCES zones(zone_id),
  FOREIGN KEY (vehicle_id) REFERENCES vehicles(vehicle_id)
);

CREATE TABLE schedules (
  schedule_id INT AUTO_INCREMENT PRIMARY KEY,
  zone_id INT NOT NULL,
  collection_day VARCHAR(20) NOT NULL,
  collection_time VARCHAR(20) NOT NULL,
  frequency VARCHAR(30) NOT NULL,
  FOREIGN KEY (zone_id) REFERENCES zones(zone_id)
);

CREATE TABLE pickup_requests (
  request_id INT AUTO_INCREMENT PRIMARY KEY,
  resident_id INT NOT NULL,
  collector_id INT,
  request_date DATE NOT NULL,
  waste_type VARCHAR(50) NOT NULL,
  status ENUM('pending', 'assigned', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (resident_id) REFERENCES residents(resident_id),
  FOREIGN KEY (collector_id) REFERENCES collectors(collector_id)
);

CREATE TABLE payments (
  payment_id INT AUTO_INCREMENT PRIMARY KEY,
  resident_id INT NOT NULL,
  request_id INT NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  payment_method ENUM('mtn_mobile_money', 'airtel_money', 'cash') NOT NULL,
  status ENUM('pending', 'paid') NOT NULL DEFAULT 'paid',
  FOREIGN KEY (resident_id) REFERENCES residents(resident_id),
  FOREIGN KEY (request_id) REFERENCES pickup_requests(request_id)
);

CREATE TABLE complaints (
  complaint_id INT AUTO_INCREMENT PRIMARY KEY,
  resident_id INT NOT NULL,
  description TEXT NOT NULL,
  date_submitted TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  status ENUM('open', 'resolved') NOT NULL DEFAULT 'open',
  admin_response VARCHAR(255),
  FOREIGN KEY (resident_id) REFERENCES residents(resident_id)
);
