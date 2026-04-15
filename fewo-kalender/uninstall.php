<?php
/**
 * Entfernt Plugin-Daten bei Deinstallation.
 */

if (! defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

$calendars = $wpdb->prefix . 'fewo_calendars';
$statuses  = $wpdb->prefix . 'fewo_calendar_statuses';

$wpdb->query("DROP TABLE IF EXISTS {$statuses}"); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
$wpdb->query("DROP TABLE IF EXISTS {$calendars}"); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

delete_option('fewo_kalender_version');
