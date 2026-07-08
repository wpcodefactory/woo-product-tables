# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

"Product Table for WooCommerce by WBW" — a WordPress/WooCommerce plugin (text domain `woo-product-tables`) that displays WooCommerce products in a searchable/sortable table via shortcode. This is a plain PHP WordPress plugin with no build system, package manager, or automated test suite — there is no `package.json`, `composer.json`, or `phpunit.xml`. Development is done by editing PHP/JS/CSS directly and testing in a live WordPress + WooCommerce install.

Deployment: pushing a git tag triggers `.github/workflows/deploy-to-wp.yml`, which syncs the tag to the WordPress.org SVN repo (no build step runs).

## Coding standards

All new/edited PHP, JS, and CSS must follow the [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/) (WPCS), except where this file's own [Naming conventions](#naming-conventions) or [Versioning conventions](#versioning-conventions) explicitly override them. Where the two conflict, this repo's existing conventions win (e.g. keep the `Wtbp`-suffixed/camelCase names already used throughout `classes/` and `modules/` — don't rewrite them to WP's typical `snake_case`).

Practical rules to apply on every edit:
- **Indentation**: tabs, not spaces, for PHP/JS (match [WordPress-Extra](https://github.com/WordPress/WordPress-Coding-Standards)/PHPCS defaults and the surrounding file).
- **Braces**: Allman-style is NOT used — opening brace on the same line as the control structure/function declaration (`function foo() {`), per WP style.
- **Spacing**: single space after control-structure keywords (`if (`, `foreach (`, `while (`) and around operators (`$a === $b`, not `$a===$b`); no space between a function name and its opening parenthesis (`foo( $bar )` — WP style also adds a space just inside parens).
- **Yoda conditions**: comparisons against a constant/literal put the literal first (`if ( true === $foo )`, `if ( 'bar' === $baz )`), to avoid accidental assignment.
- **Arrays**: use `array()` long-form in existing files that already use it for consistency, unless the surrounding file already uses `[]` short-form — match what's already in that file rather than mixing styles.
- **Security**: escape all output (`esc_html()`, `esc_attr()`, `esc_url()`, `wp_kses_post()` as appropriate) and sanitize/validate all input (`sanitize_text_field()`, `absint()`, nonce checks via `wp_verify_nonce()`/`check_admin_referer()`) at every new boundary touched — this applies even in the framework's DbWtbp/DispatcherWtbp wrappers.
- **DB queries**: use `$wpdb->prepare()` for any query with interpolated values, consistent with how [classes/db.php](classes/db.php) already wraps `$wpdb`.
- **i18n**: wrap all user-facing strings in `__()`/`_e()`/`esc_html__()` etc. with the `woo-product-tables` text domain.
- **Comparisons**: prefer strict comparison (`===`, `!==`) over loose (`==`, `!=`) unless the existing code in that exact spot intentionally relies on loose comparison.

Don't run a WPCS/PHPCS bulk auto-fix pass across untouched code — apply these rules only to lines you are actually adding or modifying, consistent with the surgical `@version`-tag policy below.

## Commands

There are no lint/build/test commands in this repo. Verify changes by loading the plugin in a WordPress site with WooCommerce active and exercising the relevant admin/frontend flow.

## Architecture

The plugin is built on a custom in-house MVC micro-framework (classes suffixed `Wtbp`), not a modern WP boilerplate. Everything bootstraps from [woo-producttables.php](woo-producttables.php), which loads [config.php](config.php) (constants) and [functions.php](functions.php) (global helper functions), then imports the core framework classes from `classes/` via `importClassWtbp()`.

### Framework core (`classes/`)

- **FrameWtbp** ([classes/frame.php](classes/frame.php)) — singleton application kernel (`FrameWtbp::_()`). Parses the request route (`?pl=wtbp&mod=...&action=...`), discovers active modules from the `@__modules` DB table, instantiates them, dispatches to the matching controller action, and manages script/style enqueueing.
- **ModuleWtbp** ([classes/module.php](classes/module.php)) — abstract base for a "module" (a self-contained feature living under `modules/<code>/`). Lazily creates its Controller/Helper by convention (`modules/<code>/controller.php`, `modules/<code>/helper.php`).
- **ControllerWtbp** ([classes/controller.php](classes/controller.php)), **ModelWtbp** ([classes/model.php](classes/model.php)), **ViewWtbp** ([classes/view.php](classes/view.php)) — standard MVC roles per module. Views render PHP templates from `modules/<code>/views/tpl/`.
- **TableWtbp** ([classes/table.php](classes/table.php)) — query-builder-style abstraction over a DB table; concrete table classes live in `classes/tables/` (e.g. `modules.php`, `columns.php`, `favorites.php`) and are auto-loaded by `FrameWtbp::_extractTables()`.
- **DbWtbp** ([classes/db.php](classes/db.php)) — thin wrapper around `$wpdb`; table names use the `@__` placeholder prefix, expanded via `prepareQuery()`.
- **DispatcherWtbp** ([classes/dispatcher.php](classes/dispatcher.php)) — wraps `add_action`/`do_action`/`add_filter`/`apply_filters`, auto-prefixing hook names with `wtbp_` unless already prefixed.
- **InstallerWtbp** ([classes/installer.php](classes/installer.php)) — runs on every load (`InstallerWtbp::update()`); creates/migrates the plugin's custom DB tables (`@__modules`, `@__columns`, etc.) and seeds the initial module registry.
- Several classes in `classes/` (LangWtbp, ReqWtbp, UriWtbp, HtmlWtbp, ResponseWtbp, FieldAdapterWtbp, ValidatorWtbp, ErrorsWtbp, UtilsWtbp, ModInstallerWtbp, InstallerDbUpdaterWtbp, DateWtbp) are marked `@deprecated since version 1.0.1` in [woo-producttables.php](woo-producttables.php) but are still actively imported/used — don't be misled by the tag into assuming they're dead code.

### Modules (`modules/`)

Each subdirectory is a self-contained feature registered in the `@__modules` DB table (seeded in [classes/installer.php](classes/installer.php)):
- `wootablepress` — the core module: table rendering, columns, settings, favorites (the actual product-table feature).
- `adminmenu`, `admin_nav`, `pages`, `options`, `user` — admin UI plumbing (menus, nav, settings pages, options storage, user/permission handling).
- `templates` — frontend template rendering support.
- `mail`, `promo` — auxiliary features.

A module directory conventionally contains `mod.php` (module class), `controller.php`, `models/`, `views/` (with `views/tpl/*.php` templates), plus its own `js/` and `css/`.

### Naming conventions

- All framework/core class names end in the `Wtbp` suffix (e.g. `FrameWtbp`, `DbWtbp`, `TableWtbp`); global helper functions likewise end in `Wtbp` (e.g. `importClassWtbp()`, `toeCreateObjWtbp()`).
- Constants are prefixed `WTBP_` (defined in [config.php](config.php)).
- DB tables use the `wtbp_` prefix and are referenced in queries with the `@__` placeholder (expanded to the real `$wpdb->prefix` + `wtbp_`).

### Versioning conventions

- The plugin version lives in two places that must stay in sync when preparing a release: the `Version:` header in [woo-producttables.php](woo-producttables.php) and the `WTBP_VERSION` constant in [config.php](config.php).
- **While actively editing the plugin** (not yet released), bump the version above the last official release and append a dev suffix: `-dev-YYYYMMDD-HHMM` (e.g. official `1.0.0` → `1.0.1-dev-20240718-2010` while working). It's fine if this dev timestamp doesn't get updated on every single edit.
- When a version is actually released, drop the dev suffix so the version matches the plugin header field exactly (e.g. `1.0.1`).
- Every PHPDoc `@version` tag (on functions, methods, classes, class members, requires/includes, hooks, file headers, constants) must be updated to match the new plugin version, but **only on the elements actually changed** in that edit, plus the containing class/file header — don't bulk-update `@version` tags on untouched code.
- The `@version` tag never includes the `-dev-...` suffix — strip it. If the working version is `2.3.0-dev-20260707-0445`, the `@version` tag is `2.3.0`.
- If an element being changed has no `@version` tag (or no docblock at all), add one — don't skip it just because it wasn't there before.
- A new/updated docblock is never just `@version`/`@since` tags on their own — it must always start with a one-line title, then a blank comment line, then the tags. This applies even to a short docblock added solely to carry a `@version` bump. **The title format depends on file type — see "Doc block shape by file type" below; don't default to the method-docblock style (`renderHtml.`) for file headers.**
- Whenever any method inside a class is changed and its docblock gets a `@version` bump, the containing class's own file-header docblock must also get its `@version` bumped (or added if missing) in the same edit — this is required any time a method changes, not only when the file had zero docblocks to begin with.
- Tag order: `@version` (and `@since`, when present) always comes immediately after the title line/blank line, before any other tags (`@param`, `@return`, `@see`, `@author`, etc.) — never appended at the end of an existing tag block.
- When adding or updating a docblock directly above a method/property, make sure there's a blank line separating it from the previous line of code (e.g. the closing `}` of the prior method) if one doesn't already exist — a docblock must never be glued directly to the preceding statement.
- `@since` is only added/set on genuinely new files, new classes, or new methods/functions — this applies to any file type (PHP class files, PHP templates, JS files, etc.), not just PHP classes. It is not touched on pre-existing elements that are merely being edited.
- `readme.txt` (`Stable tag:`) and `changelog.txt` are updated separately at actual release time, not during dev-version bumps.

Doc block shape by file type (using version `2.3.0` as the example target). **The two shapes below are not interchangeable — a class file header must never use the template title format, and a template file header must never use the class title format. Check which kind of file you're editing before writing the title line.**

Class-based PHP file (any file that declares a class, e.g. `classes/*.php`, `modules/*/mod.php`, `modules/*/controller.php`, `modules/*/models/*.php`) — file header title is `Product Table by WBW - <ClassName without the Wtbp suffix> class.` (period at the end, no `@author` tag), plus a block per changed method:
```php
<?php
/**
 * Product Table by WBW - Utils class.
 *
 * @version 2.3.0
 * @since   1.0.0
 */

class UtilsWtbp {

	/**
	 * Method A.
	 *
	 * @version 2.3.0
	 * @since   1.0.0
	 */
	function method_a() {
		echo 'Some text';
	}

}
```
Real examples already in the repo: [classes/utils.php](classes/utils.php) (`Utils class.`), [classes/logger.php](classes/logger.php) (`LoggerWtbp class.` — an older file that kept the `Wtbp` suffix; prefer omitting it in new/updated titles).

PHP template file (any file under `modules/*/views/tpl/*.php`, or a similar file-header-only PHP file that doesn't declare a class, e.g. `languages/customTitle.php`) — file header only, title is `Product Table for WooCommerce by WBW - <Human-Readable Title>` (Title Case, **no trailing period**, and **no "Pro"** — this plugin is the free version, per the `Plugin Name:` header in [woo-producttables.php](woo-producttables.php)), and **must** include `@author woobewoo`. `@since` matches the version the template was first introduced in (or the current version if it's new):
```php
<?php
/**
 * Product Table for WooCommerce by WBW - Edit Admin Custom Meta
 *
 * @version 2.3.0
 * @since 2.3.0
 *
 * @author woobewoo
 */
```
Real examples already in the repo: [modules/pages/views/tpl/deactivatePage.php](modules/pages/views/tpl/deactivatePage.php), [modules/options/views/tpl/optionsAdminMain.php](modules/options/views/tpl/optionsAdminMain.php). Note: [modules/wootablepress/views/tpl/wootablepressEditAdmin.php](modules/wootablepress/views/tpl/wootablepressEditAdmin.php) has `... WBW Pro - Edit Admin` in its title — that's a leftover from a Pro-plugin-derived file, not the pattern to copy; don't propagate "Pro" into new/updated titles.

JS file (e.g. `wootables.admin.pro.js`) — file header only; `@since` omitted here only because this example file is pre-existing (add it if the file is new):
```js
/**
 * <plugin name> - WooTables admin pro
 *
 * @version 2.3.0
 */
```
