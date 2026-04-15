<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Frontend-Shortcode und Monatsnavigation per AJAX.
 */
class Fewo_Kalender_Shortcode
{
    public function __construct()
    {
        add_shortcode('fewo_kalender', array($this, 'render_shortcode'));

        add_action('wp_ajax_fewo_kalender_get_month', array($this, 'ajax_get_month'));
        add_action('wp_ajax_nopriv_fewo_kalender_get_month', array($this, 'ajax_get_month'));
    }

    /**
     * @param array<string, string> $atts
     * @return string
     */
    public function render_shortcode($atts)
    {
        $atts = shortcode_atts(
            array(
                'id' => 0,
                'design' => '',
            ),
            $atts,
            'fewo_kalender'
        );

        $calendar_id = absint($atts['id']);
        if ($calendar_id <= 0) {
            return '<p>' . esc_html__('Fewo Kalender: Bitte gueltige Kalender-ID angeben.', 'fewo-kalender') . '</p>';
        }

        $calendar = Fewo_Kalender_DB::get_calendar($calendar_id);
        if (! $calendar) {
            return '<p>' . esc_html__('Kalender nicht gefunden.', 'fewo-kalender') . '</p>';
        }

        wp_enqueue_style(
            'fewo-kalender-frontend',
            FEWO_KALENDER_URL . 'assets/css/frontend.css',
            array(),
            FEWO_KALENDER_VERSION
        );

        wp_enqueue_script(
            'fewo-kalender-frontend',
            FEWO_KALENDER_URL . 'assets/js/frontend.js',
            array(),
            FEWO_KALENDER_VERSION,
            true
        );

        wp_localize_script(
            'fewo-kalender-frontend',
            'fewoKalenderFrontend',
            array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce'   => wp_create_nonce('fewo_frontend_nonce'),
                'labels'  => array(
                    'free'       => __('frei', 'fewo-kalender'),
                    'booked'     => __('belegt', 'fewo-kalender'),
                    'changeover' => __('Wechseltag', 'fewo-kalender'),
                    'today'      => __('Heute', 'fewo-kalender'),
                ),
            )
        );

        $month = current_time('Y-m');
        $design = '' !== $atts['design']
            ? Fewo_Kalender_DB::normalize_design($atts['design'])
            : Fewo_Kalender_DB::normalize_design(isset($calendar->design) ? $calendar->design : 'modern');

        return $this->render_calendar_wrapper($calendar, $month, $design);
    }

    public function ajax_get_month()
    {
        check_ajax_referer('fewo_frontend_nonce', 'nonce');

        $calendar_id = isset($_POST['calendar_id']) ? absint($_POST['calendar_id']) : 0;
        $month       = isset($_POST['month']) ? sanitize_text_field(wp_unslash($_POST['month'])) : '';

        if ($calendar_id <= 0 || ! preg_match('/^\d{4}-\d{2}$/', $month)) {
            wp_send_json_error(array('message' => __('Ungueltige Anfrage.', 'fewo-kalender')), 400);
        }

        $calendar = Fewo_Kalender_DB::get_calendar($calendar_id);
        if (! $calendar) {
            wp_send_json_error(array('message' => __('Kalender nicht gefunden.', 'fewo-kalender')), 404);
        }

        ob_start();
        echo $this->render_month_grid($calendar_id, $month); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        $html = ob_get_clean();

        wp_send_json_success(
            array(
                'html'       => $html,
                'monthLabel' => $this->month_label($month),
            )
        );
    }

    /**
     * @param object $calendar
     * @param string $month
     * @param string $design
     * @return string
     */
    private function render_calendar_wrapper($calendar, $month, $design)
    {
        $container_id = 'fewo-calendar-' . absint($calendar->id) . '-' . wp_rand(100, 999);
        $theme_class  = 'fewo-theme-' . Fewo_Kalender_DB::normalize_design($design);

        ob_start();
        include FEWO_KALENDER_PATH . 'templates/frontend-calendar.php';

        return (string) ob_get_clean();
    }

    /**
     * @param int $calendar_id
     * @param string $month
     * @return string
     */
    public function render_month_grid($calendar_id, $month)
    {
        $parts = explode('-', $month);
        $year  = (int) $parts[0];
        $m     = (int) $parts[1];

        $statuses = Fewo_Kalender_DB::get_statuses_for_month($calendar_id, $year, $m);

        $first_day_ts  = strtotime(sprintf('%04d-%02d-01', $year, $m));
        $days_in_month = (int) date('t', $first_day_ts);
        $start_weekday = (int) date('N', $first_day_ts);
        $today         = current_time('Y-m-d');

        $weekdays = array('Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa', 'So');

        ob_start();
        ?>
        <div class="fewo-month-grid" role="table" aria-label="<?php echo esc_attr($this->month_label($month)); ?>">
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
                    $date   = sprintf('%04d-%02d-%02d', $year, $m, $day);
                    $status = isset($statuses[$date]) ? Fewo_Kalender_DB::normalize_status($statuses[$date]) : 'free';

                    $classes = array('fewo-day', 'fewo-status-' . $status);
                    if ($date === $today) {
                        $classes[] = 'fewo-today';
                    }

                    echo '<div class="' . esc_attr(implode(' ', $classes)) . '" data-date="' . esc_attr($date) . '">';
                    echo '<span class="fewo-day-number">' . esc_html((string) $day) . '</span>';
                    echo '</div>';
                }
                ?>
            </div>
        </div>
        <?php

        return (string) ob_get_clean();
    }

    /**
     * @param string $month
     * @return string
     */
    public function month_label($month)
    {
        $ts = strtotime($month . '-01');

        return wp_date('F Y', $ts);
    }
}
