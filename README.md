# Collaborative Wiki Application

This is a fully functional, collaborative wiki application built with PHP, designed to be open-source and highly customizable. It features WYSIWYG editing, versioning, categories, auto-indexing, and robust security measures.

## Features

*   **User Authentication:** Secure registration, login, and logout functionalities.
*   **Page Management (CRUD):** Create, view, edit, and update wiki pages.
*   **Versioning:** Automatic creation of new page versions upon each update, allowing for historical tracking of content changes.
*   **Categories:** Management of categories and assignment of multiple categories to wiki pages for better organization.
*   **Auto-Indexing & Search:** A search function to find pages and categories by keywords in titles and content.
*   **WYSIWYG Editing:** Integrated CKEditor 5 provides rich text editing capabilities.
*   **Markdown Support:** Pages can be saved and rendered using Markdown syntax, processed by Parsedown.
*   **Raw HTML Editing:** The editor allows for direct raw HTML input.
*   **@ Mention Tag:** Within the editor, typing `@` brings up a popup to link to other wiki pages.
*   **Security:**
    *   Password hashing (using `password_hash`).
    *   HTML sanitization (using HTMLPurifier) to prevent XSS attacks.
    *   CSRF protection for all POST requests.
*   **Configuration:** Environment variables managed via `.env` file (`vlucas/phpdotenv`).
*   **Database Management:** Utilizes `paigejulianne/picoorm` for database interactions.
*   **Admin Settings:** System administrators can update basic site settings like site name and logo URL.
Copyright (c) 2025 HomeRun AI Technologies. Released under the MIT License.

## Setup Instructions

Follow these steps to get the wiki application running on your local server.

### 1. Prerequisites

*   PHP 8.0 or higher
*   Composer (PHP dependency manager)
*   MySQL or MariaDB database server
*   A web server (Apache with mod_rewrite enabled, or Nginx)

### 2. Clone the Repository

Assuming you have this project already cloned or downloaded to `/home/paige/public_html/wiki/`.

### 3. Install PHP Dependencies

Navigate to the project root directory in your terminal and install Composer dependencies:

```bash
composer install
```

### 4. Configure Environment Variables

Copy the example environment file and then edit it with your database credentials and application URL.

```bash
cp .env.example .env
```

Open the `.env` file and update the following variables:

```dotenv
# Application Configuration
APP_URL=http://localhost/~paige/wiki # IMPORTANT: Set this to your actual application URL
APP_ENV=development # Change to 'production' for production environments

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=wiki
DB_USERNAME=root # Your database username
DB_PASSWORD=Katie1218Heather911 # Your database password
```

**Important:** Ensure the `DB_DATABASE` (e.g., `wiki`) exists on your MySQL/MariaDB server and that `DB_USERNAME` has full privileges on this database.

### 5. Run Database Migrations

Execute the migration script to create all necessary tables in your database:

```bash
php migrate.php
```

If you encounter an "Access denied" error, verify your `DB_USERNAME` and `DB_PASSWORD` in `.env` and ensure the user has privileges. If you get an "Unknown database" error, ensure the database name (`wiki` in this example) exists on your server. You might need to create it manually:

```bash
mysql -u your_db_user -p -e "CREATE DATABASE IF NOT EXISTS wiki;"
```

### 6. Web Server Configuration (Apache Example)

Ensure your web server points to the `public/` directory.

**Apache (.htaccess):** Make sure `mod_rewrite` is enabled. The `public/.htaccess` file should handle routing.

**Nginx:** You'll need a server block configuration similar to this:

```nginx
server {
    listen 80;
    server_name yourdomain.com; # Replace with your domain or IP

    root /home/paige/public_html/wiki/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.x-fpm.sock; # Adjust PHP-FPM socket path
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Deny access to .env and other sensitive files
    location ~ /\.env {
        deny all;
    }
}
```

### 7. Access the Application

Once your web server is configured, open your web browser and navigate to your application's URL (e.g., `http://localhost/~paige/wiki`).

## Usage

*   **Register:** Navigate to `/register` to create a new user account.
*   **Login:** Navigate to `/login` to access the wiki.
*   **Create Pages:** If you navigate to a non-existent page (e.g., `http://localhost/~paige/wiki/my-new-page`), logged-in users will be prompted to create it.
*   **Edit Pages:** When viewing a page, logged-in users will see an "Edit this page" link.
*   **Categories:** Access `/categories` to view and manage categories (admin role required to create/edit).
*   **Search:** Use the search bar in the header to find content.
*   **Settings:** Access `/settings` to manage site name and logo URL (admin role required).

Enjoy your collaborative wiki!