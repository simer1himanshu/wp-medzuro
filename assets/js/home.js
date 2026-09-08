/* Medzuro homepage.

   Ported from the inline <script> in sections/medzuro-reference-home.liquid.
   Two horizontal rails — best sellers and reviews — each scrolled one card at
   a time by its own pair of arrows. Unchanged apart from the root selector,
   which was scoped to the Shopify section id. */

(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    var root = document.querySelector('.mz-home-ref');
    if (!root) return;

    function bindScroller(viewSelector, prevSelector, nextSelector) {
      var viewport = root.querySelector(viewSelector);
      var prev = root.querySelector(prevSelector);
      var next = root.querySelector(nextSelector);
      if (!viewport || !prev || !next) return;

      function scrollByCard(dir) {
        var card = viewport.querySelector('li');
        var distance = card
          ? card.getBoundingClientRect().width + 16
          : viewport.clientWidth * 0.8;

        viewport.scrollBy({ left: distance * dir, behavior: 'smooth' });
      }

      prev.addEventListener('click', function () {
        scrollByCard(-1);
      });
      next.addEventListener('click', function () {
        scrollByCard(1);
      });
    }

    bindScroller('[data-mz-home-products]', '[data-mz-home-prev]', '[data-mz-home-next]');
    bindScroller('[data-mz-reviews]', '[data-mz-reviews-prev]', '[data-mz-reviews-next]');
  });
})();
