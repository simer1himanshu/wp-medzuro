# DHL Express shipping workflow (how it works today, and the upgrade)

## Today (manual, already live)

1. Customer chooses **Home Delivery (DHL Express)** and pays with M-PAiSA.
   The order arrives as **Processing**.
2. Staff pack the order and book the shipment with DHL (MyDHL+ website or
   phone), which gives a **waybill number** and label.
3. Staff open the order in WooCommerce and type the waybill into the
   **DHL Express tracking** box, then save.
4. The order becomes **Shipped** and the customer is emailed the DHL tracking
   link automatically. The tracking link also shows on their order page.
5. When DHL delivers, staff set the order to **Completed**.

Nothing extra is needed from the client for this to keep working.

## Upgrade: book DHL from the order screen (needs the client's DHL account)

The plugin **Shipi - DHL Express Integration** is already installed but
switched off. Once active and connected it can:

- create the DHL shipment and print the label/waybill from the order screen,
- request the courier pickup,
- (optional) show live DHL rates at checkout. Currently delivery is free, so
  this is only needed if the client wants to charge shipping.

What the client must supply:

- DHL Express **account number** (Fiji),
- **MyDHL API** credentials (API key/username and secret) - requested from
  the DHL Express account manager or developer.dhl.com,
- shipper address and contact details, default parcel size/weight,
- decision: free delivery (current) or live DHL rates at checkout.

After that: activate the plugin on a staging copy, connect the account, book a
test shipment, then switch on live. The theme's tracking box and "Shipped"
email keep working either way.
