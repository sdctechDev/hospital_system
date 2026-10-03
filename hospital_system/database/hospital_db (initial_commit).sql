-- ============================================
-- Hospital Appointment and Payment Platform
-- Database: hospital_db
-- ============================================

DROP DATABASE IF EXISTS hospital_db;
CREATE DATABASE hospital_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hospital_db;

-- 1. ADMINS
CREATE TABLE admins (
    admin_id   INT AUTO_INCREMENT PRIMARY KEY,
    full_name  VARCHAR(100) NOT NULL,
    email      VARCHAR(100) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. SPECIALIZATIONS
CREATE TABLE specializations (
    specialization_id INT AUTO_INCREMENT PRIMARY KEY,
    name              VARCHAR(100) NOT NULL UNIQUE,
    description       TEXT
) ENGINE=InnoDB;

-- 3. PATIENTS
CREATE TABLE patients (
    patient_id    INT AUTO_INCREMENT PRIMARY KEY,
    full_name     VARCHAR(100) NOT NULL,
    email         VARCHAR(100) NOT NULL UNIQUE,
    password      VARCHAR(255) NOT NULL,
    phone         VARCHAR(20),
    gender        ENUM('male','female','other'),
    date_of_birth DATE,
    address       VARCHAR(255),
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 4. DOCTORS
CREATE TABLE doctors (
    doctor_id         INT AUTO_INCREMENT PRIMARY KEY,
    full_name         VARCHAR(100) NOT NULL,
    email             VARCHAR(100) NOT NULL UNIQUE,
    password          VARCHAR(255) NOT NULL,
    phone             VARCHAR(20),
    specialization_id INT NOT NULL,
    consultation_fee  DECIMAL(10,2) NOT NULL,
    bio               TEXT,
    status            ENUM('active','inactive') DEFAULT 'active',
    created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (specialization_id) REFERENCES specializations(specialization_id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 5. DOCTOR AVAILABILITY
CREATE TABLE doctor_availability (
    availability_id INT AUTO_INCREMENT PRIMARY KEY,
    doctor_id       INT NOT NULL,
    day_of_week     ENUM('Mon','Tue','Wed','Thu','Fri','Sat','Sun') NOT NULL,
    start_time      TIME NOT NULL,
    end_time        TIME NOT NULL,
    slot_duration   INT NOT NULL DEFAULT 30,
    FOREIGN KEY (doctor_id) REFERENCES doctors(doctor_id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

-- 6. APPOINTMENTS
CREATE TABLE appointments (
    appointment_id   INT AUTO_INCREMENT PRIMARY KEY,
    patient_id       INT NOT NULL,
    doctor_id        INT NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    reason           TEXT,
    fee              DECIMAL(10,2) NOT NULL,
    status           ENUM('pending','confirmed','completed','cancelled') DEFAULT 'pending',
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    -- NULL when cancelled, so a cancelled slot can be booked again
    active_slot      VARCHAR(40) AS (
        IF(status = 'cancelled', NULL,
           CONCAT(doctor_id, '|', appointment_date, '|', appointment_time))
    ) STORED,
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(doctor_id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    UNIQUE KEY unique_active_slot (active_slot)
) ENGINE=InnoDB;

-- 7. PAYMENTS
CREATE TABLE payments (
    payment_id      INT AUTO_INCREMENT PRIMARY KEY,
    appointment_id  INT NOT NULL,
    amount          DECIMAL(10,2) NOT NULL,
    payment_method  ENUM('card','bank_transfer') NOT NULL,
    transaction_ref VARCHAR(50) NOT NULL UNIQUE,
    status          ENUM('success','failed','refunded') NOT NULL,
    paid_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    refund_ref      VARCHAR(50) NULL,
    refunded_at     TIMESTAMP NULL,
    FOREIGN KEY (appointment_id) REFERENCES appointments(appointment_id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;
