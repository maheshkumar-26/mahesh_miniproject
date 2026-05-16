-- ============================================================
-- Employee Compensation Insights - Database Schema
-- Database: employee_compensation_insights
-- ============================================================

CREATE DATABASE IF NOT EXISTS `employee_compensation_insights`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `employee_compensation_insights`;

-- ============================================================
-- Table: departments
-- ============================================================
CREATE TABLE IF NOT EXISTS `departments` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- Table: users (admin + employees login)
-- ============================================================
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(191) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin','employee') NOT NULL DEFAULT 'employee',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- Table: employees
-- ============================================================
CREATE TABLE IF NOT EXISTS `employees` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `employee_code` VARCHAR(20) NOT NULL UNIQUE,
  `full_name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(191) NOT NULL,
  `phone` VARCHAR(20),
  `gender` ENUM('Male','Female','Other') DEFAULT 'Male',
  `department_id` INT UNSIGNED NOT NULL,
  `designation` VARCHAR(100),
  `joining_date` DATE,
  `experience_years` DECIMAL(4,1) DEFAULT 0,
  `address` TEXT,
  `profile_image` VARCHAR(255) DEFAULT 'default.png',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_emp_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_emp_dept` FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ============================================================
-- Table: payroll
-- ============================================================
CREATE TABLE IF NOT EXISTS `payroll` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `employee_id` INT UNSIGNED NOT NULL,
  `month` TINYINT UNSIGNED NOT NULL COMMENT '1-12',
  `year` YEAR NOT NULL,
  `basic_salary` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `hra` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `allowances` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `bonus` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `incentives` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `overtime_pay` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `tax_deduction` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `pf_deduction` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `insurance_deduction` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `gross_salary` DECIMAL(12,2) GENERATED ALWAYS AS (
    `basic_salary` + `hra` + `allowances` + `bonus` + `incentives` + `overtime_pay`
  ) STORED,
  `total_deductions` DECIMAL(12,2) GENERATED ALWAYS AS (
    `tax_deduction` + `pf_deduction` + `insurance_deduction`
  ) STORED,
  `net_salary` DECIMAL(12,2) GENERATED ALWAYS AS (
    (`basic_salary` + `hra` + `allowances` + `bonus` + `incentives` + `overtime_pay`)
    - (`tax_deduction` + `pf_deduction` + `insurance_deduction`)
  ) STORED,
  `status` ENUM('pending','paid','cancelled') NOT NULL DEFAULT 'pending',
  `remarks` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_payroll_emp` FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `uq_payroll_emp_month` (`employee_id`,`month`,`year`)
) ENGINE=InnoDB;

-- ============================================================
-- Table: salary_feedback
-- ============================================================
CREATE TABLE IF NOT EXISTS `salary_feedback` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `employee_id` INT UNSIGNED NOT NULL,
  `salary_satisfaction` TINYINT UNSIGNED NOT NULL DEFAULT 3 COMMENT '1-5',
  `work_life_balance` TINYINT UNSIGNED NOT NULL DEFAULT 3 COMMENT '1-5',
  `benefits_rating` TINYINT UNSIGNED NOT NULL DEFAULT 3 COMMENT '1-5',
  `comments` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_feedback_emp` FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- Table: notifications
-- ============================================================
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `message` TEXT NOT NULL,
  `type` ENUM('info','success','warning','danger') DEFAULT 'info',
  `is_read` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- Table: password_reset
-- ============================================================
CREATE TABLE IF NOT EXISTS `password_reset` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(191) NOT NULL,
  `token` VARCHAR(255) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `used` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- Sample Data: Departments
-- ============================================================
INSERT INTO `departments` (`name`, `description`) VALUES
('Information Technology', 'Software development and IT infrastructure'),
('Human Resources', 'Recruitment, payroll and employee relations'),
('Finance', 'Accounting, budgeting and financial planning'),
('Marketing', 'Brand management and digital marketing'),
('Operations', 'Day-to-day business operations'),
('Sales', 'Revenue generation and client management');

-- ============================================================
-- Sample Data: Users (password = Admin@123 hashed)
-- ============================================================
INSERT INTO `users` (`name`, `email`, `password`, `role`) VALUES
('Super Admin', 'admin@eci.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Rahul Sharma', 'rahul@eci.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee'),
('Priya Patel', 'priya@eci.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee'),
('Amit Kumar', 'amit@eci.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee'),
('Sneha Reddy', 'sneha@eci.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee'),
('Vikram Singh', 'vikram@eci.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee');

-- ============================================================
-- Sample Data: Employees
-- ============================================================
INSERT INTO `employees` (`user_id`,`employee_code`,`full_name`,`email`,`phone`,`gender`,`department_id`,`designation`,`joining_date`,`experience_years`,`address`) VALUES
(2,'ECI-001','Rahul Sharma','rahul@eci.com','9876543210','Male',1,'Senior Developer','2021-03-15',4.5,'123 MG Road, Bangalore'),
(3,'ECI-002','Priya Patel','priya@eci.com','9876543211','Female',2,'HR Manager','2020-07-01',5.0,'45 Park Street, Mumbai'),
(4,'ECI-003','Amit Kumar','amit@eci.com','9876543212','Male',3,'Finance Analyst','2022-01-10',3.0,'78 Civil Lines, Delhi'),
(5,'ECI-004','Sneha Reddy','sneha@eci.com','9876543213','Female',4,'Marketing Lead','2021-09-20',4.0,'22 Jubilee Hills, Hyderabad'),
(6,'ECI-005','Vikram Singh','vikram@eci.com','9876543214','Male',5,'Operations Manager','2019-05-12',6.5,'56 Anna Nagar, Chennai');

-- ============================================================
-- Sample Data: Payroll (last 3 months for each employee)
-- ============================================================
INSERT INTO `payroll` (`employee_id`,`month`,`year`,`basic_salary`,`hra`,`allowances`,`bonus`,`incentives`,`overtime_pay`,`tax_deduction`,`pf_deduction`,`insurance_deduction`,`status`) VALUES
-- Rahul Sharma (ECI-001)
(1,3,2026,45000,18000,5000,3000,2000,1500,6500,5400,1200,'paid'),
(1,4,2026,45000,18000,5000,0,2000,0,6500,5400,1200,'paid'),
(1,5,2026,45000,18000,5000,5000,2000,2000,6500,5400,1200,'pending'),
-- Priya Patel (ECI-002)
(2,3,2026,38000,15200,4000,2000,1500,0,5500,4560,1000,'paid'),
(2,4,2026,38000,15200,4000,0,1500,0,5500,4560,1000,'paid'),
(2,5,2026,38000,15200,4000,3000,1500,0,5500,4560,1000,'pending'),
-- Amit Kumar (ECI-003)
(3,3,2026,32000,12800,3500,1500,1000,0,4500,3840,800,'paid'),
(3,4,2026,32000,12800,3500,0,1000,0,4500,3840,800,'paid'),
(3,5,2026,32000,12800,3500,2000,1000,1000,4500,3840,800,'pending'),
-- Sneha Reddy (ECI-004)
(4,3,2026,40000,16000,4500,2500,1800,0,5800,4800,1100,'paid'),
(4,4,2026,40000,16000,4500,0,1800,0,5800,4800,1100,'paid'),
(4,5,2026,40000,16000,4500,4000,1800,1500,5800,4800,1100,'pending'),
-- Vikram Singh (ECI-005)
(5,3,2026,50000,20000,6000,4000,2500,2000,7500,6000,1500,'paid'),
(5,4,2026,50000,20000,6000,0,2500,0,7500,6000,1500,'paid'),
(5,5,2026,50000,20000,6000,6000,2500,3000,7500,6000,1500,'pending');

-- ============================================================
-- Sample Data: Salary Feedback
-- ============================================================
INSERT INTO `salary_feedback` (`employee_id`,`salary_satisfaction`,`work_life_balance`,`benefits_rating`,`comments`) VALUES
(1,4,4,3,'Good salary but benefits could be improved.'),
(2,3,5,4,'Work-life balance is excellent.'),
(3,4,3,4,'Satisfied with current package.'),
(4,5,4,5,'Very happy with compensation.'),
(5,3,3,3,'Expecting a raise soon.');

-- ============================================================
-- Sample Data: Notifications
-- ============================================================
INSERT INTO `notifications` (`user_id`,`title`,`message`,`type`) VALUES
(2,'Payroll Generated','Your payroll for May 2026 has been generated.','success'),
(3,'Payroll Generated','Your payroll for May 2026 has been generated.','success'),
(4,'Bonus Added','A bonus of ₹2,000 has been added to your May payroll.','info'),
(5,'Payroll Generated','Your payroll for May 2026 has been generated.','success'),
(6,'Payroll Generated','Your payroll for May 2026 has been generated.','success');
