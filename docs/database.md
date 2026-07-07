# Database Schema

To ensure WooCommerce scalability, Order Shield bypasses `wp_postmeta` and uses highly optimized custom tables.

## \`wp_os_fraud_logs\`
Stores every checkout attempt (both successful and blocked).
- `id` (BIGINT)
- `ip_address` (VARCHAR)
- `phone_number` (VARCHAR)
- `email_address` (VARCHAR)
- `status` (VARCHAR): 'success' or 'blocked'
- `reason` (TEXT)
- `city`, `region`, `country`, `zip`, `lat`, `lon`, `isp` (Geographic Data)
- `created_at` (DATETIME)

## \`wp_os_rules\`
Stores the global blocklist.
- `id` (BIGINT)
- `rule_type` (VARCHAR): 'blacklist' or 'whitelist'
- `target_type` (VARCHAR): 'ip', 'phone', or 'email'
- `target_value` (VARCHAR)
- `reason` (TEXT)