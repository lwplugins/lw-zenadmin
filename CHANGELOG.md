# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

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
