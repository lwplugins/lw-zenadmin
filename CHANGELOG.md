# Changelog

## [1.4.0] - 2026-09-25

### Added
- Redesigned settings screen: side navigation, Save / Discard in the top bar with Ctrl/Cmd+S, unsaved-change tracking and a leave-page warning, loading skeletons, mobile layout
- Search field on the widget, menu and admin bar lists (ignores case and accents) and per-group "show all / hide all" actions that skip protected items
- REST API for the settings (`lw-zenadmin/v1/admin/settings`, administrators only) with validated, all-or-nothing saves

### Fixed
- Menu, admin bar and widget names no longer include count badges or hidden text (e.g. "Comments 0 comments awaiting moderation"); stored names clean themselves on the next admin page load
- Widgets with uppercase letters in their ID could never be shown again after saving; the exact ID is now stored
- Items you did not change keep their visibility on save in every list (previously only on the Admin Bar tab)

### Changed
- Requires WordPress 6.6: the new settings screen needs the `react-jsx-runtime` script core registers from 6.6
- The classic settings tabs are removed

## [1.3.5] - 2026-09-25

### Fixed
- Notices from themes and other plugins (for example a theme's purchase-code or recommended-plugins notice) could show on the LW ZenAdmin screen. They are now kept off every LW Plugins screen, whatever their markup.
- The Notices sidebar no longer collects notices on LW Plugins screens (it would have shown the ones kept off the page); it works as before everywhere else.

## [1.3.4] - 2026-09-22

### Fixed
- Admin bar items nested two or more levels deep (e.g. the account menu's user info and Log Out) are now listed on the Admin Bar tab, indented by level. Previously they had no checkbox, and saving the tab hid the unprotected ones with no way to restore them
- Saving the settings no longer hides an admin bar item that the submitted form did not show; it keeps its current visibility
- A numeric admin bar item ID no longer breaks the Admin Bar tab

## [1.3.3] - 2026-09-18

### Fixed
- Review-request banners that skip the standard `notice` / `updated` / `error` / `update-nag` classes (any `div` whose class contains `review-notice`, e.g. Reno Product Gallery's `sp-woogs-review-notice`) are now collected into the Notices panel and hidden early, instead of staying on the page

## [1.3.2] - 2026-09-06

### Fixed
- The release package and the Composer/Packagist dist no longer ship tests, docs or development configuration (`.gitattributes` export-ignore plus unified release excludes). A hosting malware scanner had flagged a unit-test fixture on a customer site

## [1.3.1] - 2026-08-20

### Fixed
- Added `uninstall.php`: deleting the plugin now removes every `lw_zenadmin_*` option (settings, discovered widgets / menus / admin-bar items). Previously they were left behind.

### Changed
- Tested up to WordPress 7.1.

## [1.3.0] - 2026-07-19

### Fixed
- Submenu items whose parent menu was missing from the discovered data were silently dropped from the settings screen, leaving them unmanageable. They are now listed under Third-party.

### Changed
- Minimum PHP requirement lowered from 8.1 to 8.0.

### Added
- Test suite (PHPUnit + Brain Monkey), PHPStan level 5 with an empty baseline, and a CI workflow running code style, static analysis and tests on PHP 8.1–8.5.

## [1.2.3] - 2026-03-22

### Added
- LW Site Manager integration - admin cleanup abilities for AI agents
- `lw-zenadmin/get-options` ability - get ZenAdmin settings
- `lw-zenadmin/set-options` ability - update settings
- `lw-zenadmin/list-widgets` ability - list dashboard widgets with visibility status

## [1.2.2]

### Fixed
- Smarter autoloader fallback - supports root Composer dependency installs

## [1.2.1]

### Fixed
- Graceful error when autoloader is missing (admin notice instead of fatal error)

## [1.2.0]

### Added
- Admin Bar Manager - hide/show admin bar items (LiteSpeed, WP Rocket, etc.)
- Auto-discovery of all registered admin bar nodes
- Grouped UI: WordPress Core, WooCommerce, Third-party
- Protected items (My Account, Logout) cannot be hidden
- Works on both admin pages and frontend
- Feature is disabled by default - opt-in via settings

## [1.1.2]

### Fixed
- Minor fix

## [1.1.1]

### Added
- Hash-based tab navigation on settings page
- Updated ParentPage with SVG icon support from registry

## [1.1.0]

### Added
- Admin Menu Manager - hide/show admin sidebar menu items
- Auto-discovery of all registered menus and submenus
- Grouped UI: WordPress Core, WooCommerce, LW Plugins, Third-party
- Protected menus (Dashboard, Settings, Plugins, LW Plugins) cannot be hidden
- Individual submenu control with parent grouping
- WP-CLI commands: `menu list`, `show`, `hide`, `show-all`, `hide-all`, `reset`
- Feature is disabled by default - opt-in via settings

## [1.0.4]

### Fixed
- Minor fix

## [1.0.3]

### Fixed
- Minor fix

## [1.0.2]

### Changed
- Updated Hungarian translation (hu_HU)
- Regenerated MO binary

## [1.0.1]

### Added
- GitHub Actions release workflow
- Hungarian translation (hu_HU)
- POT template file for translations

## [1.0.0]

### Added
- Initial release
- Notice Collector with sidebar panel
- Dashboard Widget Manager with auto-discovery
