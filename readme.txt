=== LW ZenAdmin ===
Contributors: lwplugins
Tags: admin, notices, dashboard, widgets, cleanup
Requires at least: 6.0
Tested up to: 6.7
Stable tag: 1.0.2
Requires PHP: 8.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Clean up your WordPress admin — collect notices into a sidebar panel and manage dashboard widgets.

== Description ==

LW ZenAdmin declutters your WordPress admin interface with two focused features:

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
