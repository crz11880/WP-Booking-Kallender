<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Admin-Bereich fuer Kalenderverwaltung.
 */
class Fewo_Kalender_Admin
{
    /**
     * @var string
     */
    private $menu_slug = 'fewo-kalender';

    /**
     * @var string
     */
    private $edit_slug = 'fewo-kalender-edit';

    public function __construct()
    {
        add_action('admin_menu', array($this, 'register_menu'));
        add_action('admin_init', array($this, 'handle_actions'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
    }

    public function register_menu()
    {
        add_menu_page(
            __('Fewo Kalender', 'fewo-kalender'),
            __('Fewo Kalender', 'fewo-kalender'),
            'manage_options',
            $this->menu_slug,
            array($this, 'render_list_page'),
            'dashicons-calendar-alt',
            58
        );

        add_submenu_page(
            null,
            __('Kalender bearbeiten', 'fewo-kalender'),
            __('Kalender bearbeiten', 'fewo-kalender'),
            'manage_options',
            $this->edit_slug,
            array($this, 'render_edit_page')
        );
    }

    public function enqueue_assets($hook)
    {
        $allowed_hooks = array(
            'toplevel_page_' . $this->menu_slug,
            'fewo-kalender_page_' . $this->edit_slug,
            'admin_page_' . $this->edit_slug,
        );

        if (! in_array($hook, $allowed_hooks, true)) {
            return;
        }

        wp_enqueue_style(
            'fewo-kalender-admin',
            FEWO_KALENDER_URL . 'assets/css/admin.css',
            array(),
            FEWO_KALENDER_VERSION
        );

        wp_enqueue_script(
            'fewo-kalender-admin',
            FEWO_KALENDER_URL . 'assets/js/admin.js',
            array(),
            FEWO_KALENDER_VERSION,
            true
        );
    }

    public function handle_actions()
    {
        if (! current_user_can('manage_options')) {
            return;
        }

        $page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';

        if ($this->menu_slug === $page) {
            $this->handle_list_actions();
        }

        if ($this->edit_slug === $page) {
            $this->handle_edit_actions();
        }
    }

    private function handle_list_actions()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $action = isset($_POST['fewo_action']) ? sanitize_key(wp_unslash($_POST['fewo_action'])) : '';

        if ('create_calendar' === $action) {
            check_admin_referer('fewo_create_calendar');

            $name        = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
            $description = isset($_POST['description']) ? sanitize_textarea_field(wp_unslash($_POST['description'])) : '';
            $design      = Fewo_Kalender_DB::normalize_design(isset($_POST['design']) ? wp_unslash($_POST['design']) : '');

            if ('' === $name) {
                $this->redirect_list('error_name');
            }

            $created = Fewo_Kalender_DB::create_calendar($name, $description, $design);

            if (! $created) {
                $this->redirect_list('error_create');
            }

            $this->redirect_list('created');
        }

        if ('delete_calendar' === $action) {
            check_admin_referer('fewo_delete_calendar');

            $calendar_id = isset($_POST['calendar_id']) ? absint($_POST['calendar_id']) : 0;

            if ($calendar_id > 0) {
                Fewo_Kalender_DB::delete_calendar($calendar_id);
            }

            $this->redirect_list('deleted');
        }
    }

    private function handle_edit_actions()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $action = isset($_POST['fewo_action']) ? sanitize_key(wp_unslash($_POST['fewo_action'])) : '';

        if ('update_calendar_meta' === $action) {
            check_admin_referer('fewo_update_calendar_meta');

            $calendar_id  = isset($_POST['calendar_id']) ? absint($_POST['calendar_id']) : 0;
            $name         = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
            $description  = isset($_POST['description']) ? sanitize_textarea_field(wp_unslash($_POST['description'])) : '';
            $design       = Fewo_Kalender_DB::normalize_design(isset($_POST['design']) ? wp_unslash($_POST['design']) : '');
            $month_string = $this->sanitize_month(isset($_POST['month']) ? wp_unslash($_POST['month']) : '');

            if ($calendar_id > 0 && '' !== $name) {
                Fewo_Kalender_DB::update_calendar($calendar_id, $name, $description, $design);
            }

            $this->redirect_edit($calendar_id, $month_string, 'meta_saved');
        }

        if ('save_day_statuses' === $action) {
            check_admin_referer('fewo_save_day_statuses');

            $calendar_id  = isset($_POST['calendar_id']) ? absint($_POST['calendar_id']) : 0;
            $month_string = $this->sanitize_month(isset($_POST['month']) ? wp_unslash($_POST['month']) : '');
            $json         = isset($_POST['day_statuses']) ? wp_unslash($_POST['day_statuses']) : '';

            $status_map = json_decode($json, true);
            if (! is_array($status_map)) {
                $status_map = array();
            }

            $cleaned = array();
            foreach ($status_map as $date => $status) {
                $date   = sanitize_text_field((string) $date);
                $status = sanitize_key((string) $status);

                if (! Fewo_Kalender_DB::is_valid_date($date)) {
                    continue;
                }

                $cleaned[$date] = Fewo_Kalender_DB::normalize_status($status);
            }

            // Loescht erst alle gespeicherten Abweichungen im aktuellen Monat, dann speichert neue.
            $this->clear_month_statuses($calendar_id, $month_string);
            Fewo_Kalender_DB::save_statuses($calendar_id, $cleaned);

            $this->redirect_edit($calendar_id, $month_string, 'saved');
        }
    }

    private function clear_month_statuses($calendar_id, $month_string)
    {
        global $wpdb;

        $parts = explode('-', $month_string);
        if (2 !== count($parts)) {
            return;
        }

        $year  = (int) $parts[0];
        $month = (int) $parts[1];

        $start = sprintf('%04d-%02d-01', $year, $month);
        $end   = date('Y-m-t', strtotime($start));
        $table = Fewo_Kalender_DB::statuses_table();

        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$table} WHERE calendar_id = %d AND status_date BETWEEN %s AND %s",
                $calendar_id,
                $start,
                $end
            )
        );
    }

    private function redirect_list($message)
    {
        wp_safe_redirect(
            add_query_arg(
                array(
                    'page'    => $this->menu_slug,
                    'fewoMsg' => $message,
                ),
                admin_url('admin.php')
            )
        );
        exit;
    }

    private function redirect_edit($calendar_id, $month, $message)
    {
        wp_safe_redirect(
            add_query_arg(
                array(
                    'page'        => $this->edit_slug,
                    'calendar_id' => $calendar_id,
                    'month'       => $month,
                    'fewoMsg'     => $message,
                ),
                admin_url('admin.php')
            )
        );
        exit;
    }

    private function sanitize_month($month)
    {
        $month = sanitize_text_field((string) $month);

        if (preg_match('/^\d{4}-\d{2}$/', $month)) {
            return $month;
        }

        return current_time('Y-m');
    }

    public function render_list_page()
    {
        if (! current_user_can('manage_options')) {
            wp_die(esc_html__('Keine Berechtigung.', 'fewo-kalender'));
        }

        $calendars = Fewo_Kalender_DB::get_calendars();
        $design_options = Fewo_Kalender_DB::get_design_options();
        $message   = isset($_GET['fewoMsg']) ? sanitize_key(wp_unslash($_GET['fewoMsg'])) : '';

        include FEWO_KALENDER_PATH . 'templates/admin-list.php';
    }

    public function render_edit_page()
    {
        if (! current_user_can('manage_options')) {
            wp_die(esc_html__('Keine Berechtigung.', 'fewo-kalender'));
        }

        $calendar_id = isset($_GET['calendar_id']) ? absint($_GET['calendar_id']) : 0;
        $month       = $this->sanitize_month(isset($_GET['month']) ? wp_unslash($_GET['month']) : current_time('Y-m'));

        $calendar = Fewo_Kalender_DB::get_calendar($calendar_id);
        if (! $calendar) {
            wp_die(esc_html__('Kalender nicht gefunden.', 'fewo-kalender'));
        }

        $parts      = explode('-', $month);
        $year       = (int) $parts[0];
        $month_num  = (int) $parts[1];
        $statuses   = Fewo_Kalender_DB::get_statuses_for_month($calendar_id, $year, $month_num);
        $design_options = Fewo_Kalender_DB::get_design_options();
        $message    = isset($_GET['fewoMsg']) ? sanitize_key(wp_unslash($_GET['fewoMsg'])) : '';
        $month_date = strtotime(sprintf('%04d-%02d-01', $year, $month_num));

        $prev_month = date('Y-m', strtotime('-1 month', $month_date));
        $next_month = date('Y-m', strtotime('+1 month', $month_date));

        $weekdays = array('Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa', 'So');

        include FEWO_KALENDER_PATH . 'templates/admin-edit.php';
    }
}
