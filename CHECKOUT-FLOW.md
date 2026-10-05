# Checkout flow (delivery or pickup)

Built to the client's "Initial Flow" and "Home Delivery" screens.

| Step | Where |
|---|---|
| Cart: "Choose how you want to receive" cards | `woocommerce/cart/cart-shipping.php` |
| Checkout stepper: Delivery → Details → Payment | `woocommerce/checkout/form-checkout.php`, `assets/js/checkout-steps.js` |
| Fiji fields, +679 mobile, contact preference, payment rules | `inc/checkout-flow.php` |
| DHL Express / Store Pickup rates | `inc/delivery.php` |
| Pickup options: full, 10% deposit, reserve without payment | `inc/checkout-flow.php`, `inc/order-flow.php` |
| "Payment successful!" page | `woocommerce/checkout/thankyou.php` |
| Statuses, DHL tracking, emails, progress timeline | `inc/order-flow.php` |

## How it works

- The cart and checkout pages are rendered with WooCommerce's **classic**
  shortcodes through `woocommerce/mz-page-shell.php`, whatever blocks the
  pages contain. The block checkout has no multi-step mode and ignores the
  classic field hooks, so it can't do this flow. Delete the
  `template_include` filter in `inc/checkout-flow.php` to go back to blocks.
- Only two rates are ever shown: **Home Delivery (DHL Express)**, free, and
  **Store Pickup**, where the customer picks one of two Suva stores
  (Nakasi or Princes Road; see `medzuro_pickup_stores()` in `inc/delivery.php`).
  The choice is saved on the order as `_mz_pickup_store`. Other zone rates (Free shipping, Flat rate)
  are hidden. To change cost, delivery time or pickup address, add
  "Medzuro Delivery / Pickup" to the Fiji zone in
  *WooCommerce → Settings → Shipping*. Otherwise the defaults are used.
- **Home delivery** is M-PAiSA only.
- **Pickup** offers:
  - **Full payment:** M-PAiSA for the whole amount.
  - **10% to reserve:** M-PAiSA charges 10% and the order becomes
    *Deposit paid*. The balance is collected in store.
  - **Reserve without payment:** the order becomes *Reserved*. This is not
    guaranteed and nothing is charged.

## Pickup store details

The "Select pickup location" step lists both stores as radio options, each
with its name, address, opening hours and Google Maps link:

- **Medzuro Retail - Nakasi**: 18, Valili Street, Vishnu Deo Road, Nakasi, Suva
- **Medzuro Retail - Princes Road**: 204, Princes Road, Suva, Fiji

The stores are defined in `medzuro_pickup_stores()` in `inc/delivery.php`
(filterable with `medzuro_pickup_stores`). Opening hours come from the
"Medzuro Delivery / Pickup" method settings when it is in the Fiji zone.
The chosen store is shown in the order summary, on the thank-you page, in the
confirmation and ready-for-pickup emails, and in the admin follow-up box.
Add a photo as `assets/img/pickup-store-nakasi.jpg` or
`assets/img/pickup-store-princes-road.jpg` to show it on that step.

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
| Pickup | When packed, set *Ready for pickup*. The customer is emailed, with any balance due. Set *Completed* when collected, after taking the balance. |

Customers see a progress timeline on the thank-you page and in
*My Account → Orders* (Placed → Paid → Shipped/Ready → Delivered/Collected).

## DHL logo

A plain "DHL Express" text badge is shown. If the client has DHL's official
artwork, add it as `assets/img/dhl-express.svg` (or `.png`) and it replaces
the badge everywhere.

## Not included

- MyCash (Digicel), parked for now.
- The design's step 4 ("Open M-PAiSA, enter merchant number…") describes a
  manual flow. The live integration sends the customer to Vodafone's hosted
  M-PAiSA page instead, which confirms with their number + PIN and returns
  them to the thank-you page, so that screen isn't needed.
