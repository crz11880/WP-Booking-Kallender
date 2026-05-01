<?php
if (! defined('ABSPATH')) {
    exit;
}
?>
<div class="fewo-frontend-calendar <?php echo esc_attr($theme_class); ?>" id="<?php echo esc_attr($container_id); ?>" data-calendar-id="<?php echo esc_attr((string) $calendar->id); ?>" data-month="<?php echo esc_attr($month); ?>">
    <div class="fewo-head">
        <div>
            <h3 class="fewo-title"><?php echo esc_html($calendar->name); ?></h3>
            <?php if (! empty($calendar->description)) : ?>
                <p class="fewo-description"><?php echo esc_html($calendar->description); ?></p>
            <?php endif; ?>
        </div>

        <div class="fewo-nav" aria-label="Monatsnavigation">
            <button type="button" class="fewo-nav-btn" data-direction="prev" aria-label="Vorheriger Monat">&larr;</button>
            <span class="fewo-month-label"><?php echo esc_html($this->month_label($month)); ?></span>
            <button type="button" class="fewo-nav-btn" data-direction="next" aria-label="Naechster Monat">&rarr;</button>
        </div>
    </div>

    <div class="fewo-grid-wrap">
        <?php echo $this->render_month_grid((int) $calendar->id, $month); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
    </div>

    <div class="fewo-legend" aria-label="Legende">
        <span class="fewo-legend-item"><i class="fewo-legend-color fewo-status-free"></i><?php esc_html_e('frei', 'fewo-kalender'); ?></span>
        <span class="fewo-legend-item"><i class="fewo-legend-color fewo-status-booked"></i><?php esc_html_e('belegt', 'fewo-kalender'); ?></span>
        <span class="fewo-legend-item"><i class="fewo-legend-color fewo-status-changeover"></i><?php esc_html_e('Wechseltag', 'fewo-kalender'); ?></span>
        <span class="fewo-legend-item"><i class="fewo-legend-color fewo-status-halfday"></i><?php esc_html_e('Halber Tag (belegt/frei)', 'fewo-kalender'); ?></span>
        <span class="fewo-legend-item"><i class="fewo-legend-color fewo-status-halfday-reverse"></i><?php esc_html_e('Halber Tag (frei/belegt)', 'fewo-kalender'); ?></span>
        <span class="fewo-legend-item"><i class="fewo-legend-color fewo-today-mark"></i><?php esc_html_e('Heute', 'fewo-kalender'); ?></span>
    </div>

    <?php if (! empty($request_message)) : ?>
        <?php echo $request_message; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
    <?php endif; ?>

    <?php if ($inquiry_enabled && '' !== $inquiry_email) : ?>
        <div class="fewo-request-box">
            <h4><?php esc_html_e('Buchungsanfrage', 'fewo-kalender'); ?></h4>
            <p class="fewo-request-hint"><?php esc_html_e('Anreise und Abreise direkt im Kalender auswaehlen, danach Formular absenden.', 'fewo-kalender'); ?></p>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="fewo-request-form">
                <input type="hidden" name="action" value="fewo_kalender_booking_request" />
                <input type="hidden" name="calendar_id" value="<?php echo esc_attr((string) $calendar->id); ?>" />
                <input type="hidden" name="fewo_form_ts" value="<?php echo esc_attr((string) time()); ?>" />
                <?php wp_nonce_field('fewo_booking_request', 'fewo_booking_nonce'); ?>

                <?php /* Honeypot: Dieses Feld muss leer bleiben – Bots fuellen es aus */ ?>
                <div class="fewo-hp-wrap" aria-hidden="true">
                    <label for="fewo_hp_email">E-Mail bestaetigen</label>
                    <input type="text" id="fewo_hp_email" name="fewo_hp" value="" autocomplete="off" tabindex="-1" />
                </div>

                <div class="fewo-form-feedback" style="display:none;"></div>

                <div class="fewo-request-grid">
                    <label>
                        <span><?php esc_html_e('Vorname', 'fewo-kalender'); ?> <span class="fewo-required">*</span></span>
                        <input type="text" name="first_name" required />
                        <span class="fewo-field-error" data-field="first_name"></span>
                    </label>
                    <label>
                        <span><?php esc_html_e('Nachname', 'fewo-kalender'); ?> <span class="fewo-required">*</span></span>
                        <input type="text" name="last_name" required />
                        <span class="fewo-field-error" data-field="last_name"></span>
                    </label>
                    <label class="fewo-request-full">
                        <span><?php esc_html_e('E-Mail-Adresse', 'fewo-kalender'); ?> <span class="fewo-required">*</span></span>
                        <input type="email" name="email" required />
                        <span class="fewo-field-error" data-field="email"></span>
                    </label>
                    <label>
                        <span><?php esc_html_e('Von', 'fewo-kalender'); ?> <span class="fewo-required">*</span></span>
                        <input type="text" name="from_date_display" class="fewo-from-display" readonly placeholder="TT.MM.JJJJ" />
                        <span class="fewo-field-error" data-field="from_date"></span>
                    </label>
                    <label>
                        <span><?php esc_html_e('Bis', 'fewo-kalender'); ?> <span class="fewo-required">*</span></span>
                        <input type="text" name="to_date_display" class="fewo-to-display" readonly placeholder="TT.MM.JJJJ" />
                        <span class="fewo-field-error" data-field="to_date"></span>
                    </label>
                    <label class="fewo-request-full">
                        <span><?php esc_html_e('Nachricht (optional)', 'fewo-kalender'); ?></span>
                        <textarea name="message" rows="4"></textarea>
                    </label>
                </div>

                <input type="hidden" name="from_date" class="fewo-from-date" value="" />
                <input type="hidden" name="to_date" class="fewo-to-date" value="" />

                <button type="submit" class="fewo-request-submit"><?php esc_html_e('Buchungsanfrage senden', 'fewo-kalender'); ?></button>
            </form>
        </div>
    <?php endif; ?>
</div>
