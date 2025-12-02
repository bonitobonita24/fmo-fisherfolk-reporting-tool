-- Fisherfolk Management System Database Schema
-- Created for Calapan City FMO by Powerbyte IT Solutions

-- Create database
CREATE DATABASE IF NOT EXISTS fmo_fisherfolk_management_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE fmo_fisherfolk_management_system;

-- Fisherfolk table
CREATE TABLE IF NOT EXISTS fisherfolk (
    id_number VARCHAR(50) PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    date_of_birth DATE NOT NULL,
    address VARCHAR(255) NOT NULL,
    sex VARCHAR(10) NOT NULL,
    image VARCHAR(255),
    signature VARCHAR(255),
    rsbsa VARCHAR(50),
    contact_number VARCHAR(20),
    boat_owneroperator TINYINT(1) DEFAULT 0,
    capture_fishing TINYINT(1) DEFAULT 0,
    gleaning TINYINT(1) DEFAULT 0,
    vendor TINYINT(1) DEFAULT 0,
    fish_processing TINYINT(1) DEFAULT 0,
    aquaculture TINYINT(1) DEFAULT 0,
    date_registered TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_address (address),
    INDEX idx_sex (sex),
    INDEX idx_date_of_birth (date_of_birth)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
