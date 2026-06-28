<?php
/**
 * Ecosystem Lifecycle Status Pool Definitions Workspace.
 *
 * Provides the user interface configuration layout screen for managing custom
 * lifecycle milestone statuses across different engineering object types.
 *
 * @package    VA_PLM_Admin_Suite
 * @subpackage VA_PLM_Admin_Suite/admin/views
 */

if ( ! defined( 'ABSPATH' ) ) {
    die; 
}

$custom_statuses = get_option( 'vaplm_custom_lifecycle_statuses', array() );

$vaplm_objects = array(
    'vaplm_part'         => __( 'Parts Directory', 'va-plm-admin-suite' ),
    'vaplm_document'     => __( 'Documents Vault', 'va-plm-admin-suite' ),
    'vaplm_bom'          => __( 'BOM Assemblies', 'va-plm-admin-suite' ),
    'vaplm_change_order' => __( 'Change Orders', 'va-plm-admin-suite' )
);
?>

<div class="vaplm-card" style="margin-top: 0;">
    <h2><?php esc_html_e( 'Ecosystem Lifecycle Status Pool Definitions', 'va-plm-admin-suite' ); ?></h2>
    <p class="description">
        <?php esc_html_e( 'Define and review the customized status milestones used to explicitly organize engineering post lifecycles across different object types. Core system states cannot be purged.', 'va-plm-admin-suite' ); ?>
    </p>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; margin-top: 25px;">
        <?php foreach ( $vaplm_objects as $cpt_slug => $cpt_label ) : 
            // Fallback to system core default states if no custom ones are stored
            $pool = isset( $custom_statuses[$cpt_slug] ) ? $custom_statuses[$cpt_slug] : array( 'Draft', 'In Review', 'Released', 'Obsolete' );
            ?>
            <div style="background: #fafafa; border: 1px solid #ccd0d4; padding: 15px; border-radius: 4px;">
                <h3 style="margin-top: 0; font-size: 13px; text-transform: uppercase; border-bottom: 1px solid #e0e0e0; padding-bottom: 8px; display: flex; justify-content: space-between; align-items: center;">
                    <span><?php echo esc_html( $cpt_label ); ?></span>
                    <code style="font-size: 10px; color: #646970; text-transform: none;"><?php echo esc_html( $cpt_slug ); ?></code>
                </h3>
                <ul style="margin: 0; padding: 0; list-style: none;">
                    <?php foreach ( $pool as $status ) : ?>
                        <li style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px dashed #e0e0e0;">
                            <span class="vaplm-item-type-pill" style="font-size: 11px;"><?php echo esc_html( $status ); ?></span>
                            
                            <?php if ( in_array( $status, array( 'Draft', 'In Review', 'Released', 'Obsolete' ), true ) ) : ?>
                                <span style="font-size: 10px; color: #2271b1; font-weight: 600; text-transform: uppercase;"><?php esc_html_e( 'System Core', 'va-plm-admin-suite' ); ?></span>
                            <?php else : ?>
                                <form method="post" action="" style="margin: 0;">
                                    <?php wp_nonce_field( 'vaplm_admin_save_action', 'vaplm_admin_nonce_field' ); ?>
                                    <input type="hidden" name="vaplm_action_type" value="delete_status" />
                                    <input type="hidden" name="vaplm_status_object_scope" value="<?php echo esc_attr( $cpt_slug ); ?>" />
                                    <input type="hidden" name="vaplm_delete_status" value="<?php echo esc_attr( $status ); ?>" />
                                    <button type="submit" class="button button-link-delete" style="color: #d63638; text-decoration: none; padding: 0;" onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to permanently purge this milestone option?', 'va-plm-admin-suite' ); ?>');">
                                        <?php esc_html_e( 'Drop Milestone', 'va-plm-admin-suite' ); ?>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="vaplm-card" style="margin-top: 20px;">
    <h2><?php esc_html_e( 'Define New Shared Status Gate', 'va-plm-admin-suite' ); ?></h2>
    
    <form method="post" action="">
        <?php wp_nonce_field( 'vaplm_admin_save_action', 'vaplm_admin_nonce_field' ); ?>
        <input type="hidden" name="vaplm_action_type" value="manage_statuses" />

        <div style="display:flex; gap:15px; align-items: flex-end; padding-top: 10px;">
            <div>
                <label style="font-weight:600; display:block; margin-bottom:4px; font-size:11px; text-transform:uppercase; color:#646970;"><?php esc_html_e( 'Target Post Class Base *', 'va-plm-admin-suite' ); ?></label>
                <select name="vaplm_status_object_scope" style="width: 250px; height: 32px;" required>
                    <option value="vaplm_part"><?php esc_html_e( 'Parts (vaplm_part)', 'va-plm-admin-suite' ); ?></option>
                    <option value="vaplm_document"><?php esc_html_e( 'Documents (vaplm_document)', 'va-plm-admin-suite' ); ?></option>
                    <option value="vaplm_bom"><?php esc_html_e( 'BOM Assemblies (vaplm_bom)', 'va-plm-admin-suite' ); ?></option>
                    <option value="vaplm_change_order"><?php esc_html_e( 'Change Orders (vaplm_change_order)', 'va-plm-admin-suite' ); ?></option>
                </select>
            </div>
            <div>
                <label style="font-weight:600; display:block; margin-bottom:4px; font-size:11px; text-transform:uppercase; color:#646970;"><?php esc_html_e( 'Status Target Label *', 'va-plm-admin-suite' ); ?></label>
                <input type="text" name="vaplm_new_status" placeholder="e.g., Prototype, SupplierHold" required style="width: 280px; height: 32px;" />
            </div>
            <input type="submit" class="button button-secondary" value="<?php esc_attr_e( 'Inject Status Variant', 'va-plm-admin-suite' ); ?>" style="height: 32px;" />
        </div>
    </form>
</div>