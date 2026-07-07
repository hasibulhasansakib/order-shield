# Developer Guide

Order Shield is highly extensible.

## Modifying the Plugin
1. We use vanilla CSS and JS for the admin dashboard to keep the plugin lightweight and dependency-free.
2. Admin scripts are located in `assets/js/admin.js` and styles in `assets/css/admin.css`.
3. If you modify core PHP files, remember to dump the composer autoload: `composer dump-autoload`.

## Code Standards
Please ensure your code is compatible with PHP 7.4+ and uses strict typing (`declare(strict_types=1);`).

For a list of available action and filter hooks, please see `hooks.md`.