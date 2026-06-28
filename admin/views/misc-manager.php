<?php
/**
 * Consolidated Miscellaneous Administration Engine - LOV & CSV Matrix.
 *
 * Combines List of Values (LOV) manual row builders, transactional CSV bulk
 * database loaders, shared operational lifecycle status definition matrices, 
 * and custom two-dimensional RBAC access control permissions charts.
 *
 * @package    VA_PLM_Admin_Suite
 * @subpackage VA_PLM_Admin_Suite/admin/views
 */

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

global $wpdb;
$table_lov = $wpdb->prefix . 'vaplm_lov_entries';

// Re-evaluate active datasets currently populated within lookups registry tables
$lov_directories = $wpdb->get_results( "SELECT list_slug, COUNT(*) as option_count FROM $table_lov GROUP BY list_slug ORDER BY list_slug ASC" );
?>

<div class="vaplm-misc-tabs-wrapper" style="display: flex; gap: 20px; margin-top: 15px;">
    
    <div class="vaplm-misc-sidebar-menu" style="width: 20%; background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 10px;">
        <ul style="margin: 0; padding: 0; list-style: none;">
            <li><button type="button" class="button vaplm-misc-sub-tab-anchor-btn button-link active" data-action-scope="lov" style="display: block; width: 100%; text-align: left; padding: 8px 12px; text-decoration: none; font-weight: 600;">🧩 List of Values & CSV</button></li>
            <li><button type="button" class="button vaplm-misc-sub-tab-anchor-btn button-link" data-action-scope="statuses" style="display: block; width: 100%; text-align: left; padding: 8px 12px; text-decoration: none; font-weight: 600;">🚦 Lifecycle Statuses</button></li>
            <li><button type="button" class="button vaplm-misc-sub-tab-anchor-btn button-link" data-action-scope="roles" style="display: block; width: 100%; text-align: left; padding: 8px 12px; text-decoration: none; font-weight: 600;">🛡️ Role Management</button></li>
        </ul>
    </div>

    <div class="vaplm-misc-viewport-panes" style="width: 80%;">
        
        <div id="vaplm-misc-pane-lov" class="vaplm-misc-sub-panel active-pane" style="display: block;">
            
            <div class="vaplm-lov-dual-grid-wrapper" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                
                <div class="vaplm-card" style="margin-top: 0; background: #fff;">
                    <h4 style="margin-top: 0; font-weight: 600; text-transform: uppercase; font-size: 12px; color: #1d2327; border-bottom: 1px solid #f0f0f1; padding-bottom: 6px;">
                        ✍️ Manual Property Row Builder
                    </h4>
                    <div style="margin-bottom: 12px;">
                        <label style="font-weight: 600; display: block; margin-bottom: 4px; font-size: 11px; text-transform: uppercase; color: #646970;"><?php esc_html_e( 'List Dictionary Slug Key', 'va-plm-admin-suite' ); ?></label>
                        <input type="text" name="vaplm_new_lov_slug" placeholder="e.g., material_grades" style="width: 100%; font-family: monospace;" />
                    </div>
                    <div style="margin-bottom: 12px;">
                        <label style="font-weight: 600; display: block; margin-bottom: 4px; font-size: 11px; text-transform: uppercase; color: #646970;"><?php esc_html_e( 'Programmatic Choice Value', 'va-plm-admin-suite' ); ?></label>
                        <input type="text" name="vaplm_new_lov_val" placeholder="e.g., al_6061_t6" style="width: 100%; font-family: monospace;" />
                    </div>
                    <div>
                        <label style="font-weight: 600; display: block; margin-bottom: 4px; font-size: 11px; text-transform: uppercase; color: #646970;"><?php esc_html_e( 'Public Display Label', 'va-plm-admin-suite' ); ?></label>
                        <input type="text" name="vaplm_new_lov_lbl" placeholder="e.g., Aluminum 6061-T6" style="width: 100%;" />
                    </div>
                </div>

                <div class="vaplm-card" style="margin-top: 0; background: #fafafa; border-color: #ccd0d4;">
                    <h4 style="margin-top: 0; font-weight: 600; text-transform: uppercase; font-size: 12px; color: #1d2327; border-bottom: 1px solid #e0e0e0; padding-bottom: 6px;">
                        📥 Bulk CSV Import Uploader
                    </h4>
                    <p class="description" style="margin-bottom: 15px; font-size: 12px; line-height: 1.4;">
                        <?php esc_html_e( 'Populate hundreds of parameterized selections at once by checking in structured engineering lookup files.', 'va-plm-admin-suite' ); ?>
                    </p>
                    
                    <div class="vaplm-csv-upload-dropzone" style="border: 2px dashed #b5bfc7; background: #fff; padding: 20px; border-radius: 4px; text-align: center;">
                        <span class="dashicons dashicons-media-spreadsheet" style="font-size: 28px; width: 28px; height: 28px; color: #8c8f94; margin-bottom: 4px;"></span>
                        <input type="file" id="vaplm_lov_csv_input_field" name="vaplm_lov_csv_file" accept=".csv" style="display: block; margin: 10px auto; font-size: 12px;" />
                        
                        <div style="margin-top: 15px; border-top: 1px solid #f0f0f1; padding-top: 10px;">
                            <a href="<?php echo esc_url( admin_url( 'admin-post.php?action=vaplm_download_lov_template' ) ); ?>" class="button button-link" style="color: var(--vaplm-primary, #2271b1); font-weight: 600; text-decoration: none; font-size: 12px;">
                                <span class="dashicons dashicons-download" style="font-size: 16px; margin-top: 3px; vertical-align: middle;"></span> <?php esc_html_e( 'Download Canonical CSV Template File', 'va-plm-admin-suite' ); ?>
                            </a>
                        </div>
                    </div>
                </div>

            </div>

            <div class="vaplm-card" style="margin-top: 0;">
                <h4 style="margin-top: 0; font-weight: 600; text-transform: uppercase; font-size: 11px; color: #646970; letter-spacing: 0.02em;">
                    <?php esc_html_e( 'Active System Option Dictionary Inventories', 'va-plm-admin-suite' ); ?>
                </h4>
                <table class="wp-list-table widefat fixed striped vaplm-data-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Dictionary Lookup Identifier Slug', 'va-plm-admin-suite' ); ?></th>
                            <th><?php esc_html_e( 'Total Properties Keys Mapped', 'va-plm-admin-suite' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ( ! empty( $lov_directories ) ) : foreach ( $lov_directories as $dir ) : ?>
                            <tr>
                                <td><code><?php echo esc_html( $dir->list_slug ); ?></code></td>
                                <td><strong><?php echo esc_html( $dir->option_count ); ?> <?php esc_html_e( 'values compiled', 'va-plm-admin-suite' ); ?></strong></td>
                            </tr>
                        <?php endforeach; else : ?>
                            <tr>
                                <td colspan="2" style="text-align: center; color: #646970; padding: 15px;">
                                    <?php esc_html_e( 'No customizable dropdown configurations currently deployed.', 'va-plm-admin-suite' ); ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="vaplm-misc-pane-statuses" class="vaplm-misc-sub-panel" style="display: none;">
            <div class="vaplm-card" style="margin-top:0;">
                <h3><?php esc_html_e( 'Object Lifecycle Status Workflow Engine', 'va-plm-admin-suite' ); ?></h3>
                <div style="display: flex; gap: 15px; margin-bottom: 20px; align-items: flex-end;">
                    <div>
                        <label style="font-weight:600; display:block; margin-bottom:4px; font-size:11px; text-transform:uppercase; color:#646970;"><?php esc_html_e( 'Target Post Class Base', 'va-plm-admin-suite' ); ?></label>
                        <select name="vaplm_status_object_scope" style="width: 220px; height: 30px;">
                            <option value="vaplm_part"><?php esc_html_e( 'Parts (vaplm_part)', 'va-plm-admin-suite' ); ?></option>
                            <option value="vaplm_document"><?php esc_html_e( 'Documents (vaplm_document)', 'va-plm-admin-suite' ); ?></option>
                            <option value="vaplm_bom"><?php esc_html_e( 'BOM Assemblies (vaplm_bom)', 'va-plm-admin-suite' ); ?></option>
                            <option value="vaplm_change_order"><?php esc_html_e( 'Change Orders (vaplm_change_order)', 'va-plm-admin-suite' ); ?></option>
                        </select>
                    </div>
                    <div>
                        <label style="font-weight:600; display:block; margin-bottom:4px; font-size:11px; text-transform:uppercase; color:#646970;"><?php esc_html_e( 'New Milestone Target State', 'va-plm-admin-suite' ); ?></label>
                        <input type="text" name="vaplm_new_status" placeholder="e.g., Prototype, SupplierHold" style="width: 260px;" />
                    </div>
                </div>
                <div class="vaplm-active-milestones-stack">
                    <pre style="background:#f0f0f1; padding:15px; border-radius:4px; font-family:monospace; max-height:220px; overflow-y:auto; margin:0; border:1px solid #ccd0d4; color:#2c3338;"><?php echo esc_html( print_r( isset($custom_statuses) ? $custom_statuses : array(), true ) ); ?></pre>
                </div>
            </div>
        </div>

        <div id="vaplm-misc-pane-roles" class="vaplm-misc-sub-panel" style="display: none;">
            <div class="vaplm-card" style="margin-top:0;">
                <h3><?php esc_html_e( 'Granular User Roles & Permissions Matrix Configuration', 'va-plm-admin-suite' ); ?></h3>
                <div style="display: flex; gap: 15px; margin-bottom: 25px; align-items: flex-end; border-bottom: 1px dashed #ccd0d4; padding-bottom: 20px;">
                    <div>
                        <label style="font-weight:600; display:block; margin-bottom:4px; font-size:11px; text-transform:uppercase; color:#646970;"><?php esc_html_e( 'Programmatic Role Slug ID', 'va-plm-admin-suite' ); ?></label>
                        <input type="text" name="vaplm_new_role_slug" placeholder="e.g., vaplm_quality_inspector" style="width: 240px; font-family: monospace;" />
                    </div>
                    <div>
                        <label style="font-weight:600; display:block; margin-bottom:4px; font-size:11px; text-transform:uppercase; color:#646970;"><?php esc_html_e( 'Public Display Name', 'va-plm-admin-suite' ); ?></label>
                        <input type="text" name="vaplm_new_role_label" placeholder="e.g., Quality Inspector" style="width: 280px;" />
                    </div>
                </div>

                <table class="wp-list-table widefat fixed striped vaplm-data-table">
                    <thead>
                        <tr>
                            <th style="width: 25%;"><?php esc_html_e( 'Registered User Profile Role Name', 'va-plm-admin-suite' ); ?></th>
                            <th style="text-align: center;"><?php esc_html_e( 'Create (Author)', 'va-plm-admin-suite' ); ?></th>
                            <th style="text-align: center;"><?php esc_html_e( 'Update (Modify)', 'va-plm-admin-suite' ); ?></th>
                            <th style="text-align: center;"><?php esc_html_e( 'View (Read)', 'va-plm-admin-suite' ); ?></th>
                            <th style="text-align: center;"><?php esc_html_e( 'Upload Binaries', 'va-plm-admin-suite' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $wp_roles = wp_roles()->get_names();
                        $rbac_matrix = get_option( 'vaplm_field_permissions_matrix', array() );
                        
                        foreach ( $wp_roles as $role_slug => $role_name ) : 
                            $c_checked = isset( $rbac_matrix[$role_slug]['create'] ) ? 'checked' : '';
                            $u_checked = isset( $rbac_matrix[$role_slug]['update'] ) ? 'checked' : '';
                            $v_checked = isset( $rbac_matrix[$role_slug]['view'] ) ? 'checked' : '';
                            $f_checked = isset( $rbac_matrix[$role_slug]['upload'] ) ? 'checked' : '';

                            if ( $role_slug === 'administrator' ) {
                                $c_checked = $u_checked = $v_checked = $f_checked = 'checked disabled="disabled"';
                            }
                            ?>
                            <tr>
                                <td><strong><?php echo esc_html( $role_name ); ?></strong> <small style="display:block; color:#646970; font-family:monospace; font-size:10px;"><?php echo esc_html( $role_slug ); ?></small></td>
                                <td style="text-align: center;"><input type="checkbox" name="vaplm_rbac_matrix[<?php echo esc_attr( $role_slug ); ?>][create]" value="1" <?php echo esc_attr( $c_checked ); ?> class="vaplm-matrix-cb" /></td>
                                <td style="text-align: center;"><input type="checkbox" name="vaplm_rbac_matrix[<?php echo esc_attr( $role_slug ); ?>][update]" value="1" <?php echo esc_attr( $u_checked ); ?> class="vaplm-matrix-cb" /></td>
                                <td style="text-align: center;"><input type="checkbox" name="vaplm_rbac_matrix[<?php echo esc_attr( $role_slug ); ?>][view]" value="1" <?php echo esc_attr( $v_checked ); ?> class="vaplm-matrix-cb" /></td>
                                <td style="text-align: center;"><input type="checkbox" name="vaplm_rbac_matrix[<?php echo esc_attr( $role_slug ); ?>][upload]" value="1" <?php echo esc_attr( $f_checked ); ?> class="vaplm-matrix-cb" /></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>