<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Datenbankzugriff fuer Kalender und Tagesstatus.
 */
class Fewo_Kalender_DB
{
    const DEFAULT_DESIGN = 'modern';

    /**
     * @return string
     */
    public static function calendars_table()
    {
        global $wpdb;

        return $wpdb->prefix . 'fewo_calendars';
    }

    /**
     * @return string
     */
    public static function statuses_table()
    {
        global $wpdb;

        return $wpdb->prefix . 'fewo_calendar_statuses';
    }

    /**
     * Erstellt Datenbanktabellen bei Aktivierung.
     */
    public static function activate()
    {
        self::install_or_update_tables();
        update_option('fewo_kalender_version', FEWO_KALENDER_VERSION);
    }

    /**
     * Fuehrt automatische Updates fuer bestehende Installationen aus.
     *
     * @return void
     */
    public static function maybe_upgrade()
    {
        $installed_version = (string) get_option('fewo_kalender_version', '0.0.0');

        if (version_compare($installed_version, FEWO_KALENDER_VERSION, '>=')) {
            return;
        }

        self::install_or_update_tables();
        update_option('fewo_kalender_version', FEWO_KALENDER_VERSION);
    }

    /**
     * Erstellt oder aktualisiert Datenbanktabellen.
     *
     * @return void
     */
    private static function install_or_update_tables()
    {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset_collate = $wpdb->get_charset_collate();
        $calendars       = self::calendars_table();
        $statuses        = self::statuses_table();

        $sql_calendars = "CREATE TABLE {$calendars} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(191) NOT NULL,
            description TEXT NULL,
            design VARCHAR(32) NOT NULL DEFAULT 'modern',
            inquiry_enabled TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
            inquiry_email VARCHAR(191) NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id)
        ) {$charset_collate};";

        $sql_statuses = "CREATE TABLE {$statuses} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            calendar_id BIGINT(20) UNSIGNED NOT NULL,
            status_date DATE NOT NULL,
            status VARCHAR(20) NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY calendar_date (calendar_id, status_date),
            KEY calendar_id (calendar_id)
        ) {$charset_collate};";

        dbDelta($sql_calendars);
        dbDelta($sql_statuses);

        // Fallback fuer Hosts, auf denen dbDelta Spaltenaenderungen unzuverlaessig anlegt.
        $has_design_column = $wpdb->get_var("SHOW COLUMNS FROM {$calendars} LIKE 'design'"); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        if (! $has_design_column) {
            $wpdb->query("ALTER TABLE {$calendars} ADD COLUMN design VARCHAR(32) NOT NULL DEFAULT 'modern' AFTER description"); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        }

        $has_inquiry_enabled_column = $wpdb->get_var("SHOW COLUMNS FROM {$calendars} LIKE 'inquiry_enabled'"); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        if (! $has_inquiry_enabled_column) {
            $wpdb->query("ALTER TABLE {$calendars} ADD COLUMN inquiry_enabled TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 AFTER design"); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        }

        $has_inquiry_email_column = $wpdb->get_var("SHOW COLUMNS FROM {$calendars} LIKE 'inquiry_email'"); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        if (! $has_inquiry_email_column) {
            $wpdb->query("ALTER TABLE {$calendars} ADD COLUMN inquiry_email VARCHAR(191) NULL AFTER inquiry_enabled"); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        }
    }

    /**
     * @return array<int, object>
     */
    public static function get_calendars()
    {
        global $wpdb;

        $table = self::calendars_table();

        return $wpdb->get_results("SELECT id, name, description, design, inquiry_enabled, inquiry_email FROM {$table} ORDER BY id ASC"); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    }

    /**
     * @param int $id
     * @return object|null
     */
    public static function get_calendar($id)
    {
        global $wpdb;

        $table = self::calendars_table();

        return $wpdb->get_row($wpdb->prepare("SELECT id, name, description, design, inquiry_enabled, inquiry_email FROM {$table} WHERE id = %d", $id));
    }

    /**
     * @param string $name
     * @param string $description
     * @param string $design
     * @param bool $inquiry_enabled
     * @param string $inquiry_email
     * @return int|false
     */
    public static function create_calendar($name, $description = '', $design = self::DEFAULT_DESIGN, $inquiry_enabled = false, $inquiry_email = '')
    {
        global $wpdb;

        $table = self::calendars_table();
        $design = self::normalize_design($design);
        $inquiry_enabled = $inquiry_enabled ? 1 : 0;
        $inquiry_email = self::normalize_inquiry_email($inquiry_email);

        $inserted = $wpdb->insert(
            $table,
            array(
                'name'        => $name,
                'description' => $description,
                'design'      => $design,
                'inquiry_enabled' => $inquiry_enabled,
                'inquiry_email' => $inquiry_email,
                'created_at'  => current_time('mysql'),
            ),
            array('%s', '%s', '%s', '%d', '%s', '%s')
        );

        if (! $inserted) {
            return false;
        }

        return (int) $wpdb->insert_id;
    }

    /**
     * @param int $id
     * @param string $name
     * @param string $description
     * @param string $design
     * @param bool $inquiry_enabled
     * @param string $inquiry_email
     * @return bool
     */
    public static function update_calendar($id, $name, $description = '', $design = self::DEFAULT_DESIGN, $inquiry_enabled = false, $inquiry_email = '')
    {
        global $wpdb;

        $table = self::calendars_table();
        $design = self::normalize_design($design);
        $inquiry_enabled = $inquiry_enabled ? 1 : 0;
        $inquiry_email = self::normalize_inquiry_email($inquiry_email);

        $updated = $wpdb->update(
            $table,
            array(
                'name'        => $name,
                'description' => $description,
                'design'      => $design,
                'inquiry_enabled' => $inquiry_enabled,
                'inquiry_email' => $inquiry_email,
            ),
            array('id' => $id),
            array('%s', '%s', '%s', '%d', '%s'),
            array('%d')
        );

        return false !== $updated;
    }

    /**
     * @param int $id
     * @return bool
     */
    public static function delete_calendar($id)
    {
        global $wpdb;

        $calendars = self::calendars_table();
        $statuses  = self::statuses_table();

        $wpdb->delete($statuses, array('calendar_id' => $id), array('%d'));
        $deleted = $wpdb->delete($calendars, array('id' => $id), array('%d'));

        return (bool) $deleted;
    }

    /**
     * @param int $calendar_id
     * @param int $year
     * @param int $month
     * @return array<string, string>
     */
    public static function get_statuses_for_month($calendar_id, $year, $month)
    {
        $start = sprintf('%04d-%02d-01', $year, $month);
        $end   = date('Y-m-t', strtotime($start));

        return self::get_statuses_for_range($calendar_id, $start, $end);
    }

    /**
     * @param int $calendar_id
     * @param string $start
     * @param string $end
     * @return array<string, string>
     */
    public static function get_statuses_for_range($calendar_id, $start, $end)
    {
        global $wpdb;

        $table = self::statuses_table();
        $rows  = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT status_date, status FROM {$table} WHERE calendar_id = %d AND status_date BETWEEN %s AND %s",
                $calendar_id,
                $start,
                $end
            )
        );

        $statuses = array();

        foreach ($rows as $row) {
            $statuses[$row->status_date] = $row->status;
        }

        return $statuses;
    }

    /**
     * Speichert nur Tage, die von "frei" abweichen.
     *
     * @param int $calendar_id
     * @param array<string, string> $status_map
     * @return void
     */
    public static function save_statuses($calendar_id, $status_map)
    {
        global $wpdb;

        $table = self::statuses_table();

        foreach ($status_map as $date => $status) {
            if (! self::is_valid_date($date)) {
                continue;
            }

            $status = self::normalize_status($status);

            if ('free' === $status) {
                $wpdb->delete(
                    $table,
                    array(
                        'calendar_id' => $calendar_id,
                        'status_date' => $date,
                    ),
                    array('%d', '%s')
                );
                continue;
            }

            $existing_id = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT id FROM {$table} WHERE calendar_id = %d AND status_date = %s LIMIT 1",
                    $calendar_id,
                    $date
                )
            );

            if ($existing_id) {
                $wpdb->update(
                    $table,
                    array('status' => $status),
                    array('id' => (int) $existing_id),
                    array('%s'),
                    array('%d')
                );
            } else {
                $wpdb->insert(
                    $table,
                    array(
                        'calendar_id' => $calendar_id,
                        'status_date' => $date,
                        'status'      => $status,
                    ),
                    array('%d', '%s', '%s')
                );
            }
        }
    }

    /**
     * @param string $value
     * @return string
     */
    public static function normalize_status($value)
    {
        $value = strtolower((string) $value);

        if ('booked' === $value || 'changeover' === $value || 'halfday' === $value || 'halfday_reverse' === $value) {
            return $value;
        }

        return 'free';
    }

    /**
     * @return array<string, string>
     */
    public static function get_design_options()
    {
        return array(
            'modern'   => __('Modern', 'fewo-kalender'),
            'ocean'    => __('Ocean', 'fewo-kalender'),
            'terracotta' => __('Terracotta', 'fewo-kalender'),
        );
    }

    /**
     * @param string $design
     * @return string
     */
    public static function normalize_design($design)
    {
        $design  = sanitize_key((string) $design);
        $options = self::get_design_options();

        if (isset($options[$design])) {
            return $design;
        }

        return self::DEFAULT_DESIGN;
    }

    /**
     * @param string $email
     * @return string
     */
    public static function normalize_inquiry_email($email)
    {
        $email = sanitize_email((string) $email);

        return is_email($email) ? $email : '';
    }

    /**
     * @param string $date
     * @return bool
     */
    public static function is_valid_date($date)
    {
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return false;
        }

        $parts = explode('-', $date);

        return checkdate((int) $parts[1], (int) $parts[2], (int) $parts[0]);
    }
}
