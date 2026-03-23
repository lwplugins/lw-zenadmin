# WP-CLI Commands

## Feature Management

### `wp lw-zenadmin status`

Display the current status of all features.

```
$ wp lw-zenadmin status
+-----------------+----------+
| feature         | status   |
+-----------------+----------+
| notices_enabled | enabled  |
| widgets_enabled | enabled  |
+-----------------+----------+
```

### `wp lw-zenadmin enable <feature>`

Enable a feature.

```bash
wp lw-zenadmin enable notices_enabled
wp lw-zenadmin enable widgets_enabled
```

### `wp lw-zenadmin disable <feature>`

Disable a feature.

```bash
wp lw-zenadmin disable notices_enabled
wp lw-zenadmin disable widgets_enabled
```

## Widget Management

### `wp lw-zenadmin widget list`

List all discovered widgets and their visibility status.

```
$ wp lw-zenadmin widget list
+------------------------+-------------------+---------+-------------+
| widget_id              | name              | visible | group       |
+------------------------+-------------------+---------+-------------+
| dashboard_right_now    | At a Glance       | yes     | core        |
| dashboard_activity     | Activity          | yes     | core        |
| dashboard_quick_press  | Quick Draft       | yes     | core        |
| dashboard_primary      | WordPress Events  | yes     | core        |
| dashboard_site_health  | Site Health       | yes     | core        |
| woocommerce_dashboard  | WooCommerce Stats | yes     | woocommerce |
| my_custom_widget       | My Plugin Widget  | no      | third-party |
+------------------------+-------------------+---------+-------------+
```

Supports `--format=table` (default), `csv`, `json`, `yaml`:

```bash
wp lw-zenadmin widget list --format=json
```

### `wp lw-zenadmin widget show <widget_id>`

Show a widget on the dashboard.

```bash
wp lw-zenadmin widget show dashboard_quick_press
# Success: Widget 'Quick Draft' (dashboard_quick_press) is now shown.
```

### `wp lw-zenadmin widget hide <widget_id>`

Hide a widget from the dashboard.

```bash
wp lw-zenadmin widget hide dashboard_primary
# Success: Widget 'WordPress Events' (dashboard_primary) is now hidden.
```

### `wp lw-zenadmin widget show-all`

Show all discovered widgets.

```bash
wp lw-zenadmin widget show-all
# Success: All widgets are now visible.
```

### `wp lw-zenadmin widget hide-all`

Hide all widgets from the dashboard.

```bash
wp lw-zenadmin widget hide-all
# Success: All widgets are now hidden.
```

### `wp lw-zenadmin widget reset`

Reset widget visibility to defaults (Core + WooCommerce visible, Third-party hidden).

```bash
wp lw-zenadmin widget reset
# Success: Widget visibility reset to defaults.
```

## Menu Management

### `wp lw-zenadmin menu list`

List all discovered admin menu items and their visibility.

```
$ wp lw-zenadmin menu list
+---------------------+-----------+---------+-------------+-----------+
| slug                | name      | visible | group       | protected |
+---------------------+-----------+---------+-------------+-----------+
| index.php           | Dashboard | yes     | core        | yes       |
| edit.php            | Posts     | yes     | core        | no        |
| tools.php           | Tools     | yes     | core        | no        |
+---------------------+-----------+---------+-------------+-----------+
```

Supports `--format=table` (default), `csv`, `json`, `yaml`.

### `wp lw-zenadmin menu show <slug>`

Show a menu item in the admin sidebar.

```bash
wp lw-zenadmin menu show tools.php
# Success: Menu 'Tools' (tools.php) is now shown.
```

### `wp lw-zenadmin menu hide <slug>`

Hide a menu item from the admin sidebar. Protected menus cannot be hidden.

```bash
wp lw-zenadmin menu hide tools.php
# Success: Menu 'Tools' (tools.php) is now hidden.
```

### `wp lw-zenadmin menu show-all`

Show all menu items.

```bash
wp lw-zenadmin menu show-all
# Success: All menu items are now visible.
```

### `wp lw-zenadmin menu hide-all`

Hide all non-protected menu items.

```bash
wp lw-zenadmin menu hide-all
# Success: All non-protected menu items are now hidden.
```

### `wp lw-zenadmin menu reset`

Reset menu visibility to defaults (all visible).

```bash
wp lw-zenadmin menu reset
# Success: Menu visibility reset to defaults (all visible).
```

## Notes

- `widget list` only shows widgets after the Dashboard page has been visited at least once in the browser (auto-discovery).
- `menu list` only shows menus after the admin has been visited at least once in the browser (auto-discovery).
- Use `widget list` / `menu list` to look up IDs for `show` and `hide` commands.
- `widget reset` / `menu reset` deletes saved settings and falls back to built-in defaults.
