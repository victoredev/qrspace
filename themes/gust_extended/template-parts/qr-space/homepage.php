<?php
/**
 * QR Space homepage markup.
 *
 * @package Gust_Extended
 */

if (!defined('ABSPATH')) {
    exit;
}

$features = [
    [
        'title' => __('Dynamic QR Codes', 'gust-extended'),
        'text'  => __('Update your destination anytime without reprinting.', 'gust-extended'),
        'art'   => 'stand',
    ],
    [
        'title' => __('Scan Analytics', 'gust-extended'),
        'text'  => __('Track scans, locations, devices and get valuable insights.', 'gust-extended'),
        'art'   => 'analytics',
    ],
    [
        'title' => __('QR Profiles', 'gust-extended'),
        'text'  => __('Create beautiful profile pages with buttons, links and more.', 'gust-extended'),
        'art'   => 'profile',
    ],
    [
        'title' => __('Custom Designs', 'gust-extended'),
        'text'  => __('Add colors, logos, frames and create designs that match your brand.', 'gust-extended'),
        'art'   => 'cup',
    ],
    [
        'title' => __('Organize & Manage', 'gust-extended'),
        'text'  => __('Folders, tags, teams and easy organization for all your codes.', 'gust-extended'),
        'art'   => 'folders',
    ],
    [
        'title' => __('Smart Redirects', 'gust-extended'),
        'text'  => __('Send users to the right content based on time, device and location.', 'gust-extended'),
        'art'   => 'signs',
    ],
    [
        'title' => __('Schedule & Expire', 'gust-extended'),
        'text'  => __('Schedule campaigns or set expiration dates for your QR codes.', 'gust-extended'),
        'art'   => 'calendar',
    ],
    [
        'title' => __('Scan Notifications', 'gust-extended'),
        'text'  => __('Get instant email alerts when your QR codes are scanned.', 'gust-extended'),
        'art'   => 'notify',
    ],
    [
        'title' => __('Offline QR Codes', 'gust-extended'),
        'text'  => __('Create reliable QR codes that work even without an internet connection.', 'gust-extended'),
        'art'   => 'wifi',
    ],
    [
        'title' => __('Secure & Private', 'gust-extended'),
        'text'  => __('Your data is encrypted and your privacy is our top priority.', 'gust-extended'),
        'art'   => 'lock',
    ],
];
?>
<main class="qs-main">
    <section class="qs-hero" id="create">
        <div class="qs-shell qs-hero__grid">
            <div class="qs-hero__copy">
                <p class="qs-kicker"><?php esc_html_e('100% FREE TO CREATE', 'gust-extended'); ?></p>
                <h1><?php esc_html_e('Make Your QR Code Free', 'gust-extended'); ?></h1>
                <p class="qs-lead"><?php esc_html_e('Create a QR code in seconds.', 'gust-extended'); ?><br><?php esc_html_e('No credit card required.', 'gust-extended'); ?></p>
                <ul class="qs-checks">
                    <li><?php esc_html_e('Free to Create', 'gust-extended'); ?></li>
                    <li><?php esc_html_e('No Credit Card', 'gust-extended'); ?></li>
                    <li><?php esc_html_e('Works on Any Phone', 'gust-extended'); ?></li>
                    <li><?php esc_html_e('No App Required', 'gust-extended'); ?></li>
                </ul>
                <p class="qs-rating">
                    <span class="qs-stars" aria-hidden="true">★★★★★</span>
                    <span><?php esc_html_e('4.9/5  Trusted by thousands', 'gust-extended'); ?></span>
                </p>
            </div>

            <div class="qs-hero__creator">
                <div id="qrCodeCreator" class="qr-code-creator"></div>
            </div>
        </div>
    </section>

    <section class="qs-solutions" id="solutions">
        <div class="qs-shell qs-solutions__grid">
            <article class="qs-split qs-split--business">
                <div class="qs-split__visual qs-art qs-art--stand-hero" aria-hidden="true">
                    <div class="qs-stand">
                        <div class="qs-stand__screen">
                            <span>SCAN TO<br>LEARN MORE</span>
                            <div class="qs-mini-qr"></div>
                        </div>
                        <div class="qs-stand__base"></div>
                    </div>
                </div>
                <div class="qs-split__body">
                    <h2><?php esc_html_e('For Business', 'gust-extended'); ?></h2>
                    <p><?php esc_html_e('Everything you need to grow your business.', 'gust-extended'); ?></p>
                    <ul class="qs-icon-row">
                        <li><span class="qs-ico qs-ico--menu"></span><?php esc_html_e('Share products & brochures', 'gust-extended'); ?></li>
                        <li><span class="qs-ico qs-ico--card"></span><?php esc_html_e('Menus, catalogs, payments', 'gust-extended'); ?></li>
                        <li><span class="qs-ico qs-ico--star"></span><?php esc_html_e('Collect reviews', 'gust-extended'); ?></li>
                        <li><span class="qs-ico qs-ico--users"></span><?php esc_html_e('Capture reviews & feedback', 'gust-extended'); ?></li>
                        <li><span class="qs-ico qs-ico--gift"></span><?php esc_html_e('Grow your audience', 'gust-extended'); ?></li>
                        <li><span class="qs-ico qs-ico--chart"></span><?php esc_html_e('Track performance', 'gust-extended'); ?></li>
                    </ul>
                    <a class="qs-text-link" href="#features"><?php esc_html_e('Explore Business Solutions', 'gust-extended'); ?> →</a>
                </div>
            </article>

            <article class="qs-split qs-split--personal">
                <div class="qs-split__body">
                    <h2><?php esc_html_e('For Personal Use', 'gust-extended'); ?></h2>
                    <p><?php esc_html_e('Share what matters in everyday life.', 'gust-extended'); ?></p>
                    <ul class="qs-icon-row qs-icon-row--green">
                        <li><span class="qs-ico qs-ico--link"></span><?php esc_html_e('Share links & websites', 'gust-extended'); ?></li>
                        <li><span class="qs-ico qs-ico--wifi"></span><?php esc_html_e('Connect on social media', 'gust-extended'); ?></li>
                        <li><span class="qs-ico qs-ico--share"></span><?php esc_html_e('Share Wi‑Fi access', 'gust-extended'); ?></li>
                        <li><span class="qs-ico qs-ico--phone"></span><?php esc_html_e('Send files & event details', 'gust-extended'); ?></li>
                        <li><span class="qs-ico qs-ico--photo"></span><?php esc_html_e('Share photos & files', 'gust-extended'); ?></li>
                        <li><span class="qs-ico qs-ico--pin"></span><?php esc_html_e('Keep life in one place', 'gust-extended'); ?></li>
                    </ul>
                    <a class="qs-text-link qs-text-link--green" href="#profiles"><?php esc_html_e('Explore Personal Solutions', 'gust-extended'); ?> →</a>
                </div>
                <div class="qs-split__visual qs-art qs-art--phone-hero" aria-hidden="true">
                    <div class="qs-phone qs-phone--profile">
                        <div class="qs-phone__notch"></div>
                        <div class="qs-phone__avatar"></div>
                        <strong>Alex Johnson</strong>
                        <small>QRSPACE CREATOR</small>
                        <div class="qs-phone__btns">
                            <span></span><span></span><span></span><span></span>
                        </div>
                    </div>
                </div>
            </article>
        </div>
    </section>

    <section class="qs-features" id="features">
        <div class="qs-shell">
            <div class="qs-section-head">
                <h2><?php esc_html_e('Powerful Features When You Need More', 'gust-extended'); ?></h2>
                <p><?php esc_html_e('Start free. Upgrade anytime.', 'gust-extended'); ?></p>
            </div>
            <div class="qs-feature-grid">
                <?php foreach ($features as $feature) : ?>
                    <article class="qs-feature-card">
                        <div class="qs-feature-card__art qs-art--<?php echo esc_attr($feature['art']); ?>" aria-hidden="true"></div>
                        <h3><?php echo esc_html($feature['title']); ?></h3>
                        <p><?php echo esc_html($feature['text']); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
            <p class="qs-center">
                <a class="qs-text-link" href="#profiles"><?php esc_html_e('View all features', 'gust-extended'); ?> →</a>
            </p>
        </div>
    </section>

    <section class="qs-profiles" id="profiles">
        <div class="qs-shell">
            <div class="qs-profiles__intro">
                <span class="qs-badge"><?php esc_html_e('EXCLUSIVE', 'gust-extended'); ?></span>
                <h2><?php esc_html_e('QR Profiles (Button Profiles)', 'gust-extended'); ?></h2>
                <p class="qs-lead"><?php esc_html_e('Share What You Want. When You Want.', 'gust-extended'); ?></p>
                <p><?php esc_html_e('Create different profiles for different situations. Your visitors will see only the buttons you choose.', 'gust-extended'); ?></p>
                <ul class="qs-checks">
                    <li><?php esc_html_e('Change buttons anytime', 'gust-extended'); ?></li>
                    <li><?php esc_html_e('Perfect for work, personal, dating or social', 'gust-extended'); ?></li>
                    <li><?php esc_html_e('No need to reprint your QR code', 'gust-extended'); ?></li>
                    <li><?php esc_html_e('Create unlimited profiles', 'gust-extended'); ?></li>
                </ul>
            </div>

            <ol class="qs-steps">
                <li>
                    <span class="qs-steps__num">1</span>
                    <h3><?php esc_html_e('Choose a Profile', 'gust-extended'); ?></h3>
                    <p><?php esc_html_e('Pick the profile you want to share.', 'gust-extended'); ?></p>
                    <div class="qs-phone qs-phone--list" aria-hidden="true">
                        <div class="qs-phone__notch"></div>
                        <strong>My QR Profiles</strong>
                        <ul>
                            <li class="is-active">Work</li>
                            <li>Personal</li>
                            <li>Dating</li>
                            <li>Event</li>
                            <li>Social Media</li>
                        </ul>
                    </div>
                </li>
                <li>
                    <span class="qs-steps__num">2</span>
                    <h3><?php esc_html_e('Show Your QR Code', 'gust-extended'); ?></h3>
                    <p><?php esc_html_e('Your QR code for that profile is ready to show.', 'gust-extended'); ?></p>
                    <div class="qs-phone qs-phone--qr" aria-hidden="true">
                        <div class="qs-phone__notch"></div>
                        <strong>Work</strong>
                        <div class="qs-mini-qr qs-mini-qr--lg"></div>
                        <em>SCAN ME</em>
                    </div>
                </li>
                <li>
                    <span class="qs-steps__num">3</span>
                    <h3><?php esc_html_e('They Scan It', 'gust-extended'); ?></h3>
                    <p><?php esc_html_e('The other person scans your QR code.', 'gust-extended'); ?></p>
                    <div class="qs-scan-photo" aria-hidden="true">
                        <div class="qs-scan-photo__hand">
                            <div class="qs-mini-qr"></div>
                        </div>
                    </div>
                </li>
                <li>
                    <span class="qs-steps__num">4</span>
                    <h3><?php esc_html_e('Only Your Buttons Appear', 'gust-extended'); ?></h3>
                    <p><?php esc_html_e('Their phone shows ONLY the buttons from that profile.', 'gust-extended'); ?></p>
                    <div class="qs-phone qs-phone--buttons" aria-hidden="true">
                        <div class="qs-phone__notch"></div>
                        <strong>Work</strong>
                        <div class="qs-phone__btns qs-phone__btns--labeled">
                            <span>Website</span>
                            <span>LinkedIn</span>
                            <span>Call Me</span>
                            <span>Book a Meeting</span>
                        </div>
                    </div>
                </li>
            </ol>
            <p class="qs-center qs-muted"><?php esc_html_e('One QR code. Endless possibilities.', 'gust-extended'); ?></p>
        </div>
    </section>

    <section class="qs-trust" id="trust">
        <div class="qs-shell qs-trust__grid">
            <article>
                <span class="qs-trust__icon qs-trust__icon--card" aria-hidden="true"></span>
                <h3><?php esc_html_e('No Credit Card', 'gust-extended'); ?></h3>
                <p><?php esc_html_e('Free forever. No hidden fees.', 'gust-extended'); ?></p>
            </article>
            <article>
                <span class="qs-trust__icon qs-trust__icon--lock" aria-hidden="true"></span>
                <h3><?php esc_html_e('Privacy First', 'gust-extended'); ?></h3>
                <p><?php esc_html_e('Your data is safe with us. We respect your privacy.', 'gust-extended'); ?></p>
            </article>
            <article>
                <span class="qs-trust__icon qs-trust__icon--bolt" aria-hidden="true"></span>
                <h3><?php esc_html_e('Always Free to Start', 'gust-extended'); ?></h3>
                <p><?php esc_html_e('Upgrade only when you need more. Create and manage your first QR code completely free.', 'gust-extended'); ?></p>
            </article>
            <article>
                <span class="qs-trust__icon qs-trust__icon--people" aria-hidden="true"></span>
                <h3><?php esc_html_e('Trusted by Thousands', 'gust-extended'); ?></h3>
                <p><?php esc_html_e('Join people and businesses worldwide.', 'gust-extended'); ?></p>
            </article>
        </div>
    </section>

    <section class="qs-cta">
        <div class="qs-shell qs-cta__inner">
            <div class="qs-cta__copy">
                <div class="qs-cta__laptop" aria-hidden="true"></div>
                <div>
                    <h2><?php esc_html_e('Ready to Create Your QR Code?', 'gust-extended'); ?></h2>
                    <p><?php esc_html_e('Create your QR code free in seconds.', 'gust-extended'); ?></p>
                </div>
            </div>
            <div class="qs-cta__action">
                <a class="qs-btn qs-btn--light" href="#create"><?php esc_html_e('Create My Free QR Code', 'gust-extended'); ?> →</a>
                <small><?php esc_html_e('No credit card. No limits on your first code.', 'gust-extended'); ?></small>
            </div>
        </div>
    </section>
</main>
