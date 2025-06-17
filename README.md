# Laravel 12 Demo Application

A modern web application built with Laravel 12, featuring a robust authentication system, role-based access control, and a beautiful AdminLTE-based interface.

## Features

- 🔐 **Authentication & Authorization**
  - User registration and login
  - Role-based access control (RBAC)
  - Permission management
  - Super admin functionality

- 🎨 **Modern UI/UX**
  - AdminLTE 3 integration
  - Responsive dashboard
  - Interactive statistics
  - User management interface

- 👥 **User Management**
  - User CRUD operations
  - Role assignment
  - Permission management
  - User activity tracking

- 📊 **Dashboard Features**
  - User statistics
  - Role distribution
  - Recent user activity
  - System overview

## Requirements

- PHP >= 8.2
- Composer
- MySQL/MariaDB
- Node.js & NPM (for frontend assets)

## Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/Meo-ICAR/laravel12demo.git
   cd laravel12demo
   ```

2. Install PHP dependencies:
   ```bash
   composer install
   ```

3. Install frontend dependencies:
   ```bash
   npm install
   ```

4. Create environment file:
   ```bash
   cp .env.example .env
   ```

5. Generate application key:
   ```bash
   php artisan key:generate
   ```

6. Configure your database in `.env` file

7. Run migrations:
   ```bash
   php artisan migrate
   ```

8. Create a super admin user:
   ```bash
   php artisan create:super-admin
   ```

9. Start the development server:
   ```bash
   php artisan serve
   ```

## Usage

1. Access the application at `http://localhost:8000`
2. Login with your super admin credentials
3. Navigate through the dashboard to manage users, roles, and permissions

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Author

- [Meo-ICAR](https://github.com/Meo-ICAR)

## Acknowledgments

- [Laravel](https://laravel.com)
- [AdminLTE](https://adminlte.io)
- [Bootstrap](https://getbootstrap.com)
