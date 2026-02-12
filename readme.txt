=== LW ZenAdmin ===
Contributors: lwplugins
Tags: admin, notices, dashboard, widgets, cleanup
Requires at least: 6.0
Tested up to: 6.7
Stable tag: 1.1.1
Requires PHP: 8.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Clean up your WordPress admin — notices sidebar, dashboard widget manager, and admin menu manager.

== Description ==

LW ZenAdmin declutters your WordPress admin interface with three focused features:

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
