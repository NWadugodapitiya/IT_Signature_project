# IT Signature E-commerce Project

A full-featured e-commerce system with real-time inventory management, secure user authentication, and shopping cart functionality.

🌐 **Live Demo**: [https://testproject.infy.uk](https://testproject.infy.uk)

## Features

- 🔐 **Secure Authentication System**
  - User registration with mobile verification
  - OTP-based login using TextIt API
  - Role-based access control (Admin/User)
  - Password hashing and secure session management

- 🛍️ **Product Management**
  - Real-time inventory tracking
  - Product listing with stock status
  - Dynamic stock updates
  - Admin product management interface

- 🛒 **Shopping Cart System**
  - Real-time cart updates
  - Stock validation
  - Transaction support
  - Quantity management
  - Total calculation

- 📱 **Responsive Design**
  - Bootstrap 5 framework
  - Mobile-first approach
  - Clean and modern UI
  - Intuitive user experience

## Technologies Used

### Frontend
- HTML5, CSS3, JavaScript
- Bootstrap 5.3.0
- jQuery 3.6.0
- Font Awesome 6.4.0
- SweetAlert2

### Backend
- PHP 7.4+
- MySQL 5.7+
- PDO Database Layer
- TextIt API Integration

### Security Features
- PDO Prepared Statements
- Password Hashing
- XSS Prevention
- CSRF Protection
- Input Validation
- Session Management

## Database Structure

### Users Table
```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    mobile VARCHAR(15) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    type ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Products Table
```sql
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Cart Table
```sql
CREATE TABLE card (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    qty INT NOT NULL DEFAULT 1,
    datetime TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);
```

## Setup Instructions

1. **Clone the Repository**
   ```bash
   git clone https://github.com/yourusername/IT_Signature_project.git
   cd IT_Signature_project
   ```

2. **Database Configuration**
   - Create a MySQL database
   - Import the SQL schema files
   - Configure database connection in `config.php`:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_NAME', 'your_database');
     define('DB_USER', 'your_username');
     define('DB_PASS', 'your_password');
     ```

3. **TextIt API Configuration**
   - Sign up for a TextIt account
   - Get your API credentials
   - Configure in `config.php`:
     ```php
     define('TEXTIT_API_KEY', 'your_api_key');
     ```

4. **Web Server Setup**
   - Configure your web server (Apache/Nginx)
   - Set document root to project directory
   - Ensure PHP has required extensions:
     - PDO
     - PDO_MySQL
     - curl
     - json

5. **File Permissions**
   ```bash
   chmod 755 -R /path/to/project
   chmod 777 -R /path/to/project/uploads
   ```

## Security Considerations

- All user inputs are sanitized and validated
- Passwords are hashed using PHP's password_hash()
- PDO prepared statements prevent SQL injection
- Session handling prevents hijacking
- HTTPS is required for production use

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Acknowledgments

- Bootstrap team for the amazing framework
- TextIt for SMS integration
- Font Awesome for icons
- SweetAlert2 for beautiful alerts

## Support

For support, email nirmalw.infy.uk or create an issue in the repository.