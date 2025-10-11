# Security Management Database System

A comprehensive web-based database management system for security operations, featuring all major SQL operations through an intuitive user interface.

## Features

### 🔒 Database Schema
- **Roles**: User role management
- **Users**: User account information
- **Incidents**: Security incident tracking
- **Assets**: IT asset management
- **Vulnerabilities**: Security vulnerability tracking
- **Logs**: Activity logging
- **Incident_Assets**: Many-to-many relationship between incidents and assets

### 🛠️ Operations Supported

#### 1. CRUD Operations
- **Create**: Add new records to any table
- **Read**: View all records with proper foreign key resolution
- **Update**: Edit existing records with validation
- **Delete**: Remove records with confirmation

#### 2. Constraints & Filtering
- **WHERE**: Filter records based on conditions
- **ORDER BY**: Sort results in ascending/descending order
- **GROUP BY**: Group results by specified columns
- **HAVING**: Filter grouped results
- **LIMIT**: Control result set size

#### 3. SELECT Commands & Aggregates
- **Pattern Matching**: LIKE, NOT LIKE, REGEXP, length checks
- **Aggregate Functions**: COUNT, SUM, AVG, MIN, MAX
- **Complex SELECT**: Multi-field selection with conditions
- **Grouping with Aggregation**: Combined GROUP BY and aggregate functions

#### 4. Subqueries & Set Operations
- **Subqueries**: EXISTS, IN, NOT IN, ALL, ANY, correlated, scalar
- **UNION**: Combine results from multiple queries
- **INTERSECT**: Simulated intersection operations
- **EXCEPT**: Simulated difference operations
- **Views**: Create, manage, and query database views

#### 5. JOIN Operations
- **INNER JOIN**: Records existing in both tables
- **LEFT JOIN**: All left table records + matching right records
- **RIGHT JOIN**: All right table records + matching left records
- **FULL OUTER JOIN**: All records from both tables (simulated)
- **CROSS JOIN**: Cartesian product of tables
- **NATURAL JOIN**: Join on columns with matching names
- **SELF JOIN**: Join table with itself
- **EQUI JOIN**: Joins using equality conditions
- **NON-EQUI JOIN**: Joins using comparison operators

## 🚀 Installation & Setup

### Prerequisites
- XAMPP (Apache + MySQL + PHP)
- Web browser (Chrome, Firefox, Safari, Edge)

### Installation Steps

1. **Install XAMPP**
   - Download from [https://www.apachefriends.org/](https://www.apachefriends.org/)
   - Install and start Apache and MySQL services

2. **Clone/Download Project**
   ```bash
   # Navigate to XAMPP htdocs directory
   cd /Applications/XAMPP/xamppfiles/htdocs/

   # The project should be in DB_project folder
   ```

3. **Configure MySQL Port**
   - Default configuration uses port 4306
   - If your MySQL runs on different port, edit `config/database.php`
   ```php
   private $port = '4306'; // Change to your MySQL port
   ```

4. **Database Setup**
   - Open your browser and navigate to: `http://localhost/DB_project/setup.php`
   - This will create the database and populate it with sample data
   - After successful setup, click "Go to Main Application"

5. **Access the Application**
   - Main URL: `http://localhost/DB_project/`
   - The application will load with a modern dashboard interface

## 📱 User Interface

### Navigation
- **Responsive Design**: Works on desktop, tablet, and mobile devices
- **Section-based Layout**: Each operation type has its own section
- **Scroll Areas**: All sections include scroll bars for easy navigation
- **Real-time Results**: Query results display immediately with syntax highlighting

### Sections Overview
1. **CRUD Operations**: Tabbed interface for each table with forms and data display
2. **Constraints**: Interactive forms for WHERE, ORDER BY, GROUP BY, HAVING operations
3. **Aggregates**: Pattern matching, aggregate functions, and complex SELECT operations
4. **Subqueries**: Predefined and custom subquery operations with set operations
5. **Joins**: All JOIN types with examples and interactive execution

## 🎨 Design Features

### Modern UI Elements
- **Bootstrap 5**: Responsive framework with modern components
- **Font Awesome Icons**: Professional iconography throughout
- **Custom CSS**: Attractive gradients, animations, and hover effects
- **Scroll Areas**: Contained scrolling for large result sets
- **Status Indicators**: Color-coded status badges and criticality levels

### Responsive Design
- **Mobile-First**: Optimized for all screen sizes
- **Grid System**: Flexible layouts that adapt to different devices
- **Touch-Friendly**: Large buttons and touch targets for mobile users
- **Readable Typography**: Clear fonts and appropriate sizing

## 📊 Sample Data

The system includes comprehensive sample data:
- 4 different user roles (Administrator, Security Analyst, IT Manager, User)
- 5 users with different roles and join dates
- 5 IT assets with varying criticality levels
- 4 security incidents with different statuses
- 4 vulnerabilities with various severity levels
- Multiple log entries and asset-incident relationships

## 🔧 Technical Specifications

### Backend
- **PHP 7.4+**: Server-side logic and database operations
- **PDO**: Secure database connectivity with prepared statements
- **MySQL 5.7+**: Database engine with full SQL feature support

### Frontend
- **HTML5**: Modern semantic markup
- **CSS3**: Advanced styling with flexbox and grid
- **JavaScript ES6+**: Interactive functionality and dynamic content
- **Bootstrap 5**: Responsive framework and components

### Security Features
- **Prepared Statements**: Protection against SQL injection
- **Input Validation**: Client and server-side validation
- **Error Handling**: Graceful error management and user feedback
- **Foreign Key Constraints**: Database integrity maintenance

## 🎯 Usage Examples

### Creating Records
1. Navigate to CRUD Operations section
2. Select the desired table tab
3. Fill in the form fields (foreign keys show dropdown selections)
4. Click "Add" to create the record

### Running Complex Queries
1. Go to the appropriate section (Constraints, Aggregates, etc.)
2. Select operation type and parameters
3. Click "Execute" to run the query
4. View results in the scrollable table below
5. See the generated SQL query for learning purposes

### Managing Views
1. Navigate to Subqueries section
2. Use predefined views or create custom ones
3. View management operations for listing and querying existing views

## 🐛 Troubleshooting

### Common Issues

**Database Connection Error**
- Verify XAMPP MySQL is running
- Check port configuration in `config/database.php`
- Ensure MySQL credentials are correct

**Setup Page Not Working**
- Verify Apache is running in XAMPP
- Check file permissions in htdocs directory
- Ensure PHP is properly configured

**Blank Pages or Errors**
- Check PHP error logs in XAMPP control panel
- Verify all project files are present
- Check browser console for JavaScript errors

## 📈 Future Enhancements

Potential improvements for the system:
- User authentication and session management
- Export functionality (CSV, PDF, Excel)
- Advanced report generation
- Real-time notifications
- API endpoints for external integrations
- Advanced query builder with drag-and-drop interface

## 📝 License

This project is created for educational purposes. Feel free to use and modify as needed for learning database concepts and web development.

## 🤝 Support

For issues or questions:
1. Check the troubleshooting section above
2. Verify all setup steps have been completed
3. Check that sample data loaded correctly via setup.php

---

**Enjoy exploring database operations with this comprehensive management system!** 🚀