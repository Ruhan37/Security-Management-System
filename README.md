# 🔐 Security Management Database System

> A powerful web-based database management system for security operations with comprehensive SQL capabilities and an intuitive interface.

[![PHP](https://img.shields.io/badge/PHP-7.4%2B-blue)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-orange)](https://www.mysql.com/)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-purple)](https://getbootstrap.com/)
[![License](https://img.shields.io/badge/License-MIT-green)](LICENSE)

---

## ✨ Features

### � Complete Database Schema
7 interconnected tables managing security operations:
- **Roles** → User role management
- **Users** → Account information with role assignments
- **Incidents** → Security incident tracking
- **Assets** → IT asset inventory
- **Vulnerabilities** → Security flaw documentation
- **Logs** → Activity tracking
- **Incident_Assets** → Many-to-many relationships

### 🛠️ Full SQL Operations Suite

#### **CRUD Operations**
Complete Create, Read, Update, Delete with:
- ✅ Dynamic forms with validation
- ✅ Foreign key dropdowns
- ✅ Confirmation dialogs
- ✅ Real-time updates

#### **Advanced Query Builder**
- **Filtering**: WHERE, LIKE, IN, BETWEEN
- **Sorting**: ORDER BY (ASC/DESC)
- **Grouping**: GROUP BY with HAVING
- **Limiting**: Result set controls
- **Multi-condition** support with dynamic WHERE clauses

#### **JOIN Operations** (All Types)
- INNER JOIN
- LEFT/RIGHT OUTER JOIN
- CROSS JOIN
- NATURAL JOIN
- SELF JOIN
- EQUI & NON-EQUI JOINS

---

## 🚀 Quick Start

### Prerequisites
- XAMPP (Apache + MySQL + PHP 7.4+)
- Modern web browser

### Installation

1. **Setup XAMPP**
   ```bash
   # Start Apache and MySQL from XAMPP Control Panel
   # Ensure MySQL is running on port 4306 (or update config/database.php)
   ```

2. **Initialize Database**
   ```
   Navigate to: http://localhost/DB_project/setup.php
   Click "Setup Database" button
   ```

3. **Launch Application**
   ```
   Open: http://localhost/DB_project/
   ```

**That's it!** 🎉 The system comes pre-loaded with sample data.

---

## � Key Highlights

### 🎨 **Modern UI/UX**
- Responsive Bootstrap 5 design
- Gradient themes & smooth animations
- Mobile-optimized interface
- Font Awesome icons throughout

### 🔒 **Security First**
- PDO prepared statements (SQL injection protection)
- Input validation (client & server-side)
- Foreign key constraints
- Error handling with user-friendly messages

### 📱 **Fully Responsive**
- Works seamlessly on desktop, tablet, and mobile
- Adaptive layouts for all screen sizes
- Touch-friendly controls

### 📈 **Rich Sample Data**
- 10 Roles, 15 Users
- 20 Assets, 15 Incidents
- 20 Vulnerabilities, 30 Log entries
- Perfect for testing and demonstrations

---

## � Screenshots

### Dashboard
Clean, modern interface with easy navigation to all operations.

### Query Builder
Interactive form supporting all JOIN types with real-time SQL preview and results.

### CRUD Operations
Intuitive tabbed interface for managing all database tables.

---

## 🎯 Use Cases

- 🏫 **Educational**: Learn SQL operations hands-on
- 💼 **Portfolio**: Demonstrate full-stack database skills
- 🔬 **Prototyping**: Rapid security management system development
- 📚 **Teaching**: Interactive SQL teaching tool

---

## 🛠️ Tech Stack

| Technology | Purpose |
|------------|---------|
| PHP 7.4+ | Backend logic & database operations |
| MySQL 5.7+ | Relational database management |
| Bootstrap 5 | Responsive UI framework |
| JavaScript ES6+ | Dynamic interactions |
| Font Awesome | Professional iconography |
| PDO | Secure database connectivity |

---

## 📖 Documentation

Comprehensive guides included:
- 📋 **TESTING_GUIDE.md** - Step-by-step testing procedures
- 🔗 **JOIN_OPERATIONS_GUIDE.md** - All JOIN types explained
- ✅ **LEFT_RIGHT_JOIN_TEST.md** - OUTER JOIN testing

---

## 🔧 Configuration

**Default MySQL Port:** 4306

To change, edit `config/database.php`:
```php
private $port = '3306'; // Your MySQL port
```

---

## 🐛 Troubleshooting

| Issue | Solution |
|-------|----------|
| Connection Error | Verify MySQL is running, check port in config |
| Blank Page | Check PHP errors in XAMPP logs |
| Setup Fails | Ensure proper file permissions |

---

## 🎓 Perfect For

- ✅ Database course projects
- ✅ Security management demonstrations
- ✅ SQL learning and practice
- ✅ Portfolio showcases
- ✅ Job interviews

---

## � Support

Having issues? Check:
1. ✅ XAMPP services are running
2. ✅ Database setup completed successfully
3. ✅ Browser console for errors

---

## � License

MIT License - Free for educational and personal use.

---

## 🌟 Show Your Support

Give a ⭐️ if this project helped you learn database management!

---

**Built with ❤️ for learning and innovation** | [View Demo](http://localhost/DB_project/) | [Report Bug](https://github.com/Ruhan37/DB_PROJECT/issues) | [Documentation](TESTING_GUIDE.md)
