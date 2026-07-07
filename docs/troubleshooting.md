# Troubleshooting

## The Plugin Doesn't Activate
**Error: "Composer dependencies are missing."**
If you downloaded the plugin directly from the `main` branch rather than a Release zip, you must run `composer install` inside the plugin directory via SSH/Terminal.

## Location Data Not Showing
Order Shield uses `ip-api.com` to fetch location data. If your server is making too many requests, they may temporarily rate-limit your IP.

## Updates Are Failing
Order Shield connects to GitHub to download updates. Ensure your server allows outbound cURL requests to `api.github.com`.