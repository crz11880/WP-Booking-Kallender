<?php
if (! defined('ABSPATH')) {
    exit;
}

$first_day_ts  = strtotime(sprintf('%04d-%02d-01', $year, $month_num));
$days_in_month = (int) date('t', $first_day_ts);
$start_weekday = (int) date('N', $first_day_ts);
$today         = current_time('Y-m-d');
?>
<div class="wrap fewo-admin-wrap">
    <h1><?php esc_html_e('Kalender bearbeiten', 'fewo-kalender'); ?>: <?php echo esc_html($calendar->name); ?></h1>
    <p><a href="<?php echo esc_url(add_query_arg(array('page' => 'fewo-kalender'), admin_url('admin.php'))); ?>">&larr; <?php esc_html_e('Zurueck zur Uebersicht', 'fewo-kalender'); ?></a></p>

    <?php if ('saved' === $message) : ?>
        <div class="notice notice-success is-dismissible"><p><?php esc_html_e('Tagesstatus gespeichert.', 'fewo-kalender'); ?></p></div>
    <?php elseif ('meta_saved' === $message) : ?>
        <div class="notice notice-success is-dismissible"><p><?php esc_html_e('Kalenderdaten gespeichert.', 'fewo-kalender'); ?></p></div>
    <?php endif; ?>

    <div class="fewo-admin-card">
        <h2><?php esc_html_e('Kalenderdaten', 'fewo-kalender'); ?></h2>
        <form method="post">
            <?php wp_nonce_field('fewo_update_calendar_meta'); ?>
            <input type="hidden" name="fewo_action" value="update_calendar_meta" />
            <input type="hidden" name="calendar_id" value="<?php echo esc_attr((string) $calendar->id); ?>" />
            <input type="hidden" name="month" value="<?php echo esc_attr($month); ?>" />

            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="fewo_edit_name"><?php esc_html_e('Name', 'fewo-kalender'); ?></label></th>
                    <td><input type="text" id="fewo_edit_name" name="name" class="regular-text" required value="<?php echo esc_attr($calendar->name); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="fewo_edit_description"><?php esc_html_e('Beschreibung', 'fewo-kalender'); ?></label></th>
                    <td><textarea id="fewo_edit_description" name="description" rows="3" class="large-text"><?php echo esc_textarea((string) $calendar->description); ?></textarea></td>
                </tr>
                <tr>
                    <th scope="row"><label for="fewo_edit_design"><?php esc_html_e('Design', 'fewo-kalender'); ?></label></th>
                    <td>
                        <select id="fewo_edit_design" name="design" class="fewo-design-select">
                            <?php foreach ($design_options as $design_key => $design_label) : ?>
                                <option value="<?php echo esc_attr($design_key); ?>" <?php selected(isset($calendar->design) ? $calendar->design : 'modern', $design_key); ?>><?php echo esc_html($design_label); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="fewo-design-previews" aria-hidden="true">
                            <span class="fewo-design-chip fewo-design-modern"><?php esc_html_e('Modern', 'fewo-kalender'); ?></span>
                            <span class="fewo-design-chip fewo-design-ocean"><?php esc_html_e('Ocean', 'fewo-kalender'); ?></span>
                            <span class="fewo-design-chip fewo-design-terracotta"><?php esc_html_e('Terracotta', 'fewo-kalender'); ?></span>
                        </div>
                    </td>
                </tr>
            </table>

            <?php submit_button(__('Kalenderdaten speichern', 'fewo-kalender')); ?>
        </form>
    </div>

    <div class="fewo-admin-card">
        <div class="fewo-month-nav">
            <a class="button" href="<?php echo esc_url(add_query_arg(array('page' => 'fewo-kalender-edit', 'calendar_id' => $calendar->id, 'month' => $prev_month), admin_url('admin.php'))); ?>">&larr; <?php esc_html_e('Vorheriger Monat', 'fewo-kalender'); ?></a>
            <h2><?php echo esc_html(wp_date('F Y', $first_day_ts)); ?></h2>
            <a class="button" href="<?php echo esc_url(add_query_arg(array('page' => 'fewo-kalender-edit', 'calendar_id' => $calendar->id, 'month' => $next_month), admin_url('admin.php'))); ?>"><?php esc_html_e('Naechster Monat', 'fewo-kalender'); ?> &rarr;</a>
        </div>

        <p class="fewo-legend">
            <span class="fewo-badge fewo-free"><?php esc_html_e('frei', 'fewo-kalender'); ?></span>
            <span class="fewo-badge fewo-booked"><?php esc_html_e('belegt', 'fewo-kalender'); ?></span>
            <span class="fewo-badge fewo-changeover"><?php esc_html_e('Wechseltag', 'fewo-kalender'); ?></span>
        </p>

        <form method="post" id="fewo-status-form">
            <?php wp_nonce_field('fewo_save_day_statuses'); ?>
            <input type="hidden" name="fewo_action" value="save_day_statuses" />
            <input type="hidden" name="calendar_id" value="<?php echo esc_attr((string) $calendar->id); ?>" />
            <input type="hidden" name="month" value="<?php echo esc_attr($month); ?>" />
            <input type="hidden" name="day_statuses" id="fewo-day-statuses" value="{}" />

            <div class="fewo-month-grid" role="table" aria-label="<?php echo esc_attr(wp_date('F Y', $first_day_ts)); ?>">
                <div class="fewo-weekdays" role="rowgroup">
                    <?php foreach ($weekdays as $weekday) : ?>
                        <div class="fewo-weekday" role="columnheader"><?php echo esc_html($weekday); ?></div>
                    <?php endforeach; ?>
                </div>

                <div class="fewo-days" role="rowgroup">
                    <?php
                    for ($i = 1; $i < $start_weekday; $i++) {
                        echo '<div class="fewo-day fewo-empty" aria-hidden="true"></div>';
                    }

                    for ($day = 1; $day <= $days_in_month; $day++) {
                        $date   = sprintf('%04d-%02d-%02d', $year, $month_num, $day);
                        $status = isset($statuses[$date]) ? Fewo_Kalender_DB::normalize_status($statuses[$date]) : 'free';

                        $classes = array('fewo-day', 'fewo-status-' . $status);
                        if ($date === $today) {
                            $classes[] = 'fewo-today';
                        }

                        echo '<button type="button" class="' . esc_attr(implode(' ', $classes)) . '" data-date="' . esc_attr($date) . '" data-status="' . esc_attr($status) . '">';
                        echo '<span class="fewo-day-number">' . esc_html((string) $day) . '</span>';
                        echo '<span class="fewo-day-status-label">' . esc_html($status) . '</span>';
                        echo '</button>';
                    }
                    ?>
                </div>
            </div>

            <?php submit_button(__('Status speichern', 'fewo-kalender')); ?>
        </form>
    </div>
</div>
