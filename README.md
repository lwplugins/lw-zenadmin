# Lightweight ZenAdmin

Clean up your WordPress admin — notices sidebar, dashboard widget manager, and admin menu manager.

[![PHP](https://img.shields.io/badge/PHP-8.1%2B-blue.svg)](https://php.net)
[![WordPress](https://img.shields.io/badge/WordPress-6.0%2B-blue.svg)](https://wordpress.org)
[![License](https://img.shields.io/badge/License-GPL--2.0-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

![LW ZenAdmin Settings](.github/screenshot.png)

## Before & After

| Before | After |
|--------|-------|
| ![Before — cluttered admin notices](.github/assets/before.webp) | ![After — clean admin with sidebar panel](.github/assets/after.webp) |

## Features

### Notice Collector

Collects all admin notices into a slide-in sidebar panel accessible from the admin bar.

- **Admin bar button** — "Notices" label with a live badge count
- **Sidebar panel** — slides in from the right with all collected notices
- **Flash-free** — early CSS hides notices before JS loads
- **Dynamic** — picks up notices generated during plugin updates/installs
- **Multiple close methods** — Escape key, overlay click, or X button

### Dashboard Widget Manager

Controls which widgets appear on the WordPress dashboard, grouped by source.

- **Auto-discovery** — detects all registered dashboard widgets automatically
- **Grouped display** — WordPress Core, WooCommerce, Third-party
- **Sensible defaults** — Core + WooCommerce visible, Third-party hidden
- **Checkbox UI** — simple table interface on the settings page

### Admin Menu Manager

The WordPress admin sidebar is packed with menu items — most of which are rarely needed after the initial site setup. Media, Comments, Tools, and various plugin menus just add clutter and hurt usability, especially for non-technical users. The Admin Menu Manager lets you decide which menu items stay visible.

- **Auto-discovery** — automatically detects all registered menus and submenus
- **Grouped display** — WordPress Core, WooCommerce, LW Plugins, Third-party
- **Protected menus** — Dashboard, Settings, Plugins, and LW Plugins can never be hidden, preventing lockouts
- **Submenu-level control** — manage individual submenu items, not just top-level menus
- **Disabled by default** — only activates when you enable it in the settings

### WP-CLI

Full CLI support for toggling features, managing widgets and menus.

```bash
# Feature management
wp lw-zenadmin status
wp lw-zenadmin enable notices_enabled
wp lw-zenadmin disable widgets_enabled

# Widget management
wp lw-zenadmin widget list
wp lw-zenadmin widget show dashboard_quick_press
wp lw-zenadmin widget hide dashboard_primary
wp lw-zenadmin widget show-all
wp lw-zenadmin widget hide-all
wp lw-zenadmin widget reset

# Menu management
wp lw-zenadmin menu list
wp lw-zenadmin menu show tools.php
wp lw-zenadmin menu hide edit-comments.php
wp lw-zenadmin menu show-all
wp lw-zenadmin menu hide-all
wp lw-zenadmin menu reset
```

See [docs/CLI.md](docs/CLI.md) for the complete command reference.

## Installation

```bash
composer require lwplugins/lw-zenadmin
```

Or download and upload to `/wp-content/plugins/`.

## Usage

1. Go to **LW Plugins → ZenAdmin**
2. Toggle Notice Collector, Widget Manager and Menu Manager on/off
3. Configure widget visibility in the **Widgets** tab
4. Configure menu visibility in the **Menus** tab

## Settings

| Option | Default | Description |
|--------|---------|-------------|
| `notices_enabled` | `true` | Enable/disable the notice collector |
| `widgets_enabled` | `true` | Enable/disable the widget manager |
| `menu_enabled` | `false` | Enable/disable the admin menu manager |

Widget and menu visibility are stored separately and can be managed from the admin UI or CLI.

## Documentation

- [CLI Commands](docs/CLI.md)
- [Hooks Reference](docs/HOOKS.md)

## Development

```bash
# Install dependencies
composer install

# Run code sniffer
composer phpcs

# Fix coding standards
composer phpcbf
```

## Links

- [GitHub](https://github.com/lwplugins/lw-zenadmin)
- [LW Plugins](https://lwplugins.com)

## License

GPL-2.0-or-later
