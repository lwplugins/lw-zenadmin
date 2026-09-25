# React admin REST contract

The settings screen (`admin.php?page=lw-zenadmin`) is a React app (`src/` →
`build/`). It reads and writes everything through one route:

```
GET  /wp-json/lw-zenadmin/v1/admin/settings
POST /wp-json/lw-zenadmin/v1/admin/settings
```

- `permission_callback`: `current_user_can( 'manage_options' )`.
- Cookie auth + `X-WP-Nonce` (`wp_rest`) — `@wordpress/api-fetch` sends it.
- Storage is unchanged: `lw_zenadmin_options` plus the three visibility lists
  (`lw_zenadmin_widget_settings`, `lw_zenadmin_menu_settings`,
  `lw_zenadmin_adminbar_settings`), so the CLI, the Site Manager abilities and
  the features keep reading the same data.

## Discovery

Widgets, menu items and admin bar nodes are *discovered* by the features while
the matching screen renders — exactly as with the classic tabs:

| Section  | Discovered by                                     | When                                                          |
|----------|---------------------------------------------------|---------------------------------------------------------------|
| widgets  | `WidgetManager::filter_widgets` (`wp_dashboard_setup`, 999) | Dashboard visit while the widget manager is on        |
| menus    | `MenuManager::filter_menus` (`admin_menu`, 9999)  | Every admin page load while the menu manager is on            |
| adminbar | `AdminBarManager::filter_nodes` (`wp_before_admin_bar_render`, 9999) | Every page with the admin bar while the admin bar manager is on |

The settings page itself is an admin page with the admin bar, so its own load
refreshes the menu and admin bar discovery *before* the app calls the REST
route (the classic tabs read the same freshly written options during render).
The REST request never runs discovery (no `admin_menu`, no admin bar, no
dashboard), it only reads what was stored. When a list is empty the UI tells the
user what to do: open the Dashboard (widgets) or reload the page once the
manager is on and saved (menus, admin bar) — the classic save redirect did that
reload implicitly.

## GET response

```jsonc
{
  "options": {
    "notices_enabled": true,
    "widgets_enabled": true,
    "menu_enabled": false,
    "adminbar_enabled": false
  },
  "sections": {
    "widgets": {
      "saved": false,            // a visibility list was ever saved (false = defaults apply)
      "count": 7,                // discovered items
      "groups": [
        {
          "id": "core",          // core | woocommerce | third_party
          "label": "WordPress Core",
          "items": [
            { "id": "dashboard_primary", "title": "WordPress Events and News",
              "visible": true, "protected": false, "depth": 0 }
          ]
        }
      ]
    },
    "menus": {
      "saved": true, "count": 42,
      "groups": [                // core | woocommerce | lw_plugins | third_party
        { "id": "core", "label": "WordPress Core", "items": [
          { "id": "tools.php", "title": "Tools", "visible": true, "protected": false, "depth": 0 },
          { "id": "tools.php::import.php", "title": "Import", "visible": false, "protected": false, "depth": 1 }
        ] }
      ]
    },
    "adminbar": {
      "saved": false, "count": 20,
      "groups": [                // core | woocommerce | third_party
        { "id": "core", "label": "WordPress Core", "items": [
          { "id": "top-secondary", "title": "top-secondary", "visible": true, "protected": true, "depth": 0 },
          { "id": "my-account", "title": "Howdy, admin", "visible": true, "protected": true, "depth": 1 }
        ] }
      ]
    }
  },
  "meta": {
    "defaults": { "notices_enabled": true, "widgets_enabled": true, "menu_enabled": false, "adminbar_enabled": false },
    "dashboard_url": "https://example.com/wp-admin/index.php",
    "docs_url": "https://github.com/lwplugins/lw-zenadmin#readme"
  }
}
```

Rules carried over from the classic tabs:

- Only non-empty groups are listed, in the classic order.
- Items are in render order: menus list each submenu (`parent::child`, depth 1)
  right after its parent, an orphaned submenu goes to `third_party`; admin bar
  nodes are flattened depth-first (`AdminBarNodeGrouper`), depth unlimited.
- `visible` is the effective state (`WidgetManager::is_widget_visible`,
  `MenuManager::is_menu_visible`, `AdminBarManager::is_node_visible`); a
  never-saved widget list shows Core + WooCommerce only, never-saved menu and
  admin bar lists show everything.
- `protected` items are always `visible: true` and cannot be hidden:
  menus — `index.php`, `options-general.php`, `plugins.php`, `lw-plugins`,
  every `lw-*` slug, and a submenu when both its parent and its slug are
  protected; admin bar — `my-account`, `top-secondary`, `user-actions`,
  `logout`; widgets — none.
- Titles are plain text, cleaned at discovery (`Features\PlainTitle`): count
  badges (`awaiting-mod`, `update-plugins`, `count-N`, `plugin-count`, …),
  hidden alternates (`hide-if-js`) and `<kbd>` hints are dropped with their
  content; screen-reader text is used only when nothing readable is left
  (icon-only admin bar nodes), minus its numbers. A title stored before the
  cleaner existed is rewritten on the next load that discovers it (any admin
  page for menus / admin bar, the Dashboard for widgets). The UI renders
  titles as text.

## POST (partial, validated, atomic)

Send only what changed. Every key is optional:

```json
{
  "options":  { "menu_enabled": true },
  "widgets":  { "dashboard_primary": false },
  "menus":    { "tools.php::import.php": false },
  "adminbar": { "wp-logo": false, "comments": true }
}
```

- `options`: known keys only, boolean values (`true`/`false`, `0`/`1` accepted).
  The rest of the options keep their stored value; the merged set goes through
  `SettingsSanitizer::sanitize_options()`.
- `widgets` / `menus` / `adminbar`: `{ id: visible }` for the items the user
  changed. IDs must be discovered items of that section; a protected item can
  only be sent as `true`. Items that are not mentioned keep their current
  effective visibility (the classic "rendered marker" rule, now for every
  section), so an item discovered after the page loaded is never hidden by a
  save. The stored list is rebuilt from the discovered items (like the classic
  form): visible, non-protected IDs; stale IDs of items no longer discovered are
  dropped.
- Validation runs over the whole body first; any error saves **nothing** and
  answers `400`:

```json
{
  "code": "lw_zenadmin_invalid",
  "message": "Some settings are not valid. Nothing was saved.",
  "data": {
    "status": 400,
    "fields": {
      "menu_enabled": [ "Must be true or false." ],
      "adminbar": [ "\"logout\" is protected and cannot be hidden." ],
      "widgets": [ "Unknown item: \"foo\"." ],
      "bogus": [ "Unknown setting." ]
    }
  }
}
```

  Field keys are option keys for `options`, the section key for a section, and
  the top-level key for anything unknown.
- A body over 64 KB answers `413 lw_zenadmin_too_large` without reading it.
- An empty body is a no-op and answers the current state.
- Success answers `200` with the same shape as GET (fresh from storage).

## Errors

| Status | Code                   | When                              |
|--------|------------------------|-----------------------------------|
| 400    | `lw_zenadmin_invalid`  | Validation failed (`data.fields`) |
| 401/403| `rest_forbidden`       | Not logged in / no manage_options |
| 413    | `lw_zenadmin_too_large`| Body over 64 KB                   |

## Classic control → React equivalent

| Classic tab / control | React |
|---|---|
| Vertical tab nav (`#notices`, `#widgets`, `#menu`, `#adminbar`), hash kept on reload/save | Side nav with the same hash slugs; `hashchange` switches, mobile collapses behind a current-section toggle |
| "Save Changes" button + redirect with `updated=1` notice | Top bar Save / Discard, Cmd/Ctrl+S, dirty tracking, "leave page?" warning, snackbar "Settings saved." |
| Notices: "Collect notices into sidebar panel" checkbox + description | Notices tab state bar with a switch (card level) + explanation |
| Widgets: "Manage dashboard widget visibility" checkbox | Widgets tab state bar switch |
| Widgets: grouped table (Show / Widget Name / Widget ID) | Grouped list: checkbox + title, ID in `<code>`, group headings with counts |
| Widgets: "No widgets discovered yet. Visit the Dashboard once…" | Empty state with the same guidance + "Open the Dashboard" link |
| Menus: "Manage admin menu visibility" checkbox | Menus tab state bar switch |
| Menus: grouped table, submenus indented with "—", top level bold, protected rows disabled + "(protected)" | Same grouping; depth indentation, top level bold, protected rows checked + disabled with a "Protected" badge |
| Menus: "No menu items discovered yet. Enable the feature and visit any admin page." | Empty state with the same guidance + "Reload page" button |
| Admin Bar: "Manage admin bar visibility" checkbox | Admin Bar tab state bar switch |
| Admin Bar: grouped table, depth-indented rows, protected rows, hidden "rendered" markers | Same rows; the marker rule is now server-side for every section (unsent = unchanged) |
| Admin Bar: "No admin bar items discovered yet…" | Empty state + "Reload page" button |
| Menu save skipped when nothing discovered; admin bar save skipped when nothing discovered | Nothing to send (no items), and unknown IDs are rejected |
