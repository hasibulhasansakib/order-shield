# Action & Filter Hooks

Order Shield provides several hooks to allow developers to modify its behavior without altering the core files.

*(This section will be expanded as more hooks are added to the core).*

## Filters
- `os_daily_order_limit`: Modify the daily order limit programmatically per user.
- `os_is_fake_phone`: Override the built-in fake phone detection logic.

## Actions
- `os_before_fraud_check`: Fires immediately before a checkout request is evaluated.
- `os_order_blocked`: Fires when an order is successfully blocked by Order Shield.