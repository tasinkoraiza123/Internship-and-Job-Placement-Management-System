CREATE DATABASE IF NOT EXISTS placement_system;
USE placement_system;


-- admin table
CREATE TABLE IF NOT EXISTS admin (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    adminname VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL
);

INSERT INTO admin (adminname, password, email) 
VALUES ('admin', 'admin1234', 'admin@university.edu');

CREATE TABLE IF NOT EXISTS students (
    student_id VARCHAR(20) PRIMARY KEY,
    admin_id INT NOT NULL,
    password VARCHAR(255) NOT NULL,
    f_name VARCHAR(100) NOT NULL,
    l_name VARCHAR(100),
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    cgpa DECIMAL(3,2) CHECK (cgpa >= 0 AND cgpa <= 4.00),
    department VARCHAR(100), 
    FOREIGN KEY (admin_id) REFERENCES admin (admin_id) ON DELETE CASCADE
);


CREATE TABLE IF NOT EXISTS companies (
    company_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    description TEXT
);

CREATE TABLE IF NOT EXISTS jobs (
    job_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    min_cgpa DECIMAL(3,2) DEFAULT 0.00,
    salary VARCHAR(50),
    street_no INT, 
    city VARCHAR(100),
    company_id INT NOT NULL,
    FOREIGN KEY (company_id) REFERENCES companies(company_id) ON DELETE CASCADE
);


CREATE TABLE IF NOT EXISTS events (
    event_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    type VARCHAR(100),
    event_date DATE NOT NULL,
    street_no INT, 
    city VARCHAR(100),
    max_attendees INT DEFAULT 100
);

CREATE TABLE IF NOT EXISTS applications (
    application_id INT AUTO_INCREMENT PRIMARY KEY,
    job_id INT NOT NULL,
    applied_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    interview_date DATE,
    offer_salary VARCHAR(50),
    FOREIGN KEY (job_id) REFERENCES jobs(job_id) ON DELETE CASCADE
);
 
CREATE TABLE IF NOT EXISTS event_attendance (
    attendance_id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS skills (
    skill_id INT AUTO_INCREMENT PRIMARY KEY,
    skill_name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT
);

-- belong to relationship 
CREATE TABLE IF NOT EXISTS belong_to (
    student_id VARCHAR(20) NOT NULL,
    skill_id INT NOT NULL,
    proficiency_level ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
    PRIMARY KEY (student_id, skill_id),
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (skill_id) REFERENCES skills(skill_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS departments (
    dept_id INT AUTO_INCREMENT PRIMARY KEY,
    dept_name VARCHAR(100) NOT NULL UNIQUE,
    dept_head VARCHAR(100),
    email VARCHAR(100)
);

CREATE TABLE IF NOT EXISTS interviews (
    interview_id INT AUTO_INCREMENT PRIMARY KEY,
    application_id INT NOT NULL,
    interviewer_name VARCHAR(100),
    scheduled_date DATETIME,
    FOREIGN KEY (application_id) REFERENCES applications(application_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS placement_records (
    placement_id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    job_title VARCHAR(200),
    join_date DATE,
    salary_package VARCHAR(50),
    FOREIGN KEY (company_id) REFERENCES companies(company_id) ON DELETE CASCADE
);

-- specialization tables
CREATE TABLE IF NOT EXISTS fulltime_job (
    job_id INT PRIMARY KEY,
    shift VARCHAR(50),
    annual_salary DECIMAL(10,2),
    FOREIGN KEY (job_id) REFERENCES jobs(job_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS internship_job (
    job_id INT PRIMARY KEY,
    internship_type ENUM('summer', 'winter', 'semester', 'year-round') DEFAULT 'summer',
    duration VARCHAR(50),
    FOREIGN KEY (job_id) REFERENCES jobs(job_id) ON DELETE CASCADE
);



-- multi-valued attributes tables

-- student phones 
CREATE TABLE IF NOT EXISTS student_phone (
    student_id VARCHAR(20) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    PRIMARY KEY (student_id, phone),
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE
);

-- company phones (Multi-valued attribute)
CREATE TABLE IF NOT EXISTS company_phone (
    company_id INT NOT NULL,
    phone VARCHAR(20) NOT NULL,
    PRIMARY KEY (company_id, phone),
    FOREIGN KEY (company_id) REFERENCES companies(company_id) ON DELETE CASCADE
);

-- ternary relationship tables
CREATE TABLE IF NOT EXISTS apply (
    student_id VARCHAR(20) NOT NULL,
    company_id INT NOT NULL,
    job_id INT NOT NULL,
    PRIMARY KEY (student_id, company_id, job_id),
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (company_id) REFERENCES companies(company_id) ON DELETE CASCADE,
    FOREIGN KEY (job_id) REFERENCES jobs(job_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS gets (
    student_id VARCHAR(20) NOT NULL,
    company_id INT NOT NULL,
    placement_id INT NOT NULL,
    PRIMARY KEY (student_id, company_id, placement_id),
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (company_id) REFERENCES companies(company_id) ON DELETE CASCADE,
    FOREIGN KEY (placement_id) REFERENCES placement_records(placement_id) ON DELETE CASCADE
);

-- Normalize department in students
ALTER TABLE students
ADD COLUMN dept_id INT,
ADD FOREIGN KEY (dept_id) REFERENCES departments(dept_id);

