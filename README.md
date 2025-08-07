# Estimator – PHP Docker MVC App

This is an estimation application (planning poker) with JavaScript frontend and PHP backend (MVC architecture), running in Docker.

---

## 📦 Project Structure

- **index.php** – Single entry point (all requests go through it, MVC routing)
- **classes/**
  - **Core/** – MVC Framework (App, Router, Controller, Model, View, Database, Autoloader)
  - **Controllers/** – Controllers for API, login, main page
  - **Models/** – Models for User and Room
  - **Views/** – Views for login and main application
- **js/** – Modular JavaScript (app.js, login.js, modal.js)
- **css/** – Custom styles (Bootstrap 5 + Flatly theme)
- **session/** – JSON files for persisting users and votes per room
- **tests/unit/** – PHPUnit unit tests for models
- **Makefile** – Quick commands for development (docker up/down, tests)
- **docker-compose.yml, Dockerfile** – Docker infrastructure
- **composer.json** – Autoload and PHP dependencies (including PHPUnit)
- **phpunit.xml** – Configuration for running unit tests

---

## 🚀 Quick Start

1. **Build & start Docker:**
   ```sh
   make up
   ```
2. **Access the application:**
   - [http://localhost:8080](http://localhost:8080)

---

## 🧑‍💻 Main Features

- **Login/Room Creation** – User can create or join a room with admin or user role.
- **Fibonacci Voting** – Users choose a Fibonacci number, admin can "flip" to reveal votes.
- **Admin** – Can remove users, see statistics, has access to invitation link.
- **Live Updates** – 2-second polling for user/vote synchronization.
- **Logout** – Any user can log out, session is cleared.
- **Modern UI** – Bootstrap 5, Flatly theme, Bootstrap Icons.

---

## 🧪 Unit Tests

- Tests for RoomModel and UserModel with PHPUnit.
- Run tests with:
  ```sh
  make unit-tests
  ```
  or directly:
  ```sh
  docker-compose exec web vendor/bin/phpunit --testdox
  ```

---

## 🛠️ Useful Commands (Makefile)

- `make up` – starts Docker containers
- `make down` – stops Docker containers
- `make unit-tests` – runs unit tests

---

## ⚙️ Rewrite Configuration (Apache)

Make sure you have the `.htaccess` file in root:
```
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [QSA,L]
```

---

If you want to add additional instructions (e.g., frontend development, extensions, contributions), let me know!
