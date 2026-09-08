<?php
/**
 * Ported from sections/medzuro-about-page.liquid.
 * CSS: assets/css/about-page.css (enqueue with medzuro_style( 'about-page' )).
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="mz-about">
  <div class="mz-about__hero">
    <div class="mz-about__wrap mz-about__hero-grid">
      <div>
        <p class="mz-about__eyebrow">About Medzuro Retail</p>
        <h1>Wellness, Now Closer to Home</h1>
        <p class="mz-about__intro">Medzuro Retail is the nutraceutical and wellness business of Medzuro Wellness PTE Limited, Fiji. Established in October 2023, Medzuro began with a focus on healthcare and medical treatment, and has grown into a wider healthcare vision that includes wellness, nutrition and pharmaceuticals.</p>
        <p>Today, Medzuro operates through three dedicated businesses: Medzuro, Medzuro Retail and Medzuro Lifescience. Through Medzuro Retail, we focus on making trusted wellness products easier to access locally in Fiji.</p>
        <div class="mz-about__hero-actions">
          <a class="mz-about__btn" href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>">Shop Medzuro Retail</a>
          <a class="mz-about__btn mz-about__btn--light" href="#discover-medzuro">Discover Medzuro</a>
        </div>
      </div>

      <div class="mz-about__visual" aria-label="Medzuro wellness products">
        <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/medzuro-home-hero.png' ); ?>" alt="Medzuro wellness products in Fiji" loading="eager" width="900" height="700">
        <div class="mz-about__visual-band" aria-hidden="true">
          <div class="mz-about__stat-chip">
            <strong>2023</strong>
            <span>Established in Fiji</span>
          </div>
          <div class="mz-about__stat-chip">
            <strong>3</strong>
            <span>Healthcare businesses</span>
          </div>
          <div class="mz-about__stat-chip">
            <strong>Local</strong>
            <span>Ordering, pickup and support</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="mz-about__section">
    <div class="mz-about__wrap mz-about__split">
      <div class="mz-about__copy">
        <p class="mz-about__eyebrow">Our Story</p>
        <h2>From Healthcare to Everyday Wellness</h2>
        <p>Medzuro started in October 2023 with a focus on healthcare and medical treatment. As the business developed, we saw that people's health needs do not begin and end with clinical care. Everyday wellness, nutrition and preventive choices also matter.</p>
        <p>That understanding led to the creation of Medzuro Retail: a local platform for nutraceuticals, supplements and wellness products. Our goal is simple - to bring selected, established brands closer to customers in Fiji, with the convenience of local shopping.</p>
      </div>
      <div class="mz-about__timeline">
        <div class="mz-about__time">
          <strong>October 2023</strong>
          <p>Medzuro begins with a healthcare and medical treatment focus in Fiji.</p>
        </div>
        <div class="mz-about__time">
          <strong>Next Step</strong>
          <p>Medzuro Retail is created to make nutraceuticals and wellness products easier to buy locally.</p>
        </div>
        <div class="mz-about__time">
          <strong>Today</strong>
          <p>Medzuro continues growing across healthcare, wellness and life sciences through three focused businesses.</p>
        </div>
      </div>
    </div>
  </div>

  <div class="mz-about__section mz-about__section--soft">
    <div class="mz-about__wrap">
      <div class="mz-about__section-head">
        <p class="mz-about__eyebrow">The Medzuro Group</p>
        <h2>Three Businesses. One Growing Healthcare Vision.</h2>
        <p>Medzuro Retail is one part of a broader Medzuro healthcare ecosystem built around access, quality and long-term service.</p>
      </div>
      <div class="mz-about__cards">
        <article class="mz-about__card">
          <span class="mz-about__card-label">Medical Treatment & Healthcare</span>
          <h3>Medzuro</h3>
          <p>Focused on medical treatment and healthcare services as the foundation of the Medzuro vision.</p>
          <a class="mz-about__link" href="https://medzuro.com">Visit Medzuro &rarr;</a>
        </article>
        <article class="mz-about__card mz-about__card--accent">
          <span class="mz-about__card-label">Nutraceuticals & Wellness</span>
          <h3>Medzuro Retail</h3>
          <p>A local destination for supplements, wellness products, delivery, pickup and Fiji-based support.</p>
          <a class="mz-about__link" href="/">Visit Medzuro Retail &rarr;</a>
        </article>
        <article class="mz-about__card">
          <span class="mz-about__card-label">Pharmaceuticals & Life Sciences</span>
          <h3>Medzuro Lifescience</h3>
          <p>Focused on pharmaceuticals and life sciences as Medzuro expands its healthcare capabilities.</p>
          <a class="mz-about__link" href="https://medzurolifescience.com">Visit Medzuro Lifescience &rarr;</a>
        </article>
      </div>
    </div>
  </div>

  <div class="mz-about__section">
    <div class="mz-about__wrap">
      <div class="mz-about__section-head">
        <p class="mz-about__eyebrow">Why Medzuro Retail</p>
        <h2>Making Wellness Shopping Simpler</h2>
        <p>We started Medzuro Retail to make buying wellness products less complicated for customers in Fiji.</p>
      </div>
      <div class="mz-about__feature-grid">
        <article class="mz-about__feature">
          <span class="mz-about__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M12 21s7-5.3 7-12a7 7 0 0 0-14 0c0 6.7 7 12 7 12z"/><circle cx="12" cy="9" r="2.5"/></svg>
          </span>
          <h3>Local Access</h3>
          <p>Selected wellness products available locally, without overseas ordering guesswork.</p>
        </article>
        <article class="mz-about__feature">
          <span class="mz-about__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M12 3l7 3v5c0 4.8-3 8.4-7 10-4-1.6-7-5.2-7-10V6l7-3z"/><path d="M9 12l2 2 4-5"/></svg>
          </span>
          <h3>Genuine Products</h3>
          <p>We focus on authentic products from established brands customers can choose confidently.</p>
        </article>
        <article class="mz-about__feature">
          <span class="mz-about__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M3 7h11v9H3z"/><path d="M14 10h4l3 3v3h-7z"/><circle cx="7" cy="18" r="2"/><circle cx="18" cy="18" r="2"/></svg>
          </span>
          <h3>Convenient Delivery</h3>
          <p>Order from Fiji with local delivery options designed around everyday customers.</p>
        </article>
        <article class="mz-about__feature">
          <span class="mz-about__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M5 10h14l-1 10H6L5 10z"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
          </span>
          <h3>Local Pickup</h3>
          <p>Pickup support helps make shopping practical for customers who prefer collection.</p>
        </article>
        <article class="mz-about__feature">
          <span class="mz-about__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M4 12a8 8 0 0 1 16 0"/><path d="M4 12v4a2 2 0 0 0 2 2h1v-6H4z"/><path d="M20 12v4a2 2 0 0 1-2 2h-1v-6h3z"/><path d="M9 20h3"/></svg>
          </span>
          <h3>Fiji-Based Support</h3>
          <p>Our local team is here to help customers choose and order with more clarity.</p>
        </article>
      </div>
    </div>
  </div>

  <div class="mz-about__section mz-about__section--warm">
    <div class="mz-about__wrap">
      <div class="mz-about__brand-quality">
        <article class="mz-about__panel mz-about__panel--dark">
          <div class="mz-about__panel-body">
            <p class="mz-about__eyebrow">Our Brands</p>
            <h2>Brands We Bring Closer to Fiji</h2>
            <p>Medzuro Retail focuses on established wellness and nutraceutical brands. We select products that are relevant for our customers, with attention to brand standards, market presence and product information.</p>
            <p>Holyoak is one of the key wellness brands available through Medzuro Retail, and our range will continue to grow carefully over time.</p>
            <a class="mz-about__link" href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>">Explore our brands &rarr;</a>
          </div>
          <div class="mz-about__panel-logo">
            <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/medzuro-logo.png' ); ?>" alt="Medzuro Retail logo" loading="lazy" width="520" height="220">
          </div>
        </article>

        <article class="mz-about__panel">
          <div class="mz-about__panel-media">
            <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/medzuro-lab-trust.png' ); ?>" alt="Laboratory quality testing for wellness products" loading="lazy" width="700" height="420">
          </div>
          <div class="mz-about__panel-body">
            <p class="mz-about__eyebrow">Quality & Transparency</p>
            <h2>Know What You're Choosing</h2>
            <p>We believe customers should understand what they are buying. Medzuro Retail aims to provide clear product information, authentic products and quality documentation where available.</p>
            <p>For us, quality is not only about the product. It is also about giving customers enough information to choose responsibly.</p>
          </div>
        </article>

        <article class="mz-about__panel">
          <div class="mz-about__panel-body">
            <p class="mz-about__eyebrow">Built for Fiji</p>
            <h2>Local Access, Designed Around Our Customers</h2>
            <p>Buying wellness products from international websites can be difficult for customers in Fiji. Product sourcing, delivery timing and customer support are not always simple.</p>
            <p>Medzuro Retail was built to change that through a local Fiji presence, local ordering, delivery and pickup options, and a team customers can contact.</p>
          </div>
        </article>
      </div>
    </div>
  </div>

  <div class="mz-about__section">
    <div class="mz-about__wrap">
      <div class="mz-about__section-head">
        <p class="mz-about__eyebrow">Our Approach</p>
        <h2>Simple Principles. Long-Term Vision.</h2>
      </div>
      <div class="mz-about__principles">
        <div class="mz-about__principle">
          <strong>People</strong>
          <span>We keep customers and communities at the centre of our decisions.</span>
        </div>
        <div class="mz-about__principle">
          <strong>Trust</strong>
          <span>We build confidence through authenticity, clarity and responsible service.</span>
        </div>
        <div class="mz-about__principle">
          <strong>Quality</strong>
          <span>We work with established products and keep standards visible.</span>
        </div>
        <div class="mz-about__principle">
          <strong>Convenience</strong>
          <span>We make ordering, pickup and delivery easier for Fiji customers.</span>
        </div>
        <div class="mz-about__principle">
          <strong>Responsibility</strong>
          <span>We support informed wellness decisions without overclaiming.</span>
        </div>
      </div>
    </div>
  </div>

  <div class="mz-about__section mz-about__section--soft">
    <div class="mz-about__wrap">
      <div class="mz-about__vision-grid">
        <article class="mz-about__vision">
          <p class="mz-about__eyebrow">Looking Ahead</p>
          <h2>Building Something for the Long Term</h2>
          <p>Medzuro is still a young business, but our direction is clear. Since starting in October 2023, we have continued to grow across healthcare, wellness and life sciences.</p>
          <p>Through Medzuro Retail, we plan to expand our product range, work with trusted brands and keep improving access to wellness products for customers in Fiji.</p>
        </article>
        <article class="mz-about__vision">
          <p class="mz-about__eyebrow">Our Vision</p>
          <h2>A More Accessible Healthcare & Wellness Future</h2>
          <p>We believe healthcare is becoming more connected. Treatment, nutrition, wellness products and pharmaceuticals all play different but important roles in supporting people.</p>
          <p>Medzuro's vision is to develop these capabilities while keeping people, accessibility and responsible healthcare at the centre.</p>
        </article>
      </div>
    </div>
  </div>

  <div id="discover-medzuro" class="mz-about__dark-section">
    <div class="mz-about__wrap">
      <div class="mz-about__section-head">
        <p class="mz-about__eyebrow">Discover Medzuro</p>
        <h2>One Medzuro ecosystem, three focused destinations.</h2>
      </div>
      <div class="mz-about__discover">
        <article class="mz-about__discover-card">
          <h3>Medical Treatment</h3>
          <p>Explore Medzuro's healthcare and medical treatment services.</p>
          <a class="mz-about__link" href="https://medzuro.com">Visit Medzuro &rarr;</a>
        </article>
        <article class="mz-about__discover-card">
          <h3>Wellness & Supplements</h3>
          <p>Shop Medzuro Retail for nutraceuticals, supplements and wellness products.</p>
          <a class="mz-about__link" href="/">Visit Medzuro Retail &rarr;</a>
        </article>
        <article class="mz-about__discover-card">
          <h3>Pharmaceuticals & Life Sciences</h3>
          <p>Learn more about Medzuro Lifescience and the broader healthcare vision.</p>
          <a class="mz-about__link" href="https://medzurolifescience.com">Visit Medzuro Lifescience &rarr;</a>
        </article>
      </div>
    </div>
  </div>

  <div class="mz-about__final">
    <div class="mz-about__wrap">
      <div class="mz-about__final-box">
        <div>
          <p class="mz-about__eyebrow">Medzuro Retail</p>
          <h2>Your local destination for wellness products.</h2>
          <p>Shop locally. Choose confidently.</p>
          <div class="mz-about__final-meta">
            <span>Established 2023</span>
            <span>Medzuro Wellness PTE Limited, Fiji</span>
            <span>Nutraceuticals | Supplements | Wellness</span>
          </div>
        </div>
        <div class="mz-about__cta-actions">
          <a class="mz-about__btn mz-about__btn--light" href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>">Shop Medzuro Retail</a>
        </div>
      </div>
    </div>
  </div>
</section>
