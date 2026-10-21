# PharmaCare Admin Management System

A complete admin panel for managing medicines in a pharmacy system with full CRUD operations, authentication, and modern UI.

## Features

### 🔐 Admin Authentication
- Secure login system with session management
- Password protection with hashing
- Logout functionality

### 📊 Admin Dashboard
- Overview statistics (total medicines, active items, stock alerts)
- Recent medicines display
- Low stock warnings
- Quick access to all functions

### 💊 Medicine Management
- **Add Medicine**: Complete form with all necessary fields
- **Manage Medicines**: List view with search, filtering, and sorting
- **Edit Medicine**: Update all medicine details with live preview
- **View Medicine**: Detailed view of medicine information
- **Delete Medicine**: Safe deletion with confirmation
- **Toggle Status**: Activate/deactivate medicines

### 🎨 Modern UI Features
- Responsive Bootstrap 5 design
- Gradient sidebar navigation
- Interactive tables with hover effects
- Stock level indicators (low stock, out of stock)
- Prescription required badges
- Image upload/preview functionality

### 📦 Medicine Fields
- Name, Description, Category
- Price (current & old price)
- Stock quantity with alerts
- Manufacturer information
- Expiry date tracking
- Image URL support
- Prescription requirement flag
- Active/inactive status

## Installation

1. **Database Setup**
   ```sql
   -- Import the database structure
   -- Run the database.sql file in your MySQL/MariaDB server
   ```

2. **Configuration**
   - Update `config.php` with your database credentials:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_NAME', 'pharmacy_db');
     define('DB_USER', 'your_username');
     define('DB_PASS', 'your_password');
     ```

3. **Default Admin Login**
   - Username: `admin`
   - Password: `admin123`

## File Structure

```
after_login/
├── config.php                 # Database configuration and functions
├── database.sql               # Database structure and sample data
├── login.php                  # Admin login page
├── logout.php                 # Logout functionality
├── admin_dashboard.php        # Main admin dashboard
├── manage_medicines.php       # List and manage all medicines
├── add_medicine.php           # Add new medicine form
├── edit_medicine.php          # Edit existing medicine
├── view_medicine.php          # View medicine details
├── delete_medicine.php        # Delete medicine with confirmation
├── toggle_medicine_status.php # Toggle medicine active/inactive status
├── Dashboard.php              # User dashboard (existing)
└── README.md                  # This documentation
```

## Usage

### Accessing the Admin Panel
1. Navigate to `login.php` in your browser
2. Login with admin credentials
3. You'll be redirected to the admin dashboard

### Managing Medicines
1. **Add Medicine**: Click "Add Medicine" in the sidebar
2. **View All**: Click "Medicines" in the sidebar
3. **Edit**: Click the pencil icon on any medicine
4. **Delete**: Click the trash icon with confirmation
5. **Toggle Status**: Click the toggle icon to activate/deactivate

### Search and Filter
- Use the search bar to find medicines by name, description, or manufacturer
- Filter by category or status
- Export data to CSV format

## Security Features

- SQL injection protection with prepared statements
- XSS protection with input sanitization
- Session-based authentication
- Password hashing with PHP's password_hash()
- CSRF protection on forms

## Database Schema

### Admin Table
- `id`, `username`, `password`, `email`, `created_at`

### Medicines Table
- `id`, `name`, `description`, `category`, `price`, `old_price`
- `stock_quantity`, `manufacturer`, `expiry_date`, `image_url`
- `prescription_required`, `status`, `created_at`, `updated_at`

## Customization

### Adding New Categories
Edit the category dropdown in `add_medicine.php` and `edit_medicine.php`:
```html
<option value="New Category">New Category</option>
```

### Customizing Colors
Update the gradient colors in the CSS sections:
```css
background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
```

### Adding New Fields
1. Add column to the medicines table
2. Update the form fields in add/edit pages
3. Update the SQL queries accordingly

## Support

This admin system provides a complete foundation for medicine management. All essential CRUD operations are implemented with a modern, user-friendly interface.
