<?php
/**
 * Presentation Partial: Bulk Engineering Objects Data Injector Engine.
 *
 * Renders the HTML file upload canvas, dynamic object classification target menus,
 * template generation buttons, and data validation logging matrices.
 *
 * @package    VA_PLM_Admin_Suite
 * @subpackage VA_PLM_Admin_Suite/admin/views
 */

if ( ! defined( 'ABSPATH' ) ) {
    die; 
}

$field_definitions = get_option( 'vaplm_dynamic_field_definitions', array() );
?>

<div class="vaplm-card" style="margin-top: 0; padding: 25px; border-left: 4px solid #2271b1;">
    <h3 style="margin-top: 0; font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.03em; color: #1d2327;">
        📥 <?php esc_html_e( 'Bulk Object Property Engine & CSV Mass Data Injector', 'va-plm-admin-suite' ); ?>
    </h3>
    <p class="description" style="margin-bottom: 20px; line-height: 1.5;">
        <?php esc_html_e( 'Import product configuration lifecycles from standardized tabular flat files. The parsing engine evaluates columns sequentially, mapping records to system core identifiers and dynamic meta attributes definitions registries simultaneously.', 'va-plm-admin-suite' ); ?>
    </p>

    <div style="background: #f6f7f7; border: 1px solid #ccd0d4; padding: 15px; border-radius: 4px; margin-bottom: 25px; display: flex; align-items: center; justify-content: space-between; gap: 20px;">
        <div style="max-width: 65%;">
            <strong style="display: block; font-size: 13px; color: #1d2327; margin-bottom: 4px;"><?php esc_html_e( 'Download Canonical Mapping Template File', 'va-plm-admin-suite' ); ?></strong>
            <span class="description" style="font-size: 12px; line-height: 1.4; display: block;">
                <?php esc_html_e( 'Extract a baseline formatting blueprint containing standard required tracking headers (`object_title`, `lifecycle_status`) alongside your active dynamic attributes definitions columns keys configuration structure.', 'va-plm-admin-suite' ); ?>
            </span>
        </div>
        <div>
            <a href="<?php echo esc_url( admin_url( 'admin-post.php?action=vaplm_download_lov_template&type=objects_template' ) ); ?>" class="button button-secondary" style="font-weight: 600; height: 32px; line-height: 30px;">
                ⚙️ <?php esc_html_e( 'Download .CSV Structural Template', 'va-plm-admin-suite' ); ?>
            </a>
        </div>
    </div>

    <form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=vaplm-configuration-control&tab=bulk-import' ) ); ?>" enctype="multipart/form-data" id="vaplm-bulk-import-execution-form" style="max-width: 700px;">
        
        <?php wp_nonce_field( 'vaplm_admin_save_action', 'vaplm_admin_nonce_field' ); ?>
        <input type="hidden" name="vaplm_action_type" value="bulk_import_objects" />

        <table class="form-table vaplm-vertical-stack" style="width: 100%; margin-bottom: 20px;">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="vaplm_import_target_post_type" style="font-weight: 600; font-size: 13px; color: #1d2327;">
                            <?php esc_html_e( 'Target Object Classification Post Class *', 'va-plm-admin-suite' ); ?>
                        </label>
                    </th>
                    <td>
                        <select id="vaplm_import_target_post_type" name="vaplm_import_target_post_type" required style="width: 100%; max-width: 400px; height: 32px;">
                            <option value="vaplm_part">📐 <?php esc_html_e( 'Parts Inventory Records (vaplm_part)', 'va-plm-admin-suite' ); ?></option>
                            <option value="vaplm_document">📄 <?php esc_html_e( 'Documents Repository Files (vaplm_document)', 'va-plm-admin-suite' ); ?></option>
                            <option value="vaplm_bom">📊 <?php esc_html_e( 'BOM Hierarchy Assemblies (vaplm_bom)', 'va-plm-admin-suite' ); ?></option>
                            <option value="vaplm_change_order">🔄 <?php esc_html_e( 'Engineering Change Orders (vaplm_change_order)', 'va-plm-admin-suite' ); ?></option>
                        </select>
                        <span class="description" style="display: block; margin-top: 5px;">
                            <?php esc_html_e( 'Ensure incoming records columns precisely mirror the target post type definitions scope schemas to guarantee database integrity.', 'va-plm-admin-suite' ); ?>
                        </span>
                    </td>
                </tr>

                <tr>
                    <th scope="row" style="padding-top: 15px;">
                        <label for="vaplm_import_csv_file_stream" style="font-weight: 600; font-size: 13px; color: #1d2327;">
                            <?php esc_html_e( 'Select Tabular Flat Dataset Source File *', 'va-plm-admin-suite' ); ?>
                        </label>
                    </th>
                    <td style="padding-top: 15px;">
                        <div style="border: 2px dashed #ccd0d4; padding: 25px; border-radius: 4px; background: #fafafa; text-align: center; max-width: 400px; box-sizing: border-box;">
                            <span class="dashicons dashicons-media-spreadsheet" style="font-size: 36px; width: 36px; height: 36px; color: #646970; margin-bottom: 10px; display: inline-block;"></span>
                            <input type="file" id="vaplm_import_csv_file_stream" name="vaplm_import_csv_file_stream" accept=".csv" required style="display: block; width: 100%; font-size: 12px; margin-top: 5px;" />
                        </div>
                        <span class="description" style="display: block; margin-top: 8px;">
                            <?php esc_html_e( 'Accepts only valid uncompressed `.csv` files formatted with UTF-8 character encoding frameworks.', 'va-plm-admin-suite' ); ?>
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>

        <div style="padding-top: 10px; border-top: 1px solid #f0f0f1; margin-top: 20px;">
            <?php 
            submit_button( 
                __( 'Execute Server-Side Mass Import', 'va-plm-admin-suite' ), 
                'primary large', 
                'vaplm_execute_bulk_import_submit_btn', 
                false, 
                array( 'id' => 'vaplm-bulk-import-submit-trigger-btn' ) 
            ); 
            ?>
        </div>
    </form>

    <?php if ( isset( $GLOBALS['vaplm_bulk_import_results'] ) && is_array( $GLOBALS['vaplm_bulk_import_results'] ) ) : 
        $metrics = $GLOBALS['vaplm_bulk_import_results']; ?>
        <div id="vaplm-bulk-import-metrics-display-card" class="vaplm-card" style="margin-top: 30px; background: #fafafa; border: 1px solid #ccd0d4; padding: 20px; border-left: 4px solid #46b450;">
            <h4 style="margin-top: 0; font-size: 13px; font-weight: 600; color: #1d2327; margin-bottom: 15px; text-transform: uppercase;">
                📊 <?php esc_html_e( 'Batch Processing Execution Summary', 'va-plm-admin-suite' ); ?>
            </h4>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 15px; margin-bottom: 20px;">
                <div style="background: #fff; padding: 10px; border: 1px solid #e0e0e0; border-radius: 3px; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
                    <small style="color: #646970; font-weight: 600; display: block; font-size: 10px; text-transform: uppercase;"><?php esc_html_e( 'Rows Scanned', 'va-plm-admin-suite' ); ?></small>
                    <strong style="font-size: 20px; color: #1d2327; display: block; margin-top: 4px;"><?php echo intval( $metrics['total_rows'] ); ?></strong>
                </div>
                <div style="background: #fff; padding: 10px; border: 1px solid #e0e0e0; border-radius: 3px; box-shadow: 0 1px 2px rgba(0,0,0,0.02); border-left: 3px solid #46b450;">
                    <small style="color: #46b450; font-weight: 600; display: block; font-size: 10px; text-transform: uppercase;"><?php esc_html_e( 'Committed Records', 'va-plm-admin-suite' ); ?></small>
                    <strong style="font-size: 20px; color: #46b450; display: block; margin-top: 4px;"><?php echo intval( $metrics['inserted_count'] ); ?></strong>
                </div>
                <div style="background: #fff; padding: 10px; border: 1px solid #e0e0e0; border-radius: 3px; box-shadow: 0 1px 2px rgba(0,0,0,0.02); border-left: 3px solid #dc3232;">
                    <small style="color: #dc3232; font-weight: 600; display: block; font-size: 10px; text-transform: uppercase;"><?php esc_html_e( 'Skipped Rows', 'va-plm-admin-suite' ); ?></small>
                    <strong style="font-size: 20px; color: #dc3232; display: block; margin-top: 4px;"><?php echo intval( $metrics['failed_count'] ); ?></strong>
                </div>
            </div>

            <?php if ( ! empty( $metrics['error_logs'] ) ) : ?>
                <div style="border-top: 1px solid #ccd0d4; padding-top: 15px;">
                    <strong style="font-size: 11px; text-transform: uppercase; color: #d63638; display: block; margin-bottom: 8px; font-weight: 600;">
                        ⚠️ <?php esc_html_e( 'Parsing Engine Schema Violations Log', 'va-plm-admin-suite' ); ?>
                    </strong>
                    <div style="max-height: 180px; overflow-y: auto; background: #fff; border: 1px solid #e0e0e0; border-radius: 3px; padding: 8px 12px; font-family: Consolas, Monaco, monospace; font-size: 12px; line-height: 1.5; color: #3c434a;">
                        <?php foreach ( $metrics['error_logs'] as $log_line ) : ?>
                            <div style="border-bottom: 1px solid #f0f0f1; padding: 4px 0; color: #b32d2e;">
                                <?php echo esc_html( $log_line ); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>