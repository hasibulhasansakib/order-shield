# Architecture Overview

Order Shield is built with modern PHP standards and an enterprise-grade architecture.

## Namespace & Autoloading
The plugin strictly follows PSR-4 autoloading via Composer. All classes are under the `\OrderShield` namespace and located in the `src/` directory.

## Core Components
- `Plugin.php`: The main singleton class that initializes the application.
- `Installer.php`: Handles plugin activation, deactivation, and database schema migrations.
- `EventLogger.php`: Manages insertion into the `os_fraud_logs` table.
- `RulesEngine.php`: Evaluates checkout data against the blocklist and daily limits.
- `DashboardController.php`: Provides the REST-like AJAX API for the admin interface.

## Database Tables
We utilize custom database tables rather than standard WP options/postmeta to ensure massive scalability (see `database.md`).