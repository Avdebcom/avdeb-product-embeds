=== AVDEB Product Embeds ===
Contributors: avdeb
Tags: affiliate, creator, product embed, shortcode, block
Requires at least: 6.3
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Show AVDEB products — a creator's shop, a category or single products — as a block or shortcode, with your affiliate referral code on every link.

== Description ==

AVDEB Product Embeds shows products from [AVDEB](https://avdeb.com/) (3D printed goods, stickers and custom gifts) on your WordPress site.

* **Creators** show the products they designed on their own site with one block.
* **Affiliates** paste their AVDEB API key (or just a referral code) once; every product link carries their referral code.
* **Anyone** can feature a category or a single product in a post.

Features:

* "AVDEB Products" block with a live preview in the editor.
* Show a creator's products, a category, search results or a single product.
* Shortcodes: `[avdeb_products]`, `[avdeb_product]` and `[avdeb_link]`.
* Responsive product grid that inherits your theme's fonts and colors.
* Links that carry a referral code get `rel="sponsored"`, and an optional affiliate disclosure is shown under the grid.
* Fast: each product list is cached on your server (6 hours by default). If AVDEB can't be reached, the last good copy is shown.
* Private: no cookies, no JavaScript on the front end and no visitor data sent anywhere.

= Shortcodes =

`[avdeb_products creator="your-creator-slug" limit="6" columns="3"]`
One creator's products. The slug is the last part of the creator page URL (avdeb.com/creators/your-slug/).

`[avdeb_products tag="stickers" limit="8"]`
Products from a category (avdeb.com/category/stickers/).

`[avdeb_products q="cat stickers" limit="6"]`
The best matches for a search.

`[avdeb_product handle="product-handle"]`
One product card (avdeb.com/store/product-handle/).

`[avdeb_link url="/store/product-handle/"]Get yours[/avdeb_link]`
A text link to any avdeb.com page, with your referral code added.

== External services ==

This plugin connects to AVDEB (https://avdeb.com), the store whose products it displays.

* **What is sent and when:** when a block or shortcode is shown and its cached copy has expired (default every 6 hours, or after "Refresh products now"), your server requests the matching products from the AVDEB catalog API at `https://api.avdeb.com/v1/catalog/products`. The request contains the block's settings (creator, category, product, search text, number of products), your referral code or API key if you saved one, and — like any web request — your server's IP address and site URL plus the plugin version (in the user agent). When an API key is saved, the settings page also checks it at `https://api.avdeb.com/v1/catalog/key`. No data about your visitors is sent.
* **Images:** product images in the grid are loaded by your visitors' browsers from AVDEB's image CDN (cdn.avdeb.com), which sees the visitor's IP address and browser details as with any embedded image.
* **Links:** product links go to avdeb.com. If you set a referral code, it is added to the link as `?ref=CODE`. AVDEB only records the referral when a visitor clicks through.

AVDEB [Terms of Service](https://avdeb.com/terms/) and [Privacy Policy](https://avdeb.com/privacy/).

== Installation ==

1. Install and activate the plugin.
2. Go to Settings → AVDEB and (optionally) paste your secret API key from the AVDEB partner dashboard (Embeds & API), or just your referral code.
3. Add the "AVDEB Products" block to a post or page, or paste a shortcode.

== Frequently Asked Questions ==

= Do I need an AVDEB account? =

No. Anyone can embed products. You only need an account to earn commission (affiliates) or to have your own designs listed (creators).

= API key or referral code? =

Both work. An API key (partner dashboard → Embeds & API, "secret key") adds your referral code automatically, shows who you're connected as, and allows up to 48 products per block. Without a key, up to 12 products are shown and the referral code you enter is used. Never share your secret key or put it in page content.

= Where do I find my referral code? =

In your AVDEB affiliate dashboard under Links. Not a partner yet? See https://avdeb.com/affiliate/

= How do I show only my own designs as a creator? =

Use the block with "A creator's products" and your creator slug, or `[avdeb_products creator="your-slug"]`.

= Prices look out of date. =

Prices refresh with the cache (default every 6 hours). Click "Refresh products now" on the settings page to update immediately. The price at checkout on avdeb.com always applies.

= Does it work with page caching plugins? =

Yes. The output is plain HTML with no per-visitor content.

== Screenshots ==

1. The AVDEB Products block in the editor.
2. A creator's products on a blog post.
3. Settings → AVDEB.

== Changelog ==

= 0.1.0 =
* First release: block, shortcodes (creator, category, search, product, link), API key or referral code, disclosure, per-query caching with fallback.
