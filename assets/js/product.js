/* Medzuro product page.

   Ported from the inline <script> in sections/medzuro-product-page.liquid.
   The gallery, quantity stepper and pack-selector behaviour are unchanged;
   what differs is what a pack selection writes into the form. Shopify posted a
   single `id` field, whereas WooCommerce needs variation_id plus one
   attribute_* field per attribute, so the radio carries them as data and this
   script copies them into the hidden inputs. */

(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    var root = document.querySelector('[data-mz-pdp]');
    if (!root) return;

    /* Gallery */
    var mainImage = root.querySelector('.mz-pdp-main-image');
    var thumbs = root.querySelectorAll('[data-mz-pdp-thumb]');

    thumbs.forEach(function (thumb) {
      thumb.addEventListener('click', function () {
        thumbs.forEach(function (item) {
          item.classList.remove('is-active');
        });
        thumb.classList.add('is-active');

        if (mainImage && thumb.dataset.image) {
          mainImage.src = thumb.dataset.image;
          mainImage.removeAttribute('srcset');
          mainImage.removeAttribute('sizes');
          mainImage.alt = thumb.dataset.alt || '';
        }
      });
    });

    /* Quantity stepper */
    var qty = root.querySelector('input[name="quantity"]');
    var minus = root.querySelector('[data-mz-pdp-minus]');
    var plus = root.querySelector('[data-mz-pdp-plus]');

    if (qty && minus && plus) {
      minus.addEventListener('click', function () {
        qty.value = Math.max(1, parseInt(qty.value || '1', 10) - 1);
      });
      plus.addEventListener('click', function () {
        qty.value = parseInt(qty.value || '1', 10) + 1;
      });
    }

    /* Pack selector */
    var price = root.querySelector('[data-mz-pdp-price]');
    var compare = root.querySelector('[data-mz-pdp-compare]');
    var save = root.querySelector('[data-mz-pdp-save]');
    var savingLine = root.querySelector('[data-mz-pdp-saving-line]');
    var addButton = root.querySelector('.mz-pdp-add');
    var variationInput = root.querySelector('[data-mz-pdp-variation]');
    var packs = root.querySelectorAll('[data-mz-pdp-pack]');

    function applyVariation(pack) {
      if (variationInput) {
        variationInput.value = pack.dataset.variation || '';
      }

      var attributes = {};
      try {
        attributes = JSON.parse(pack.dataset.attributes || '{}') || {};
      } catch (e) {
        attributes = {};
      }

      Object.keys(attributes).forEach(function (name) {
        var field = root.querySelector('[data-mz-pdp-attribute="' + name + '"]');
        if (field) field.value = attributes[name];
      });
    }

    packs.forEach(function (pack) {
      pack.addEventListener('change', function () {
        root.querySelectorAll('.mz-pdp-pack').forEach(function (item) {
          item.classList.remove('is-selected');
        });

        var label = pack.closest('.mz-pdp-pack');
        if (label) label.classList.add('is-selected');

        if (qty && pack.dataset.quantity) qty.value = pack.dataset.quantity;
        if (price && pack.dataset.price) price.textContent = pack.dataset.price;

        applyVariation(pack);

        if (compare) {
          compare.hidden = !pack.dataset.compare;
          compare.textContent = pack.dataset.compare || '';
        }

        if (save) {
          if (pack.dataset.save) {
            save.hidden = false;
            save.textContent = 'SAVE ' + pack.dataset.save;
            if (savingLine) {
              savingLine.textContent = 'You are saving ' + pack.dataset.save + ' on this order.';
            }
          } else {
            save.hidden = true;
            save.textContent = '';
            if (savingLine) {
              savingLine.textContent = 'Ask us about current bundle savings.';
            }
          }
        }

        if (addButton) {
          var available = pack.dataset.available === 'true';
          addButton.disabled = !available;

          var labelText = addButton.querySelector('span');
          if (labelText) {
            labelText.textContent = available ? 'Add To Cart' : 'Sold Out';
          }
        }
      });
    });
  });
})();
