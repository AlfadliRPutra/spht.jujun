# SPHT Jujun - E-Commerce Platform

This is a Laravel-based E-Commerce platform specifically tailored for farmers (Petani), customers (Pelanggan), and administrators. The application allows farmers to sell their agricultural products directly to customers, complete with cart management, order processing, shipping calculations based on Indonesian regions (Wilayah), and payment integration using Midtrans.

## 🚀 Features

*   **Multi-Role Authentication:**
    *   **Admin:** Manages users, master data (categories, wilayah), and overall system monitoring.
    *   **Petani (Farmer):** Can manage products, view and process incoming orders, and manage store profiles.
    *   **Pelanggan (Customer):** Can browse the product catalog, add items to the cart, manage shipping addresses, and place orders.
*   **Product Catalog & Inventory:** Products with categories, sub-categories, weights, and stock management.
*   **Shopping Cart & Checkout:** Comprehensive checkout process.
*   **Shipping & Logistics:** Integrated with Indonesian region data (Provinsi, Kabupaten/Kota, Kecamatan) for address management and shipping configuration.
*   **Payment Gateway Integration:** Integrated with **Midtrans** for secure and automated payment processing (Supports 3DS).
*   **Responsive UI:** Built with modern web development tools (Tailwind CSS/Blade Components).

## 🛠️ Tech Stack

*   **Framework:** Laravel 11.x / 12.x
*   **PHP:** >= 8.2
*   **Database:** SQLite (Default for development) / MySQL / PostgreSQL
*   **Payment Gateway:** Midtrans PHP Client
*   **Frontend:** Blade Templates, Tailwind CSS, Vite

## 📋 Requirements

*   PHP >= 8.2
*   Composer
*   Node.js & NPM
*   Midtrans Account (for testing payments)

## ⚙️ Installation & Setup

1. **Clone the repository** (if applicable) or navigate to the project folder:
   ```bash
   cd spht.jujun
   ```

2. **Install PHP dependencies:**
   ```bash
   composer install
   ```

3. **Install NPM dependencies:**
   ```bash
   npm install
   ```

4. **Environment Setup:**
   Copy the example environment file and generate the application key.
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure Database & Midtrans (`.env` file):**
   Open the `.env` file and set up your database connection. By default, it uses SQLite. If you want to use MySQL, update `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.

   **Midtrans Configuration:**
   You must set up your Midtrans sandbox/production credentials to process payments.
   ```env
   MIDTRANS_SERVER_KEY=your-server-key-here
   MIDTRANS_CLIENT_KEY=your-client-key-here
   MIDTRANS_MERCHANT_ID=your-merchant-id-here
   MIDTRANS_IS_PRODUCTION=false
   MIDTRANS_IS_SANITIZED=true
   MIDTRANS_IS_3DS=true
   ```

6. **Run Database Migrations and Seeders:**
   This will create the necessary tables and populate the database with dummy data, including users, categories, wilayah (regions), and products.
   ```bash
   php artisan migrate --seed
   ```

7. **Link Storage:**
   Ensure public uploads (like product images, user avatars) are accessible.
   ```bash
   php artisan storage:link
   ```

## 💻 Running the Application

To run the application locally, you need to start both the Laravel development server and the Vite development server for frontend assets.

You can use the built-in development script which uses `concurrently` (if configured in `composer.json`):
```bash
composer run dev
```

**OR** run them in separate terminal windows:

Terminal 1 (Laravel Server):
```bash
php artisan serve
```

Terminal 2 (Vite Assets):
```bash
npm run dev
```

Terminal 3 (Queue Worker for Background Jobs/Emails):
```bash
php artisan queue:listen
```

The application will be accessible at `http://localhost:8000`.

## 🧪 Testing

To run the automated tests:
```bash
php artisan test
```

## 🔒 License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT). This project's specific licensing may vary.
