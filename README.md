# AVDEB Product Embeds (WordPress plugin)

Show [AVDEB](https://avdeb.com/) products — a creator's shop, a category or single products — on a WordPress site as a block or shortcode, with the site owner's affiliate referral code on every link.

**Docs:** [avdeb.com/developers](https://avdeb.com/developers/#wordpress)

- `AVDEB Products` block (server-rendered, live preview, no build step)
- Shortcodes: `[avdeb_products creator="slug" tag="slug" limit="6" columns="3"]`, `[avdeb_product handle="…"]`, `[avdeb_link url="/store/…/"]text[/avdeb_link]`
- Referral links get `rel="sponsored"`; optional disclosure under the grid
- Reads the catalog API `https://api.avdeb.com/v1/catalog/products` server-side (optional secret key in
  `Authorization: Bearer avdeb_sk_…`), cached per query (transient + 7-day last-good fallback)
- No front-end JavaScript, no cookies, no visitor data

## Development

Requires PHP 7.4+ and WordPress 6.3+.

```sh
# lint
find . -name '*.php' -not -path './vendor/*' -print0 | xargs -0 -n1 php -l
# WordPress coding standards
composer global require wp-coding-standards/wpcs dealerdirect/phpcodesniffer-composer-installer
phpcs --standard=WordPress --extensions=php --ignore=vendor .
```

Try it locally with [wp-now](https://github.com/WordPress/playground-tools/tree/trunk/packages/wp-now): `npx @wp-now/wp-now start` in this folder.

## Structure

```
avdeb-product-embeds.php          bootstrap: style, shortcodes, block, settings link
includes/class-avdeb-pe-api.php   catalog API client: query, validate, cache, key info
includes/class-avdeb-pe-render.php  cards, grid, referral links, disclosure
includes/class-avdeb-pe-shortcodes.php
includes/class-avdeb-pe-settings.php  Settings → AVDEB
blocks/products/                  block.json, editor script, render.php
readme.txt                        WordPress.org listing
```

## License

GPL-2.0-or-later
