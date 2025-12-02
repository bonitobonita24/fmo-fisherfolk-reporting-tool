-- Sample data for Fisherfolk Management System
-- Barangays in Calapan City

USE fisherfolk_db;

-- Insert sample fisherfolk data
INSERT INTO fisherfolk (id_number, full_name, date_of_birth, address, sex, rsbsa, contact_number, boat_owneroperator, capture_fishing, gleaning, vendor, fish_processing, aquaculture) VALUES
-- Barangay Poblacion
('FF2024-0001', 'Juan dela Cruz', '1980-05-15', 'Barangay Poblacion', 'Male', 'RSBSA-2024-001', '09171234567', 1, 1, 0, 0, 0, 0),
('FF2024-0002', 'Maria Santos', '1985-08-22', 'Barangay Poblacion', 'Female', 'RSBSA-2024-002', '09187654321', 0, 0, 1, 1, 0, 0),
('FF2024-0003', 'Pedro Reyes', '1975-12-10', 'Barangay Poblacion', 'Male', 'RSBSA-2024-003', '09191234567', 0, 1, 0, 0, 0, 0),

-- Barangay Guinobatan
('FF2024-0004', 'Ana Cruz', '1990-03-18', 'Barangay Guinobatan', 'Female', 'RSBSA-2024-004', '09201234567', 0, 0, 1, 1, 1, 0),
('FF2024-0005', 'Ramon Torres', '1982-07-25', 'Barangay Guinobatan', 'Male', 'RSBSA-2024-005', '09211234567', 1, 1, 0, 0, 0, 0),
('FF2024-0006', 'Luz Mercado', '1988-11-30', 'Barangay Guinobatan', 'Female', 'RSBSA-2024-006', '09221234567', 0, 0, 0, 1, 1, 0),

-- Barangay Canubing I
('FF2024-0007', 'Carlos Bautista', '1978-01-20', 'Barangay Canubing I', 'Male', 'RSBSA-2024-007', '09231234567', 1, 1, 0, 0, 0, 1),
('FF2024-0008', 'Elena Ramos', '1992-06-14', 'Barangay Canubing I', 'Female', 'RSBSA-2024-008', '09241234567', 0, 0, 1, 0, 0, 0),
('FF2024-0009', 'Roberto Santiago', '1970-09-05', 'Barangay Canubing I', 'Male', 'RSBSA-2024-009', '09251234567', 1, 1, 0, 0, 0, 0),

-- Barangay Canubing II
('FF2024-0010', 'Sofia Aquino', '1995-02-28', 'Barangay Canubing II', 'Female', 'RSBSA-2024-010', '09261234567', 0, 0, 1, 1, 0, 0),
('FF2024-0011', 'Miguel Flores', '1983-04-12', 'Barangay Canubing II', 'Male', 'RSBSA-2024-011', '09271234567', 0, 1, 0, 0, 0, 0),
('FF2024-0012', 'Teresa Valdez', '1987-10-08', 'Barangay Canubing II', 'Female', 'RSBSA-2024-012', '09281234567', 0, 0, 0, 1, 1, 0),

-- Barangay Suqui
('FF2024-0013', 'Antonio Garcia', '1976-03-22', 'Barangay Suqui', 'Male', 'RSBSA-2024-013', '09291234567', 1, 1, 0, 0, 0, 1),
('FF2024-0014', 'Carmen Lopez', '1991-07-17', 'Barangay Suqui', 'Female', 'RSBSA-2024-014', '09301234567', 0, 0, 1, 1, 0, 0),
('FF2024-0015', 'Francisco Rivera', '1979-12-03', 'Barangay Suqui', 'Male', 'RSBSA-2024-015', '09311234567', 0, 1, 0, 0, 0, 0),

-- Barangay Lumang Bayan
('FF2024-0016', 'Isabella Cruz', '1993-05-25', 'Barangay Lumang Bayan', 'Female', 'RSBSA-2024-016', '09321234567', 0, 0, 1, 0, 1, 0),
('FF2024-0017', 'Jorge Diaz', '1981-08-19', 'Barangay Lumang Bayan', 'Male', 'RSBSA-2024-017', '09331234567', 1, 1, 0, 0, 0, 0),
('FF2024-0018', 'Rosa Mendoza', '1986-11-11', 'Barangay Lumang Bayan', 'Female', 'RSBSA-2024-018', '09341234567', 0, 0, 0, 1, 1, 0),

-- Barangay Sta. Rita
('FF2024-0019', 'Eduardo Morales', '1974-02-14', 'Barangay Sta. Rita', 'Male', 'RSBSA-2024-019', '09351234567', 1, 1, 0, 0, 0, 1),
('FF2024-0020', 'Linda Castro', '1989-06-07', 'Barangay Sta. Rita', 'Female', 'RSBSA-2024-020', '09361234567', 0, 0, 1, 1, 0, 0),
('FF2024-0021', 'Gabriel Santos', '1977-09-29', 'Barangay Sta. Rita', 'Male', 'RSBSA-2024-021', '09371234567', 0, 1, 0, 0, 0, 0),

-- Barangay Tawagan
('FF2024-0022', 'Victoria Reyes', '1994-01-16', 'Barangay Tawagan', 'Female', 'RSBSA-2024-022', '09381234567', 0, 0, 1, 1, 1, 0),
('FF2024-0023', 'Manuel Torres', '1984-04-30', 'Barangay Tawagan', 'Male', 'RSBSA-2024-023', '09391234567', 1, 1, 0, 0, 0, 0),
('FF2024-0024', 'Patricia Mercado', '1990-08-23', 'Barangay Tawagan', 'Female', 'RSBSA-2024-024', '09401234567', 0, 0, 0, 1, 1, 0),

-- Barangay Silonay
('FF2024-0025', 'Ricardo Bautista', '1972-11-27', 'Barangay Silonay', 'Male', 'RSBSA-2024-025', '09411234567', 1, 1, 0, 0, 0, 1),
('FF2024-0026', 'Angelina Ramos', '1996-03-09', 'Barangay Silonay', 'Female', 'RSBSA-2024-026', '09421234567', 0, 0, 1, 0, 0, 0),
('FF2024-0027', 'Diego Santiago', '1980-07-21', 'Barangay Silonay', 'Male', 'RSBSA-2024-027', '09431234567', 0, 1, 0, 0, 0, 0),

-- Barangay Bayanan I
('FF2024-0028', 'Beatriz Aquino', '1987-12-05', 'Barangay Bayanan I', 'Female', 'RSBSA-2024-028', '09441234567', 0, 0, 1, 1, 1, 0),
('FF2024-0029', 'Alejandro Flores', '1973-05-18', 'Barangay Bayanan I', 'Male', 'RSBSA-2024-029', '09451234567', 1, 1, 0, 0, 0, 0),
('FF2024-0030', 'Cristina Valdez', '1991-09-12', 'Barangay Bayanan I', 'Female', 'RSBSA-2024-030', '09461234567', 0, 0, 0, 1, 1, 0);

-- Additional records for better data distribution
INSERT INTO fisherfolk (id_number, full_name, date_of_birth, address, sex, rsbsa, contact_number, boat_owneroperator, capture_fishing, gleaning, vendor, fish_processing, aquaculture) VALUES
('FF2024-0031', 'Fernando Garcia', '1969-04-10', 'Barangay Poblacion', 'Male', 'RSBSA-2024-031', '09471234567', 1, 1, 0, 0, 0, 1),
('FF2024-0032', 'Margarita Lopez', '1998-08-15', 'Barangay Guinobatan', 'Female', 'RSBSA-2024-032', '09481234567', 0, 0, 1, 1, 0, 0),
('FF2024-0033', 'Rodrigo Rivera', '1965-01-07', 'Barangay Canubing I', 'Male', 'RSBSA-2024-033', '09491234567', 1, 1, 0, 0, 0, 0),
('FF2024-0034', 'Esperanza Cruz', '2000-06-20', 'Barangay Suqui', 'Female', 'RSBSA-2024-034', '09501234567', 0, 0, 1, 0, 0, 0),
('FF2024-0035', 'Leonardo Diaz', '1968-11-14', 'Barangay Sta. Rita', 'Male', 'RSBSA-2024-035', '09511234567', 1, 1, 0, 0, 0, 1);
