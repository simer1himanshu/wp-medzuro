# Medzuro — WooCommerce theme

Port of the Medzuro Shopify theme (`../medzuro`) to WordPress + WooCommerce.

## Install

For a real host, follow [DEPLOY.md](DEPLOY.md) — it covers hosting
requirements, WooCommerce setup, payments, shipping and the pre-launch
checklist. The short version:

1. Copy this folder into `wp-content/themes/` and activate it.
2. Install and activate **WooCommerce**.
3. Create the pages below and assign each a template under
   *Page Attributes → Template*:

   | Page slug | Template |
   |---|---|
   | `about` | Medzuro — About |
   | `holyoak` | Medzuro — HolyOak |
   | `lab-test-and-purity` | Medzuro — Lab Testing & Purity |
   | `contact` | Medzuro — Contact |

4. Build the menus in *Appearance → Menus* and assign them to the
   **Primary**, **Footer column 1–3**, **Footer legal links** and optionally
   **Product categories** locations.
5. In *Settings → Reading*, set the front page to a static page so
   `front-page.php` renders the homepage.

## Status

| Phase | State |
|---|---|
| 1. Data migration | N/A — no live data; enter products directly |
| 2. Theme shell | Done |
| 3. Static pages | Done (contact form still needs a plugin) |
| 4. Product page | Done |
| 5. Collection + product cards | Done |
| 6. Home, cart, checkout | Done |

## How the port works

Each Shopify section was self-contained HTML plus one scoped `<style>` block,
so `tools/port_section.py` does the mechanical part:

```bash
python tools/port_section.py medzuro-about-page --root .mz-about
python tools/port_section.py medzuro-product-page --root .mz-pdp \
    --settings-from product.json --out product-page
```

It extracts the `<style>` block, rewrites `#shopify-section-{{ section.id }}`
to a stable class, resolves `{{ s.* }}` against `config/settings_data.json`
(falling back to `{% schema %}` defaults), inlines `section.settings.*` copy,
converts `asset_url`, drops the schema, and reports any Liquid it could not
handle so the leftovers are visible rather than silent.

`--set KEY=VALUE` overrides a resolved value. This is needed where a schema
default was authored for a different colour scheme than the one configured —
the footer's defaults assume a dark background, but `bg` was overridden to
white, which left the bottom divider invisible.

There are two more generators, for content the converter could not reach:
`tools/export_home_content.py` (the homepage's 35 settings and 25 blocks, from
`templates/index.json`) and `tools/export_ref_icons.py` (14 homepage icons,
from `snippets/medzuro-ref-icon.liquid`). Both write PHP that should be
regenerated rather than hand-edited while the Shopify theme is the source of
truth.

Static sections come out complete. Logic-heavy ones come out as a starting
point: the product page's CSS (1,109 lines) and its marketing sections
converted cleanly, while the gallery and buybox were hand-written against
WooCommerce in `woocommerce/single-product.php`.

## Layout

```
functions.php            theme supports, per-section CSS enqueuing, cart fragment
header.php  footer.php   from snippets/header.liquid, sections/medzuro-footer.liquid
inc/content.php          all editable copy, in one array
inc/icons.php            inline SVG, replacing the Adorn icon font
front-page.php           the homepage
inc/content.php          site-wide copy      inc/home-content.php  homepage copy (generated)
inc/icons.php            UI icons            inc/ref-icons.php     homepage icons (generated)
inc/product.php          medzuro_field(), medzuro_pdp_packs(), medzuro_product_brand()
inc/collection.php       sort options, pagination, menu walker
inc/home.php             URL translation, carousel query
inc/cart.php             cart/checkout hooks, seller badge
woocommerce/             Woo template overrides
template-parts/          ported section markup
assets/css/              one file per section, mirroring Shopify's split
tools/                   the converter and two content generators
```

## Decisions worth knowing

**Copy is hardcoded.** WooCommerce has no equivalent of Shopify's theme
editor. Rather than scatter the strings, they live in `inc/content.php` behind
a `medzuro_content` filter, so a Customizer or ACF layer can be added later
without touching a template.

**Only the configuration in use was ported.** `header.liquid` branched on ~15
settings; `settings_data.json` shows snow, announcement bar, age check, RTL,
boxed layout and info-bar all disabled, so those branches were dropped rather
than reimplemented.

**Product fields are plain post meta.** The six Shopify metafields from
`MEDZURO-SETUP.md` are read through `medzuro_field()`, which prefers ACF's
`get_field()` when available and falls back to `get_post_meta()`. Templates do
not care whether ACF is installed. Note the product template only ever read
`subtitle` and `serving`; the other four were for a facts block that was never
assigned to the product template.

**The empty-shop fallback was kept.** The collection page rendered five demo
cards when a category had no products, and `template-parts/collection-fallback.php`
preserves that. It matters while the catalogue is empty; delete it once real
products are published.

**Brands stand in for `product.vendor`.** `medzuro_product_brand()` prefers
WooCommerce 9.4+'s native `product_brand` taxonomy, then the third-party
`pwb-brand` taxonomy, then a `medzuro_brand` meta value.

**The cart is WooCommerce's own.** The Shopify cart was 736 lines of stock
Avone with exactly one Medzuro addition: the reassurance block from
`snippets/medzuro-cart-extras.liquid`. Woo's cart template already handles
coupons, shipping calculation, quantity updates and cross-sells, so it is left
alone and the Medzuro block is hooked onto `woocommerce_cart_collaterals`.
The seller badge goes onto checkout the same way. Reimplementing Avone's cart
markup would have been days of work for no visible difference.

**The header CSS is not a port.** Everything else here is converted from a
section's own `<style>` block, but the Shopify header was Avone's, styled
across an 87KB `theme.css` against Avone's class names and ~15 setting
branches. `header.php` renders only the configuration in use with its own
markup, so `assets/css/header.css` styles that markup from the brand tokens.

**The pack selector has two modes**, as in the original: a variable product
renders up to four variations, and a single-variant product renders four
quantity bundles priced as multiples. Both are normalised by
`medzuro_pdp_packs()` so the template has one loop instead of the two branches
the Liquid carried.

## Still needs a plugin

These were enabled in Shopify but have no WooCommerce equivalent:

- **Wishlist** (`enable_wishlist`) — header markup is in place but gated
  behind `defined( 'YITH_WCWL' )`.
- **Multi-currency** (`show_multiple_currencies`) — not ported.
- **AJAX cart drawer** (`ajax_cart`) — the cart count updates via Woo's
  fragments; the slide-out drawer itself is not built.
- **Contact form** — `template-parts/contact-page.php` still contains the
  Shopify `{% form 'contact' %}` block, to be replaced with a form plugin's
  shortcode.
- **Payment icons** — Shopify generated these from `enabled_payment_types`.
  Add SVGs to `assets/img/` as `pay-visa.svg` etc.; the footer renders only the
  ones whose file exists.
