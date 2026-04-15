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
        <span class="fewo-legend-item"><i class="fewo-legend-color fewo-today-mark"></i><?php esc_html_e('Heute', 'fewo-kalender'); ?></span>
    </div>
</div>
