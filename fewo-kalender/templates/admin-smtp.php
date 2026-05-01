<?php
if (! defined('ABSPATH')) {
    exit;
}

$smtp_enabled    = ! empty($smtp['enabled']);
$smtp_host       = isset($smtp['host']) ? (string) $smtp['host'] : '';
$smtp_port       = isset($smtp['port']) ? (int) $smtp['port'] : 587;
$smtp_encryption = isset($smtp['encryption']) ? (string) $smtp['encryption'] : 'tls';
$smtp_username   = isset($smtp['username']) ? (string) $smtp['username'] : '';
$smtp_from_email = isset($smtp['from_email']) ? (string) $smtp['from_email'] : '';
$smtp_from_name  = isset($smtp['from_name']) ? (string) $smtp['from_name'] : '';
?>
<div class="wrap fewo-admin-wrap">
    <h1><?php esc_html_e('E-Mail Einstellungen (SMTP)', 'fewo-kalender'); ?></h1>
    <p><a href="<?php echo esc_url(add_query_arg(array('page' => 'fewo-kalender'), admin_url('admin.php'))); ?>">&larr; <?php esc_html_e('Zurueck zur Uebersicht', 'fewo-kalender'); ?></a></p>

    <?php if ('smtp_saved' === $message) : ?>
        <div class="notice notice-success is-dismissible"><p><?php esc_html_e('SMTP-Einstellungen gespeichert.', 'fewo-kalender'); ?></p></div>
    <?php endif; ?>

    <div class="fewo-admin-card">
        <h2><?php esc_html_e('SMTP-Konfiguration', 'fewo-kalender'); ?></h2>
        <p class="description"><?php esc_html_e('Trage hier deine SMTP-Zugangsdaten ein, damit Buchungsanfragen per E-Mail versendet werden.', 'fewo-kalender'); ?></p>

        <form method="post">
            <?php wp_nonce_field('fewo_save_smtp'); ?>
            <input type="hidden" name="fewo_action" value="save_smtp" />

            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php esc_html_e('SMTP aktivieren', 'fewo-kalender'); ?></th>
                    <td>
                        <label for="fewo_smtp_enabled">
                            <input type="checkbox" id="fewo_smtp_enabled" name="smtp_enabled" value="1" <?php checked($smtp_enabled); ?> />
                            <?php esc_html_e('E-Mails ueber SMTP versenden', 'fewo-kalender'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="fewo_smtp_host"><?php esc_html_e('SMTP-Host', 'fewo-kalender'); ?></label></th>
                    <td>
                        <input type="text" id="fewo_smtp_host" name="smtp_host" class="regular-text" value="<?php echo esc_attr($smtp_host); ?>" placeholder="smtp.gmail.com" />
                        <p class="description"><?php esc_html_e('z.B. smtp.gmail.com, smtp.web.de, mail.strato.de', 'fewo-kalender'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="fewo_smtp_port"><?php esc_html_e('SMTP-Port', 'fewo-kalender'); ?></label></th>
                    <td>
                        <input type="number" id="fewo_smtp_port" name="smtp_port" class="small-text" value="<?php echo esc_attr((string) $smtp_port); ?>" min="1" max="65535" />
                        <p class="description"><?php esc_html_e('Typisch: 587 (TLS), 465 (SSL), 25 (unverschluesselt)', 'fewo-kalender'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="fewo_smtp_encryption"><?php esc_html_e('Verschluesselung', 'fewo-kalender'); ?></label></th>
                    <td>
                        <select id="fewo_smtp_encryption" name="smtp_encryption">
                            <option value="tls" <?php selected($smtp_encryption, 'tls'); ?>>STARTTLS (Port 587)</option>
                            <option value="ssl" <?php selected($smtp_encryption, 'ssl'); ?>>SSL/TLS (Port 465)</option>
                            <option value="none" <?php selected($smtp_encryption, 'none'); ?>><?php esc_html_e('Keine', 'fewo-kalender'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="fewo_smtp_username"><?php esc_html_e('Benutzername', 'fewo-kalender'); ?></label></th>
                    <td>
                        <input type="text" id="fewo_smtp_username" name="smtp_username" class="regular-text" value="<?php echo esc_attr($smtp_username); ?>" autocomplete="off" />
                        <p class="description"><?php esc_html_e('Deine E-Mail-Adresse oder SMTP-Benutzername', 'fewo-kalender'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="fewo_smtp_password"><?php esc_html_e('Passwort', 'fewo-kalender'); ?></label></th>
                    <td>
                        <input type="password" id="fewo_smtp_password" name="smtp_password" class="regular-text" value="" autocomplete="new-password" />
                        <p class="description">
                            <?php if (! empty($smtp['password'])) : ?>
                                <span style="color:#2e7d32;">&#10003; <?php esc_html_e('Passwort gespeichert. Nur neu ausfuellen zum Aendern.', 'fewo-kalender'); ?></span>
                            <?php else : ?>
                                <?php esc_html_e('SMTP-Passwort eingeben', 'fewo-kalender'); ?>
                            <?php endif; ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="fewo_smtp_from_email"><?php esc_html_e('Absender-E-Mail', 'fewo-kalender'); ?></label></th>
                    <td>
                        <input type="email" id="fewo_smtp_from_email" name="smtp_from_email" class="regular-text" value="<?php echo esc_attr($smtp_from_email); ?>" />
                        <p class="description"><?php esc_html_e('E-Mail-Adresse, von der gesendet wird (sollte mit SMTP-Benutzer uebereinstimmen)', 'fewo-kalender'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="fewo_smtp_from_name"><?php esc_html_e('Absender-Name', 'fewo-kalender'); ?></label></th>
                    <td>
                        <input type="text" id="fewo_smtp_from_name" name="smtp_from_name" class="regular-text" value="<?php echo esc_attr($smtp_from_name); ?>" placeholder="Fewo Buchung" />
                    </td>
                </tr>
            </table>

            <?php submit_button(__('Einstellungen speichern', 'fewo-kalender')); ?>
        </form>
    </div>

    <div class="fewo-admin-card">
        <h2><?php esc_html_e('Test-E-Mail senden', 'fewo-kalender'); ?></h2>
        <p class="description"><?php esc_html_e('Sendet eine Test-E-Mail mit den aktuell gespeicherten SMTP-Einstellungen.', 'fewo-kalender'); ?></p>
        <form method="post">
            <?php wp_nonce_field('fewo_smtp_test'); ?>
            <input type="hidden" name="fewo_action" value="smtp_test" />
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="fewo_test_email"><?php esc_html_e('Test-Empfaenger', 'fewo-kalender'); ?></label></th>
                    <td>
                        <input type="email" id="fewo_test_email" name="test_email" class="regular-text" value="<?php echo esc_attr($smtp_from_email); ?>" required />
                    </td>
                </tr>
            </table>
            <?php submit_button(__('Test senden', 'fewo-kalender'), 'secondary'); ?>
        </form>
        <?php
        if ('test_sent' === $message) :
            echo '<div class="notice notice-success inline"><p>' . esc_html__('Test-E-Mail wurde gesendet.', 'fewo-kalender') . '</p></div>';
        elseif ('test_failed' === $message) :
            echo '<div class="notice notice-error inline"><p>' . esc_html__('Test-E-Mail konnte nicht gesendet werden. SMTP-Einstellungen pruefen.', 'fewo-kalender') . '</p></div>';
        endif;
        ?>
    </div>
</div>
