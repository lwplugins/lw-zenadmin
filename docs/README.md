# LW ZenAdmin

Clean up your WordPress admin — notices sidebar & dashboard widget manager.

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

Controls which admin sidebar menu items are visible.

- **Auto-discovery** — detects all registered top-level and submenu items
- **Protected menus** — core items (Dashboard, LW Plugins) cannot be hidden
- **Submenu support** — hide individual submenu items independently
- **Grouped display** — WordPress Core, WooCommerce, Third-party

### Admin Bar Manager

Controls which admin bar nodes are visible.

- **Auto-discovery** — detects all registered admin bar nodes
- **Protected nodes** — core items cannot be hidden
- **Settings UI** — checkbox table on the Admin Bar tab

### WP-CLI

Full CLI support for toggling features and managing widget visibility.

See [CLI.md](CLI.md) for the complete command reference.

## Installation

```bash
# Via Composer
composer require lwplugins/lw-zenadmin

# Manual
# Copy the lw-zenadmin folder to wp-content/plugins/
```

Activate the plugin, then go to **LW Plugins → ZenAdmin**.

## Settings

| Option | Default | Description |
|--------|---------|-------------|
| `notices_enabled` | `true` | Enable/disable the notice collector |
| `widgets_enabled` | `true` | Enable/disable the widget manager |
| `menu_enabled` | `false` | Enable/disable the admin menu manager |
| `adminbar_enabled` | `false` | Enable/disable the admin bar manager |

Widget visibility is stored separately (`lw_zenadmin_widget_settings`) and can be managed from the admin UI or CLI.

## Documentation

- [CLI Commands](CLI.md)
- [Hooks Reference](HOOKS.md)
