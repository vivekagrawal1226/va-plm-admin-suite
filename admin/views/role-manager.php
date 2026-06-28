<?php
/**
 * RBAC Roles Matrix Configuration Workspace.
 *
 * Provides the user interface configuration layout screen for managing user roles,
 * provisioning new clearance profiles, and mapping granular access controls.
 *
 * @package    VA_PLM_Admin_Suite
 * @subpackage VA_PLM_Admin_Suite/admin/views
 */

if ( ! defined( 'ABSPATH' ) ) {
    die; 
}

$wp_roles = wp_roles()->get_names();
$rbac_matrix = get_option( 'vaplm_field_permissions_matrix', array() );
?>

<div class="vaplm-card" style="margin-top: 0;">
    <h2><?php esc_html_e( 'Active System User Roles Directory', 'va-plm-admin-suite' ); ?></h2>
    <p class="description">
        <?php esc_html_e( 'Review and manage the customized security profiles operating within the PLM framework. Core system roles cannot be deleted to preserve fallback integrity.', 'va-plm-admin-suite' ); ?>
    </p>

    <table class="wp-list-table widefat fixed striped vaplm-data-table" style="margin-top: 15px;">
        <thead>
            <tr>
                <th style="width: 30%;"><?php esc_html_e( 'Role Identifier (Slug)', 'va-plm-admin-suite' ); ?></th>
                <th style="width: 40%;"><?php esc_html_e( 'Role Public Title Label', 'va-plm-admin-suite' ); ?></th>
                <th style="width: 30%;"><?php esc_html_e( 'Operations Boundary', 'va-plm-admin-suite' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ( $wp_roles as $slug => $name ) : ?>
                <tr>
                    <td style="vertical-align: middle;"><code><?php echo esc_html( $slug ); ?></code></td>
                    <td style="vertical-align: middle;"><strong><?php echo esc_html( $name ); ?></strong></td>
                    <td style="vertical-align: middle;">
                        <?php if ( in_array( $slug, array( 'administrator', 'vaplm_manager', 'vaplm_engineer', 'vaplm_guest' ), true ) ) : ?>
                            <span class="description" style="color:#2271b1; font-weight:600;"><?php esc_html_e( 'System Protected Core Framework Profile', 'va-plm-admin-suite' ); ?></span>
                        <?php else : ?>
                            <form method="post" action="" style="display: inline-block;">
                                <?php wp_nonce_field( 'vaplm_admin_save_action', 'vaplm_admin_nonce_field' ); ?>
                                <input type="hidden" name="vaplm_action_type" value="delete_role" />
                                <input type="hidden" name="vaplm_delete_role_slug" value="<?php echo esc_attr( $slug ); ?>" />
                                <button type="submit" class="button button-link-delete" style="color: #d63638; text-decoration: none;" onclick="return confirm('<?php esc_attr_e( 'Permanently drop this role parameter configuration?', 'va-plm-admin-suite' ); ?>');">
                                    <?php esc_html_e( 'Purge Role', 'va-plm-admin-suite' ); ?>
                                </button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="vaplm-card" style="margin-top: 20px;">
    <h2><?php esc_html_e( 'Provision Custom Role Clearance Profile', 'va-plm-admin-suite' ); ?></h2>
    
    <form method="post" action="">
        <?php wp_nonce_field( 'vaplm_admin_save_action', 'vaplm_admin_nonce_field' ); ?>
        <input type="hidden" name="vaplm_action_type" value="manage_roles" />

        <div style="display:flex; gap:15px; align-items: flex-end; padding-bottom: 20px;">
            <div>
                <label style="display:block; margin-bottom:5px;"><strong><?php esc_html_e( 'Role Slug Key (Lowercase, no spaces) *', 'va-plm-admin-suite' ); ?></strong></label>
                <input type="text" name="vaplm_new_role_slug" placeholder="e.g., vaplm_qa_inspector" pattern="[a-z0-9_]+" style="width:250px; height:32px; font-family: monospace;" required />
            </div>
            <div>
                <label style="display:block; margin-bottom:5px;"><strong><?php esc_html_e( 'Role Functional Public Label Title *', 'va-plm-admin-suite' ); ?></strong></label>
                <input type="text" name="vaplm_new_role_label" placeholder="e.g., QA Compliance Inspector" style="width:280px; height:32px;" required />
            </div>
            <input type="submit" class="button button-secondary" value="<?php esc_attr_e( 'Provision Custom Role', 'va-plm-admin-suite' ); ?>" style="height: 32px;" />
        </div>
    </form>
</div>

<div class="vaplm-card" style="margin-top: 20px;">
    <h2><?php esc_html_e( 'Granular User Roles & Permissions Matrix Configuration', 'va-plm-admin-suite' ); ?></h2>
    <p class="description">
        <?php esc_html_e( 'Bind explicit CRUD (Create, Read, Update, Delete) capability mappings across customized roles to enforce strict governance access controls.', 'va-plm-admin-suite' ); ?>
    </p>

    <form method="post" action="">
        <?php wp_nonce_field( 'vaplm_admin_save_action', 'vaplm_admin_nonce_field' ); ?>
        <input type="hidden" name="vaplm_action_type" value="manage_roles" />

        <table class="wp-list-table widefat fixed striped vaplm-data-table" style="margin-top: 15px;">
            <thead>
                <tr>
                    <th style="width: 25%;"><?php esc_html_e( 'Registered User Profile Role Name', 'va-plm-admin-suite' ); ?></th>
                    <th style="text-align: center; width: 18%;"><?php esc_html_e( 'Create (Author)', 'va-plm-admin-suite' ); ?></th>
                    <th style="text-align: center; width: 18%;"><?php esc_html_e( 'Update (Modify)', 'va-plm-admin-suite' ); ?></th>
                    <th style="text-align: center; width: 18%;"><?php esc_html_e( 'View (Read)', 'va-plm-admin-suite' ); ?></th>
                    <th style="text-align: center; width: 18%;"><?php esc_html_e( 'Upload Binaries', 'va-plm-admin-suite' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $wp_roles as $role_slug => $role_name ) : 
                    $c_checked = isset( $rbac_matrix[$role_slug]['create'] ) ? 'checked' : '';
                    $u_checked = isset( $rbac_matrix[$role_slug]['update'] ) ? 'checked' : '';
                    $v_checked = isset( $rbac_matrix[$role_slug]['view'] )   ? 'checked' : '';
                    $f_checked = isset( $rbac_matrix[$role_slug]['upload'] ) ? 'checked' : '';

                    if ( $role_slug === 'administrator' ) {
                        $c_checked = $u_checked = $v_checked = $f_checked = 'checked disabled="disabled"';
                    }
                    ?>
                    <tr>
                        <td style="vertical-align: middle;">
                            <strong><?php echo esc_html( $role_name ); ?></strong> 
                            <small style="display:block; color:#646970; font-family:monospace; font-size:10px;"><?php echo esc_html( $role_slug ); ?></small>
                        </td>
                        <td style="text-align: center; vertical-align: middle;">
                            <input type="checkbox" name="vaplm_rbac_matrix[<?php echo esc_attr( $role_slug ); ?>][create]" value="1" <?php echo esc_attr( $c_checked ); ?> class="vaplm-matrix-cb" />
                        </td>
                        <td style="text-align: center; vertical-align: middle;">
                            <input type="checkbox" name="vaplm_rbac_matrix[<?php echo esc_attr( $role_slug ); ?>][update]" value="1" <?php echo esc_attr( $u_checked ); ?> class="vaplm-matrix-cb" />
                        </td>
                        <td style="text-align: center; vertical-align: middle;">
                            <input type="checkbox" name="vaplm_rbac_matrix[<?php echo esc_attr( $role_slug ); ?>][view]" value="1" <?php echo esc_attr( $v_checked ); ?> class="vaplm-matrix-cb" />
                        </td>
                        <td style="text-align: center; vertical-align: middle;">
                            <input type="checkbox" name="vaplm_rbac_matrix[<?php echo esc_attr( $role_slug ); ?>][upload]" value="1" <?php echo esc_attr( $f_checked ); ?> class="vaplm-matrix-cb" />
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #f0f0f1;">
            <?php submit_button( __( 'Save RBAC Permissions Matrix', 'va-plm-admin-suite' ), 'primary large', 'vaplm_save_rbac_btn', false ); ?>
        </div>
    </form>
</div>