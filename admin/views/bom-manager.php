<?php
/**
 * High-Density Attribute Configuration Engine & BOM Sub-type Matrix.
 *
 * @package    VA_PLM_Admin_Suite
 * @subpackage VA_PLM_Admin_Suite/admin/views
 */

if ( ! defined( 'ABSPATH' ) ) { 
    die; 
}

$dynamic_fields = get_option( 'vaplm_dynamic_field_definitions', array() );

$bom_general_attributes = array_filter( $dynamic_fields, function( $field ) {
    return isset( $field['object_type'] ) && $field['object_type'] === 'vaplm_bom' && ( ! isset( $field['object_subtype'] ) || $field['object_subtype'] !== 'bom_relationship_column' );
});

$bom_table_columns = array_filter( $dynamic_fields, function( $field ) {
    return isset( $field['object_type'] ) && $field['object_type'] === 'vaplm_bom' && isset( $field['object_subtype'] ) && $field['object_subtype'] === 'bom_relationship_column';
});

global $wpdb;
$lov_dictionaries = $wpdb->get_col( "SELECT DISTINCT list_slug FROM {$wpdb->prefix}vaplm_lov_entries ORDER BY list_slug ASC" );
?>

<div class="wrap-vaplm-split-attributes-workspace" style="display: flex; flex-direction: column; gap: 30px;">
    
    <div class="vaplm-card" style="margin-top: 0; padding: 25px;">
        <div style="margin-bottom: 20px; border-bottom: 1px solid #f0f0f1; padding-bottom: 10px;">
            <h3 style="margin: 0; font-size: 14px; text-transform: uppercase; color:#2271b1;">📋 <?php esc_html_e( 'BOM Header & General Properties Custom Attributes', 'va-plm-admin-suite' ); ?></h3>
            <p class="description" style="margin-top: 5px;">
                <?php esc_html_e( 'Provision metadata properties (e.g., Project Number, Density) assigned to the static General Properties tab section of the Bill of Materials editor form.', 'va-plm-admin-suite' ); ?>
            </p>
        </div>

        <form method="post" action="">
            <?php wp_nonce_field( 'vaplm_admin_save_action', 'vaplm_admin_nonce_field' ); ?>
            <input type="hidden" name="vaplm_action_type" value="manage_fields" />
            
            <input type="hidden" name="vaplm_field_scope_tab" value="vaplm_bom" />
            <input type="hidden" name="vaplm_field_sub_scope" value="general" />

            <table class="wp-list-table widefat fixed striped vaplm-data-table" style="width: 100%; border-collapse: collapse;" id="vaplm-bom-general-attributes-table">
                <thead>
                    <tr>
                        <th style="padding: 10px; font-weight: 600; width: 25%;"><?php esc_html_e( 'Attribute Identifier Key', 'va-plm-admin-suite' ); ?></th>
                        <th style="padding: 10px; font-weight: 600; width: 25%;"><?php esc_html_e( 'Display Field Label', 'va-plm-admin-suite' ); ?></th>
                        <th style="padding: 10px; font-weight: 600; width: 20%;"><?php esc_html_e( 'UI Control Type', 'va-plm-admin-suite' ); ?></th>
                        <th style="padding: 10px; font-weight: 600; width: 20%;"><?php esc_html_e( 'Dictionary Target (LOV)', 'va-plm-admin-suite' ); ?></th>
                        <th style="padding: 10px; font-weight: 600; width: 10%; text-align: center;"><?php esc_html_e( 'Action', 'va-plm-admin-suite' ); ?></th>
                    </tr>
                </thead>
                <tbody id="vaplm-bom-general-attributes-injection-body">
                    <?php if ( ! empty( $bom_general_attributes ) ) : ?>
                        <?php foreach ( $bom_general_attributes as $key => $config ) : ?>
                            <tr data-field-key="<?php echo esc_attr( $key ); ?>">
                                <td style="padding: 12px; vertical-align: middle;">
                                    <input type="text" name="vaplm_dynamic_fields[<?php echo esc_attr( $key ); ?>][key]" value="<?php echo esc_attr( $key ); ?>" readonly class="vaplm-readonly-token-field" style="width: 100%; font-family: monospace;" />
                                    <input type="hidden" name="vaplm_dynamic_fields[<?php echo esc_attr( $key ); ?>][object_type]" value="vaplm_bom" />
                                    <input type="hidden" name="vaplm_dynamic_fields[<?php echo esc_attr( $key ); ?>][object_subtype]" value="general" />
                                </td>
                                <td style="padding: 12px; vertical-align: middle;">
                                    <input type="text" name="vaplm_dynamic_fields[<?php echo esc_attr( $key ); ?>][label]" value="<?php echo esc_attr( $config['label'] ?? '' ); ?>" required style="width: 100%;" />
                                </td>
                                <td style="padding: 12px; vertical-align: middle;">
                                    <select name="vaplm_dynamic_fields[<?php echo esc_attr( $key ); ?>][field_type]" class="vaplm-schema-attribute-ui-selector-trigger" style="width: 100%;">
                                        <option value="text" <?php selected( $config['field_type'] ?? '', 'text' ); ?>><?php esc_html_e( 'Single-Line Text', 'va-plm-admin-suite' ); ?></option>
                                        <option value="textarea" <?php selected( $config['field_type'] ?? '', 'textarea' ); ?>><?php esc_html_e( 'Multi-Line Paragraph', 'va-plm-admin-suite' ); ?></option>
                                        <option value="lov_dropdown" <?php selected( $config['field_type'] ?? '', 'lov_dropdown' ); ?>><?php esc_html_e( 'LOV Dictionary Dropdown', 'va-plm-admin-suite' ); ?></option>
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
                                    <button type="button" class="button vaplm-remove-schema-row-btn" style="color: #d63638; border-color: #ccd0d4;">✕</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr class="vaplm-empty-schema-fallback-row"><td colspan="5" style="text-align: center; color: #646970; padding: 15px; font-style: italic;"><?php esc_html_e( 'No custom metadata properties registered against BOM forms.', 'va-plm-admin-suite' ); ?></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <div style="margin-top: 15px; display: flex; justify-content: space-between; align-items: center;">
                <button type="button" class="button button-secondary vaplm-add-schema-row-btn" data-target="vaplm-bom-general-attributes-table" data-object-type="vaplm_bom" data-sub-scope="general">➕ <?php esc_html_e( 'Append New General Property Field', 'va-plm-admin-suite' ); ?></button>
                <?php submit_button( __( 'Save General Properties Attributes', 'va-plm-admin-suite' ), 'primary medium', 'vaplm_save_bom_gen_btn', false ); ?>
            </div>
        </form>
    </div>

    <div class="vaplm-card" style="margin-top: 0; padding: 25px;">
        <div style="margin-bottom: 20px; border-bottom: 1px solid #f0f0f1; padding-bottom: 10px;">
            <h3 style="margin: 0; font-size: 14px; text-transform: uppercase; color:#e4a300;">📊 <?php esc_html_e( 'BOM Structure Grid Table Custom Columns', 'va-plm-admin-suite' ); ?></h3>
            <p class="description" style="margin-top: 5px;">
                <?php esc_html_e( 'Define and register structural columns (e.g., Reference Designator, Torque N-m) added as data rows into the active parent-child Bills of Materials structure grid layout.', 'va-plm-admin-suite' ); ?>
            </p>
        </div>

        <form method="post" action="">
            <?php wp_nonce_field( 'vaplm_admin_save_action', 'vaplm_admin_nonce_field' ); ?>
            <input type="hidden" name="vaplm_action_type" value="manage_fields" />
            
            <input type="hidden" name="vaplm_field_scope_tab" value="vaplm_bom" />
            <input type="hidden" name="vaplm_field_sub_scope" value="bom_relationship_column" />

            <table class="wp-list-table widefat fixed striped vaplm-data-table" style="width: 100%; border-collapse: collapse;" id="vaplm-bom-table-columns-table">
                <thead>
                    <tr>
                        <th style="padding: 10px; font-weight: 600; width: 25%;"><?php esc_html_e( 'Column DB Identifier Key', 'va-plm-admin-suite' ); ?></th>
                        <th style="padding: 10px; font-weight: 600; width: 25%;"><?php esc_html_e( 'Grid Header Label Text', 'va-plm-admin-suite' ); ?></th>
                        <th style="padding: 10px; font-weight: 600; width: 20%;"><?php esc_html_e( 'Input Control Type', 'va-plm-admin-suite' ); ?></th>
                        <th style="padding: 10px; font-weight: 600; width: 20%;"><?php esc_html_e( 'Dictionary Target (LOV)', 'va-plm-admin-suite' ); ?></th>
                        <th style="padding: 10px; font-weight: 600; width: 10%; text-align: center;"><?php esc_html_e( 'Action', 'va-plm-admin-suite' ); ?></th>
                    </tr>
                </thead>
                <tbody id="vaplm-bom-table-columns-injection-body">
                    <?php if ( ! empty( $bom_table_columns ) ) : ?>
                        <?php foreach ( $bom_table_columns as $key => $config ) : ?>
                            <tr data-field-key="<?php echo esc_attr( $key ); ?>">
                                <td style="padding: 12px; vertical-align: middle;">
                                    <input type="text" name="vaplm_dynamic_fields[<?php echo esc_attr( $key ); ?>][key]" value="<?php echo esc_attr( $key ); ?>" readonly class="vaplm-readonly-token-field" style="width: 100%; font-family: monospace;" />
                                    <input type="hidden" name="vaplm_dynamic_fields[<?php echo esc_attr( $key ); ?>][object_type]" value="vaplm_bom" />
                                    <input type="hidden" name="vaplm_dynamic_fields[<?php echo esc_attr( $key ); ?>][object_subtype]" value="bom_relationship_column" />
                                </td>
                                <td style="padding: 12px; vertical-align: middle;">
                                    <input type="text" name="vaplm_dynamic_fields[<?php echo esc_attr( $key ); ?>][label]" value="<?php echo esc_attr( $config['label'] ?? '' ); ?>" required style="width: 100%;" />
                                </td>
                                <td style="padding: 12px; vertical-align: middle;">
                                    <select name="vaplm_dynamic_fields[<?php echo esc_attr( $key ); ?>][field_type]" class="vaplm-schema-attribute-ui-selector-trigger" style="width: 100%;">
                                        <option value="text" <?php selected( $config['field_type'] ?? '', 'text' ); ?>><?php esc_html_e( 'Single-Line Text Input', 'va-plm-admin-suite' ); ?></option>
                                        <option value="lov_dropdown" <?php selected( $config['field_type'] ?? '', 'lov_dropdown' ); ?>><?php esc_html_e( 'LOV Dictionary Dropdown', 'va-plm-admin-suite' ); ?></option>
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
                                    <button type="button" class="button vaplm-remove-schema-row-btn" style="color: #d63638; border-color: #ccd0d4;">✕</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr class="vaplm-empty-schema-fallback-row"><td colspan="5" style="text-align: center; color: #646970; padding: 15px; font-style: italic;"><?php esc_html_e( 'No custom table grid relationship columns registered.', 'va-plm-admin-suite' ); ?></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <div style="margin-top: 15px; display: flex; justify-content: space-between; align-items: center;">
                <button type="button" class="button button-secondary vaplm-add-schema-row-btn" data-target="vaplm-bom-table-columns-table" data-object-type="vaplm_bom" data-sub-scope="bom_relationship_column">➕ <?php esc_html_e( 'Append Grid Relationship Column', 'va-plm-admin-suite' ); ?></button>
                <?php submit_button( __( 'Save Dynamic Grid Column Updates', 'va-plm-admin-suite' ), 'primary medium', 'vaplm_save_bom_cols_btn', false ); ?>
            </div>
        </form>
    </div>

</div>