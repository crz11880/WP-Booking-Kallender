<?php
/**
 * Plugin Name: Fewo Kalender
 * Description: Einfacher Belegungskalender fuer mehrere Ferienwohnungen mit Backend-Verwaltung und Shortcode-Anzeige.
 * Version: 1.1.2
 * Author: Your Name
 * Text Domain: fewo-kalender
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if (! defined('ABSPATH')) {
    exit;
}

define('FEWO_KALENDER_VERSION', '1.1.2');
define('FEWO_KALENDER_FILE', __FILE__);
define('FEWO_KALENDER_PATH', plugin_dir_path(__FILE__));
define('FEWO_KALENDER_URL', plugin_dir_url(__FILE__));

require_once FEWO_KALENDER_PATH . 'includes/class-db.php';
require_once FEWO_KALENDER_PATH . 'includes/class-admin.php';
require_once FEWO_KALENDER_PATH . 'includes/class-shortcode.php';

register_activation_hook(FEWO_KALENDER_FILE, array('Fewo_Kalender_DB', 'activate'));

/**
 * Hauptklasse fuer Plugin-Initialisierung.
 */
final class Fewo_Kalender
{
    /**
     * @var Fewo_Kalender|null
     */
    private static $instance = null;

    /**
     * @var Fewo_Kalender_Admin
     */
    private $admin;

    /**
     * @var Fewo_Kalender_Shortcode
     */
    private $shortcode;

    /**
     * @return Fewo_Kalender
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
        Fewo_Kalender_DB::maybe_upgrade();

        $this->admin     = new Fewo_Kalender_Admin();
        $this->shortcode = new Fewo_Kalender_Shortcode();
    }
}

add_action('plugins_loaded', array('Fewo_Kalender', 'instance'));
