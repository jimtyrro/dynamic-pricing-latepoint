# Dynamic Pricing for LatePoint - Enhanced Edition

LatePoint add-on that allows dynamic service/booking prices based on customizable conditions. **Updated and improved for LatePoint 5.2.4+ compatibility with cart support, multiple services, and performance optimizations.**

[![Version](https://img.shields.io/badge/version-1.0.3.5-blue.svg)](https://github.com/YOUR_USERNAME/dynamic-pricing-latepoint/releases)
[![LatePoint](https://img.shields.io/badge/LatePoint-5.2.5+-green.svg)](https://latepoint.com/)
[![WordPress](https://img.shields.io/badge/WordPress-5.0+-blue.svg)](https://wordpress.org/)

---

## ✨ Features

### Core Functionality
- **Dynamic Pricing Rules** - Create unlimited pricing rules based on conditions
- **Date-Based Pricing** - Festival dates, special occasions, seasonal pricing
- **Percentage or Fixed Modifiers** - Flexible pricing adjustments
- **Service-Level & Booking-Level Modifiers** - Target specific calculation points
- **Cost Breakdown Display** - Transparent pricing shown to customers

### Enhanced for LatePoint 5.2.4+
- ✅ **Cart Support** - Full compatibility with LatePoint's cart system
- ✅ **Multiple Services** - Handle multiple services in a single booking
- ✅ **Correct Subtotal Calculation** - Base prices shown separately from modifiers
- ✅ **Service Name Prefixes** - Clear labeling for multi-service bookings
- ✅ **Backward Compatible** - Works with LatePoint 4.9.x and 5.2.4+

### Security & Performance (v1.0.3.5)
- 🔒 **XSS Protection** - Service names properly escaped to prevent XSS attacks
- ⚡ **Production Optimized** - Zero debug logging overhead when `WP_DEBUG=false`
- 📊 **72 Debug Statements Optimized** - Conditional logging for development only

---

## 🆕 What's New in v1.0.3.5

### Security Enhancements
- **Fixed XSS vulnerability** in service name output
- Added `esc_html()` escaping for all user-generated content in cost breakdown

### Performance Improvements
- **100% reduction** in debug logging overhead on production sites
- All 72 `error_log()` statements now wrapped with `WP_DEBUG` checks
- Significant performance improvement for high-traffic sites

### Compatibility
- Tested with **LatePoint 5.2.5**
- Confirmed working with WordPress 6.x

---

## 📥 Installation

### Requirements
- WordPress 5.0 or higher
- LatePoint 5.2.4+ (or 4.9.x for backward compatibility)
- PHP 7.4 or higher

### Steps

1. **Download** the plugin
   ```bash
   git clone https://github.com/YOUR_USERNAME/dynamic-pricing-latepoint.git
   ```

2. **Upload** to your WordPress installation
   ```
   wp-content/plugins/dynamic-pricing-latepoint/
   ```

3. **Activate** the plugin through WordPress admin

4. **Configure** your pricing rules in LatePoint → Dynamic Pricing

---

## 📝 Changelog

### Version 1.0.3.5 (December 2025)
- ✅ Fixed XSS vulnerability in service name output
- ✅ Wrapped 72 debug statements with WP_DEBUG checks
- ✅ Zero logging overhead in production
- ✅ Tested with LatePoint 5.2.5

### Version 1.0.3.4 (December 2025)
- ✅ Fixed subtotal calculation
- ✅ Support for multiple services in cart
- ✅ Service name prefixes for clarity

### Version 1.0.3.3 (December 2025)
- ✅ Fixed 500 error with cart/booking type mismatch
- ✅ Added object type detection

### Version 1.0.3.2 (December 2025)
- ✅ Added cart hook support
- ✅ Cost breakdown displays correctly

### Version 1.0.3.1 (December 2025)
- ✅ Fixed hook registration for LatePoint 5.2.4+
- ✅ Removed deprecated code

---

## 👏 Credits

### Original Author
**TechXela** - [CodeCanyon](https://codecanyon.net/user/tech-xela) | [Website](http://techxela.com)

### LatePoint 5.2.4+ Compatibility Update
**Dmitriy Salynin** - [5notedesign.com](https://5notedesign.com)

---

## 📄 License

Licensed under the [CodeCanyon Regular License](https://codecanyon.net/licenses/standard). You must have a valid license to use this plugin.

---

## 🐛 Support

- **GitHub Issues:** [Report bugs](https://github.com/YOUR_USERNAME/dynamic-pricing-latepoint/issues)
- **Documentation:** See `/dev/` folder for technical docs
- **Original Support:** [TechXela](http://techxela.com)

---

**Version 1.0.3.5** | **Last Updated: December 2025** | **Status: ✅ Production Ready**
