# Order Shield

<div align="center">
  <h3>A production-ready, open-source WooCommerce fraud prevention system.</h3>
  <p>Protect your eCommerce store from fake orders, spam customers, and fraudsters with real-time IP tracking and smart limitation rules.</p>
</div>

## 🚀 Features

* **Daily Order Limits:** Restrict the maximum number of successful orders a user can place in a 24-hour window.
* **Smart Tracking:** Automatically logs the exact IP address, Geolocation (City, Country, ISP), Phone, and Email for every checkout attempt.
* **Rules Engine:** Permanently block (Blacklist) or always allow (Whitelist) specific IPs, Phone Numbers, or Email Addresses.
* **Premium Dashboard:** A beautiful, real-time admin interface to monitor activity and manage your protection rules.
* **High Performance:** Utilizes custom, optimized database tables (`wp_os_fraud_logs`, `wp_os_rules`) ensuring zero impact on your site's speed, even with thousands of blocked attempts.
* **100% HPOS Compatible:** Fully supports WooCommerce High-Performance Order Storage.

## 🛠️ Installation

1. Download the latest version of the plugin as a `.zip` file.
2. Go to your WordPress Admin panel -> **Plugins** -> **Add New** -> **Upload Plugin**.
3. Upload the `.zip` file and click **Install Now**.
4. Click **Activate Plugin**. The required database tables will be created automatically.
5. Navigate to the new **Order Shield** menu in your sidebar to view the dashboard and configure your settings.

## ⚙️ Configuration

Head over to the **Settings** tab inside the Order Shield dashboard to configure:
- Maximum allowed orders per day.
- Custom error messages displayed to blocked users at the WooCommerce checkout.

## 🤝 Contribution

Contributions, issues, and feature requests are welcome! Feel free to check the [issues page](https://github.com/hasibulhasansakib/order-shield/issues).

## 📄 License

This project is licensed under the **GPL-3.0-or-later** License. 

---
**Developed with ❤️ by [Hasibul Hasan Sakib](https://github.com/hasibulhasansakib)**
