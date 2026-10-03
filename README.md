# AVDEB Product Embeds (WordPress plugin)

Show [AVDEB](https://avdeb.com/) products — a creator's shop, a category or single products — on a WordPress site as a block or shortcode, with the site owner's affiliate referral code on every link.

**Docs:** [avdeb.com/developers](https://avdeb.com/developers/#wordpress)

- `AVDEB Products` block (server-rendered, live preview, no build step)
- Shortcodes: `[avdeb_products creator="slug" tag="slug" limit="6" columns="3"]`, `[avdeb_product handle="…"]`, `[avdeb_link url="/store/…/"]text[/avdeb_link]`
- Referral links get `rel="sponsored"`; optional disclosure under the grid
- Reads the catalog API `https://api.avdeb.com/v1/catalog/products` server-side (optional secret key in
  `Authorization: Bearer avdeb_sk_…`), cached per query (transient + 7-day last-good fallback)
- No front-end JavaScript, no cookies, no visitor data

## Become an AVDEB affiliate (or creator)

The plugin works without an account; you need one to earn commission. Program details:
[avdeb.com/affiliate](https://avdeb.com/affiliate/) — 5% commission, 30-day cookie, monthly PayPal payouts in USD
($50 minimum), open worldwide, approval usually within 24 hours.

1. Open [avdeb.com/affiliate/apply](https://avdeb.com/affiliate/apply/).
2. **Account** — email + password (or *Sign in* if you already have an AVDEB account).
3. **Program** — *Affiliate* (share products, earn commission), *Creator* (submit sticker / 3D designs that
   AVDEB prints and ships; you earn on every sale), or both.
4. **Personal information & details** — first/last name, country, tax residency, legal name (business name optional).
5. **Address** — street, city, state/province, postal code.
6. **Payout & promotion** — PayPal email for commissions; optionally your website and social links.
7. Accept the Terms & Conditions and Privacy Policy → **Create Account & Apply**.
8. After approval you get your referral link by email and in the dashboard.
9. Partner dashboard → **Embeds & API** ([direct link](https://avdeb.com/affiliate/dashboard/developers/)) →
   create a **secret key** (`avdeb_sk_…`), or copy your referral code from **Links**.
10. WordPress → **Settings → AVDEB** → paste the key (or code) → **Save**. All product links now carry your code.

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
.wordpress-org/                   WordPress.org banner, icon and screenshots (SVN /assets, not shipped)
```

## License

GPL-2.0-or-later
