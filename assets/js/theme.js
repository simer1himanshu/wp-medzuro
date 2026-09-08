/* Medzuro theme JS.
   Replaces the parts of the Shopify theme.js the ported markup depends on:
   the mobile nav toggle, the search toggle and the sticky header. Everything
   else in the Shopify bundle (cart AJAX, currency, wishlist, photoswipe) is
   handled by WooCommerce or a plugin. */

(function () {
  'use strict';

  var doc = document;

  function toggle(btn, targetId) {
    var target = doc.getElementById(targetId);
    if (!btn || !target) return;

    btn.addEventListener('click', function () {
      var open = btn.getAttribute('aria-expanded') === 'true';
      btn.setAttribute('aria-expanded', String(!open));
      target.hidden = open;
    });
  }

  doc.addEventListener('DOMContentLoaded', function () {
    toggle(doc.querySelector('.js-mobile-nav-toggle'), 'MobileNav');
    toggle(doc.querySelector('.js-search-toggle'), 'SearchDrawer');

    // settings.show_sticky_header was true in the Shopify build.
    var header = doc.getElementById('header');
    if (!header) return;

    var onScroll = function () {
      header.classList.toggle('is-scrolled', window.scrollY > 10);
    };

    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  });
})();
