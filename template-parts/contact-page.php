<?php
/**
 * Ported from sections/medzuro-contact-page.liquid.
 * CSS: assets/css/contact-page.css (enqueue with medzuro_style( 'contact-page' )).
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="ct-page">
  <div class="ct-page__hero">
    <div class="ct-page__wrap ct-page__hero-grid">
      <div class="ct-page__hero-copy">
        <p class="ct-page__eyebrow">Contact Medzuro Retail</p>
        <h1>We're Here<br>to Help</h1>
        <div class="ct-page__underline" aria-hidden="true"></div>
        <p>Whether you have a question about a product, need help with an order, want to discuss a partnership, or simply want to learn more about Medzuro Retail, our team is here to assist.</p>
        <p>With operations in Fiji and India, Medzuro Retail combines local customer support with an international healthcare and wellness network.</p>
        <p class="ct-page__lead">Have a question? Let's talk.</p>
      </div>

      <div class="ct-page__map" aria-label="Medzuro Retail global support network">
        <svg viewBox="0 0 680 360" role="img" aria-label="Connection between Fiji and India">
          <defs>
            <pattern id="ct-dot-pattern-{{ section.id }}" width="12" height="12" patternUnits="userSpaceOnUse">
              <circle cx="3" cy="3" r="1.8" class="ct-page__map-dot"></circle>
            </pattern>
            <clipPath id="ct-map-mask-{{ section.id }}">
              <path d="M70 112c38-48 95-58 153-32 32 14 61 31 99 21 56-15 100-51 164-29 43 15 81 51 113 82 20 20 31 48 17 74-22 41-87 37-126 22-30-12-57-35-91-27-44 10-70 54-116 60-54 7-85-43-121-72-32-27-83-20-104-61-7-14-3-26 12-38z"></path>
              <path d="M96 212c28-5 55 1 75 18 14 12 23 31 13 47-13 20-45 22-69 14-26-9-51-30-49-55 1-12 12-21 30-24z"></path>
              <path d="M514 86c28-19 70-21 90 7 18 25 2 61-27 69-32 9-68-13-75-42-3-13 2-25 12-34z"></path>
            </clipPath>
          </defs>
          <rect x="0" y="0" width="680" height="360" fill="transparent"></rect>
          <rect x="36" y="38" width="600" height="270" fill="url(#ct-dot-pattern-{{ section.id }})" clip-path="url(#ct-map-mask-{{ section.id }})"></rect>
          <path class="ct-page__map-arc" d="M190 198C300 84 430 88 538 202"></path>
          <path class="ct-page__map-arc" d="M190 198C325 128 430 140 538 202"></path>
          <g transform="translate(158 162)">
            <path class="ct-page__pin-blue" d="M32 0C14 0 0 14 0 32c0 24 32 62 32 62s32-38 32-62C64 14 50 0 32 0z"></path>
            <circle cx="32" cy="31" r="12" fill="#fff"></circle>
          </g>
          <g transform="translate(506 182)">
            <path class="ct-page__pin-orange" d="M27 0C12 0 0 12 0 27c0 21 27 53 27 53s27-32 27-53C54 12 42 0 27 0z"></path>
            <circle cx="27" cy="26" r="9" fill="#fff"></circle>
          </g>
        </svg>
      </div>
    </div>

    <div class="ct-page__wrap">
      <div class="ct-page__support-card">
        <div class="ct-page__support-item">
          <span class="ct-page__icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 12a8 8 0 0 1 16 0"/><path d="M4 12v4a2 2 0 0 0 2 2h1v-6H4z"/><path d="M20 12v4a2 2 0 0 1-2 2h-1v-6h3z"/><path d="M9 20h3"/></svg></span>
          <div><strong>Fast Response</strong><span>We reply quickly</span></div>
        </div>
        <div class="ct-page__support-item">
          <span class="ct-page__icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 3l7 3v5c0 4.8-3 8.4-7 10-4-1.6-7-5.2-7-10V6l7-3z"/><path d="M9 12l2 2 4-5"/></svg></span>
          <div><strong>Trusted Support</strong><span>Reliable & secure</span></div>
        </div>
        <div class="ct-page__support-item">
          <span class="ct-page__icon" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="7" r="4"/><path d="M6 21v-2a6 6 0 0 1 12 0v2"/><path d="M12 11v4"/></svg></span>
          <div><strong>Real People</strong><span>We're here for you</span></div>
        </div>
        <div class="ct-page__support-item">
          <span class="ct-page__icon" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M2 12h20"/><path d="M12 2a15 15 0 0 1 0 20"/><path d="M12 2a15 15 0 0 0 0 20"/></svg></span>
          <div><strong>Global Network</strong><span>Local presence, global connection</span></div>
        </div>
      </div>
    </div>
  </div>

  <div class="ct-page__section">
    <div class="ct-page__wrap">
      <div class="ct-page__office-card">
        <div class="ct-page__section-title">Our Offices</div>
        <div class="ct-page__office-grid">
          <div class="ct-page__office">
            <div class="ct-page__flag" aria-hidden="true">FJ</div>
            <div>
              <h3>Fiji Office</h3>
              <div class="ct-page__mini-line" aria-hidden="true"></div>
              <div class="ct-page__contact-list">
                <div class="ct-page__contact-row"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.3 19.3 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 2 .7 2.9a2 2 0 0 1-.5 2.1L8 10a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.5c.9.3 1.9.6 2.9.7a2 2 0 0 1 1.7 2z"/></svg><a href="tel:+6798029837">+679 8029837</a></div>
                <div class="ct-page__contact-row"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4h16v16H4z"/><path d="M4 7l8 6 8-6"/></svg><a href="mailto:sales@medzuroretail.com">sales@medzuroretail.com</a></div>
                <div class="ct-page__contact-row"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4h16v16H4z"/><path d="M4 7l8 6 8-6"/></svg><a href="mailto:fiji@medzuroretail.com">fiji@medzuroretail.com</a></div>
              </div>
            </div>
          </div>

          <div class="ct-page__office-mid">
            <div>
              <span class="ct-page__icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 21s7-5.3 7-12a7 7 0 0 0-14 0c0 6.7 7 12 7 12z"/><circle cx="12" cy="9" r="2.5"/></svg></span>
              <strong>Two Locations.<br>One Connected Team.</strong>
            </div>
          </div>

          <div class="ct-page__office ct-page__office--right">
            <div class="ct-page__flag ct-page__flag--india" aria-hidden="true">IN</div>
            <div>
              <h3>India Office</h3>
              <div class="ct-page__mini-line" aria-hidden="true"></div>
              <div class="ct-page__contact-list">
                <div class="ct-page__contact-row"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.3 19.3 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 2 .7 2.9a2 2 0 0 1-.5 2.1L8 10a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.5c.9.3 1.9.6 2.9.7a2 2 0 0 1 1.7 2z"/></svg><a href="tel:+919024417352">+91 9024417352</a></div>
                <div class="ct-page__contact-row"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4h16v16H4z"/><path d="M4 7l8 6 8-6"/></svg><a href="mailto:hq@medzuroretail.com">hq@medzuroretail.com</a></div>
              </div>
            </div>
          </div>
        </div>

        <div class="ct-page__address-card">
          <div class="ct-page__address-head">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21s7-5.3 7-12a7 7 0 0 0-14 0c0 6.7 7 12 7 12z"/><circle cx="12" cy="9" r="2.5"/></svg>
            Our Registered Addresses
          </div>
          <div class="ct-page__address">
            <strong>Medzuro Wellness PTE Limited</strong>
            <p>18, Valili st, Vishnu Deo Road,<br>Nakasi, Suva, Fiji Islands.</p>
          </div>
          <div class="ct-page__address">
            <strong>Medzuro Wellness Private Limited</strong>
            <p>95, Raghunandan Vihar,<br>Jagatpura, Jaipur, Rajasthan, India - 302017.</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="ct-page__form-section">
    <div class="ct-page__wrap">
      <div class="ct-page__form-head">
        <p class="ct-page__eyebrow">Get In Touch</p>
        <h2>Send Us a Message</h2>
        <p>Fill out the form below and our team will get back to you.</p>
      </div>

      <div class="ct-page__form-card">
        {% form 'contact', id: 'MedzuroContactForm' %}
          {% if form.posted_successfully? %}
            <p class="ct-page__notice">{{ 'contact.form.post_success' | t }}</p>
          {% endif %}
          {% if form.errors %}
            <div class="ct-page__errors">{{ form.errors | default_errors }}</div>
          {% endif %}

          <div class="ct-page__form-grid">
            <div class="ct-page__field">
              <label for="MedzuroContactName">Full Name <span>*</span></label>
              <div class="ct-page__input-wrap">
                <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="7" r="4"/><path d="M6 21v-2a6 6 0 0 1 12 0v2"/></svg>
                <input id="MedzuroContactName" type="text" name="contact[name]" autocomplete="name" placeholder="Enter your full name" value="{% if form.name %}{{ form.name }}{% elsif customer %}{{ customer.name }}{% endif %}" required>
              </div>
            </div>

            <div class="ct-page__field">
              <label for="MedzuroContactEmail">Email Address <span>*</span></label>
              <div class="ct-page__input-wrap">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4h16v16H4z"/><path d="M4 7l8 6 8-6"/></svg>
                <input id="MedzuroContactEmail" type="email" name="contact[email]" autocomplete="email" autocorrect="off" autocapitalize="off" placeholder="Enter your email address" value="{% if form.email %}{{ form.email }}{% elsif customer %}{{ customer.email }}{% endif %}" required>
              </div>
            </div>

            <div class="ct-page__field">
              <label for="MedzuroContactPhone">Phone Number <span>*</span></label>
              <div class="ct-page__input-wrap">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.3 19.3 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 2 .7 2.9a2 2 0 0 1-.5 2.1L8 10a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.5c.9.3 1.9.6 2.9.7a2 2 0 0 1 1.7 2z"/></svg>
                <input id="MedzuroContactPhone" type="tel" name="contact[phone]" autocomplete="tel" pattern="[0-9+() -]*" placeholder="Enter your phone number" value="{% if form.phone %}{{ form.phone }}{% elsif customer %}{{ customer.phone }}{% endif %}" required>
              </div>
            </div>

            <div class="ct-page__field">
              <label for="MedzuroContactCountry">Country <span>*</span></label>
              <div class="ct-page__input-wrap">
                <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M2 12h20"/><path d="M12 2a15 15 0 0 1 0 20"/><path d="M12 2a15 15 0 0 0 0 20"/></svg>
                <select id="MedzuroContactCountry" name="contact[country]" required>
                  <option value="">Select your country</option>
                  <option value="Fiji">Fiji</option>
                  <option value="India">India</option>
                  <option value="Other">Other</option>
                </select>
              </div>
            </div>

            <div class="ct-page__field ct-page__field--half">
              <label for="MedzuroContactType">Enquiry Type <span>*</span></label>
              <div class="ct-page__input-wrap">
                <select id="MedzuroContactType" name="contact[enquiry_type]" required>
                  <option value="">Select enquiry type</option>
                  <option value="Product question">Product question</option>
                  <option value="Order support">Order support</option>
                  <option value="Delivery or pickup">Delivery or pickup</option>
                  <option value="Partnership">Partnership</option>
                  <option value="General enquiry">General enquiry</option>
                </select>
              </div>
            </div>

            <div class="ct-page__field ct-page__field--half">
              <label for="MedzuroContactSubject">Subject <span>*</span></label>
              <div class="ct-page__input-wrap">
                <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="7" r="4"/><path d="M6 21v-2a6 6 0 0 1 12 0v2"/></svg>
                <input id="MedzuroContactSubject" type="text" name="contact[subject]" placeholder="What is your enquiry about?" required>
              </div>
            </div>

            <div class="ct-page__field ct-page__field--full">
              <label for="MedzuroContactMessage">Your Message <span>*</span></label>
              <div class="ct-page__input-wrap ct-page__input-wrap--textarea">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                <textarea id="MedzuroContactMessage" name="contact[body]" placeholder="Tell us how we can help you..." required>{% if form.body %}{{ form.body }}{% endif %}</textarea>
              </div>
            </div>
          </div>

          <div class="ct-page__submit-row">
            <button class="ct-page__submit" type="submit">
              <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M22 2L11 13"/><path d="M22 2l-7 20-4-9-9-4 20-7z"/></svg>
              Submit Enquiry
            </button>
            <span class="ct-page__safe">
              <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
              Your information is safe with us. We will never share your details.
            </span>
          </div>
        {% endform %}
      </div>
    </div>
  </div>

  <div class="ct-page__method-strip">
    <div class="ct-page__wrap ct-page__method-grid">
      <div class="ct-page__method">
        <span class="ct-page__icon ct-page__icon--round" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.3 19.3 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 2 .7 2.9a2 2 0 0 1-.5 2.1L8 10a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.5c.9.3 1.9.6 2.9.7a2 2 0 0 1 1.7 2z"/></svg></span>
        <div class="ct-page__method-copy"><strong>Call Us</strong><span>Speak directly with our team.</span></div>
      </div>
      <div class="ct-page__method">
        <span class="ct-page__icon ct-page__icon--round" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 4h16v16H4z"/><path d="M4 7l8 6 8-6"/></svg></span>
        <div class="ct-page__method-copy"><strong>Email Us</strong><span>We'll get back to you as soon as possible.</span></div>
      </div>
      <div class="ct-page__method">
        <span class="ct-page__icon ct-page__icon--round" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 12a8 8 0 0 1 16 0"/><path d="M4 12v4a2 2 0 0 0 2 2h1v-6H4z"/><path d="M20 12v4a2 2 0 0 1-2 2h-1v-6h3z"/><path d="M9 20h3"/></svg></span>
        <div class="ct-page__method-copy"><strong>Support</strong><span>We're here to assist with any questions.</span></div>
      </div>
      <div class="ct-page__method">
        <span class="ct-page__icon ct-page__icon--round" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M8 12l3 3 5-6"/><path d="M3 12l5-5 4 4 4-4 5 5-9 9-9-9z"/></svg></span>
        <div class="ct-page__method-copy"><strong>Partnerships</strong><span>Let's build a healthier future together.</span></div>
      </div>
    </div>
  </div>
</section>
