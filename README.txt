STUDENT ENROLLMENT MANAGEMENT SYSTEM
Front end: HTML/CSS
Server-side language: PHP
Database: MySQL / MariaDB
Recommended local environment: XAMPP

SETUP STEPS
1. Install XAMPP.
2. Start Apache and MySQL in the XAMPP Control Panel.
3. Open http://localhost/phpmyadmin/
4. Click Import and choose database.sql from this project folder, then click Import/Go.
   Alternative: open the SQL tab and paste the contents of database.sql.
5. Copy the entire student_enrollment_system folder into:
   Windows: C:\xampp\htdocs\
   macOS (typical XAMPP): /Applications/XAMPP/htdocs/
   Linux (typical XAMPP): /opt/lampp/htdocs/
6. Open config.php. The default database username is root and password is blank.
   If your MySQL setup has a password, update $username and $password.
7. Visit http://localhost/student_enrollment_system/
8. Start with Programs, Subjects, Terms, and Sections. Add a student, then create an enrollment.

PROJECT FILES
- database.sql: database and seven related tables, plus starter academic records
- config.php: PDO connection and shared helper functions
- header.php / footer.php: shared page layout
- index.php: dashboard
- students.php: student CRUD and search
- programs.php: program CRUD
- subjects.php: subject CRUD
- terms.php: academic term CRUD
- sections.php: section CRUD and capacity display
- enrollments.php: enrollment creation, section selection, status update, deletion
- style.css: responsive styling

NOTES
- This is an educational/demo project. It does not include a login system or production-ready authorization.
- Do not place real sensitive student data in a public server without appropriate security, authorization, privacy safeguards, backups, and review.
- SQL deletion can be blocked if a record is referenced by another table. This protects database relationships.
- One enrollment per student per term is enforced by a unique key. The same section cannot be added twice to the same enrollment.
- Capacity is checked when a new enrollment is submitted, but a production system would require stronger concurrency controls and additional validation.
