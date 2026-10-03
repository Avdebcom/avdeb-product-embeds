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

= Earn commission: join the AVDEB affiliate program =

The plugin works without an account. To earn from the products you show, join the free [AVDEB affiliate program](https://avdeb.com/affiliate/): 5% commission on referred sales, a 30-day cookie, monthly PayPal payouts in USD (minimum $50), open worldwide.

1. Open the application form at [avdeb.com/affiliate/apply/](https://avdeb.com/affiliate/apply/).
2. Create your account with an email address and password (already have one? Sign in instead).
3. Choose the program: **Affiliate** (share AVDEB products and earn commission), **Creator** (submit your sticker or 3D designs; AVDEB prints and ships them and you earn on every sale), or both.
4. Fill in your name, country, tax residency, legal name (business name is optional) and address.
5. Enter the PayPal email where commissions should be sent, and optionally your website and social media links.
6. Accept the Terms & Conditions and Privacy Policy and click **Create Account & Apply**.
7. Wait for approval — usually within 24 hours, at most 1–3 days. You'll get your referral link by email and in your affiliate dashboard.
8. In your [partner dashboard](https://avdeb.com/affiliate/dashboard/developers/) open **Embeds & API** and create a secret key (it starts with `avdeb_sk_`), or copy your referral code from **Links**.
9. In WordPress go to **Settings → AVDEB** and paste the secret key (or the referral code). Every product link the plugin shows now carries your code.

Please disclose your affiliate links to your readers — the plugin can show a disclosure note under every embed for you.

== External services ==

This plugin connects to AVDEB (https://avdeb.com), the store whose products it displays.

* **What is sent and when:** when a block or shortcode is shown and its cached copy has expired (default every 6 hours, or after "Refresh products now"), your server requests the matching products from the AVDEB catalog API at `https://api.avdeb.com/v1/catalog/products`. The request contains the block's settings (creator, category, product, search text, number of products), your referral code or API key if you saved one, and — like any web request — your server's IP address and site URL plus the plugin version (in the user agent). When an API key is saved, the settings page also checks it at `https://api.avdeb.com/v1/catalog/key`. No data about your visitors is sent.
* **Images:** product images in the grid are loaded by your visitors' browsers from AVDEB's image CDN (cdn.avdeb.com), which sees the visitor's IP address and browser details as with any embedded image.
* **Links:** product links go to avdeb.com. If you set a referral code, it is added to the link as `?ref=CODE`. AVDEB only records the referral when a visitor clicks through.

AVDEB [Terms of Service](https://avdeb.com/terms/) and [Privacy Policy](https://avdeb.com/privacy/).

== Installation ==

1. In wp-admin go to **Plugins → Add New Plugin**, search for "AVDEB Product Embeds", click **Install Now**, then **Activate**. (Or upload the plugin zip under **Plugins → Add New Plugin → Upload Plugin**.)
2. Optional: go to **Settings → AVDEB** and paste your secret API key from the AVDEB partner dashboard (Embeds & API), or just your referral code. Not a partner yet? [Apply here](https://avdeb.com/affiliate/apply/) — see "Earn commission" above.
3. Add the "AVDEB Products" block to a post or page, or paste a shortcode.

== Frequently Asked Questions ==

= Do I need an AVDEB account? =

No. Anyone can embed products. You only need an account to earn commission (affiliates) or to have your own designs listed (creators).

= API key or referral code? =

Both work. An API key (partner dashboard → Embeds & API, "secret key") adds your referral code automatically, shows who you're connected as, and allows up to 48 products per block. Without a key, up to 12 products are shown and the referral code you enter is used. Never share your secret key or put it in page content.

= How do I become an AVDEB affiliate? =

Apply for free at [avdeb.com/affiliate/apply/](https://avdeb.com/affiliate/apply/): create an account, choose "Affiliate", fill in your details and PayPal email, and submit. Approval usually takes under 24 hours. The step-by-step guide is in the Description above; program details are at [avdeb.com/affiliate/](https://avdeb.com/affiliate/).

= How much do affiliates earn? =

5% of every completed sale you refer, with a 30-day cookie and no cap. Commissions are paid monthly via PayPal in USD once your balance reaches $50; smaller balances roll over to the next month. AVDEB's affiliate terms apply.

= I design stickers or 3D prints. Can I sell them on AVDEB? =

Yes. Choose "Creator" on the [application form](https://avdeb.com/affiliate/apply/). Once your designs are listed, show them on your site with the block's "A creator's products" option.

= Where do I find my referral code? =

In your AVDEB affiliate dashboard under Links (it's also in your approval email). Not a partner yet? See [avdeb.com/affiliate/](https://avdeb.com/affiliate/).

= How do I show only my own designs as a creator? =

Use the block with "A creator's products" and your creator slug, or `[avdeb_products creator="your-slug"]`.

= Prices look out of date. =

Prices refresh with the cache (default every 6 hours). Click "Refresh products now" on the settings page to update immediately. The price at checkout on avdeb.com always applies.

= Does it work with page caching plugins? =

Yes. The output is plain HTML with no per-visitor content.

== Screenshots ==

1. The AVDEB Products block in the editor: pick a creator, category, product or search and see a live preview.
2. Products from a category in a blog post, with the affiliate disclosure underneath (Twenty Twenty-Five theme).
3. Settings → AVDEB: API key or referral code, default columns, disclosure text and cache time.

== Changelog ==

= 0.1.0 =
* First release: block, shortcodes (creator, category, search, product, link), API key or referral code, disclosure, per-query caching with fallback.

== Upgrade Notice ==

= 0.1.0 =
First release.
