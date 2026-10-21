CREATE DATABASE IF NOT EXISTS student_enrollment_db
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE student_enrollment_db;

CREATE TABLE IF NOT EXISTS Programs (
  program_id INT AUTO_INCREMENT PRIMARY KEY,
  program_code VARCHAR(20) NOT NULL UNIQUE,
  program_name VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS Students (
  student_id INT AUTO_INCREMENT PRIMARY KEY,
  student_number VARCHAR(30) NOT NULL UNIQUE,
  first_name VARCHAR(50) NOT NULL,
  last_name VARCHAR(50) NOT NULL,
  email VARCHAR(100) UNIQUE,
  birth_date DATE NULL,
  program_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_students_program FOREIGN KEY (program_id)
    REFERENCES Programs(program_id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS Subjects (
  subject_id INT AUTO_INCREMENT PRIMARY KEY,
  subject_code VARCHAR(20) NOT NULL UNIQUE,
  subject_name VARCHAR(100) NOT NULL,
  units TINYINT UNSIGNED NOT NULL DEFAULT 3
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS Terms (
  term_id INT AUTO_INCREMENT PRIMARY KEY,
  academic_year VARCHAR(9) NOT NULL,
  semester VARCHAR(20) NOT NULL,
  start_date DATE NULL,
  end_date DATE NULL,
  UNIQUE KEY uq_term (academic_year, semester)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS Sections (
  section_id INT AUTO_INCREMENT PRIMARY KEY,
  section_name VARCHAR(30) NOT NULL,
  subject_id INT NOT NULL,
  term_id INT NOT NULL,
  capacity SMALLINT UNSIGNED NOT NULL DEFAULT 40,
  CONSTRAINT fk_sections_subject FOREIGN KEY (subject_id)
    REFERENCES Subjects(subject_id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_sections_term FOREIGN KEY (term_id)
    REFERENCES Terms(term_id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  UNIQUE KEY uq_section (section_name, subject_id, term_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS Enrollments (
  enrollment_id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  term_id INT NOT NULL,
  enrollment_date DATE NOT NULL,
  status ENUM('Pending','Enrolled','Cancelled') NOT NULL DEFAULT 'Pending',
  CONSTRAINT fk_enrollments_student FOREIGN KEY (student_id)
    REFERENCES Students(student_id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_enrollments_term FOREIGN KEY (term_id)
    REFERENCES Terms(term_id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  UNIQUE KEY uq_student_term (student_id, term_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS EnrollmentDetails (
  enrollment_detail_id INT AUTO_INCREMENT PRIMARY KEY,
  enrollment_id INT NOT NULL,
  section_id INT NOT NULL,
  CONSTRAINT fk_details_enrollment FOREIGN KEY (enrollment_id)
    REFERENCES Enrollments(enrollment_id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_details_section FOREIGN KEY (section_id)
    REFERENCES Sections(section_id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  UNIQUE KEY uq_enrollment_section (enrollment_id, section_id)
) ENGINE=InnoDB;

INSERT IGNORE INTO Programs (program_code, program_name) VALUES
('BSIT','Bachelor of Science in Information Technology'),
('BSBA','Bachelor of Science in Business Administration'),
('BSED','Bachelor of Secondary Education');

INSERT IGNORE INTO Subjects (subject_code, subject_name, units) VALUES
('IT101','Introduction to Information Technology',3),
('DBMS101','Database Management Systems',3),
('WEB101','Introduction to Web Technologies',3);

INSERT IGNORE INTO Terms (academic_year, semester, start_date, end_date) VALUES
('2026-2027','1st Semester','2026-08-01','2026-12-31'),
('2026-2027','2nd Semester','2027-01-01','2027-05-31');

INSERT IGNORE INTO Sections (section_name, subject_id, term_id, capacity) VALUES
('BSIT-2C',1,1,40),
('BSIT-2C',2,1,40),
('BSIT-2C',3,1,40);
