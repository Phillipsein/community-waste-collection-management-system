-- Community Waste Collection Management System (Group 6)
-- Sample data for demos and the class presentation. Run schema.sql first,
-- then this file, against an empty database (relies on AUTO_INCREMENT IDs
-- starting at 1 in insertion order below).
--
-- Demo logins created here (see README.md for the full list):
--   Administrator : admin@group6.test       / Admin@123
--   Collectors    : collector1@group6.test  / Collector@123
--                   collector2@group6.test  / Collector@123
--                   collector3@group6.test  / Collector@123
--   Residents     : resident1@group6.test .. resident6@group6.test / Resident@123
--
-- Password hashes below were generated with PHP's password_hash() using the
-- passwords above (PASSWORD_DEFAULT / bcrypt), never store plain passwords.

-- --- Zones -------------------------------------------------------------
INSERT INTO zones (zone_name, location_description) VALUES
('Zone A - Central', 'Central business district and surrounding residential blocks'),
('Zone B - Eastside', 'Eastern suburb, mainly low-density residential estates'),
('Zone C - Riverside', 'Riverside neighbourhood along the main river road');

-- --- Administrators ------------------------------------------------------
-- Password: Admin@123
INSERT INTO administrators (full_name, email, password_hash, role) VALUES
('Group 6 Administrator', 'admin@group6.test', '$2y$10$zsv/U6jWBdPf6jwaIWys0uIjjDKrl2kuS39zAFCx2IX3oQA/StUAy', 'administrator');

-- --- Vehicles ------------------------------------------------------------
INSERT INTO vehicles (registration_number, vehicle_type, capacity_kg) VALUES
('UBH 123X', 'Truck', 2000),
('UBG 456Y', 'Van', 1000),
('UBJ 789Z', 'Truck', 2500);

-- --- Collectors ------------------------------------------------------------
-- Password (all three): Collector@123
INSERT INTO collectors (full_name, phone_number, email, password_hash, zone_id, vehicle_id) VALUES
('John Mukasa', '0700111222', 'collector1@group6.test', '$2y$10$46VzqUWURQY7DgJDODRNUOCG6g71zNqn2aDOl6N/aomOGb6gDNsuu', 1, 1),
('Grace Nabirye', '0700333444', 'collector2@group6.test', '$2y$10$46VzqUWURQY7DgJDODRNUOCG6g71zNqn2aDOl6N/aomOGb6gDNsuu', 2, 2),
('Peter Okello', '0700555666', 'collector3@group6.test', '$2y$10$46VzqUWURQY7DgJDODRNUOCG6g71zNqn2aDOl6N/aomOGb6gDNsuu', 3, 3);

-- --- Residents ------------------------------------------------------------
-- Password (all six): Resident@123
INSERT INTO residents (full_name, phone_number, email, password_hash, address, zone_id) VALUES
('Sarah Namutebi', '0771111111', 'resident1@group6.test', '$2y$10$Hz82TWRy8yQ6ai5CWA8RAegRgIl/n1pGSzfmeJmMX99N1PAE2fG9S', 'Plot 12, Kanjokya Street', 1),
('David Kato', '0772222222', 'resident2@group6.test', '$2y$10$Hz82TWRy8yQ6ai5CWA8RAegRgIl/n1pGSzfmeJmMX99N1PAE2fG9S', 'Plot 45, Bombo Road', 1),
('Esther Achieng', '0773333333', 'resident3@group6.test', '$2y$10$Hz82TWRy8yQ6ai5CWA8RAegRgIl/n1pGSzfmeJmMX99N1PAE2fG9S', 'Plot 8, Jinja Road', 2),
('Moses Ssekandi', '0774444444', 'resident4@group6.test', '$2y$10$Hz82TWRy8yQ6ai5CWA8RAegRgIl/n1pGSzfmeJmMX99N1PAE2fG9S', 'Plot 22, Spring Road', 2),
('Ritah Namuli', '0775555555', 'resident5@group6.test', '$2y$10$Hz82TWRy8yQ6ai5CWA8RAegRgIl/n1pGSzfmeJmMX99N1PAE2fG9S', 'Plot 5, Riverside Drive', 3),
('Yusuf Male', '0776666666', 'resident6@group6.test', '$2y$10$Hz82TWRy8yQ6ai5CWA8RAegRgIl/n1pGSzfmeJmMX99N1PAE2fG9S', 'Plot 17, Riverside Drive', 3);

-- --- Schedules (2 per zone) ------------------------------------------------
INSERT INTO schedules (zone_id, collection_day, collection_time, frequency) VALUES
(1, 'Monday', '8:00 AM', 'Weekly'),
(1, 'Thursday', '8:00 AM', 'Weekly'),
(2, 'Tuesday', '9:00 AM', 'Weekly'),
(2, 'Friday', '9:00 AM', 'Weekly'),
(3, 'Wednesday', '7:30 AM', 'Weekly'),
(3, 'Saturday', '7:30 AM', 'Bi-weekly');

-- --- Pickup requests (mix of statuses) -------------------------------------
-- request_id 1..8 in this order.
INSERT INTO pickup_requests (resident_id, collector_id, request_date, waste_type, status) VALUES
(1, 1, '2026-08-10', 'Household', 'completed'),
(1, NULL, '2026-08-13', 'Plastic', 'pending'),
(2, 1, '2026-08-09', 'Organic', 'completed'),
(2, 1, '2026-08-14', 'Household', 'assigned'),
(3, 2, '2026-08-11', 'Plastic', 'completed'),
(4, NULL, '2026-08-12', 'Other', 'pending'),
(5, 3, '2026-08-08', 'Household', 'completed'),
(6, 3, '2026-08-13', 'Organic', 'assigned');

-- --- Payments (linked to some of the completed requests above) ------------
-- Request 7 (resident 5, completed) is deliberately left unpaid so the
-- simulated payment flow can be demonstrated live.
INSERT INTO payments (resident_id, request_id, amount, payment_method, status) VALUES
(1, 1, 5000.00, 'mtn_mobile_money', 'paid'),
(2, 3, 3000.00, 'airtel_money', 'paid'),
(3, 5, 3000.00, 'cash', 'paid');

-- --- Complaints (at least one resolved, at least one open) ------------------
INSERT INTO complaints (resident_id, description, status, admin_response) VALUES
(1, 'Missed pickup on the scheduled Monday collection day.', 'resolved', 'We apologize for the inconvenience. The collector has been notified and will prioritize your next scheduled pickup.'),
(3, 'The collector left waste scattered near the bin instead of loading it all.', 'open', NULL),
(6, 'Requesting an additional collection day for our street during the rainy season.', 'open', NULL);
