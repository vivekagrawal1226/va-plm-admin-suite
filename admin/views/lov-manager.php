<?php
/**
 * List of Values (LOV) Lookup Engine Management Dashboard.
 *
 * Provides split-pane grid layout tools to provision standalone lookup
 * dictionaries, declare value-to-label pairs, protect data integrity, and
 * perform high-frequency bulk file uploads via standardized CSV matrices.
 *
 * @package    VA_PLM_Admin_Suite
 * @subpackage VA_PLM_Admin_Suite/admin/views
 */

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

global $wpdb;
$table_lov = $wpdb->prefix . 'vaplm_lov_entries';

// Fetch all compiled choices to populate the data grid view
$lov_records = $wpdb->get_results( "SELECT * FROM $table_lov ORDER BY list_slug ASC, option_label ASC" );
?>

<div class="vaplm-card">
    <h2><?php esc_html_e( 'List of Values (LOV) Global Dictionary Repositories', 'va-plm-admin-suite' ); ?></h2>
    <p class="description">
        <?php esc_html_e( 'Define and maintain standardized value selections. These lists can be bound to dynamic fields across parts, documents, change logs, and BOM structural lines to enforce parameterized data entry metrics.', 'va-plm-admin-suite' ); ?>
    </p>

    <!-- Consolidated Main Table Layout Grid -->
    <table class="wp-list-table widefat fixed striped vaplm-data-table" style="margin-top: 15px;">
        <thead>
            <tr>
                <th style="width: 25%;"><?php esc_html_e( 'Dictionary Key Slug', 'va-plm-admin-suite' ); ?></th>
                <th style="width: 25%;"><?php esc_html_e( 'Programmatic Choice Value', 'va-plm-admin-suite' ); ?></th>
                <th style="width: 35%;"><?php esc_html_e( 'Descriptive UI Display Label', 'va-plm-admin-suite' ); ?></th>
                <th style="width: 15%; text-align: center;"><?php esc_html_e( 'System Actions', 'va-plm-admin-suite' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( ! empty( $lov_records ) ) : foreach ( $lov_records as $row ) : ?>
                <tr>
                    <td><code class="code-font" style="background: #f0f6fa; padding: 3px 6px; border-radius: 3px; border: 1px solid #adc6ff;"><?php echo esc_html( $row->list_slug ); ?></code></td>
                    <td><code class="code-font" style="color: #d63638; font-weight: 600;"><?php echo esc_html( $row->option_value ); ?></code></td>
                    <td><strong><?php echo esc_html( $row->option_label ); ?></strong></td>
                    <td style="text-align: center;">
                        <form method="post" action="" style="display: inline-block;">
                            <?php wp_nonce_field( 'vaplm_admin_save_action', 'vaplm_admin_nonce_field' ); ?>
                            <input type="hidden" name="vaplm_action_type" value="delete_lov" />
                            <input type="hidden" name="vaplm_delete_lov_id" value="<?php echo intval( $row->id ); ?>" />
                            <button type="submit" class="button button-link-delete" style="color: #d63638; text-decoration: none;" onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to permanently drop this option from the dictionary repository?', 'va-plm-admin-suite' ); ?>');">
                                <?php esc_html_e( 'Drop Option', 'va-plm-admin-suite' ); ?>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; else : ?>
                <tr class="vaplm-empty-lov-notice">
                    <td colspan="4" style="text-align: center; padding: 3px 10px; color: #646970; line-height: 40px;">
                        <?php esc_html_e( 'No custom selection dictionaries defined yet. Register entries using the builders below.', 'va-plm-admin-suite' ); ?>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Bulk Data Upload and Template Utility Panel Split Canvas -->
<div class="vaplm-lov-split-panel-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(480px, 1fr)); gap: 20px; margin-top: 25px;">
    
    <!-- SUB-PANEL A: Reusable Single Choice Manual Row Entry -->
    <div class="vaplm-card" style="margin-top: 0;">
        <h2><?php esc_html_e( 'Append Lookup Option Manually', 'va-plm-admin-suite' ); ?></h2>
        <p class="description">
            <?php esc_html_e( 'Declare a lowercase configuration slug identifier to map isolated selection options into dropdown properties.', 'va-plm-admin-suite' ); ?>
        </p>

        <form method="post" action="">
            <?php wp_nonce_field( 'vaplm_admin_save_action', 'vaplm_admin_nonce_field' ); ?>
            <input type="hidden" name="vaplm_action_type" value="manage_lov" />

            <div class="vaplm-form-field-row" style="margin-bottom: 12px;">
                <label><strong><?php esc_html_e( 'List Category Key Slug *', 'va-plm-admin-suite' ); ?></strong></label>
                <input type="text" name="vaplm_new_lov_slug" placeholder="e.g., material_grades" pattern="[a-z0-9_]+" required style="font-family: monospace; width: 100%;" />
            </div>
            <div class="vaplm-form-field-row" style="margin-bottom: 12px;">
                <label><strong><?php esc_html_e( 'Programmatic Choice Value *', 'va-plm-admin-suite' ); ?></strong></label>
                <input type="text" name="vaplm_new_lov_val" placeholder="e.g., al_6061_t6" pattern="[a-z0-9_]+" required style="font-family: monospace; width: 100%;" />
            </div>
            <div class="vaplm-form-field-row" style="margin-bottom: 15px;">
                <label><strong><?php esc_html_e( 'Descriptive UI Display Label *', 'va-plm-admin-suite' ); ?></strong></label>
                <input type="text" name="vaplm_new_lov_lbl" placeholder="e.g., Aluminum 6061-T6" required style="width: 100%;" />
            </div>
            
            <button type="submit" class="button button-secondary" style="width: 100%; height: 32px; font-weight: 600;">
                <span class="dashicons dashicons-database-add" style="margin-top: 4px; vertical-align: middle;"></span>
                <?php esc_html_e( 'Inject Dictionary Row', 'va-plm-admin-suite' ); ?>
            </button>
        </form>
    </div>

    <!-- SUB-PANEL B: CSV Bulk Uploader Console & Template Repository -->
    <div class="vaplm-card" style="margin-top: 0; display: flex; flex-direction: column; justify-content: space-between;">
        <form method="post" action="" enctype="multipart/form-data">
            <?php wp_nonce_field( 'vaplm_admin_save_action', 'vaplm_admin_nonce_field' ); ?>
            <input type="hidden" name="vaplm_action_type" value="import_lov_csv" />

            <div>
                <h2><?php esc_html_e( 'Bulk CSV Dictionary Upload Engine', 'va-plm-admin-suite' ); ?></h2>
                <p class="description">
                    <?php esc_html_e( 'Upload multiple selection pairs concurrently. Existing key rows match validation loops to perform data updates seamlessly.', 'va-plm-admin-suite' ); ?>
                </p>
                
                <!-- Standard Formatting Download Link Anchor Component -->
                <div style="background: #f0f6fa; border: 1px solid #adc6ff; padding: 12px; border-radius: 4px; margin-bottom: 15px;">
                    <span class="dashicons dashicons-media-spreadsheet" style="color: var(--vaplm-primary); margin-right: 5px; vertical-align: middle;"></span>
                    <strong><?php esc_html_e( 'Canonical File Layout Matrix Structure:', 'va-plm-admin-suite' ); ?></strong>
                    <p class="description" style="margin: 4px 0 10px 0; font-size:12px;">
                        <?php esc_html_e( 'To prevent validation mapping routine rejections, your CSV file must utilize three explicit header columns matching this exact lowercase layout:', 'va-plm-admin-suite' ); ?> 
                        <br><code style="font-size:11px;">list_slug,option_value,option_label</code>
                    </p>
                    <a href="<?php echo esc_url( admin_url( 'admin-post.php?action=vaplm_download_lov_template' ) ); ?>" class="button button-link" style="font-weight: 600; text-decoration: none; padding: 0;">
                        <span class="dashicons dashicons-download" style="margin-top:3px; font-size:16px;"></span> <?php esc_html_e( 'Download Canonical CSV Template File', 'va-plm-admin-suite' ); ?>
                    </a>
                </div>

                <div class="vaplm-form-field-row" style="margin-bottom: 15px;">
                    <label for="vaplm_lov_csv_file"><strong><?php esc_html_e( 'Select Target CSV Data File Matrix *', 'va-plm-admin-suite' ); ?></strong></label>
                    <input type="file" id="vaplm_lov_csv_file" name="vaplm_lov_csv_file" accept=".csv" required style="border: 1px solid #8c8f94; padding: 4px; background: #fff; border-radius: 4px; width: 100%; box-sizing: border-box;" />
                </div>
            </div>

            <button type="submit" class="button button-primary" style="width: 100%; height: 32px; font-weight: 600;">
                <span class="dashicons dashicons-upload" style="margin-top: 4px; vertical-align: middle;"></span>
                <?php esc_html_e( 'Compile & Deploy CSV Target Dictionary Rows', 'va-plm-admin-suite' ); ?>
            </button>
        </form>
    </div>
</div>