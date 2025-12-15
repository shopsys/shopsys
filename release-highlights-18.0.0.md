# Release Highlights 18.0.0

Shopsys Platform 18.0.0 brings a comprehensive administration overhaul, powerful new promotional tools, enhanced security features, and significant developer experience improvements. This release focuses on modernizing the platform while adding essential e-commerce capabilities that help merchants drive sales and ensure regulatory compliance.

## Feature Enhancements

### Administration Overhaul ([#3813](https://github.com/shopsys/shopsys/pull/3813))

The entire administration interface has been completely redesigned and modernized. The legacy custom-built theme has been replaced with the [Tabler UI](https://tabler.io/) framework, delivering a cleaner, more intuitive, and fully responsive interface across all devices.

**Key improvements:**
- Modern, responsive design that works seamlessly on desktop and mobile
- Migrated from legacy LESS to modern SCSS
- Replaced outdated jQuery plugins with modern libraries (tom-select, coloris)
- Improved form rendering with better input symbols, currency indicators, and recommended length counters
- New modal windows with status icons and copy-to-clipboard functionality
- Reduced codebase by approximately 12,000 lines of legacy code

This is a major release with significant changes for any project with admin customizations. Custom admin themes, CSS overrides, and JavaScript customizations will require updates to target the new Tabler-based DOM structure.

### Product Gift ([#4193](https://github.com/shopsys/shopsys/pull/4193))

Merchants can now create gift campaigns where customers receive promotional products at special prices when purchasing qualifying items. Administrators can configure gift plans with specific validity periods, assign gift products to qualifying products, and set special gift pricing per domain.

The feature includes automatic flag management (products are automatically tagged as "Gift with Product"), real-time cart updates, and seamless integration with the checkout flow. Gift items appear in order summaries, confirmation emails, and order details.

### Promotion X + Y for Free ([#4194](https://github.com/shopsys/shopsys/pull/4194))

A powerful new promotional feature allows merchants to set up volume-based promotions directly on products. For example, a "3 + 2 free" promotion means customers pay for 3 items and get 2 additional items for free.

Products with active promotions are automatically flagged, making them easily identifiable to customers. The feature supports multi-domain configurations and processes flag updates asynchronously for optimal performance. This common retail strategy helps merchants drive volume sales and increase average order values.

### QR Payment for Bank Transfers ([#4195](https://github.com/shopsys/shopsys/pull/4195))

A new Bank Transfer payment method with QR code support has been added. When customers select bank transfer, they receive payment instructions with an embedded QR code containing all necessary payment details (IBAN, amount, variable symbol).

Administrators can configure bank account details (account number, IBAN, BIC/SWIFT) per domain and customize payment instructions using dynamic placeholders. QR codes are embedded directly in confirmation emails and order confirmation pages, making it easy for customers to complete payments using their banking app.

### Order Withdrawal from Contract ([#4246](https://github.com/shopsys/shopsys/pull/4246))

This feature implements the Right of Withdrawal (cooling-off period) required by consumer protection laws in the European Union and many other jurisdictions. Customers can now submit withdrawal requests for eligible orders directly from their order detail page.

Administrators can configure the withdrawal deadline (typically 14 days after delivery) and customize withdrawal instructions per domain. The system tracks delivery dates, validates eligibility, and maintains a complete audit trail. A new "Withdrawn" order status has been added, and both customers and administrators receive email notifications when withdrawal requests are submitted.

### Autocomplete Favorites ([#4215](https://github.com/shopsys/shopsys/pull/4215))

Administrators can now configure favorite products, brands, and categories that appear when users focus on the search autocomplete input before typing. This helps guide customers toward promoted or popular items.

The feature also improves search behavior for short queries (1-2 characters) by performing a simplified name-based search instead of full-text search, providing faster and more relevant results.

### Domain Configuration with Path Fragment ([#4113](https://github.com/shopsys/shopsys/pull/4113))

Multi-domain setups can now use path-based URL structures instead of subdomains. For example, you can configure `example.com/cz` for Czech and `example.com/sk` for Slovak instead of using separate subdomains.

This enables:
- Single SSL certificate for all locales
- Simplified DNS management
- Consolidated domain authority for SEO
- Complete data isolation between domains (cookies, localStorage, sessions)

The feature is particularly valuable for multi-locale deployments, regional variants, and B2B multi-tenant setups.

### Prevent Exceeding Available Stock ([#4173](https://github.com/shopsys/shopsys/pull/4173))

A new "Allow negative stock" option on products gives merchants control over whether customers can order more items than are currently in stock. When disabled, the system automatically adjusts cart quantities to match available stock and informs customers about the changes.

This prevents overselling for products where stock accuracy is critical, such as limited inventory, perishable goods, or exclusive items.

## Security Enhancements

### GraphQL POST-Only Requests ([#4236](https://github.com/shopsys/shopsys/pull/4236))

All GraphQL queries are now sent exclusively via POST requests. This change prevents sensitive data exposure in URL parameters, reduces CSRF attack surface, prevents cache poisoning issues, and aligns with GraphQL security best practices.

### CRUD Access Control Implementation ([#4250](https://github.com/shopsys/shopsys/pull/4250))

A comprehensive Role-Based Access Control (RBAC) system has been implemented for CRUD controllers. Permissions are now automatically enforced based on enabled actions, eliminating the risk of unsecured routes. Each CRUD action type (LIST, CREATE, EDIT, DELETE, DETAIL) maps to corresponding permissions, and roles are automatically generated if not manually configured.

## Developer Experience

### Symfony Clock Integration ([#4297](https://github.com/shopsys/shopsys/pull/4297))

Direct usage of `DateTime` and `DateTimeImmutable` has been replaced with Symfony Clock throughout the codebase. This enables:
- Deterministic testing by controlling what "now" means in tests
- Elimination of flaky tests that depend on execution time
- Consistent time handling patterns across the codebase
- Clear guidelines: use `$this->clock->now()` in services, `new DatePoint()` in entities/tests

### DataSource Factories with Collation Support ([#4135](https://github.com/shopsys/shopsys/pull/4135))

All DataSource implementations now use the factory pattern, improving extensibility and dependency injection. More importantly, QueryBuilderDataSources now automatically apply locale-specific collation to textual columns, ensuring grid data is sorted correctly according to the administrator's language settings. Czech characters (č, ř, š, ž) now sort correctly when a Czech administrator is logged in.

### Asynchronous Order Email Preparation ([#4266](https://github.com/shopsys/shopsys/pull/4266))

Email preparation for new orders is now fully asynchronous. Previously, while email sending was async, the preparation step (fetching templates, replacing variables) was synchronous and could slow down order placement. This change removes email preparation from the critical checkout path, resulting in faster order creation and reduced risk of failures.

### CsrfProtection Attribute ([#4263](https://github.com/shopsys/shopsys/pull/4263))

The legacy `@CsrfProtection` Doctrine annotation has been replaced with a modern PHP 8 `#[CsrfProtection]` attribute. This provides cleaner syntax, better IDE support, and improved performance through in-memory caching of protection checks.

### Reduced Product Detail Data Transfer ([#4324](https://github.com/shopsys/shopsys/pull/4324))

GraphQL queries for product detail pages now return only necessary store availability data instead of full store information. This can reduce payload size by 70-80% for store availability data, resulting in faster API responses and improved mobile performance.

## Design & Appearance

- **Order and Complaint Status Colors** ([#4235](https://github.com/shopsys/shopsys/pull/4235)) - Statuses are now color-coded for better visual identification
- **Product Images in Orders** ([#4213](https://github.com/shopsys/shopsys/pull/4213)) - Order lists now display product thumbnails
- **CKEditor Text Styles** ([#4208](https://github.com/shopsys/shopsys/pull/4208)) - Added WYSIWYG text styles for richer content editing
- **Redesigned Contact Information** ([#4221](https://github.com/shopsys/shopsys/pull/4221)) - Improved contact information layout on storefront
- **Reset Password Layout** ([#4254](https://github.com/shopsys/shopsys/pull/4254)) - Updated password reset page design

## Infrastructure

- **Node.js 24** ([#4281](https://github.com/shopsys/shopsys/pull/4281)) - Backend Node.js updated from version 20 to current LTS 24
- **RabbitMQ Upgrade** ([#4167](https://github.com/shopsys/shopsys/pull/4167)) - Updated RabbitMQ version
- **Doctrine Bundle 2.18** ([#4226](https://github.com/shopsys/shopsys/pull/4226)) - Required doctrine-bundle ^2.18

## Conclusion

Shopsys Platform 18.0.0 represents a major step forward in both merchant capabilities and developer experience. The administration overhaul modernizes the entire backend interface, while new promotional tools (Product Gifts, X+Y promotions, QR payments) give merchants powerful ways to drive sales. The Order Withdrawal feature ensures EU regulatory compliance, and numerous developer experience improvements make the platform more maintainable and testable.

For a complete list of all changes, visit the [full changelog on GitHub](https://github.com/shopsys/shopsys/releases/tag/v18.0.0).

### Resources

- [GitHub Release](https://github.com/shopsys/shopsys/releases/tag/v18.0.0)
- [Upgrade Guide](https://github.com/shopsys/shopsys/blob/18.0/upgrade/UPGRADE-v18.0.0.md)
- [Documentation](https://docs.shopsys.com)
- [Community Forum](https://github.com/shopsys/shopsys/discussions)
