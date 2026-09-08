<?php
/**
 * Ported from sections/medzuro-holyoak-page.liquid.
 * CSS: assets/css/holyoak-page.css (enqueue with medzuro_style( 'holyoak-page' )).
 */

defined( 'ABSPATH' ) || exit;
?>
<section id="about-holyoak" class="hk-about">
  <div class="hk-about__hero">
    <div class="hk-about__wrap hk-about__hero-grid">
      <div>
        <p class="hk-about__eyebrow">About Holyoak</p>
        <h1>Global Wellness. Now Available in Fiji.</h1>
        <p class="hk-about__intro">Holyoak is an international wellness and nutraceutical brand offering thoughtfully developed products for modern health and everyday wellbeing.</p>
        <p>At Medzuro Retail, we are bringing Holyoak closer to customers in Fiji - making selected Holyoak products available through a local Fiji-based retail experience with convenient delivery, pickup and customer support.</p>
        <div class="hk-about__actions">
          <a class="hk-about__btn" href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>">Shop Holyoak</a>
          <a class="hk-about__btn hk-about__btn--light" href="#holyoak-quality">Quality & Testing</a>
        </div>
      </div>
      <div class="hk-about__hero-card">
        <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/medzuro-home-hero.png' ); ?>" alt="Holyoak wellness products available in Fiji" loading="lazy" width="900" height="680">
        <div class="hk-about__hero-note">
          <div>
            <strong>2.4M+*</strong>
            <span>Products sold worldwide</span>
          </div>
          <div>
            <strong>30+</strong>
            <span>Countries and markets reached</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="hk-about__section">
    <div class="hk-about__wrap hk-about__split">
      <div class="hk-about__copy">
        <p class="hk-about__eyebrow">Wellness That Fits Your Life</p>
        <h2>Simple, practical wellness for everyday routines.</h2>
        <p>Modern life moves quickly. Work, travel, family and daily responsibilities can make maintaining a balanced lifestyle challenging.</p>
        <p>Holyoak is built around the idea that wellness should be simple, practical and easy to incorporate into everyday routines. From nutritional supplements to specialised wellness formulations, Holyoak offers products designed to complement different lifestyles and nutritional needs.</p>
      </div>
      <aside class="hk-about__quote">
        <span class="hk-about__quote-mark" aria-hidden="true">&ldquo;</span>
        <strong>A global wellness brand, now closer to home.</strong>
        <p>Through Medzuro Retail, customers in Fiji can access selected Holyoak products through a local platform rather than relying solely on overseas shopping.</p>
        <ul class="hk-about__quote-points">
          <li>No more overseas shipping delays</li>
          <li>A retailer that knows the local market</li>
          <li>Someone local to contact if something goes wrong</li>
        </ul>
      </aside>
    </div>
  </div>

  <div class="hk-about__section hk-about__section--soft">
    <div class="hk-about__wrap hk-about__markets">
      <div class="hk-about__stat-row">
        <div class="hk-about__stat">
          <strong>2.4M+*</strong>
          <span>Products Sold Worldwide</span>
        </div>
        <div class="hk-about__stat">
          <strong>30+</strong>
          <span>Countries & Markets Reached</span>
        </div>
      </div>
      <article class="hk-about__market-card">
        <p class="hk-about__eyebrow">Holyoak Around The World</p>
        <h2>From international markets to Fiji.</h2>
        <p>Holyoak products have reached customers across international markets, building a growing presence beyond its home market.</p>
        <div class="hk-about__market-grid" aria-label="Holyoak markets">
          <span>USA</span>
          <span>Canada</span>
          <span>UK</span>
          <span>Europe</span>
          <span>UAE</span>
          <span>Qatar</span>
          <span>South Africa</span>
          <span>Kenya</span>
          <span>Fiji</span>
        </div>
        <p class="hk-about__fineprint">*Figures and market availability are subject to the latest information provided by the brand.</p>
      </article>
    </div>
  </div>

  <div class="hk-about__section">
    <div class="hk-about__wrap">
      <div class="hk-about__head">
        <p class="hk-about__eyebrow">Designed For Modern Wellness</p>
        <h2>Products designed around the needs of today's consumers.</h2>
        <p>Holyoak's portfolio spans different areas of everyday wellness, allowing customers to choose products that fit their individual routines and nutritional goals.</p>
      </div>
      <div class="hk-about__focus-grid">
        <article class="hk-about__focus">
          <span class="hk-about__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M12 3v18"/><path d="M7 8c0-3 2-5 5-5 3 0 5 2 5 5 0 4-5 6-5 6s-5-2-5-6z"/><path d="M7 16h10"/></svg>
          </span>
          <h3>Daily Wellness</h3>
          <p>Products designed to complement everyday nutritional routines.</p>
        </article>
        <article class="hk-about__focus">
          <span class="hk-about__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M12 21a8 8 0 1 0 0-16 8 8 0 0 0 0 16z"/><path d="M12 8v5l3 2"/></svg>
          </span>
          <h3>Nutritional Support</h3>
          <p>Formulations created to provide targeted nutritional support.</p>
        </article>
        <article class="hk-about__focus">
          <span class="hk-about__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M9 3h6"/><path d="M10 3v6l-4 8a3 3 0 0 0 3 4h6a3 3 0 0 0 3-4l-4-8V3"/><path d="M8 15h8"/></svg>
          </span>
          <h3>Vitamins & Supplements</h3>
          <p>Convenient supplementation for modern lifestyles.</p>
        </article>
        <article class="hk-about__focus">
          <span class="hk-about__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M12 3l2.6 5.3 5.8.8-4.2 4.1 1 5.8-5.2-2.7L6.8 19l1-5.8-4.2-4.1 5.8-.8L12 3z"/></svg>
          </span>
          <h3>Specialised Nutrition</h3>
          <p>Selected products developed for specific nutritional requirements.</p>
        </article>
      </div>
    </div>
  </div>

  <div id="holyoak-quality" class="hk-about__section hk-about__section--cream">
    <div class="hk-about__wrap">
      <div class="hk-about__quality-grid">
        <article class="hk-about__panel">
          <div class="hk-about__panel-body">
            <span class="hk-about__signal">Quality Matters</span>
            <h2>What's inside matters.</h2>
            <p>When it comes to nutritional supplements, customers deserve confidence in the products they choose. Holyoak products are developed with attention to formulation, ingredients and product quality, while relevant product information helps customers make informed choices.</p>
            <p>At Medzuro Retail, we believe transparency is an important part of responsible wellness retail.</p>
            <ul class="hk-about__checklist">
              <li>
                <span class="hk-about__icon" aria-hidden="true">
                  <svg viewBox="0 0 24 24"><path d="M9 3h6"/><path d="M10 3v6l-4 8a3 3 0 0 0 3 4h6a3 3 0 0 0 3-4l-4-8V3"/><path d="M8 15h8"/></svg>
                </span>
                <div>
                  <strong>Careful Formulation</strong>
                  <span>Every product is developed with attention to ingredients and dosage.</span>
                </div>
              </li>
              <li>
                <span class="hk-about__icon" aria-hidden="true">
                  <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
                </span>
                <div>
                  <strong>Ingredient Transparency</strong>
                  <span>Clear information on what's inside, not just marketing claims.</span>
                </div>
              </li>
              <li>
                <span class="hk-about__icon" aria-hidden="true">
                  <svg viewBox="0 0 24 24"><path d="M12 3v18"/><path d="M7 8c0-3 2-5 5-5 3 0 5 2 5 5 0 4-5 6-5 6s-5-2-5-6z"/><path d="M7 16h10"/></svg>
                </span>
                <div>
                  <strong>Quality Checks</strong>
                  <span>Formulations are reviewed for consistency and product quality.</span>
                </div>
              </li>
              <li>
                <span class="hk-about__icon" aria-hidden="true">
                  <svg viewBox="0 0 24 24"><path d="M6 2h9l3 3v17H6z"/><path d="M9 9h6M9 13h6M9 17h4"/></svg>
                </span>
                <div>
                  <strong>Clear Product Info</strong>
                  <span>Relevant details are made available so customers can choose with confidence.</span>
                </div>
              </li>
            </ul>
          </div>
        </article>
        <article class="hk-about__panel">
          <div class="hk-about__panel-media">
            <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/medzuro-lab-trust.png' ); ?>" alt="Independent product testing and quality documentation" loading="lazy" width="720" height="430">
          </div>
          <div class="hk-about__panel-body">
            <span class="hk-about__signal">Tested For Quality</span>
            <h2>Lab tested. Proven. Trusted.</h2>
            <p>Where applicable, Holyoak products are supported by independent testing and quality documentation. Eurofins is one of the world's leading laboratory testing organisations, providing analytical testing services across a wide range of industries.</p>
            <p>Relevant testing and quality information is made available to customers wherever applicable to the product.</p>
          </div>
        </article>
      </div>
    </div>
  </div>

  <div class="hk-about__section">
    <div class="hk-about__wrap hk-about__fiji">
      <div class="hk-about__fiji-box">
        <p class="hk-about__eyebrow">Holyoak In Fiji</p>
        <h2>International wellness. Local access.</h2>
        <p>For customers in Fiji, purchasing products from overseas can mean international shipping, longer waiting periods and uncertainty about who to contact if something goes wrong.</p>
        <p>Medzuro Retail provides a different experience, giving customers a retailer that understands the local market and provides local assistance.</p>
      </div>
      <div class="hk-about__local-list">
        <div class="hk-about__local-item"><span>1</span><strong>Local ordering</strong></div>
        <div class="hk-about__local-item"><span>2</span><strong>Local pickup</strong></div>
        <div class="hk-about__local-item"><span>3</span><strong>Same-day delivery across most of Suva</strong></div>
        <div class="hk-about__local-item"><span>4</span><strong>Fiji-based customer support</strong></div>
        <div class="hk-about__local-item"><span>5</span><strong>EMS & CDP delivery options</strong></div>
      </div>
    </div>
  </div>

  <div class="hk-about__section hk-about__section--soft">
    <div class="hk-about__wrap">
      <div class="hk-about__relationship">
        <article>
          <p class="hk-about__eyebrow">Why Holyoak?</p>
          <h2>A growing choice for modern wellness.</h2>
          <p>Holyoak combines an international presence with a broad range of nutritional and wellness products designed for today's lifestyles.</p>
          <p>For Medzuro Retail, bringing Holyoak to Fiji represents an opportunity to give customers greater choice while making international wellness products more accessible locally.</p>
        </article>
        <article>
          <p class="hk-about__eyebrow">Holyoak x Medzuro Retail</p>
          <h2>Bringing the world of wellness closer to Fiji.</h2>
          <p>Holyoak is the brand. Medzuro Retail is your local connection to it.</p>
          <p>We make selected Holyoak products available through our Fiji-based platform, giving customers a simpler way to discover, purchase and receive their wellness products from browsing online to local delivery and pickup.</p>
        </article>
      </div>
    </div>
  </div>

  <div class="hk-about__cta">
    <div class="hk-about__wrap hk-about__cta-grid">
      <div>
        <p class="hk-about__eyebrow">Discover Holyoak</p>
        <h2>Find wellness products for your routine.</h2>
        <p>Explore the Holyoak range available through Medzuro Retail and discover products designed to complement different nutritional and lifestyle needs.</p>
        <div class="hk-about__cta-meta">
          <span>Shop Online</span>
          <span>Local Pickup</span>
          <span>Fiji Delivery</span>
          <span>Viber Support</span>
          <span>+679-8029-837</span>
        </div>
      </div>
      <div class="hk-about__actions">
        <a class="hk-about__btn hk-about__btn--light" href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>">Shop Holyoak</a>
      </div>
    </div>
  </div>
</section>
