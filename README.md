# Estimator – PHP Docker MVC App

This is an estimation application (planning poker) with JavaScript frontend and PHP backend (MVC architecture), running in Docker with SSL support and real-time WebSocket updates. The app is available at https://www.estimatorapp.site.

---

## 📦 Project Structure

- **index.php** – Single entry point (all requests go through it, MVC routing)
- **websocket-server.php** – WebSocket server entry point
- **classes/**
  - **Core/** – MVC Framework (App, Router, Controller, Model, View, Database, Autoloader, WebSocketServer)
  - **Controllers/** – Controllers for API, login, main page
  - **Models/** – Models for User and Room
  - **Views/** – Views for login and main application
- **js/** – Modular JavaScript (app.js, login.js, modal.js) with WebSocket support
- **css/** – Custom styles (Bootstrap 5 + Flatly theme)
- **session/** – JSON files for persisting users and votes per room
- **tests/unit/** – PHPUnit unit tests for models
- **ssl/** – SSL certificates (generated automatically)
- **Makefile** – Quick commands for development (docker up/down, tests, SSL)
- **docker-compose.yml, Dockerfile** – Docker infrastructure with SSL and WebSocket support
- **composer.json** – Autoload and PHP dependencies (including PHPUnit and Ratchet WebSocket)
- **phpunit.xml** – Configuration for running unit tests

---

## 🚀 Quick Start

### Option 1: HTTP Only (Port 80)
```sh
make up
```
Access: [http://www.estimatorapp.site](http://www.estimatorapp.site)

### Option 2: HTTPS with SSL (Port 80 + 443)
```sh
make setup-ssl
```
Access: [https://www.estimatorapp.site](https://www.estimatorapp.site) (auto-redirects from HTTP)

---

## 🔒 SSL Setup

The application supports both HTTP and HTTPS:

### Generate SSL Certificates
```sh
make ssl
```

### Start with SSL
```sh
make setup-ssl
```

### Manual SSL Setup
```sh
# Generate certificates
./generate-ssl.sh

# Start application
docker compose up -d
```

**Note**: Self-signed certificates are used for development. For production, use certificates from a trusted CA.

---

## 🧑‍💻 Main Features

- **Login/Room Creation** – User can create or join a room with admin or user role.
- **Fibonacci Voting** – Users choose a Fibonacci number, admin can "flip" to reveal votes.
- **Admin** – Can remove users, see statistics, has access to invitation link.
- **Real-time Updates** – WebSocket-based live updates for instant synchronization.
- **Fallback System** – Automatic fallback to polling if WebSocket fails.
- **Logout** – Any user can log out, session is cleared.
- **Modern UI** – Bootstrap 5, Flatly theme, Bootstrap Icons.
- **SSL Support** – Secure HTTPS connections with automatic HTTP to HTTPS redirect.

---

## 🔌 WebSocket Architecture

The application uses WebSocket for real-time updates:

### Frontend (JavaScript)
- **WebSocket Connection** – Automatic connection to `ws://host:8080`
- **Real-time Updates** – Instant vote updates, user joins/leaves, flip/reset
- **Auto-reconnect** – Automatic reconnection with exponential backoff
- **Fallback** – Falls back to polling if WebSocket fails

### Backend (PHP)
- **WebSocketServer** – `classes/Core/WebSocketServer.php`
- **Ratchet Framework** – WebSocket server implementation
- **Room Management** – Per-room WebSocket connections
- **Message Types** – votes_update, user_joined, user_left, vote_revealed, vote_reset

### Message Types
- `votes_update` – Vote changes broadcasted to all users
- `user_joined` – New user joined the room
- `user_left` – User left the room
- `vote_revealed` – Admin revealed votes
- `vote_reset` – Admin reset votes
- `user_removed` – User was removed by admin

---

## 🧪 Unit Tests

- Tests for RoomModel and UserModel with PHPUnit.
- Run tests with:
  ```sh
  make unit-tests
  ```
  or directly:
  ```sh
  docker compose exec web vendor/bin/phpunit --testdox
  ```

---

## 🛠️ Useful Commands (Makefile)

- `make up` – starts Docker containers (HTTP only)
- `make down` – stops Docker containers
- `make ssl` – generates SSL certificates
- `make setup-ssl` – generates SSL certificates and starts containers
- `make unit-tests` – runs unit tests
- `make logs` – view application logs
- `make rebuild` – rebuild containers from scratch

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

## 🔐 Security Features

- **SSL/TLS Support** – Encrypted HTTPS connections
- **Security Headers** – HSTS, X-Frame-Options, X-Content-Type-Options
- **Automatic Redirect** – HTTP to HTTPS redirect
- **Session Security** – Secure session handling

---

If you want to add additional instructions (e.g., frontend development, extensions, contributions), let me know!
