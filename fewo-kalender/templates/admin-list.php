<?php
if (! defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap fewo-admin-wrap">
    <h1><?php esc_html_e('Fewo Kalender', 'fewo-kalender'); ?></h1>

    <?php if ('created' === $message) : ?>
        <div class="notice notice-success is-dismissible"><p><?php esc_html_e('Kalender wurde angelegt.', 'fewo-kalender'); ?></p></div>
    <?php elseif ('deleted' === $message) : ?>
        <div class="notice notice-success is-dismissible"><p><?php esc_html_e('Kalender wurde geloescht.', 'fewo-kalender'); ?></p></div>
    <?php elseif ('error_name' === $message) : ?>
        <div class="notice notice-error is-dismissible"><p><?php esc_html_e('Bitte einen Namen eingeben.', 'fewo-kalender'); ?></p></div>
    <?php elseif ('error_create' === $message) : ?>
        <div class="notice notice-error is-dismissible"><p><?php esc_html_e('Kalender konnte nicht gespeichert werden.', 'fewo-kalender'); ?></p></div>
    <?php endif; ?>

    <div class="fewo-admin-card">
        <h2><?php esc_html_e('Neuen Kalender anlegen', 'fewo-kalender'); ?></h2>
        <form method="post">
            <?php wp_nonce_field('fewo_create_calendar'); ?>
            <input type="hidden" name="fewo_action" value="create_calendar" />

            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="fewo_name"><?php esc_html_e('Name', 'fewo-kalender'); ?></label></th>
                    <td><input type="text" id="fewo_name" name="name" class="regular-text" required /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="fewo_description"><?php esc_html_e('Beschreibung', 'fewo-kalender'); ?></label></th>
                    <td><textarea id="fewo_description" name="description" rows="3" class="large-text"></textarea></td>
                </tr>
                <tr>
                    <th scope="row"><label for="fewo_design"><?php esc_html_e('Design', 'fewo-kalender'); ?></label></th>
                    <td>
                        <select id="fewo_design" name="design" class="fewo-design-select">
                            <?php foreach ($design_options as $design_key => $design_label) : ?>
                                <option value="<?php echo esc_attr($design_key); ?>" <?php selected('modern', $design_key); ?>><?php echo esc_html($design_label); ?></option>
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

            <?php submit_button(__('Kalender anlegen', 'fewo-kalender')); ?>
        </form>
    </div>

    <div class="fewo-admin-card">
        <h2><?php esc_html_e('Kalender-Uebersicht', 'fewo-kalender'); ?></h2>

        <?php if (empty($calendars)) : ?>
            <p><?php esc_html_e('Noch keine Kalender vorhanden.', 'fewo-kalender'); ?></p>
        <?php else : ?>
            <table class="widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('ID', 'fewo-kalender'); ?></th>
                        <th><?php esc_html_e('Name', 'fewo-kalender'); ?></th>
                        <th><?php esc_html_e('Beschreibung', 'fewo-kalender'); ?></th>
                        <th><?php esc_html_e('Design', 'fewo-kalender'); ?></th>
                        <th><?php esc_html_e('Shortcode', 'fewo-kalender'); ?></th>
                        <th><?php esc_html_e('Aktionen', 'fewo-kalender'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($calendars as $cal) : ?>
                        <tr>
                            <td><?php echo esc_html((string) $cal->id); ?></td>
                            <td><?php echo esc_html($cal->name); ?></td>
                            <td><?php echo esc_html((string) $cal->description); ?></td>
                            <td><?php echo esc_html(isset($design_options[$cal->design]) ? $design_options[$cal->design] : $design_options['modern']); ?></td>
                            <td>
                                <code>[fewo_kalender id="<?php echo esc_attr((string) $cal->id); ?>"]</code>
                            </td>
                            <td class="fewo-actions-col">
                                <a class="button button-primary" href="<?php echo esc_url(add_query_arg(array('page' => 'fewo-kalender-edit', 'calendar_id' => $cal->id), admin_url('admin.php'))); ?>">
                                    <?php esc_html_e('Bearbeiten', 'fewo-kalender'); ?>
                                </a>

                                <form method="post" class="fewo-inline-form" onsubmit="return confirm('<?php echo esc_js(__('Diesen Kalender wirklich loeschen?', 'fewo-kalender')); ?>');">
                                    <?php wp_nonce_field('fewo_delete_calendar'); ?>
                                    <input type="hidden" name="fewo_action" value="delete_calendar" />
                                    <input type="hidden" name="calendar_id" value="<?php echo esc_attr((string) $cal->id); ?>" />
                                    <button type="submit" class="button button-link-delete"><?php esc_html_e('Loeschen', 'fewo-kalender'); ?></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
