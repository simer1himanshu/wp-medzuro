# Deploying the Medzuro theme

Step-by-step from an empty host to a live store. Assumes no data migration —
there is nothing to import from Shopify, so products are entered directly.

Work through it in order. Sections 1–6 get the site running; 7–9 are the gaps
that must be closed before customers see it.

---

## 1. Host requirements

| Requirement | Minimum | Why |
|---|---|---|
| PHP | 8.1+ | Theme uses arrow functions and `?:` chains throughout |
| MySQL / MariaDB | 8.0 / 10.5+ | WooCommerce requirement |
| HTTPS | Required | Checkout collects payment details |
| Memory limit | 256M | WooCommerce admin is heavy |
| Disk | 1GB+ | WordPress, Woo, product images |

Any mainstream shared host meets this. Avoid hosts that only offer PHP 7.x —
the theme will fatal on activation.

If the host offers a one-click WordPress installer, use it and skip to §3.

---

## 2. Install WordPress

1. Create a MySQL database and a user with full privileges on it. Note the
   database name, username, password and host.
2. Download WordPress from <https://wordpress.org/download/> and upload it to
   the web root (often `public_html`).
3. Visit the domain. The installer will ask for the database details from
   step 1, then for a site title and admin account.
4. Use a strong admin password and an address you control. Do **not** use
   `admin` as the username.

---

## 3. Install WooCommerce

*Plugins → Add New → search "WooCommerce" → Install → Activate.*

Run its setup wizard and set:

- **Country/region:** Fiji
- **Currency:** Fijian dollar (FJD, `$`)
- **Product type:** Physical products
- **Sell in person:** yes, if you do local pickup

The wizard creates four pages the theme depends on — **Shop**, **Cart**,
**Checkout**, **My account**. Do not delete or rename them; the theme resolves
them through `wc_get_page_id()`, so removing one breaks links across the site.

---

## 4. Install the theme

1. Zip the `medzuro-wp` folder so the archive contains `medzuro-wp/style.css`
   at its top level.
2. *Appearance → Themes → Add New → Upload Theme* → choose the zip → Install →
   Activate.

If the host has no zip upload, upload the folder to
`wp-content/themes/medzuro-wp/` over SFTP and activate it from the themes
screen.

**On activation, check for errors.** A blank screen means a PHP fatal — turn on
debugging in `wp-config.php` to see it:

```php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
```

Errors then land in `wp-content/debug.log`. The theme also warns here about a
missing stylesheet, which is worth watching for on first load. **Turn debugging
back off before launch** — never leave `WP_DEBUG_DISPLAY` on for customers.

---

## 5. Configure

### Permalinks

*Settings → Permalinks* → choose **Post name** → Save.

Do this even if the setting already looks right — saving flushes the rewrite
rules WooCommerce needs. On "Plain" permalinks, product and category URLs will
404.

### Pages

Create these four pages (*Pages → Add New*), leave the content empty, and set
*Page Attributes → Template* on each:

| Page title | Slug | Template |
|---|---|---|
| About | `about` | Medzuro — About |
| HolyOak | `holyoak` | Medzuro — HolyOak |
| Lab Testing & Purity | `lab-test-and-purity` | Medzuro — Lab Testing & Purity |
| Contact | `contact` | Medzuro — Contact |

The slugs matter — templates link to each other by slug.

Also create a **Delivery** page with slug `delivery`. The homepage hero links
to it. This page did not exist in the Shopify theme either, so the link was
already broken there; the theme falls back to the site root rather than
emitting an empty link, but the page should exist.

Then create an empty page called **Home**, and set
*Settings → Reading → Your homepage displays → A static page → Homepage: Home*.
The theme's `front-page.php` takes over from there; the page's own content is
ignored.

### Menus

*Appearance → Menus.* Create and assign:

| Location | Contents |
|---|---|
| Primary | Shop, About, HolyOak, Lab Testing & Purity, Contact |
| Footer column 1–3 | Whatever grouping suits — the column heading is the menu's own name |
| Footer legal links | Privacy, Terms, Refunds, Shipping |
| Product categories | Optional filter bar above the shop grid |

A footer column with no menu assigned renders nothing, so it is safe to use
fewer than three.

---

## 6. Add products

### Categories and brands

Create product categories under *Products → Categories*.

For the brand line on each card, use *Products → Brands* if your WooCommerce is
9.4 or newer. The theme also accepts the Perfect Brands plugin, or a
`medzuro_brand` custom field, whichever is easiest.

### Custom fields

The product page reads two optional fields. Install **Advanced Custom Fields**
(the free tier is enough) and create a field group targeting Products:

| Field name | Type | Shows as |
|---|---|---|
| `medzuro_subtitle` | Text | Subtitle under the product title |
| `medzuro_serving` | Text | "Choose the supply that fits your routine" line |

The names must match exactly. Both are optional — the theme falls back to
sensible defaults when they are empty.

> The Shopify build defined four more metafields (benefits, ingredients,
> directions, warnings, nutrition) for a facts block that was never actually
> added to the product template. Skip them unless you want that block built.

### Product types

- **Simple product** — the page shows four quantity bundles (1–4 packs) priced
  as multiples of the base price.
- **Variable product** — the page shows up to four variations as packs.

Set a **regular price** and a lower **sale price** wherever you want the
"SAVE" figure and struck-through price to appear. With no sale price, those
elements hide themselves.

Add at least one variable product with two or three variations before going
live — it exercises the most intricate part of the theme.

### Removing the placeholders

While no products are published, the homepage carousel and shop grid show five
HolyOak demo cards. Once real products exist they disappear on their own. To
remove them permanently, delete `template-parts/home-fallback.php` and
`template-parts/collection-fallback.php`.

---

## 7. Close the gaps

These were enabled in Shopify and have no WooCommerce equivalent. Decide on
each before launch.

| Gap | What to do |
|---|---|
| **Contact form** | `template-parts/contact-page.php` still holds the Shopify form markup. Install a form plugin (Contact Form 7, WPForms, Fluent Forms) and replace that block with its shortcode. **The contact page will not submit until this is done.** |
| **Payment icons** | Add SVGs to `assets/img/` named `pay-visa.svg`, `pay-mastercard.svg`, `pay-amex.svg`, `pay-paypal.svg`. The footer renders only files that exist, so it stays tidy either way. |
| **Newsletter** | The footer and homepage forms are inert. Hook a mail plugin's shortcode onto `medzuro_footer_newsletter` and `medzuro_newsletter_form`. |
| **Wishlist** | The header icon appears only when YITH WooCommerce Wishlist is active. Install it, or ignore it and the icon stays hidden. |
| **Multi-currency** | Not ported. Add a plugin only if you actually sell outside Fiji. |
| **Cart drawer** | The header cart count updates live, but there is no slide-out drawer. Cart is a normal page. |

---

## 8. Payments, shipping, tax

*WooCommerce → Settings.*

- **Payments** — enable a gateway that supports FJD. Stripe and PayPal both do.
  Bank transfer and cash on delivery are built in and useful for local pickup.
- **Shipping** — create a Fiji zone with your rates. Add a "Local pickup"
  method, since the storefront copy promises it repeatedly.
- **Tax** — enable if you are VAT-registered. The product page states "MRP
  inclusive of all taxes", so set prices as **tax inclusive** to match, under
  *Settings → Tax → Prices entered with tax*.

Put every gateway in **test/sandbox mode** first and place a full test order
before accepting real payments.

---

## 9. Test before launch

Walk this list on a phone as well as a desktop.

**Homepage** — hero renders; trust row icons appear; product carousel arrows
scroll; reviews carousel scrolls; footer columns populated.

**Shop** — grid renders; sorting changes order; with 13+ products in one
category, pagination appears *and* sorting survives moving to page 2.

**Product (simple)** — gallery thumbnails swap the main image; the four packs
change price, saving and quantity; add to cart lands the right quantity.

**Product (variable)** — packs show the variations; selecting one updates
price; add to cart lands **the correct variation**, not just the parent. This
is the single most important check in the list.

**Cart** — reassurance block appears with delivery, secure-checkout and seller
badge; savings line shows when something is on sale; quantities update.

**Checkout** — seller badge appears; a test order completes; the confirmation
email arrives.

**Everything** — no horizontal scrollbar at 375px width; header sticks on
scroll; mobile menu button opens the nav.

---

## 10. Go live

1. **HTTPS** — install the certificate, set both WordPress and Site Address to
   `https://`, confirm no mixed-content warnings.
2. **Backups** — schedule automatic backups of files *and* database before the
   first real order, not after.
3. **Turn off debugging** — set `WP_DEBUG` back to `false`.
4. **Security** — a login-limiting plugin, strong passwords, and keep
   WordPress, WooCommerce and plugins updated. You are responsible for this
   now in a way you were not on Shopify.
5. **Email deliverability** — the default `wp_mail()` often lands in spam. Use
   an SMTP plugin with a real mail service so order confirmations arrive.
6. **Test one real order** with a real card, then refund it.

---

## Keeping the port in sync

While the Shopify theme in `../medzuro` is still the source of truth, do not
hand-edit generated files. Regenerate instead:

```bash
python tools/port_section.py <section> --root <selector>
python tools/export_home_content.py
python tools/export_ref_icons.py
```

Once Shopify is switched off, delete `tools/` and edit the PHP directly.
