<?php
/**
 * Ported from sections/medzuro-lab-purity-page.liquid.
 * CSS: assets/css/lab-purity-page.css (enqueue with medzuro_style( 'lab-purity-page' )).
 */

defined( 'ABSPATH' ) || exit;
?>
<section id="lab-test-and-purity" class="lt-page">
  <div class="lt-page__hero">
    <div class="lt-page__wrap lt-page__hero-grid">
      <div class="lt-page__hero-copy">
        <p class="lt-page__eyebrow">Lab Tested. Quality You Can Trust.</p>
        <h1>Lab Tested.<br><span class="lt-page__gold">Pure. Safe. Trusted.</span></h1>
        <p>At Medzuro Retail, your health and safety come first. That's why Holyoak products are batch tested by Eurofins, a global leader in independent laboratory testing.</p>

        <div class="lt-page__trust-row">
          <div class="lt-page__trust">
            <span class="lt-page__circle" aria-hidden="true">
              <svg viewBox="0 0 24 24"><path d="M12 3l7 3v5c0 4.8-3 8.4-7 10-4-1.6-7-5.2-7-10V6l7-3z"/><path d="M9 12l2 2 4-5"/></svg>
            </span>
            <strong>Independently Tested</strong>
          </div>
          <div class="lt-page__trust">
            <span class="lt-page__circle" aria-hidden="true">
              <svg viewBox="0 0 24 24"><path d="M9 3h6"/><path d="M10 3v6l-5 9a2 2 0 0 0 2 3h10a2 2 0 0 0 2-3l-5-9V3"/><path d="M8 16h8"/></svg>
            </span>
            <strong>Scientifically Proven</strong>
          </div>
          <div class="lt-page__trust">
            <span class="lt-page__circle" aria-hidden="true">
              <svg viewBox="0 0 24 24"><path d="M21 12a9 9 0 1 1-3-6.7"/><path d="M9 12l2 2 9-9"/></svg>
            </span>
            <strong>Quality You Can Trust</strong>
          </div>
        </div>
      </div>

      <div class="lt-page__brandline" aria-label="Holyoak and Eurofins">
        <div class="lt-page__holy">
          <span class="lt-page__holy-mark" aria-hidden="true">
            <svg viewBox="0 0 48 48"><path d="M24 8c5 5 7 10 7 15 0 7-7 12-7 12s-7-5-7-12c0-5 2-10 7-15z"/><path d="M24 8v31"/><path d="M13 17c-4 5-4 11 0 16 5 6 11 6 11 6"/><path d="M35 17c4 5 4 11 0 16-5 6-11 6-11 6"/></svg>
          </span>
          <span><strong>Holyoak</strong><small>Beyond Nutrition</small></span>
        </div>
        <span class="lt-page__brand-sep" aria-hidden="true"></span>
        <div class="lt-page__euro">
          <span class="lt-page__euro-dots" aria-hidden="true">
            <span></span><span></span><span></span><span></span>
            <span></span><span></span><span></span><span></span>
          </span>
          <strong>eurofins</strong>
        </div>
      </div>
    </div>
  </div>

  <div class="lt-page__euro-band">
    <div class="lt-page__wrap lt-page__euro-grid">
      <div class="lt-page__euro-copy">
        <p class="lt-page__eyebrow">About Eurofins</p>
        <h2><span>About</span> Eurofins</h2>
        <div class="lt-page__underline" aria-hidden="true"></div>
        <p>Eurofins is a globally recognised laboratory testing organisation with a network of laboratories in more than 50 countries. It provides scientific testing and analytical services across industries including food, supplements, pharmaceuticals, environmental and more.</p>
        <p>With advanced technology, strict quality standards and a team of experienced scientists, Eurofins helps ensure products meet high levels of quality, safety and purity.</p>
      </div>

      <div class="lt-page__facility" aria-label="Eurofins laboratory facility exterior">
        <img class="lt-page__facility-photo" src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/medzuro-eurofins-building.png' ); ?>" alt="Modern laboratory testing facility exterior" loading="lazy" width="1792" height="1024">
        <div class="lt-page__facility-badge">
          <span class="lt-page__euro-dots" aria-hidden="true">
            <span></span><span></span><span></span><span></span>
            <span></span><span></span><span></span><span></span>
          </span>
          <strong>eurofins</strong>
        </div>
      </div>
    </div>
  </div>

  <div class="lt-page__checks">
    <div class="lt-page__wrap">
      <div class="lt-page__head">
        <h2>What Does Lab Testing Check?</h2>
        <p>Depending on the product, laboratory analysis may include:</p>
      </div>

      <div class="lt-page__check-grid">
        <article class="lt-page__check">
          <span class="lt-page__circle" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M12 3l7 3v5c0 4.8-3 8.4-7 10-4-1.6-7-5.2-7-10V6l7-3z"/><path d="M9 12l2 2 4-5"/></svg>
          </span>
          <h3>Purity</h3>
          <p>Testing helps confirm the purity and quality of ingredients and finished products.</p>
        </article>
        <article class="lt-page__check">
          <span class="lt-page__circle" aria-hidden="true">
            <svg viewBox="0 0 24 24"><circle cx="6" cy="7" r="2"/><circle cx="18" cy="6" r="2"/><circle cx="16" cy="18" r="2"/><circle cx="7" cy="17" r="2"/><path d="M8 8l6 7"/><path d="M16 8l-8 7"/><path d="M9 17h5"/></svg>
          </span>
          <h3>Composition</h3>
          <p>Testing helps verify the nutritional composition and active ingredients.</p>
        </article>
        <article class="lt-page__check">
          <span class="lt-page__circle" aria-hidden="true">
            <svg viewBox="0 0 24 24"><circle cx="10" cy="10" r="6"/><path d="M15 15l5 5"/></svg>
          </span>
          <h3>Contaminants</h3>
          <p>Testing helps identify unwanted substances and contaminants, such as heavy metals, pesticides and microorganisms.</p>
        </article>
        <article class="lt-page__check">
          <span class="lt-page__circle" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M20 4c-7 1-12 5-14 12 6-1 11-5 14-12z"/><path d="M6 16c-1 2-2 3-3 4"/><path d="M9 14l5-5"/></svg>
          </span>
          <h3>Authenticity</h3>
          <p>Testing helps verify ingredient identity and detect potential adulteration.</p>
        </article>
        <article class="lt-page__check">
          <span class="lt-page__circle" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M5 20V10"/><path d="M11 20V5"/><path d="M17 20v-8"/><path d="M3 20h18"/></svg>
          </span>
          <h3>Quality Assurance</h3>
          <p>Every batch is tested to help ensure consistent quality and performance.</p>
        </article>
      </div>
    </div>
  </div>

  <div class="lt-page__lab-trust">
    <div class="lt-page__wrap lt-page__lab-grid">
      <div class="lt-page__address">
        <h2>Eurofins Laboratory<br><span class="lt-page__gold">in Bengaluru, India</span></h2>
        <div class="lt-page__contact-list">
          <div class="lt-page__contact-row">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21s7-5.3 7-12a7 7 0 0 0-14 0c0 6.7 7 12 7 12z"/><circle cx="12" cy="9" r="2.5"/></svg>
            <span>Eurofins Analytical Services India Pvt. Ltd.<br>#540/1, Doddanakundi Industrial Area 2, Hoodi, Whitefield,<br>Bengaluru - 560048, Karnataka, India.</span>
          </div>
          <div class="lt-page__contact-row">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.3 19.3 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 2 .7 2.9a2 2 0 0 1-.5 2.1L8 10a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.5c.9.3 1.9.6 2.9.7a2 2 0 0 1 1.7 2z"/></svg>
            <a href="tel:+918030982500">+91 80 30982500</a>
          </div>
          <div class="lt-page__contact-row">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4h16v16H4z"/><path d="M4 7l8 6 8-6"/></svg>
            <a href="mailto:info-india@eurofins.com">info-india@eurofins.com</a>
          </div>
          <div class="lt-page__contact-row">
            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M2 12h20"/><path d="M12 2a15 15 0 0 1 0 20"/><path d="M12 2a15 15 0 0 0 0 20"/></svg>
            <a href="https://www.eurofins.in">www.eurofins.in</a>
          </div>
        </div>
        <div class="lt-page__map" aria-hidden="true">
          <div>
            <strong>Bengaluru</strong>
            <span>Karnataka, India</span>
          </div>
        </div>
      </div>

      <div class="lt-page__why">
        <div class="lt-page__why-inner">
          <h2>Why You Can Trust Eurofins</h2>
          <ul class="lt-page__tick-list">
            <li><span>&#10003;</span>Global leader in laboratory testing and analytics</li>
            <li><span>&#10003;</span>Accredited and internationally recognised</li>
            <li><span>&#10003;</span>Advanced testing methods and technology</li>
            <li><span>&#10003;</span>Strict quality control and compliance</li>
            <li><span>&#10003;</span>Trusted by leading brands worldwide</li>
          </ul>
          <p class="lt-page__why-note">When Holyoak products are tested by Eurofins, you get an added layer of confidence.</p>
        </div>
      </div>
    </div>
  </div>

  <div class="lt-page__icon-strip">
    <div class="lt-page__wrap lt-page__strip-grid">
      <div class="lt-page__strip-item">
        <span class="lt-page__circle" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M21 16V8l-9-5-9 5v8l9 5 9-5z"/><path d="M3.5 8.5L12 13l8.5-4.5"/><path d="M12 22v-9"/></svg></span>
        <strong>Selected Holyoak Products</strong>
      </div>
      <div class="lt-page__strip-item">
        <span class="lt-page__circle" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M9 3h6"/><path d="M10 3v6l-5 9a2 2 0 0 0 2 3h10a2 2 0 0 0 2-3l-5-9V3"/><path d="M8 16h8"/></svg></span>
        <strong>Batch Tested By Eurofins</strong>
      </div>
      <div class="lt-page__strip-item">
        <span class="lt-page__circle" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 3l7 3v5c0 4.8-3 8.4-7 10-4-1.6-7-5.2-7-10V6l7-3z"/><path d="M9 12l2 2 4-5"/></svg></span>
        <strong>Quality You Can Trust</strong>
      </div>
      <div class="lt-page__strip-item">
        <span class="lt-page__circle" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M6 6h15l-2 9H8L6 6z"/><path d="M6 6L5 3H2"/><circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/></svg></span>
        <strong>Available Locally In Fiji</strong>
      </div>
      <div class="lt-page__strip-item">
        <span class="lt-page__circle" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 12a8 8 0 0 1 16 0"/><path d="M4 12v4a2 2 0 0 0 2 2h1v-6H4z"/><path d="M20 12v4a2 2 0 0 1-2 2h-1v-6h3z"/><path d="M9 20h3"/></svg></span>
        <strong>Fiji-Based Support You Can Reach</strong>
      </div>
    </div>
  </div>

  <div class="lt-page__final">
    <div class="lt-page__wrap lt-page__final-grid">
      <div class="lt-page__final-mark" aria-hidden="true">
        <svg viewBox="0 0 48 48"><path d="M24 8c5 5 7 10 7 15 0 7-7 12-7 12s-7-5-7-12c0-5 2-10 7-15z"/><path d="M24 8v31"/><path d="M13 17c-4 5-4 11 0 16 5 6 11 6 11 6"/><path d="M35 17c4 5 4 11 0 16-5 6-11 6-11 6"/></svg>
      </div>
      <div>
        <h2>International Wellness. Independent Testing. <span>Local Access.</span></h2>
        <p>Holyoak products. Tested by Eurofins. Available through Medzuro Retail in Fiji.</p>
      </div>
      <div class="lt-page__logo-text">Medzuro<span>Retail</span></div>
    </div>
  </div>
</section>
