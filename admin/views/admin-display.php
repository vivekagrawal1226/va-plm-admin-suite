<?php
/**
 * Master Settings Tabbed Dashboard Shell Canvas Layout.
 *
 * Provides the visual tab container for the Centralized Configuration Control page.
 * Processes active view states, loads horizontal tab menus, and dynamically embeds 
 * specialized attribute managers, role controllers, lifecycle pools, and bulk import screens.
 *
 * @package    VA_PLM_Admin_Suite
 * @subpackage VA_PLM_Admin_Suite/admin/views
 */

if ( ! defined( 'ABSPATH' ) ) {
    die; 
}

$active_tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'parts-schema';
?>

<div class="wrap vaplm-admin-wrap" id="vaplm-master-configuration-control-dashboard">
    <div style="margin-bottom: 20px; border-bottom: 1px solid #ccd0d4; padding-bottom: 12px;">
        <h1 style="margin: 0; font-size: 23px; font-weight: 400; color: #1d2327;">⚙️ <?php esc_html_e( 'VA PLM Central Configuration Control', 'va-plm-admin-suite' ); ?></h1>
        <p class="description" style="margin: 5px 0 0 0;">
            <?php 
            echo sprintf(
                /* translators: %s: Authoritative portal reference link */
                esc_html__( 'Created Proudly for Engineering and Manufacturing Community by Vivek Agrawal. Learn more at %s. Orchestrate lifecycle status flows, provision custom authorization matrix roles, map dynamic attributes, and perform bulk object imports.', 'va-plm-admin-suite' ),
                '<a href="' . esc_url( 'https://agrawalvivek.com/apps' ) . '" target="_blank" rel="noopener noreferrer" style="color: #2271b1; text-decoration: underline; font-weight: 600;">agrawalvivek.com/apps</a>'
            ); 
            ?>
        </p>
    </div>

    <nav class="nav-tab-wrapper vaplm-nav-tab-wrapper" style="margin-bottom: 20px;">
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=vaplm-configuration-control&tab=parts-schema' ) ); ?>" class="nav-tab <?php echo ( 'parts-schema' === $active_tab ) ? 'nav-tab-active' : ''; ?>">
            📐 <?php esc_html_e( 'Parts Schema', 'va-plm-admin-suite' ); ?>
        </a>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=vaplm-configuration-control&tab=docs-schema' ) ); ?>" class="nav-tab <?php echo ( 'docs-schema' === $active_tab ) ? 'nav-tab-active' : ''; ?>">
            📄 <?php esc_html_e( 'Documents Schema', 'va-plm-admin-suite' ); ?>
        </a>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=vaplm-configuration-control&tab=bom-schema' ) ); ?>" class="nav-tab <?php echo ( 'bom-schema' === $active_tab ) ? 'nav-tab-active' : ''; ?>">
            📊 <?php esc_html_e( 'BOM Attributes', 'va-plm-admin-suite' ); ?>
        </a>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=vaplm-configuration-control&tab=change-schema' ) ); ?>" class="nav-tab <?php echo ( 'change-schema' === $active_tab ) ? 'nav-tab-active' : ''; ?>">
            🔄 <?php esc_html_e( 'Change Management', 'va-plm-admin-suite' ); ?>
        </a>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=vaplm-configuration-control&tab=lifecycle-pools' ) ); ?>" class="nav-tab <?php echo ( 'lifecycle-pools' === $active_tab ) ? 'nav-tab-active' : ''; ?>">
            🚦 <?php esc_html_e( 'Lifecycle Milestones', 'va-plm-admin-suite' ); ?>
        </a>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=vaplm-configuration-control&tab=dictionary-lov' ) ); ?>" class="nav-tab <?php echo ( 'dictionary-lov' === $active_tab ) ? 'nav-tab-active' : ''; ?>">
            📖 <?php esc_html_e( 'LOV Dictionaries', 'va-plm-admin-suite' ); ?>
        </a>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=vaplm-configuration-control&tab=role-provisioning' ) ); ?>" class="nav-tab <?php echo ( 'role-provisioning' === $active_tab ) ? 'nav-tab-active' : ''; ?>">
            🛡️ <?php esc_html_e( 'RBAC Roles Matrix', 'va-plm-admin-suite' ); ?>
        </a>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=vaplm-configuration-control&tab=numbering-schemas' ) ); ?>" class="nav-tab <?php echo ( 'numbering-schemas' === $active_tab ) ? 'nav-tab-active' : ''; ?>">
            🔢 <?php esc_html_e( 'Numbering Schemas', 'va-plm-admin-suite' ); ?>
        </a>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=vaplm-configuration-control&tab=bulk-import' ) ); ?>" class="nav-tab <?php echo ( 'bulk-import' === $active_tab ) ? 'nav-tab-active' : ''; ?>">
            📥 <?php esc_html_e( 'Bulk Import', 'va-plm-admin-suite' ); ?>
        </a>
    </nav>

    <div class="vaplm-tab-viewports-wrapper">
        <?php
        switch ( $active_tab ) {
            case 'parts-schema':
                if ( file_exists( VAPLM_PATH . 'admin/views/part-manager.php' ) ) {
                    include_once VAPLM_PATH . 'admin/views/part-manager.php';
                }
                break;

            case 'docs-schema':
                if ( file_exists( VAPLM_PATH . 'admin/views/doc-manager.php' ) ) {
                    include_once VAPLM_PATH . 'admin/views/doc-manager.php';
                }
                break;

            case 'bom-schema':
                if ( file_exists( VAPLM_PATH . 'admin/views/bom-manager.php' ) ) {
                    include_once VAPLM_PATH . 'admin/views/bom-manager.php';
                }
                break;

            case 'change-schema':
                if ( file_exists( VAPLM_PATH . 'admin/views/change-manager.php' ) ) {
                    include_once VAPLM_PATH . 'admin/views/change-manager.php';
                }
                break;

            case 'lifecycle-pools':
                if ( file_exists( VAPLM_PATH . 'admin/views/status-manager.php' ) ) {
                    include_once VAPLM_PATH . 'admin/views/status-manager.php';
                }
                break;

            case 'dictionary-lov':
                if ( file_exists( VAPLM_PATH . 'admin/views/lov-manager.php' ) ) {
                    include_once VAPLM_PATH . 'admin/views/lov-manager.php';
                }
                break;

            case 'role-provisioning':
                if ( file_exists( VAPLM_PATH . 'admin/views/role-manager.php' ) ) {
                    include_once VAPLM_PATH . 'admin/views/role-manager.php';
                }
                break;

            case 'numbering-schemas':
                $schemas_config = get_option( 'vaplm_object_numbering_schemas', array() );
                $vaplm_objects = array(
                    'vaplm_part'         => array( 'label' => __( 'Parts Identification Schema', 'va-plm-admin-suite' ), 'default' => 'PRT-{SEQ:6}' ),
                    'vaplm_document'     => array( 'label' => __( 'Documents Vault Schema', 'va-plm-admin-suite' ), 'default' => 'DOC-{SEQ:6}' ),
                    'vaplm_bom'          => array( 'label' => __( 'BOM Assemblies Schema', 'va-plm-admin-suite' ), 'default' => 'BOM-{SEQ:6}' ),
                    'vaplm_change_order' => array( 'label' => __( 'Change Orders Schema', 'va-plm-admin-suite' ), 'default' => 'ECO-{SEQ:6}' )
                );
                ?>
                <div class="vaplm-card" style="margin-top: 0; padding: 25px;">
                    <h4 style="margin-top: 0; font-size: 14px; text-transform: uppercase;"><?php esc_html_e( 'Autonumbering Mask & Initial Sequence Counter Control Panel', 'va-plm-admin-suite' ); ?></h4>
                    <p class="description" style="margin-bottom: 20px;">
                        <?php esc_html_e( 'Construct dynamic item mask layouts below. Supported syntax tracking tokens include: `{SEQ:6}` or `{SEQ:8}` for sequential index padding width limits, and `{YYYY}`, `{YY}`, `{MM}`, `{DD}` for running transaction calendar strings stamps.', 'va-plm-admin-suite' ); ?>
                    </p>

                    <form method="post" action="">
                        <?php wp_nonce_field( 'vaplm_admin_save_action', 'vaplm_admin_nonce_field' ); ?>
                        <input type="hidden" name="vaplm_action_type" value="manage_numbering_schemas" />

                        <table class="wp-list-table widefat fixed striped vaplm-data-table" style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr>
                                    <th style="padding: 10px; font-weight: 600; width: 25%;"><?php esc_html_e( 'Engineering Object Class', 'va-plm-admin-suite' ); ?></th>
                                    <th style="padding: 10px; font-weight: 600; width: 45%;"><?php esc_html_e( 'Custom Schema Mask Template Pattern', 'va-plm-admin-suite' ); ?></th>
                                    <th style="padding: 10px; font-weight: 600; width: 30%;"><?php esc_html_e( 'Current Database Sequence Counter Base Index', 'va-plm-admin-suite' ); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ( $vaplm_objects as $cpt_key => $meta ) : 
                                    $current_mask = isset( $schemas_config[$cpt_key]['mask'] ) ? $schemas_config[$cpt_key]['mask'] : $meta['default'];
                                    $current_base = (int) get_option( 'vaplm_next_idx_' . $cpt_key, 1 );
                                    ?>
                                    <tr>
                                        <td style="padding: 12px; vertical-align: middle;">
                                            <strong><?php echo esc_html( $meta['label'] ); ?></strong><br/>
                                            <code style="font-size: 11px; color:#646970;"><?php echo esc_html( $cpt_key ); ?></code>
                                        </td>
                                        <td style="padding: 12px; vertical-align: middle;">
                                            <input type="text" name="vaplm_schema_mask[<?php echo esc_attr( $cpt_key ); ?>]" value="<?php echo esc_attr( $current_mask ); ?>" required style="width: 100%; max-width: 320px; font-family: monospace; font-size: 13px;" placeholder="e.g. PRT-{YYYY}-{SEQ:6}" />
                                        </td>
                                        <td style="padding: 12px; vertical-align: middle;">
                                            <input type="number" name="vaplm_counter_base[<?php echo esc_attr( $cpt_key ); ?>]" value="<?php echo esc_attr( $current_base ); ?>" min="1" required style="width: 100%; max-width: 120px; font-weight: 600;" />
                                            <span class="description" style="font-size: 11px; margin-left: 8px;"><?php esc_html_e( 'Next allocation: ', 'va-plm-admin-suite' ); ?> <code><?php echo esc_html( $current_base ); ?></code></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        
                        <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #f0f0f1;">
                            <?php submit_button( __( 'Commit Autonumbering Schemas Updates', 'va-plm-admin-suite' ), 'primary large', 'vaplm_save_schemas_btn', false ); ?>
                        </div>
                    </form>
                </div>
                <?php
                break;

            case 'bulk-import':
                if ( file_exists( VAPLM_PATH . 'admin/views/import-manager.php' ) ) {
                    include_once VAPLM_PATH . 'admin/views/import-manager.php';
                }
                break;

            default:
                ?>
                <div class="vaplm-card" style="margin-top: 0; padding: 20px;">
                    <p style="font-size: 13px; color: #646970; font-style: italic; text-align: center; margin: 0;">
                        <?php esc_html_e( 'Invalid configuration tab routing segment target selected.', 'va-plm-admin-suite' ); ?>
                    </p>
                </div>
                <?php
                break;
        }
        ?>
    </div>
</div>