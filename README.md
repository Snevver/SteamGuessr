# 🎮 SteamGuessr

SteamGuessr is a web application that allows the user to play a variety of mini-games based on guessing Steam games from their own library.

**❗ Make sure to read the [best practices and naming conventions document](docs/best-practices.md) before working on this project!**

## 👟 Quick Start

### 1. Prerequisites

Before you begin, ensure you have the following installed:

-   **PHP** >= 8.1
-   **Composer** - [Download here](https://getcomposer.org/)
-   **Node.js** >= 18.x & npm - [Download here](https://nodejs.org/)
-   **Steam API Key** - [Get one here](https://steamcommunity.com/dev/apikey)

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install
```

### 3. Environment Setup

```bash
# Copy the environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Steam API Key Configuration

Edit your `.env` file with your Steam API key:

```env
# Add your Steam API key
STEAM_API_KEY=your_steam_api_key_here
```

### 5. Start Development Servers

You need to run **two terminal windows** simultaneously:

**Terminal 1 - Laravel Backend:**

```bash
php artisan serve
```

This will start the server at `http://127.0.0.1:8000/`

**Terminal 2 - Vite Dev Server (React):**

```bash
npm run dev
```

This compiles your React components in real-time.

### 6. Open Your Browser

Navigate to: **http://127.0.0.1:8000/**

## 🛠️ Tech Stack

-   **Backend:** Laravel 10.x
-   **Frontend:** React 18
-   **Bridge:** Inertia.js
-   **Styling:** Tailwind CSS
-   **Build Tool:** Vite
-   **API:** Steam Web API

## 📦 Production Build

To build for production:

```bash
# Build frontend assets
npm run build
```

## 📝 License

This project is licensed under the MIT License.

## 👤 Authors

Backend developer: [Sven Hoeksema](https://github.com/Snevver)

Frontend developer: [Son Bram van der Burg](https://github.com/Penguin-09)

Logo designer: [Arwen Bakhuisen](https://www.instagram.com/catsw1t?igsh=eGFtZzk2ZWh6aG9k)
