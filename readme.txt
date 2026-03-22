=== LW ZenAdmin ===
Contributors: lwplugins
Tags: admin, notices, dashboard, widgets, cleanup
Requires at least: 6.0
Tested up to: 6.7
Stable tag: 1.2.3
Requires PHP: 8.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Clean up your WordPress admin — notices sidebar, dashboard widgets, admin menu, and admin bar manager.

== Description ==

LW ZenAdmin declutters your WordPress admin interface with four focused features:

= Notice Collector =

All admin notices are collected into a clean sidebar panel accessible from the admin bar. No more visual clutter breaking your admin layout.

* Notices badge in the admin bar with live count
* Slide-in sidebar panel with all collected notices
* Flash-free — notices are hidden before they render
* Supports dynamically added notices (plugin updates, etc.)

= Dashboard Widget Manager =

Control which widgets appear on your WordPress dashboard. Core and WooCommerce widgets are enabled by default, third-party widgets are hidden until you enable them.

* Auto-discovers all registered dashboard widgets
* Grouped by source: WordPress Core, WooCommerce, Third-party
* Simple checkbox interface to show/hide widgets
* Sensible defaults — no setup required

= Admin Bar Manager =

Control which items appear in the WordPress admin bar. Hide clutter from cache plugins, SEO tools, and other plugins that add unnecessary toolbar items.

* Auto-discovers all registered admin bar nodes
* Grouped by source: WordPress Core, WooCommerce, Third-party
* Protected items (My Account, Logout) cannot be hidden — prevents lockouts
* Works on both admin pages and frontend
* Disabled by default — opt-in via settings

== Installation ==

1. Upload the `lw-zenadmin` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu
3. Configure under LW Plugins > ZenAdmin

Or install via Composer:

`composer require lwplugins/lw-zenadmin`

= Admin Menu Manager =

Control which menu items appear in the admin sidebar. Hide unused menus to keep things clean while protected items (Dashboard, Settings, Plugins, LW Plugins) stay visible.

* Auto-discovers all registered admin menus and submenus
* Grouped by source: WordPress Core, WooCommerce, LW Plugins, Third-party
* Protected menus cannot be hidden — prevents lockouts
* Individual submenu control with parent grouping
* WP-CLI support: `wp lw-zenadmin menu list/show/hide/reset`

== Frequently Asked Questions ==

= Does this hide important notices? =

No. All notices are still accessible via the admin bar button. They are simply moved from the page into a sidebar panel.

= What happens to inline notices? =

Inline notices (`.inline`, `.below-h2`) are left in place as WordPress intended.

= Can I disable just one feature? =

Yes. You can independently enable or disable the Notice Collector and the Widget Manager from the settings page.

== Screenshots ==

1. Admin bar with Notices button and badge count
2. Sidebar panel showing collected notices
3. Dashboard Widget Manager settings

== Changelog ==

= 1.2.3 =
* New: LW Site Manager integration - admin cleanup abilities for AI agents
* New: lw-zenadmin/get-options - get ZenAdmin settings
* New: lw-zenadmin/set-options - update settings
* New: lw-zenadmin/list-widgets - list dashboard widgets with visibility status

= 1.2.2 =
* Fix: Smarter autoloader fallback - supports root Composer dependency installs

= 1.2.1 =
* Fix: Graceful error when autoloader is missing (admin notice instead of fatal error)

= 1.2.0 =
* New: Admin Bar Manager — hide/show admin bar items (LiteSpeed, WP Rocket, etc.)
* Auto-discovery of all registered admin bar nodes
* Grouped UI: WordPress Core, WooCommerce, Third-party
* Protected items (My Account, Logout) cannot be hidden
* Works on both admin pages and frontend
* Feature is disabled by default — opt-in via settings

= 1.1.2 =
* Minor fix

= 1.1.1 =
* Hash-based tab navigation on settings page
* Updated ParentPage with SVG icon support from registry

= 1.1.0 =
* Add Admin Menu Manager — hide/show admin sidebar menu items
* Auto-discovery of all registered menus and submenus
* Grouped UI: WordPress Core, WooCommerce, LW Plugins, Third-party
* Protected menus (Dashboard, Settings, Plugins, LW Plugins) cannot be hidden
* WP-CLI commands: menu list, show, hide, show-all, hide-all, reset
* Feature is disabled by default — opt-in via settings

= 1.0.4 =
* Minor fix

= 1.0.3 =
* Minor fix

= 1.0.2 =
* Update Hungarian translation (hu_HU)
* Regenerate MO binary

= 1.0.1 =
* Add GitHub Actions release workflow
* Add Hungarian translation (hu_HU)
* Add POT template file for translations

= 1.0.0 =
* Initial release
* Notice Collector with sidebar panel
* Dashboard Widget Manager with auto-discovery

== Upgrade Notice ==

= 1.0.0 =
Initial release.
