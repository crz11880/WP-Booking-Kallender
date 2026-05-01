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
        add_action('wp_ajax_fewo_kalender_booking_ajax', array($this, 'handle_booking_ajax'));
        add_action('wp_ajax_nopriv_fewo_kalender_booking_ajax', array($this, 'handle_booking_ajax'));
        add_action('admin_post_fewo_kalender_booking_request', array($this, 'handle_booking_request'));
        add_action('admin_post_nopriv_fewo_kalender_booking_request', array($this, 'handle_booking_request'));
    }

    public function handle_booking_ajax()
    {
        if (! check_ajax_referer('fewo_booking_request', 'fewo_booking_nonce', false)) {
            wp_send_json_error(array('message' => 'Sicherheitsfehler. Bitte Seite neu laden.'));
        }

        // Honeypot: Bots fuellen dieses Feld aus, Menschen nicht
        $honeypot = isset($_POST['fewo_hp']) ? (string) wp_unslash($_POST['fewo_hp']) : '';
        if ('' !== $honeypot) {
            wp_send_json_error(array('message' => 'Spam erkannt.'));
        }

        // Zeitcheck: Formular muss mindestens 3 Sekunden offen gewesen sein
        $form_ts = isset($_POST['fewo_form_ts']) ? (int) wp_unslash($_POST['fewo_form_ts']) : 0;
        if ($form_ts <= 0 || (time() - $form_ts) < 3) {
            wp_send_json_error(array('message' => 'Bitte Formular nicht zu schnell absenden.'));
        }

        $calendar_id = isset($_POST['calendar_id']) ? absint($_POST['calendar_id']) : 0;
        $first_name  = isset($_POST['first_name']) ? sanitize_text_field(wp_unslash($_POST['first_name'])) : '';
        $last_name   = isset($_POST['last_name']) ? sanitize_text_field(wp_unslash($_POST['last_name'])) : '';
        $email       = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
        $from_date   = isset($_POST['from_date']) ? sanitize_text_field(wp_unslash($_POST['from_date'])) : '';
        $to_date     = isset($_POST['to_date']) ? sanitize_text_field(wp_unslash($_POST['to_date'])) : '';
        $message     = isset($_POST['message']) ? sanitize_textarea_field(wp_unslash($_POST['message'])) : '';

        $errors = array();

        if ('' === $first_name) {
            $errors['first_name'] = 'Bitte Vornamen eingeben.';
        }
        if ('' === $last_name) {
            $errors['last_name'] = 'Bitte Nachnamen eingeben.';
        }
        if (! is_email($email)) {
            $errors['email'] = 'Bitte gueltige E-Mail-Adresse eingeben.';
        }
        if ('' === $from_date || ! Fewo_Kalender_DB::is_valid_date($from_date)) {
            $errors['from_date'] = 'Bitte Anreisedatum im Kalender auswaehlen.';
        }
        if ('' === $to_date || ! Fewo_Kalender_DB::is_valid_date($to_date)) {
            $errors['to_date'] = 'Bitte Abreisedatum im Kalender auswaehlen.';
        }
        if (empty($errors) && $from_date > $to_date) {
            $errors['from_date'] = 'Anreisedatum muss vor Abreisedatum liegen.';
        }

        if (! empty($errors)) {
            wp_send_json_error(array('errors' => $errors));
        }

        $calendar  = Fewo_Kalender_DB::get_calendar($calendar_id);
        $recipient = $calendar ? Fewo_Kalender_DB::normalize_inquiry_email((string) $calendar->inquiry_email) : '';

        if (! $calendar || empty($calendar->inquiry_enabled) || '' === $recipient) {
            wp_send_json_error(array('message' => 'Buchungsanfrage fuer diesen Kalender nicht aktiviert.'));
        }

        $non_free_days = Fewo_Kalender_DB::get_statuses_for_range($calendar_id, $from_date, $to_date);
        if (! empty($non_free_days)) {
            wp_send_json_error(array('errors' => array(
                'from_date' => 'Der gewaehlte Zeitraum enthaelt belegte Tage.',
                'to_date'   => 'Bitte nur freie Tage auswaehlen.',
            )));
        }

        $full_name = trim($first_name . ' ' . $last_name);
        $subject   = sprintf(
            'Buchungsanfrage fuer %s von %s bis %s von %s (%s)',
            (string) $calendar->name,
            $from_date,
            $to_date,
            $full_name,
            $email
        );

        $body_lines = array(
            'Kalender: ' . (string) $calendar->name,
            'Von: ' . $from_date,
            'Bis: ' . $to_date,
            'Name: ' . $full_name,
            'E-Mail: ' . $email,
        );

        if ('' !== $message) {
            $body_lines[] = '';
            $body_lines[] = 'Nachricht:';
            $body_lines[] = $message;
        }

        $body    = implode("\n", $body_lines);
        $headers = array('Reply-To: ' . $full_name . ' <' . $email . '>');
        $sent    = wp_mail($recipient, $subject, $body, $headers);

        if ($sent) {
            wp_send_json_success(array('message' => 'Danke! Deine Buchungsanfrage wurde erfolgreich versendet.'));
        } else {
            wp_send_json_error(array('message' => 'E-Mail konnte nicht gesendet werden. Bitte spaeter erneut versuchen.'));
        }
    }

    public function handle_booking_request()
    {
        check_admin_referer('fewo_booking_request', 'fewo_booking_nonce');

        $calendar_id = isset($_POST['calendar_id']) ? absint($_POST['calendar_id']) : 0;
        $first_name  = isset($_POST['first_name']) ? sanitize_text_field(wp_unslash($_POST['first_name'])) : '';
        $last_name   = isset($_POST['last_name']) ? sanitize_text_field(wp_unslash($_POST['last_name'])) : '';
        $email       = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
        $from_date   = isset($_POST['from_date']) ? sanitize_text_field(wp_unslash($_POST['from_date'])) : '';
        $to_date     = isset($_POST['to_date']) ? sanitize_text_field(wp_unslash($_POST['to_date'])) : '';
        $message     = isset($_POST['message']) ? sanitize_textarea_field(wp_unslash($_POST['message'])) : '';

        $redirect_url = wp_get_referer();
        if (! $redirect_url) {
            $redirect_url = home_url('/');
        }

        $calendar = Fewo_Kalender_DB::get_calendar($calendar_id);
        $recipient = $calendar ? Fewo_Kalender_DB::normalize_inquiry_email((string) $calendar->inquiry_email) : '';

        if (
            ! $calendar ||
            empty($calendar->inquiry_enabled) ||
            '' === $recipient ||
            '' === $first_name ||
            '' === $last_name ||
            ! is_email($email) ||
            ! Fewo_Kalender_DB::is_valid_date($from_date) ||
            ! Fewo_Kalender_DB::is_valid_date($to_date) ||
            $from_date > $to_date
        ) {
            wp_safe_redirect(
                add_query_arg(
                    array(
                        'fewoRequest'   => 'error',
                        'fewoCalendar'  => $calendar_id,
                    ),
                    $redirect_url
                )
            );
            exit;
        }

        $non_free_days = Fewo_Kalender_DB::get_statuses_for_range($calendar_id, $from_date, $to_date);
        if (! empty($non_free_days)) {
            wp_safe_redirect(
                add_query_arg(
                    array(
                        'fewoRequest'   => 'error',
                        'fewoCalendar'  => $calendar_id,
                    ),
                    $redirect_url
                )
            );
            exit;
        }

        $full_name = trim($first_name . ' ' . $last_name);

        $subject = sprintf(
            'Buchungsanfrage fuer %s von %s bis %s von %s (%s)',
            (string) $calendar->name,
            $from_date,
            $to_date,
            $full_name,
            $email
        );

        $body_lines = array(
            'Neue Buchungsanfrage',
            '',
            'Kalender: ' . (string) $calendar->name,
            'Von: ' . $from_date,
            'Bis: ' . $to_date,
            'Name: ' . $full_name,
            'E-Mail: ' . $email,
        );

        if ('' !== $message) {
            $body_lines[] = '';
            $body_lines[] = 'Nachricht:';
            $body_lines[] = $message;
        }

        $body = implode("\n", $body_lines);
        $headers = array('Reply-To: ' . $full_name . ' <' . $email . '>');

        $sent = wp_mail($recipient, $subject, $body, $headers);

        wp_safe_redirect(
            add_query_arg(
                array(
                    'fewoRequest'   => $sent ? 'success' : 'error',
                    'fewoCalendar'  => $calendar_id,
                ),
                $redirect_url
            )
        );
        exit;
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
                    'halfday'    => __('Halber Tag', 'fewo-kalender'),
                    'halfday_reverse' => __('Halber Tag (invertiert)', 'fewo-kalender'),
                    'today'      => __('Heute', 'fewo-kalender'),
                    'selectionHint' => __('Bitte zuerst Anreise und dann Abreise im Kalender anklicken.', 'fewo-kalender'),
                    'freeOnlyHint' => __('Es koennen nur freie Tage markiert werden.', 'fewo-kalender'),
                    'rangeBlockedHint' => __('Der gewaehlte Zeitraum enthaelt nicht freie Tage. Bitte nur freie Tage waehlen.', 'fewo-kalender'),
                    'bookingAjaxAction' => 'fewo_kalender_booking_ajax',
                ),
            )
        );

        $month = current_time('Y-m');
        $design = '' !== $atts['design']
            ? Fewo_Kalender_DB::normalize_design($atts['design'])
            : Fewo_Kalender_DB::normalize_design(isset($calendar->design) ? $calendar->design : 'modern');
        $request_message = '';
        $request_status = isset($_GET['fewoRequest']) ? sanitize_key(wp_unslash($_GET['fewoRequest'])) : '';
        $request_calendar = isset($_GET['fewoCalendar']) ? absint($_GET['fewoCalendar']) : 0;

        if ($request_calendar === (int) $calendar->id) {
            if ('success' === $request_status) {
                $request_message = '<div class="fewo-request-feedback is-success">' . esc_html__('Danke! Deine Buchungsanfrage wurde erfolgreich versendet.', 'fewo-kalender') . '</div>';
            } elseif ('error' === $request_status) {
                $request_message = '<div class="fewo-request-feedback is-error">' . esc_html__('Buchungsanfrage konnte nicht gesendet werden. Bitte Eingaben pruefen und erneut versuchen.', 'fewo-kalender') . '</div>';
            }
        }

        return $this->render_calendar_wrapper($calendar, $month, $design, $request_message);
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
     * @param string $request_message
     * @return string
     */
    private function render_calendar_wrapper($calendar, $month, $design, $request_message = '')
    {
        $container_id = 'fewo-calendar-' . absint($calendar->id) . '-' . wp_rand(100, 999);
        $theme_class  = 'fewo-theme-' . Fewo_Kalender_DB::normalize_design($design);
        $inquiry_enabled = ! empty($calendar->inquiry_enabled);
        $inquiry_email = Fewo_Kalender_DB::normalize_inquiry_email(isset($calendar->inquiry_email) ? (string) $calendar->inquiry_email : '');

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
