# Checkout flow (delivery or pickup)

Built to the client's "Initial Flow" and "Home Delivery" screens.

| Step | Where |
|---|---|
| Cart: "Choose how you want to receive" cards | `woocommerce/cart/cart-shipping.php` |
| Checkout stepper: Delivery → Details → Payment | `woocommerce/checkout/form-checkout.php`, `assets/js/checkout-steps.js` |
| Fiji fields, +679 mobile, contact preference, payment rules | `inc/checkout-flow.php` |
| DHL Express / Store Pickup rates | `inc/delivery.php` |
| Pickup options: pay at pickup (default) or pay online now | `inc/checkout-flow.php`, `inc/order-flow.php` |
| "Payment successful!" page | `woocommerce/checkout/thankyou.php` |
| Statuses, DHL tracking, emails, progress timeline | `inc/order-flow.php` |

## How it works

- The cart and checkout pages are rendered with WooCommerce's **classic**
  shortcodes through `woocommerce/mz-page-shell.php`, whatever blocks the
  pages contain. The block checkout has no multi-step mode and ignores the
  classic field hooks, so it can't do this flow. Delete the
  `template_include` filter in `inc/checkout-flow.php` to go back to blocks.
- Only two rates are ever shown: **Home Delivery (DHL Express)**, free, and
  **Store Pickup**, where the customer picks one of the locations set up in
  *WooCommerce → Settings → Shipping → Local pickup* (see
  `medzuro_wc_pickup_locations()` in `inc/delivery.php`).
  The choice is saved on the order as `_mz_pickup_store`. Other zone rates (Free shipping, Flat rate)
  are hidden. To change cost, delivery time or pickup address, add
  "Medzuro Delivery / Pickup" to the Fiji zone in
  *WooCommerce → Settings → Shipping*. Otherwise the defaults are used.
- **Home delivery** is M-PAiSA only.
- **Pickup is pay at the store only**: nothing is charged online, the order
  becomes *Reserved* and is paid when collected (gateway `medzuro_reserve`).
  The "How would you like to pay?" step is skipped. Online payment and the
  old 10% deposit can be offered again with the
  `medzuro_pickup_payment_offered` filter (see `medzuro_pickup_payment_choices()`).
- **Cash** (`medzuro_cash`) is a staff-only payment method: it appears when
  creating/editing orders (wp-admin and the Shop Manager dashboard) and on
  PDF invoices, never at checkout.

## Pickup locations

Managed in *WooCommerce → Settings → Shipping → Local pickup*. Every enabled
location is offered at checkout and listed on the **Pickup Locations** page
(page slug `pickup-locations`, template `page-pickup-locations.php`, or the
`[medzuro_pickup_locations]` shortcode anywhere). A location's "Pickup
details" text is shown as its opening hours; empty uses the default hours.
Locations whose name contains "Nakasi" / "Princes" keep the ids `nakasi` /
`princes-road` so older orders still match. Add a photo as
`assets/img/pickup-store-{id}.jpg` to show it.

The two WPCode snippets "Pickup locations from WooCommerce settings" did the
same job before and can be switched off once this theme version is live.

## Running orders (staff)

Every order has an **Order follow-up** box beside it in the admin, with:

- the payment status (Payment pending / 10% paid / Paid)
- customer details
- order type and payment option
- buttons: Contact customer (Viber), Call, Send email, Mark as confirmed
  (reserve orders only) and Cancel order

| Order | What to do |
|---|---|
| Home delivery | Paid orders arrive as *Processing*. Type the DHL waybill into the **DHL Express tracking** box on the order and save. The order becomes *Shipped* and the customer is emailed the tracking link. Set *Completed* when delivered. |
| Pickup | When packed, set *Ready for pickup*. The customer is emailed, with any balance due. When the customer collects and pays, click **Cash received - mark collected** in the follow-up box: the payment is recorded as Cash, the paid date is set and the order is Completed. |
| Walk-in / phone order | Create the order (wp-admin or Shop Manager), choose payment **Cash** and status *Completed* (paid now) or *Ready for pickup* (paid on collection). |

Customers see a progress timeline on the thank-you page and in
*My Account → Orders* (Placed → Paid → Shipped/Ready → Delivered/Collected).

## DHL logo

A plain "DHL Express" text badge is shown. If the client has DHL's official
artwork, add it as `assets/img/dhl-express.svg` (or `.png`) and it replaces
the badge everywhere.

## Not included

- MyCash (Digicel), waiting on the client's merchant account and API documentation.
- The design's step 4 ("Open M-PAiSA, enter merchant number…") describes a
  manual flow. The live integration sends the customer to Vodafone's hosted
  M-PAiSA page instead, which confirms with their number + PIN and returns
  them to the thank-you page, so that screen isn't needed.

## Pickups by store (staff)

*WooCommerce → Orders* shows a "Pickups waiting to be collected" box with a
count per store (Reserved, Reservation confirmed, Ready for pickup, Deposit
paid), a **Pickup store** column and an "All pickup stores" filter
(`inc/pickup-admin.php`). A location's "Pickup details" text is shown as its
opening hours only when it looks like hours (a time or weekday); otherwise the
default opening hours from the delivery settings are used.
