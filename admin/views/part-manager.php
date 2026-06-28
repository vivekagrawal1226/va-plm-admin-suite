<?php
/**
 * Parts Schema Attributes Mapping Workspace View Card.
 *
 * Provides the user interface configuration layout screen for managing custom attributes
 * assigned exclusively to Part objects directory workflows.
 *
 * @package    VA_PLM_Admin_Suite
 * @subpackage VA_PLM_Admin_Suite/admin/views
 */

if ( ! defined( 'ABSPATH' ) ) {
    die; 
}

$dynamic_fields = get_option( 'vaplm_dynamic_field_definitions', array() );

$parts_specific_attributes = array_filter( $dynamic_fields, function( $field ) {
    return isset( $field['object_type'] ) && $field['object_type'] === 'vaplm_part';
});

global $wpdb;
$lov_dictionaries = $wpdb->get_col( "SELECT DISTINCT list_slug FROM {$wpdb->prefix}vaplm_lov_entries ORDER BY list_slug ASC" );
?>

<div class="vaplm-card" style="margin-top: 0; padding: 25px;">
    <div style="margin-bottom: 20px; border-bottom: 1px solid #f0f0f1; padding-bottom: 10px;">
        <h3 style="margin: 0; font-size: 14px; text-transform: uppercase; color:#2271b1;">📐 <?php esc_html_e( 'Parts Directory Custom Attributes Schema Registry', 'va-plm-admin-suite' ); ?></h3>
        <p class="description" style="margin-top: 5px;">
            <?php esc_html_e( 'Provision customized manufacturing parameters (e.g., Material Grade, Procurement Type, Target Cost) assigned to the static General Properties workspace form view of the Parts Directory specification sheet.', 'va-plm-admin-suite' ); ?>
        </p>
    </div>

    <form method="post" action="">
        <?php wp_nonce_field( 'vaplm_admin_save_action', 'vaplm_admin_nonce_field' ); ?>
        <input type="hidden" name="vaplm_action_type" value="manage_fields" />
        <input type="hidden" name="vaplm_field_scope_tab" value="vaplm_part" />

        <table class="wp-list-table widefat fixed striped vaplm-data-table" style="width: 100%; border-collapse: collapse;" id="vaplm-parts-schema-attributes-table">
            <thead>
                <tr>
                    <th style="padding: 10px; font-weight: 600; width: 20%;"><?php esc_html_e( 'Attribute Key (DB Slug)', 'va-plm-admin-suite' ); ?></th>
                    <th style="padding: 10px; font-weight: 600; width: 20%;"><?php esc_html_e( 'Display Field Label Text', 'va-plm-admin-suite' ); ?></th>
                    <th style="padding: 10px; font-weight: 600; width: 20%;"><?php esc_html_e( 'UI Component Type', 'va-plm-admin-suite' ); ?></th>
                    <th style="padding: 10px; font-weight: 600; width: 20%;"><?php esc_html_e( 'LOV Target Mapping', 'va-plm-admin-suite' ); ?></th>
                    <th style="padding: 10px; font-weight: 600; width: 10%; text-align: center;"><?php esc_html_e( 'Mandatory', 'va-plm-admin-suite' ); ?></th>
                    <th style="padding: 10px; font-weight: 600; width: 10%; text-align: center;"><?php esc_html_e( 'Action', 'va-plm-admin-suite' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ( ! empty( $parts_specific_attributes ) ) : ?>
                    <?php foreach ( $parts_specific_attributes as $key => $config ) : ?>
                        <tr data-field-key="<?php echo esc_attr( $key ); ?>">
                            <td style="padding: 12px; vertical-align: middle;">
                                <input type="text" name="vaplm_dynamic_fields[<?php echo esc_attr( $key ); ?>][key]" value="<?php echo esc_attr( $key ); ?>" readonly class="vaplm-readonly-token-field" style="width: 100%; font-family: monospace;" />
                                <input type="hidden" name="vaplm_dynamic_fields[<?php echo esc_attr( $key ); ?>][object_type]" value="vaplm_part" />
                                <input type="hidden" name="vaplm_dynamic_fields[<?php echo esc_attr( $key ); ?>][object_subtype]" value="general" />
                            </td>
                            <td style="padding: 12px; vertical-align: middle;">
                                <input type="text" name="vaplm_dynamic_fields[<?php echo esc_attr( $key ); ?>][label]" value="<?php echo esc_attr( $config['label'] ?? '' ); ?>" required style="width: 100%;" />
                            </td>
                            <td style="padding: 12px; vertical-align: middle;">
                                <select name="vaplm_dynamic_fields[<?php echo esc_attr( $key ); ?>][field_type]" class="vaplm-schema-attribute-ui-selector-trigger" style="width: 100%;">
                                    <option value="text" <?php selected( $config['field_type'] ?? '', 'text' ); ?>><?php esc_html_e( 'Single-Line Text Input', 'va-plm-admin-suite' ); ?></option>
                                    <option value="number" <?php selected( $config['field_type'] ?? '', 'number' ); ?>><?php esc_html_e( 'Numeric Decimal', 'va-plm-admin-suite' ); ?></option>
                                    <option value="lov_dropdown" <?php selected( $config['field_type'] ?? '', 'lov_dropdown' ); ?>><?php esc_html_e( 'LOV Dictionary Dropdown Option', 'va-plm-admin-suite' ); ?></option>
                                </select>
                            </td>
                            <td style="padding: 12px; vertical-align: middle;">
                                <select name="vaplm_dynamic_fields[<?php echo esc_attr( $key ); ?>][lov_target]" class="vaplm-schema-attribute-lov-target-dropdown" style="width: 100%;" <?php echo ( ( $config['field_type'] ?? '' ) === 'lov_dropdown' ) ? '' : 'disabled'; ?>>
                                    <option value=""><?php esc_html_e( '-- Select Dictionary Mapping --', 'va-plm-admin-suite' ); ?></option>
                                    <?php foreach ( $lov_dictionaries as $lov_slug ) : ?>
                                        <option value="<?php echo esc_attr( $lov_slug ); ?>" <?php selected( $config['lov_target'] ?? '', $lov_slug ); ?>><?php echo esc_html( $lov_slug ); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td style="padding: 12px; vertical-align: middle; text-align: center;">
                                <input type="checkbox" name="vaplm_dynamic_fields[<?php echo esc_attr( $key ); ?>][required]" value="1" <?php checked( isset( $config['required'] ) ? $config['required'] : 0, 1 ); ?> />
                            </td>
                            <td style="padding: 12px; vertical-align: middle; text-align: center;">
                                <button type="button" class="button vaplm-remove-schema-row-btn" style="color: #d63638; border-color: #ccd0d4;">✕</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr class="vaplm-empty-schema-fallback-row">
                        <td colspan="6" style="text-align: center; color: #646970; padding: 20px; font-style: italic;">
                            <?php esc_html_e( 'No customized attributes registered against the Parts post directory schema topology yet.', 'va-plm-admin-suite' ); ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #f0f0f1; display: flex; justify-content: space-between; align-items: center;">
            <button type="button" class="button button-secondary vaplm-add-schema-row-btn" data-target="vaplm-parts-schema-attributes-table" data-object-type="vaplm_part">
                ➕ <?php esc_html_e( 'Append New Custom Part Field', 'va-plm-admin-suite' ); ?>
            </button>
            <?php submit_button( __( 'Save Parts Schema Attributes Changes', 'va-plm-admin-suite' ), 'primary large', 'vaplm_save_parts_schema_btn', false ); ?>
        </div>
    </form>
</div>