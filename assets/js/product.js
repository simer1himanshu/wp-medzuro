/* Medzuro product page.

   Gallery, quantity stepper, share button and option cards. The option cards
   do not submit anything themselves: they set WooCommerce's hidden attribute
   <select>, and Woo's variation script resolves variation_id from it. */

(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    var root = document.querySelector('[data-mz-pdp]');
    if (!root) return;

    /* Gallery */
    var mainImage = root.querySelector('.mz-pdp-main-image');
    var thumbs = Array.prototype.slice.call(root.querySelectorAll('[data-mz-pdp-thumb]'));
    var thumbList = root.querySelector('[data-mz-pdp-thumbs]');
    var current = 0;

    function show(index) {
      if (!thumbs.length) return;
      current = (index + thumbs.length) % thumbs.length;
      var thumb = thumbs[current];

      thumbs.forEach(function (item) {
        item.classList.remove('is-active');
        item.removeAttribute('aria-current');
      });
      thumb.classList.add('is-active');
      thumb.setAttribute('aria-current', 'true');

      if (mainImage && thumb.dataset.image) {
        mainImage.src = thumb.dataset.image;
        mainImage.removeAttribute('srcset');
        mainImage.removeAttribute('sizes');
        mainImage.alt = thumb.dataset.alt || '';
      }

      if (thumbList) {
        thumbList.scrollTo({
          top: thumb.offsetTop - (thumbList.clientHeight - thumb.offsetHeight) / 2,
          left: thumb.offsetLeft - (thumbList.clientWidth - thumb.offsetWidth) / 2,
          behavior: 'smooth'
        });
      }
    }

    thumbs.forEach(function (thumb, index) {
      thumb.addEventListener('click', function () {
        show(index);
      });
    });

    function on(selector, handler) {
      var el = root.querySelector(selector);
      if (el) el.addEventListener('click', handler);
    }

    on('[data-mz-pdp-prev]', function () { show(current - 1); });
    on('[data-mz-pdp-next]', function () { show(current + 1); });
    on('[data-mz-pdp-thumbs-prev]', function () { show(current - 1); });
    on('[data-mz-pdp-thumbs-next]', function () { show(current + 1); });

    /* Quantity stepper */
    var qty = root.querySelector('.mz-pdp-purchase input.qty');

    function step(delta) {
      if (!qty) return;
      var min = parseFloat(qty.min) || 1;
      var max = parseFloat(qty.max) || Infinity;
      var next = (parseFloat(qty.value) || min) + delta;
      qty.value = Math.min(max, Math.max(min, next));
      qty.dispatchEvent(new Event('change', { bubbles: true }));
    }

    on('[data-mz-pdp-minus]', function () { step(-1); });
    on('[data-mz-pdp-plus]', function () { step(1); });

    /* Share */
    var share = root.querySelector('[data-mz-pdp-share]');
    if (share) {
      share.addEventListener('click', function () {
        var data = { title: share.dataset.title, url: share.dataset.url };

        if (navigator.share) {
          navigator.share(data).catch(function () {});
          return;
        }

        if (navigator.clipboard) {
          navigator.clipboard.writeText(data.url).then(function () {
            var done = share.querySelector('[data-mz-pdp-share-done]');
            if (!done) return;
            done.hidden = false;
            setTimeout(function () { done.hidden = true; }, 2000);
          });
        }
      });
    }

    /* Option cards */
    var price = root.querySelector('[data-mz-pdp-price]');
    var compare = root.querySelector('[data-mz-pdp-compare]');
    var saveLine = root.querySelector('[data-mz-pdp-save-line]');
    var save = root.querySelector('[data-mz-pdp-save]');
    var discount = root.querySelector('[data-mz-pdp-discount]');
    var badge = root.querySelector('[data-mz-pdp-badge]');
    var stock = root.querySelector('[data-mz-pdp-stock]');

    root.querySelectorAll('[data-mz-pdp-option]').forEach(function (option) {
      option.addEventListener('change', function () {
        root.querySelectorAll('.mz-pdp-option').forEach(function (card) {
          card.classList.remove('is-selected');
        });
        option.closest('.mz-pdp-option').classList.add('is-selected');

        var hasDiscount = parseInt(option.dataset.discount, 10) > 0;
        var available = option.dataset.available === 'true';

        if (price) price.textContent = option.dataset.price;
        if (compare) {
          compare.textContent = option.dataset.compare;
          compare.hidden = !hasDiscount;
        }
        if (save) save.textContent = option.dataset.save;
        if (discount) discount.textContent = option.dataset.discount;
        if (saveLine) saveLine.hidden = !hasDiscount;
        if (badge) {
          badge.textContent = '-' + option.dataset.discount + '%';
          badge.hidden = !hasDiscount;
        }
        if (stock) {
          stock.textContent = available ? stock.dataset.in : stock.dataset.out;
          stock.className = available ? 'is-in-stock' : 'is-out-of-stock';
        }

        var select = root.querySelector('.mz-pdp-purchase select[name="' + option.dataset.attribute + '"]');
        if (!select) return;

        select.value = option.value;
        // Woo's variation form listens through jQuery.
        if (window.jQuery) {
          window.jQuery(select).trigger('change');
        } else {
          select.dispatchEvent(new Event('change', { bubbles: true }));
        }
      });
    });

    /*
     * Keep the product URL as a GET entry in browser history. A normal
     * WooCommerce form POST followed by a cart redirect can leave Chrome with
     * a POST entry, which triggers ERR_CACHE_MISS when the customer presses
     * Back from the cart.
     */
    var cartForm = root.querySelector('.mz-pdp-purchase form.cart:not(.grouped_form)');
    var config = window.medzuroPdp || {};

    if (cartForm && config.addToCartUrl && config.cartUrl) {
      cartForm.addEventListener('submit', function (event) {
        var submitButton = cartForm.querySelector('.single_add_to_cart_button');
        var pressedButton = event.submitter || document.activeElement;
        var buyNow = pressedButton && pressedButton.classList.contains('mz-pdp-buy-now');

        if (submitButton && (submitButton.disabled || submitButton.classList.contains('disabled'))) {
          return;
        }

        if (typeof cartForm.checkValidity === 'function' && !cartForm.checkValidity()) {
          return;
        }

        event.preventDefault();

        var data = new FormData(cartForm);
        var addToCart = data.get('add-to-cart') || (submitButton && submitButton.value);

        if (addToCart && !data.get('product_id')) {
          data.set('product_id', addToCart);
        }
        if (buyNow) data.set('medzuro_buy_now', '1');

        if (submitButton) {
          submitButton.disabled = true;
          submitButton.classList.add('is-loading');
          submitButton.setAttribute('aria-busy', 'true');
        }
        if (pressedButton && pressedButton !== submitButton) {
          pressedButton.disabled = true;
          pressedButton.classList.add('is-loading');
          pressedButton.setAttribute('aria-busy', 'true');
        }

        fetch(config.addToCartUrl, {
          method: 'POST',
          body: data,
          credentials: 'same-origin',
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
          .then(function (response) {
            if (!response.ok) throw new Error('Add to cart request failed');
            return response.json();
          })
          .then(function (response) {
            if (response && response.error) {
              throw new Error('Product validation failed');
            }

            window.location.assign(buyNow && config.checkoutUrl ? config.checkoutUrl : config.cartUrl);
          })
          .catch(function () {
            if (submitButton) {
              submitButton.disabled = false;
              submitButton.classList.remove('is-loading');
              submitButton.removeAttribute('aria-busy');
            }
            if (pressedButton && pressedButton !== submitButton) {
              pressedButton.disabled = false;
              pressedButton.classList.remove('is-loading');
              pressedButton.removeAttribute('aria-busy');
            }

            var existing = cartForm.querySelector('.mz-pdp-form-error');
            if (!existing) {
              existing = document.createElement('p');
              existing.className = 'mz-pdp-form-error';
              existing.setAttribute('role', 'alert');
              cartForm.appendChild(existing);
            }
            existing.textContent = config.errorText || 'We could not add this item. Please try again.';
          });
      });
    }
  });
})();
