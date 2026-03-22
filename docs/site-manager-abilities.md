# LW Site Manager - ZenAdmin Abilities

LW ZenAdmin registers 3 abilities with LW Site Manager, enabling AI agents and automation tools to manage admin UI settings programmatically.

These abilities are only active when LW Site Manager is also installed and activated. No hard dependency - the integration is a no-op otherwise.

## Abilities

| Ability | Type | Description |
|---------|------|-------------|
| `lw-zenadmin/get-options` | readonly | Get global ZenAdmin settings |
| `lw-zenadmin/set-options` | write | Update ZenAdmin settings |
| `lw-zenadmin/list-widgets` | readonly | List dashboard widgets and their visibility status |

## Authentication

All requests require a WordPress Application Password:

```bash
curl -u "user@example.com:XXXX XXXX XXXX XXXX XXXX XXXX" <URL>
```

## lw-zenadmin/get-options

Get all global LW ZenAdmin settings.

**Method:** GET

```bash
curl -u "user:app-password" \
  "https://example.com/wp-json/wp-abilities/v1/abilities/lw-zenadmin/get-options/run"
```

**Response:**
```json
{
  "success": true,
  "options": {
    "notices_enabled": true,
    "widgets_enabled": true,
    "menu_enabled": false,
    "adminbar_enabled": false
  }
}
```

**Available options:**

| Key | Type | Description |
|-----|------|-------------|
| `notices_enabled` | boolean | Enable admin notice collector sidebar |
| `widgets_enabled` | boolean | Enable dashboard widget manager |
| `menu_enabled` | boolean | Enable admin menu manager |
| `adminbar_enabled` | boolean | Enable admin bar manager |

## lw-zenadmin/set-options

Update ZenAdmin settings. Only the provided keys are updated; others remain unchanged.

**Method:** POST

```bash
curl -u "user:app-password" \
  -X POST -H "Content-Type: application/json" \
  -d '{
    "input": {
      "options": {
        "notices_enabled": true,
        "widgets_enabled": false
      }
    }
  }' \
  "https://example.com/wp-json/wp-abilities/v1/abilities/lw-zenadmin/set-options/run"
```

**Response:**
```json
{
  "success": true,
  "message": "2 option(s) updated.",
  "updated": ["notices_enabled", "widgets_enabled"]
}
```

**Valid keys:** `notices_enabled`, `widgets_enabled`, `menu_enabled`, `adminbar_enabled`

## lw-zenadmin/list-widgets

List all discovered dashboard widgets along with their group and current visibility status.

Widgets are only discoverable after the dashboard has been loaded at least once (they are registered at runtime by `wp_dashboard_setup`).

**Method:** GET

```bash
curl -u "user:app-password" \
  "https://example.com/wp-json/wp-abilities/v1/abilities/lw-zenadmin/list-widgets/run"
```

**Response:**
```json
{
  "success": true,
  "widgets": [
    {
      "id": "dashboard_right_now",
      "title": "At a Glance",
      "group": "core",
      "visible": true
    },
    {
      "id": "dashboard_activity",
      "title": "Activity",
      "group": "core",
      "visible": true
    },
    {
      "id": "woocommerce_dashboard_status",
      "title": "WooCommerce Status",
      "group": "woocommerce",
      "visible": true
    },
    {
      "id": "my_custom_widget",
      "title": "My Plugin Widget",
      "group": "third-party",
      "visible": false
    }
  ]
}
```

**Widget groups:**

| Group | Description |
|-------|-------------|
| `core` | WordPress core dashboard widgets |
| `woocommerce` | WooCommerce dashboard widgets |
| `third-party` | All other plugin/theme widgets |

## Permissions

| Ability | Required capability |
|---------|-------------------|
| `lw-zenadmin/get-options` | `manage_options` |
| `lw-zenadmin/set-options` | `manage_options` |
| `lw-zenadmin/list-widgets` | `manage_options` |
