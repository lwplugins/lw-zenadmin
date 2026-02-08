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

Widget visibility is stored separately (`lw_zenadmin_widget_settings`) and can be managed from the admin UI or CLI.

## Documentation

- [CLI Commands](CLI.md)
- [Hooks Reference](HOOKS.md)
