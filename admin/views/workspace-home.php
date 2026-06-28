<?php
/**
 * Analytical Workspace Cockpit, Multi-Attribute Query Engine & Reports UI.
 *
 * Renders the primary workspace console dashboard inside /wp-admin context.
 * Houses the dynamic multi-attribute relational query canvas, persistent reports
 * widget menus index, and the client-side configurable CSV data streaming engine.
 *
 * @package    VA_PLM_Admin_Suite
 * @subpackage VA_PLM_Admin_Suite/admin/views
 */

if ( ! defined( 'ABSPATH' ) ) {
    die; 
}

global $wpdb;

// Pre-fetch dynamic engineering custom attributes registered inside system options registry
$field_definitions = get_option( 'vaplm_dynamic_field_definitions', array() );
$saved_reports     = get_option( 'vaplm_saved_engineering_reports', array() );

// Standard System Core Audit Fields definitions metadata mapping pool
$system_core_fields = array(
    'vaplm_object_number'    => __( 'Unique Object Number', 'va-plm-admin-suite' ),
    'post_title'             => __( 'Object Title Name', 'va-plm-admin-suite' ),
    'vaplm_lifecycle_status' => __( 'Item Lifecycle Status', 'va-plm-admin-suite' ),
    'created_datetime'       => __( 'Create Date (UTC)', 'va-plm-admin-suite' ),
    'modified_datetime'      => __( 'Modified Date (UTC)', 'va-plm-admin-suite' ),
    'creator_user_id'        => __( 'Creator Profile User', 'va-plm-admin-suite' ),
    'modifier_user_id'       => __( 'Modifier Profile User', 'va-plm-admin-suite' ),
);
?>

<div class="wrap vaplm-admin-wrap" id="vaplm-workspace-analytics-hub" style="margin-top: 20px;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; border-bottom: 1px solid #ccd0d4; padding-bottom: 12px;">
        <div>
            <h1 style="margin: 0; font-size: 23px; font-weight: 400; color: #1d2327;">🚀 <?php esc_html_e( 'VA PLM Engineering Search Dashboard', 'va-plm-admin-suite' ); ?></h1>
            <p class="description" style="margin: 5px 0 0 0;">
                <?php 
                echo sprintf(
                    /* translators: %s: Authoritative portal reference link anchor */
                    esc_html__( 'Created Proudly for Engineering and Manufacturing Community by Vivek Agrawal. Learn more at %s. Execute multi-attribute matrix tracing lookups, persist standard cross-object queries, and extract custom engineering datasets.', 'va-plm-admin-suite' ),
                    '<a href="' . esc_url( 'https://agrawalvivek.com/apps' ) . '" target="_blank" rel="noopener noreferrer" style="color: #2271b1; text-decoration: underline; font-weight: 600;">agrawalvivek.com/apps</a>'
                ); 
                ?>
            </p>
        </div>
        <div>
            <input type="hidden" id="vaplm_workspace_security_nonce" value="<?php echo esc_attr( wp_create_nonce( 'vaplm_workspace_search_action' ) ); ?>" />
        </div>
    </div>

    <div class="vaplm-workspace-grid-container" style="display: flex; gap: 20px; align-items: flex-start; width: 100%;">
        
        <div class="vaplm-workspace-sidebar-widget" style="width: 22%; min-width: 260px; box-sizing: border-box;">
            <div class="vaplm-card" style="margin-top: 0; padding: 15px; border-top: 3px solid #2271b1;">
                <h3 style="font-size: 12px; margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between;">
                    <span>📊 <?php esc_html_e( 'Saved Engineering Reports', 'va-plm-admin-suite' ); ?></span>
                    <span class="vaplm-item-type-pill" style="font-size: 10px; padding: 1px 6px;" id="vaplm-saved-reports-count-badge"><?php echo esc_html( count( $saved_reports ) ); ?></span>
                </h3>
                
                <ul id="vaplm-saved-reports-execution-list" style="margin: 0; padding: 0; list-style: none; max-height: 400px; overflow-y: auto;">
                    <?php if ( ! empty( $saved_reports ) ) : foreach ( $saved_reports as $rep_id => $rep_meta ) : ?>
                        <li class="vaplm-report-widget-row" data-report-id="<?php echo esc_attr( $rep_id ); ?>" style="display: flex; align-items: center; justify-content: space-between; padding: 8px 10px; border-bottom: 1px solid #f0f0f1; border-radius: 3px; transition: background 0.1s; margin-bottom: 4px;">
                            <button type="button" class="vaplm-trigger-saved-report-btn button-link" data-rules="<?php echo esc_attr( wp_json_encode( $rep_meta['rules'] ) ); ?>" data-cpt="<?php echo esc_attr( $rep_meta['object_type'] ); ?>" style="text-align: left; text-decoration: none; font-weight: 500; color: #2271b1; flex-grow: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; cursor: pointer;">
                                📉 <?php echo esc_html( $rep_meta['name'] ); ?>
                                <small style="display: block; color: #646970; font-size: 10px; font-family: monospace; text-transform: uppercase; margin-top: 2px;"><?php echo esc_html( str_replace( 'vaplm_', '', $rep_meta['object_type'] ) ); ?></small>
                            </button>
                            <button type="button" class="vaplm-purge-saved-report-action-trigger-btn" title="<?php esc_attr_e( 'Purge Report Schema From Option Table Registry', 'va-plm-admin-suite' ); ?>" style="background: none; border: none; color: #d63638; cursor: pointer; padding: 4px 6px; font-size: 11px; font-weight: bold; transition: opacity 0.1s;">✕</button>
                        </li>
                    <?php endforeach; else : ?>
                        <li class="vaplm-fallback-empty-reports-notice" style="padding: 15px 10px; text-align: center; color: #646970; font-style: italic; font-size: 12px;">
                            <?php esc_html_e( 'No saved reports exist in the registry.', 'va-plm-admin-suite' ); ?>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <div class="vaplm-workspace-query-canvas" style="width: 78%; flex-grow: 1; box-sizing: border-box;" id="vaplm-workspace-dashboard-advanced-query-builder">
            
            <div class="vaplm-card" style="margin-top: 0; padding: 20px;">
                <h3 style="font-size: 13px; margin-bottom: 15px;">🔍 <?php esc_html_e( 'Multi-Property Relational Matrix Query Canvas', 'va-plm-admin-suite' ); ?></h3>
                
                <div class="vaplm-query-canvas-header-controls" style="display: flex; gap: 15px; align-items: center; background: #f6f7f7; padding: 12px 15px; border: 1px solid #ccd0d4; border-radius: 4px; margin-bottom: 15px;">
                    <div>
                        <label style="font-weight: 600; font-size: 12px; color: #1d2327; margin-right: 8px;"><?php esc_html_e( 'Target Object Base Post Class *', 'va-plm-admin-suite' ); ?></label>
                        <select id="vaplm-query-target-object" style="height: 30px; padding: 2px 8px; border-radius: 3px; border: 1px solid #8c8f94; font-weight: 500;">
                            <option value="vaplm_part">📐 <?php esc_html_e( 'Parts (vaplm_part)', 'va-plm-admin-suite' ); ?></option>
                            <option value="vaplm_document">📄 <?php esc_html_e( 'Documents (vaplm_document)', 'va-plm-admin-suite' ); ?></option>
                            <option value="vaplm_bom">📊 <?php esc_html_e( 'BOM Assemblies (vaplm_bom)', 'va-plm-admin-suite' ); ?></option>
                            <option value="vaplm_change_order">🔄 <?php esc_html_e( 'Change Orders (vaplm_change_order)', 'va-plm-admin-suite' ); ?></option>
                        </select>
                    </div>
                    <div style="color: #646970; font-size: 12px; font-weight: 500; margin-left: auto;">
                        💡 <em><?php esc_html_e( 'Stacked rule filters evaluate via implicit spatial AND criteria parameters loops.', 'va-plm-admin-suite' ); ?></em>
                    </div>
                </div>

                <div id="vaplm-query-rules-container" style="margin-bottom: 20px;">
                    <div class="vaplm-query-rule-row" style="display: flex; gap: 10px; align-items: center; margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px dashed #f0f0f1;">
                        <div style="width: 30%;">
                            <select class="vaplm-rule-field" style="width: 100%; height: 32px;">
                                <optgroup label="<?php esc_attr_e( 'Core Audit & Standard Fields', 'va-plm-admin-suite' ); ?>">
                                    <?php foreach ( $system_core_fields as $f_slug => $f_label ) : ?>
                                        <option value="<?php echo esc_attr( $f_slug ); ?>"><?php echo esc_html( $f_label ); ?></option>
                                    <?php endforeach; ?>
                                </optgroup>
                                <optgroup label="<?php esc_attr_e( 'Custom Attributes Definitions Registry', 'va-plm-admin-suite' ); ?>" class="vaplm-dynamic-fields-optgroup">
                                    <!-- Populated via Javascript -->
                                </optgroup>
                            </select>
                        </div>
                        <div style="width: 25%;">
                            <select class="vaplm-rule-op" style="width: 100%; height: 32px;">
                                <option value="equals">== <?php esc_html_e( 'Equals', 'va-plm-admin-suite' ); ?></option>
                                <option value="not_equals">!= <?php esc_html_e( 'Not Equals', 'va-plm-admin-suite' ); ?></option>
                                <option value="contains">⊂ <?php esc_html_e( 'Contains', 'va-plm-admin-suite' ); ?></option>
                                <option value="is_blank">∅ <?php esc_html_e( 'Is Blank', 'va-plm-admin-suite' ); ?></option>
                            </select>
                        </div>
                        <div style="width: 40%;">
                            <input type="text" class="vaplm-rule-val" placeholder="<?php esc_attr_e( 'Enter validation search constraint string...', 'va-plm-admin-suite' ); ?>" style="width: 100%; height: 32px; box-sizing: border-box;" />
                        </div>
                        <div style="width: 5%; text-align: center;">
                            <button type="button" class="button vaplm-remove-rule-btn" style="color: #d63638; border-color: #ccd0d4; padding: 0 8px; height: 32px; line-height: 30px;" title="<?php esc_attr_e( 'Drop Parameter Rule', 'va-plm-admin-suite' ); ?>">✕</button>
                        </div>
                    </div>
                </div>

                <div class="vaplm-query-action-execution-bar" style="display: flex; align-items: center; justify-content: space-between; border-top: 1px solid #ccd0d4; padding-top: 15px;">
                    <div>
                        <button type="button" class="button button-secondary" id="vaplm-add-query-rule-btn">➕ <?php esc_html_e( 'Add Property Rule Row', 'va-plm-admin-suite' ); ?></button>
                    </div>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <button type="button" class="button button-secondary" id="vaplm-save-query-config-btn">💾 <?php esc_html_e( 'Save Query As Report', 'va-plm-admin-suite' ); ?></button>
                        <button type="button" class="button button-primary large" id="vaplm-run-advanced-query-btn" style="font-weight: 600; padding: 0 24px;">🚀 <?php esc_html_e( 'Execute System Query', 'va-plm-admin-suite' ); ?></button>
                    </div>
                </div>
            </div>

            <div class="vaplm-card" id="vaplm-search-results-grid-card-mask" style="padding: 20px; position: relative;">
                
                <div class="vaplm-results-export-action-toolbar" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px; background: #fafafa; border: 1px solid #ccd0d4; padding: 10px 15px; border-radius: var(--vaplm-radius, 4px);">
                    <div>
                        <span class="dashicons dashicons-database" style="color: #646970; vertical-align: middle; margin-right: 4px;"></span>
                        <strong style="font-size: 13px; color: #2c3338;"><span id="vaplm-results-metrics-tally-counter">0</span></strong> <span style="color:#646970; font-size:12px; font-weight:500;"><?php esc_html_e( 'matching product lifecycle records compiled in database layer.', 'va-plm-admin-suite' ); ?></span>
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <button type="button" class="button button-secondary" id="vaplm-trigger-export-column-configuration-modal-btn">⚙️ <?php esc_html_e( 'Configure Export Columns', 'va-plm-admin-suite' ); ?></button>
                        <button type="button" class="button button-primary" id="vaplm-execute-csv-data-extraction-stream-btn" style="background:#2271b1; border-color:#2271b1;">📥 <?php esc_html_e( 'Download Engineering Dataset', 'va-plm-admin-suite' ); ?></button>
                    </div>
                </div>

                <div class="vaplm-results-table-scroll-viewport" style="width: 100%; overflow-x: auto; border: 1px solid #ccd0d4; border-radius: 3px; max-height: 500px;">
                    <table class="wp-list-table widefat fixed striped vaplm-data-table" id="vaplm-query-results-table" style="margin: 0; min-width: 1100px; border: none;">
                        <thead>
                            <tr id="vaplm-datagrid-header-injection-row">
                                <th style="width:140px;"><?php esc_html_e( 'Object ID Code', 'va-plm-admin-suite' ); ?></th>
                                <th><?php esc_html_e( 'Description Title', 'va-plm-admin-suite' ); ?></th>
                                <th style="width:120px;"><?php esc_html_e( 'Lifecycle State', 'va-plm-admin-suite' ); ?></th>
                                <th style="width:160px;"><?php esc_html_e( 'Modified Date (UTC)', 'va-plm-admin-suite' ); ?></th>
                                <th style="width:100px;"><?php esc_html_e( 'Action', 'va-plm-admin-suite' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="5" style="text-align:center; padding: 25px; color:#646970;"><?php esc_html_e( 'Awaiting query execution...', 'va-plm-admin-suite' ); ?></td></tr>
                        </tbody>
                    </table>
                </div>

            </div>

        </div>
    </div>
</div>

<div id="vaplm-export-column-selector-modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.45); z-index: 100000; align-items: center; justify-content: center;">
    <div class="vaplm-card" style="width: 520px; max-width: 90%; background: #fff; border-radius: 4px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.15); margin-top: 0;">
        <h3 style="margin-top: 0; font-size: 14px; padding-bottom: 8px; border-bottom: 1px solid #ccd0d4; text-transform: uppercase; letter-spacing: 0.02em; display: flex; align-items: center; justify-content: space-between;">
            <span>⚙️ <?php esc_html_e( 'Configure Export Schema Columns', 'va-plm-admin-suite' ); ?></span>
            <button type="button" class="vaplm-close-modal-overlay-trigger-x-btn" style="background: none; border: none; font-size: 16px; cursor: pointer; color: #646970;">✕</button>
        </h3>
        <p class="description" style="margin-bottom: 15px; line-height: 1.4;">
            <?php esc_html_e( 'Check or uncheck explicit variables rows to filter field attributes out of data extractions logs before streaming file downloads.', 'va-plm-admin-suite' ); ?>
        </p>

        <div class="vaplm-modal-checkboxes-viewport" style="max-height: 320px; overflow-y: auto; border: 1px solid #ccd0d4; padding: 12px; border-radius: 3px; background: #fafafa; margin-bottom: 20px;">
            <div style="font-weight: 700; font-size: 11px; text-transform: uppercase; color: #646970; margin-bottom: 8px; border-bottom: 1px solid #e0e0e0; padding-bottom: 4px;">
                <?php esc_html_e( 'System Compliance Forensics Headers', 'va-plm-admin-suite' ); ?>
            </div>
            <div class="vaplm-modal-checkbox-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 15px;">
                <?php foreach ( $system_core_fields as $slug => $label ) : ?>
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 12px; cursor: pointer; font-weight: 500;">
                        <input type="checkbox" class="vaplm-export-column-cb-marker" value="<?php echo esc_attr( $slug ); ?>" data-label="<?php echo esc_attr( $label ); ?>" checked style="margin: 0;" />
                        <span><?php echo esc_html( $label ); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>

            <div style="font-weight: 700; font-size: 11px; text-transform: uppercase; color: #646970; margin-bottom: 8px; border-bottom: 1px solid #e0e0e0; padding-bottom: 4px;">
                <?php esc_html_e( 'Dynamic Custom Attributes Schema', 'va-plm-admin-suite' ); ?>
            </div>
            <div class="vaplm-modal-checkbox-grid id-vaplm-custom-fields-checkbox-injection-zone" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 10px;">
            <button type="button" class="button button-secondary vaplm-close-modal-overlay-trigger-x-btn"><?php esc_html_e( 'Close & Apply Schema Filters', 'va-plm-admin-suite' ); ?></button>
        </div>
    </div>
</div>

<div id="vaplm-dynamic-fields-data-store" style="display:none;" data-fields="<?php echo esc_attr( wp_json_encode( $field_definitions ) ); ?>"></div>