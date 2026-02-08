# Hooks Reference

WordPress hooks used by the plugin.

## Actions

| Hook | Priority | Class | Purpose |
|------|----------|-------|---------|
| `plugins_loaded` | 10 | `lw-zenadmin.php` | Plugin initialization |
| `init` | 10 | `Plugin` | Load textdomain |
| `admin_menu` | 10 | `SettingsPage` | Register menu page |
| `admin_init` | 10 | `SettingsSaver` | Handle form submission |
| `admin_enqueue_scripts` | 10 | `SettingsPage` | Enqueue admin CSS/JS (settings page only) |
| `admin_enqueue_scripts` | 10 | `NoticeCollector` | Enqueue panel CSS/JS (all admin pages) |
| `admin_bar_menu` | 999 | `NoticeCollector` | Add "Notices" button to admin bar |
| `admin_head` | -9999 | `NoticeCollector` | Early CSS to hide notices before JS loads |
| `admin_footer` | 10 | `NoticePanel` | Render sidebar panel HTML |
| `wp_dashboard_setup` | 999 | `WidgetManager` | Filter dashboard widgets |
| `admin_head` | 10 | `NoticeManager` | Isolate notices on LW plugin pages |
| `admin_notices` | -9999 | `NoticeManager` | Open notice wrapper div |
| `admin_notices` | PHP_INT_MAX | `NoticeManager` | Close notice wrapper div |

## Filters

| Hook | Class | Purpose |
|------|-------|---------|
| `admin_body_class` | `NoticeManager` | Adds `lw-plugins-admin-page` body class |

## Custom Action

| Hook | Location | Purpose |
|------|----------|---------|
| `lw_plugins_overview_cards` | `ParentPage::render()` | Add extra plugin cards to the LW Plugins overview |

## Database Options

| Option Name | Type | Description |
|-------------|------|-------------|
| `lw_zenadmin_options` | `array` | Main settings (`notices_enabled`, `widgets_enabled`) |
| `lw_zenadmin_widget_settings` | `array\|false` | List of visible widget IDs, `false` if never saved |
| `lw_zenadmin_discovered_widgets` | `array` | Auto-discovered widget ID → title map |
