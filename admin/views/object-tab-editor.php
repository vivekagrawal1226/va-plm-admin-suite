<?php
/**
 * Dynamic Tabbed Create & Edit Operational Canvas Engine.
 *
 * Replaces default WordPress post screens with a high-density, multi-tab
 * interface. Manages vertically stacked property matrices, multi-binary
 * drag-and-drop attachment rows, cross-object junction links, and automated
 * parent-child BOM engineering item tables.
 *
 * @package    VA_PLM_Admin_Suite
 * @subpackage VA_PLM_Admin_Suite/admin/views
 */

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

if ( ! isset( $object_type ) ) {
    $object_type = get_current_screen()->post_type;
}

$is_edit_mode = isset( $_GET['post'] ) && ! empty( $_GET['post'] );
$post_id      = $is_edit_mode ? absint( wp_unslash( $_GET['post'] ) ) : 0;
$post_object  = $is_edit_mode ? get_post( $post_id ) : null;

$object_number = $is_edit_mode ? get_post_meta( $post_id, '_vaplm_object_number', true ) : __( '[Assigned on Save]', 'va-plm-admin-suite' );
$created_date  = $is_edit_mode ? get_post_meta( $post_id, '_vaplm_created_datetime', true ) : __( '[Assigned on Save]', 'va-plm-admin-suite' );
$modified_date = $is_edit_mode ? get_post_meta( $post_id, '_vaplm_modified_datetime', true ) : __( '[Assigned on Save]', 'va-plm-admin-suite' );
$creator_id    = $is_edit_mode ? get_post_meta( $post_id, '_vaplm_creator_user_id', true ) : 0;
$modifier_id   = $is_edit_mode ? get_post_meta( $post_id, '_vaplm_modifier_user_id', true ) : 0;

$creator_user  = $creator_id ? get_userdata( $creator_id )->user_login : __( '[Assigned on Save]', 'va-plm-admin-suite' );
$modifier_user = $modifier_id ? get_userdata( $modifier_id )->user_login : __( '[Assigned on Save]', 'va-plm-admin-suite' );

$tax_map = array(
    'vaplm_part'         => 'vaplm_part_type',
    'vaplm_document'     => 'vaplm_doc_type',
    'vaplm_bom'          => 'vaplm_bom_type',
    'vaplm_change_order' => 'vaplm_change_type'
);
$active_tax = isset( $tax_map[$object_type] ) ? $tax_map[$object_type] : '';
$available_subtypes = ! empty( $active_tax ) ? get_terms( array( 'taxonomy' => $active_tax, 'hide_empty' => false ) ) : array();

$selected_subtype = '';
if ( $is_edit_mode && ! empty( $active_tax ) ) {
    $terms = wp_get_post_terms( $post_id, $active_tax );
    if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
        $selected_subtype = $terms[0]->slug;
    }
} elseif ( isset( $_GET['vaplm_subtype'] ) ) {
    $selected_subtype = sanitize_key( wp_unslash( $_GET['vaplm_subtype'] ) );
}

$dynamic_fields = get_option( 'vaplm_dynamic_field_definitions', array() );
?>

<div class="wrap vaplm-admin-wrap" id="vaplm-tabbed-editor-canvas-container">
    <h1>
        <?php 
        if ( $is_edit_mode ) {
            /* translators: %s: Title of the engineering object being modified */
            printf( esc_html__( 'Modify Engineering Record: %s', 'va-plm-admin-suite' ), esc_html( $post_object->post_title ) );
        } else {
            esc_html_e( 'Initialize New Engineering Record Profile', 'va-plm-admin-suite' );
        }
        ?>
    </h1>

    <div class="vaplm-card vaplm-audit-card-wrapper" style="background: #f6f7f7; margin-bottom: 20px;">
        <h3 style="border-bottom-color: #ccd0d4; padding-bottom: 6px; font-size: 12px; color: #50575e;">
            🔒 <?php esc_html_e( 'System-Generated Compliance Audit Ledger', 'va-plm-admin-suite' ); ?>
        </h3>
        <div class="vaplm-audit-grid-layout" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 15px; padding: 5px 0;">
            <div>
                <label style="font-weight: 600; font-size: 11px; color: #646970; display: block; text-transform: uppercase; margin-bottom: 4px;"><?php esc_html_e( 'Unique Object ID Number', 'va-plm-admin-suite' ); ?></label>
                <input type="text" value="<?php echo esc_attr( $object_number ); ?>" readonly class="vaplm-readonly-token-field vaplm-object-number-accent" style="width: 100%;" />
            </div>
            <div>
                <label style="font-weight: 600; font-size: 11px; color: #646970; display: block; text-transform: uppercase; margin-bottom: 4px;"><?php esc_html_e( 'Create Date (UTC)', 'va-plm-admin-suite' ); ?></label>
                <input type="text" value="<?php echo esc_attr( $created_date ); ?>" readonly class="vaplm-readonly-token-field" style="width: 100%;" />
            </div>
            <div>
                <label style="font-weight: 600; font-size: 11px; color: #646970; display: block; text-transform: uppercase; margin-bottom: 4px;"><?php esc_html_e( 'Creator Profile Name', 'va-plm-admin-suite' ); ?></label>
                <input type="text" value="<?php echo esc_attr( $creator_user ); ?>" readonly class="vaplm-readonly-token-field" style="width: 100%;" />
            </div>
            <div>
                <label style="font-weight: 600; font-size: 11px; color: #646970; display: block; text-transform: uppercase; margin-bottom: 4px;"><?php esc_html_e( 'Last Modified Date (UTC)', 'va-plm-admin-suite' ); ?></label>
                <input type="text" value="<?php echo esc_attr( $modified_date ); ?>" readonly class="vaplm-readonly-token-field" style="width: 100%;" />
            </div>
            <div>
                <label style="font-weight: 600; font-size: 11px; color: #646970; display: block; text-transform: uppercase; margin-bottom: 4px;"><?php esc_html_e( 'Modifier Profile Name', 'va-plm-admin-suite' ); ?></label>
                <input type="text" value="<?php echo esc_attr( $modifier_user ); ?>" readonly class="vaplm-readonly-token-field" style="width: 100%;" />
            </div>
        </div>
    </div>

    <h2 class="nav-tab-wrapper vaplm-nav-tab-wrapper" style="margin-bottom: 20px;">
        <a href="#vaplm-pane-properties" class="nav-tab nav-tab-active" data-tab="properties">📋 <?php esc_html_e( 'Properties Matrix', 'va-plm-admin-suite' ); ?></a>
        <a href="#vaplm-pane-attachments" class="nav-tab" data-tab="attachments">📎 <?php esc_html_e( 'File Attachments', 'va-plm-admin-suite' ); ?></a>
        <a href="#vaplm-pane-relationships" class="nav-tab" data-tab="relationships">🔗 <?php esc_html_e( 'Object Relationships', 'va-plm-admin-suite' ); ?></a>
        <?php if ( 'vaplm_bom' === $object_type ) : ?>
            <a href="#vaplm-pane-bom-structure" class="nav-tab" data-tab="bom-structure">🌲 <?php esc_html_e( 'BOM Structure Rows', 'va-plm-admin-suite' ); ?></a>
        <?php endif; ?>
    </h2>

    <form method="post" action="<?php echo esc_url( admin_url( 'post.php' ) ); ?>" id="vaplm-tabbed-object-master-form">
        <?php 
        wp_nonce_field( 'update-post_' . $post_id );
        echo '<input type="hidden" name="action" value="editpost" />';
        echo '<input type="hidden" name="post_ID" value="' . esc_attr( $post_id ) . '" />';
        echo '<input type="hidden" name="post_type" value="' . esc_attr( $object_type ) . '" />';
        ?>

        <div class="vaplm-tab-panels-viewport-context-box">
            
            <div id="vaplm-pane-properties" class="vaplm-tab-panel vaplm-tab-panel-active">
                <div class="vaplm-card">
                    <h3><?php esc_html_e( 'Core Engineering Profile Values', 'va-plm-admin-suite' ); ?></h3>
                    <table class="form-table vaplm-vertical-stack">
                        <tr>
                            <th><label for="title"><?php esc_html_e( 'Engineering Item Descriptive Title *', 'va-plm-admin-suite' ); ?></label></th>
                            <td>
                                <input type="text" id="title" name="post_title" value="<?php echo $is_edit_mode ? esc_attr( $post_object->post_title ) : ''; ?>" required placeholder="<?php esc_attr_e( 'Enter short clear description tracking entry...', 'va-plm-admin-suite' ); ?>" style="width: 100%;" />
                            </td>
                        </tr>

                        <?php if ( ! empty( $active_tax ) ) : ?>
                            <tr>
                                <th><label for="vaplm_active_object_subtype"><?php esc_html_e( 'Classification Sub-Type Class *', 'va-plm-admin-suite' ); ?></label></th>
                                <td>
                                    <select id="vaplm_active_object_subtype" name="tax_input[<?php echo esc_attr( $active_tax ); ?>][]" class="vaplm-topology-selector-trigger" required style="width: 100%;">
                                        <option value=""><?php esc_html_e( '-- Choose Classification Profile --', 'va-plm-admin-suite' ); ?></option>
                                        <?php foreach ( $available_subtypes as $term ) : ?>
                                            <option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $selected_subtype, $term->slug ); ?>><?php echo esc_html( $term->name ); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <p class="description"><?php esc_html_e( 'Changing classification shifts the field validation scopes dynamically below.', 'va-plm-admin-suite' ); ?></p>
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php
                        foreach ( $dynamic_fields as $key => $meta ) {
                            if ( ! isset( $meta['object_type'] ) || $meta['object_type'] !== $object_type ) {
                                continue;
                            }

                            $sub_scope = isset( $meta['object_subtype'] ) ? $meta['object_subtype'] : '';
                            $is_global = empty( $sub_scope );
                            $row_class = 'vaplm-dynamic-property-row ' . ( $is_global ? 'vaplm-scope-universal' : 'vaplm-scope-child-node vaplm-target-subtype-' . esc_attr( $sub_scope ) );
                            $display_style = ( $is_global || $sub_scope === $selected_subtype ) ? '' : 'display: none;';

                            $current_value = $is_edit_mode ? get_post_meta( $post_id, '_' . $key, true ) : ( $meta['default'] ?? '' );
                            $is_required   = ! empty( $meta['required'] ) ? 'required="required"' : '';
                            $req_marker    = ! empty( $meta['required'] ) ? ' <span style="color:#d63638;">*</span>' : '';
                            ?>
                            <tr class="<?php echo esc_attr( $row_class ); ?>" style="<?php echo esc_attr( $display_style ); ?>" data-subtype-scope="<?php echo esc_attr( $sub_scope ); ?>">
                                <th>
                                    <label for="vaplm_f_<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $meta['label'] ); ?><?php echo wp_kses_post( $req_marker ); ?></label>
                                    <?php if ( ! $is_global ) : ?>
                                        <small class="vaplm-subtype-badge-accent"><?php echo esc_html( $sub_scope ); ?></small>
                                    <?php endif; ?>
                                </th>
                                <td>
                                    <?php if ( $meta['field_type'] === 'lov' ) : 
                                        global $wpdb;
                                        $table_lov = $wpdb->prefix . 'vaplm_lov_entries';
                                        $lov_options = $wpdb->get_results( $wpdb->prepare( "SELECT option_value, option_label FROM $table_lov WHERE list_slug = %s ORDER BY option_label ASC", $meta['lov_target'] ) );
                                        ?>
                                        <select id="vaplm_f_<?php echo esc_attr( $key ); ?>" name="vaplm_meta[<?php echo esc_attr( $key ); ?>]" <?php echo esc_attr( $is_required ); ?> style="width: 100%;">
                                            <option value=""><?php esc_html_e( '-- Select Verified Option --', 'va-plm-admin-suite' ); ?></option>
                                            <?php foreach ( $lov_options as $opt ) : ?>
                                                <option value="<?php echo esc_attr( $opt->option_value ); ?>" <?php selected( $current_value, $opt->option_value ); ?>><?php echo esc_html( $opt->option_label ); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    <?php elseif ( $meta['field_type'] === 'number' ) : ?>
                                        <input type="number" step="any" id="vaplm_f_<?php echo esc_attr( $key ); ?>" name="vaplm_meta[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $current_value ); ?>" <?php echo esc_attr( $is_required ); ?> style="width: 100%;" />
                                    <?php else : ?>
                                        <input type="text" id="vaplm_f_<?php echo esc_attr( $key ); ?>" name="vaplm_meta[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $current_value ); ?>" <?php echo esc_attr( $is_required ); ?> style="width: 100%;" />
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                    </table>
                </div>
            </div>

            <div id="vaplm-pane-attachments" class="vaplm-tab-panel">
                <div class="vaplm-card">
                    <h3><?php esc_html_e( 'Linked Product Technical Documentation & Prints Vault', 'va-plm-admin-suite' ); ?></h3>
                    <div class="vaplm-attachment-uploader-dropzone" style="border: 2px dashed #ccd0d4; padding: 25px; text-align: center; background: #fafafa; border-radius: 4px; margin-bottom: 20px;">
                        <span class="dashicons dashicons-upload" style="font-size: 32px; width: 32px; height: 32px; color: #a7aaad; margin-bottom: 8px;"></span>
                        <p style="margin: 5px 0 15px 0; font-weight: 500; color: #646970;"><?php esc_html_e( 'Drag documentation binaries here or trigger manual selection lookups.', 'va-plm-admin-suite' ); ?></p>
                        <button type="button" class="button button-secondary" id="vaplm-add-multi-binary-btn">➕ <?php esc_html_e( 'Attach New File Binaries', 'va-plm-admin-suite' ); ?></button>
                    </div>

                    <table class="wp-list-table widefat fixed striped vaplm-data-table" id="vaplm-attachments-grid-table">
                        <thead>
                            <tr>
                                <th style="width: 45%;"><?php esc_html_e( 'Vault Binary File Name', 'va-plm-admin-suite' ); ?></th>
                                <th style="width: 20%;"><?php esc_html_e( 'Format Extension', 'va-plm-admin-suite' ); ?></th>
                                <th style="width: 20%;"><?php esc_html_e( 'Date Checked In', 'va-plm-admin-suite' ); ?></th>
                                <th style="width: 15%; text-align: center;"><?php esc_html_e( 'Action Control', 'va-plm-admin-suite' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ( $is_edit_mode ) {
                                global $wpdb;
                                $table_relationships = $wpdb->prefix . 'vaplm_relationships';
                                $attached_binaries = $wpdb->get_col( $wpdb->prepare(
                                    "SELECT to_id FROM $table_relationships WHERE from_id = %d AND rel_type = 'object_to_attachment' ORDER BY id ASC",
                                    $post_id
                                ) );

                                if ( ! empty( $attached_binaries ) ) {
                                    foreach ( $attached_binaries as $att_id ) {
                                        $file_url = wp_get_attachment_url( $att_id );
                                        if ( ! $file_url ) { continue; }
                                        ?>
                                        <tr data-attachment-id="<?php echo esc_attr( $att_id ); ?>">
                                            <td><a href="<?php echo esc_url( $file_url ); ?>" target="_blank" class="vaplm-file-link-accent">📁 <?php echo esc_html( basename( $file_url ) ); ?></a></td>
                                            <td><code><?php echo esc_html( strtoupper( str_replace( 'application/', '', get_post_mime_type( $att_id ) ) ) ); ?></code></td>
                                            <td><?php echo esc_html( get_the_date( 'Y-m-d H:i:s', $att_id ) ); ?></td>
                                            <td style="text-align: center;">
                                                <button type="button" class="button vaplm-remove-attachment-row-btn" style="color: #d63638;">✕ <?php esc_html_e( 'Detach', 'va-plm-admin-suite' ); ?></button>
                                                <input type="hidden" name="vaplm_attachments[]" value="<?php echo esc_attr( $att_id ); ?>" />
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    echo '<tr class="vaplm-empty-attachments-row"><td colspan="4" style="text-align: center; color: #646970; padding: 15px;">' . esc_html__( 'No files currently attached to this engineering object records configuration context.', 'va-plm-admin-suite' ) . '</td></tr>';
                                }
                            } else {
                                echo '<tr class="vaplm-empty-attachments-row"><td colspan="4" style="text-align: center; color: #646970; padding: 15px;">' . esc_html__( 'No files currently attached to this engineering object records configuration context.', 'va-plm-admin-suite' ) . '</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="vaplm-pane-relationships" class="vaplm-tab-panel">
                <div class="vaplm-card">
                    <h3><?php esc_html_e( 'Cross-Object Traceability Matrix & Linkage Logs', 'va-plm-admin-suite' ); ?></h3>
                    <p class="description" style="margin-bottom: 12px;">
                        <?php esc_html_e( 'Search and relate external documents or specifications items to establish configuration traceability.', 'va-plm-admin-suite' ); ?>
                    </p>
                    
                    <div class="vaplm-relationship-search-bar-container" style="display: flex; gap: 10px; margin-bottom: 15px; position: relative;">
                        <input type="text" id="vaplm-relationship-search-input" placeholder="<?php esc_attr_e( 'Type targeted Part or Document unique ID parameters to link...', 'va-plm-admin-suite' ); ?>" style="flex-grow: 1; height: 32px;" />
                        <button type="button" class="button button-secondary" id="vaplm-search-query-execute-trigger">🔍 <?php esc_html_e( 'Query Registry', 'va-plm-admin-suite' ); ?></button>
                        <div id="vaplm-ajax-search-results-floating-drawer" style="display:none; position: absolute; top:34px; left:0; width:100%; background:#fff; border:1px solid #ccd0d4; box-shadow:0 4px 6px rgba(0,0,0,0.1); z-index:999; max-height:200px; overflow-y:auto;"></div>
                    </div>

                    <table class="wp-list-table widefat fixed striped vaplm-data-table" id="vaplm-relationships-grid-table">
                        <thead>
                            <tr>
                                <th style="width: 25%;"><?php esc_html_e( 'Object Number', 'va-plm-admin-suite' ); ?></th>
                                <th style="width: 45%;"><?php esc_html_e( 'Object Core Naming Description', 'va-plm-admin-suite' ); ?></th>
                                <th style="width: 15%;"><?php esc_html_e( 'Object Class Base', 'va-plm-admin-suite' ); ?></th>
                                <th style="width: 15%; text-align: center;"><?php esc_html_e( 'Action', 'va-plm-admin-suite' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ( $is_edit_mode ) {
                                global $wpdb;
                                $table_relationships = $wpdb->prefix . 'vaplm_relationships';
                                $rel_query_type = ( 'vaplm_change_order' === $object_type ) ? 'eco_affected_item' : 'object_to_object_link';
                                $linked_ids = $wpdb->get_col( $wpdb->prepare(
                                    "SELECT to_id FROM $table_relationships WHERE from_id = %d AND rel_type = %s ORDER BY id ASC",
                                    $post_id, $rel_query_type
                                ) );

                                if ( ! empty( $linked_ids ) ) {
                                    foreach ( $linked_ids as $link_id ) {
                                        $l_post = get_post( $link_id );
                                        if ( ! $l_post ) { continue; }
                                        $l_num = get_post_meta( $link_id, '_vaplm_object_number', true ) ?: '--';
                                        $l_type = get_post_type_object( $l_post->post_type )->labels->singular_name;
                                        ?>
                                        <tr data-linked-object-id="<?php echo esc_attr( $link_id ); ?>">
                                            <td><code><?php echo esc_html( $l_num ); ?></code></td>
                                            <td><strong><?php echo esc_html( $l_post->post_title ); ?></strong></td>
                                            <td><span class="vaplm-item-type-pill"><?php echo esc_html( $l_type ); ?></span></td>
                                            <td style="text-align: center;">
                                                <button type="button" class="button vaplm-remove-relationship-row-btn" style="color: #d63638;">✕ <?php esc_html_e( 'Drop Link', 'va-plm-admin-suite' ); ?></button>
                                                <input type="hidden" name="vaplm_linked_objects[]" value="<?php echo esc_attr( $link_id ); ?>" />
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    echo '<tr class="vaplm-empty-relationships-row"><td colspan="4" style="text-align: center; color: #646970; padding: 15px;">' . esc_html__( 'No active traceability reference paths linked to this item profile.', 'va-plm-admin-suite' ) . '</td></tr>';
                                }
                            } else {
                                echo '<tr class="vaplm-empty-relationships-row"><td colspan="4" style="text-align: center; color: #646970; padding: 15px;">' . esc_html__( 'No active traceability reference paths linked to this item profile.', 'va-plm-admin-suite' ) . '</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if ( 'vaplm_bom' === $object_type ) : ?>
                <div id="vaplm-pane-bom-structure" class="vaplm-tab-panel">
                    <?php 
                    $meta_box_handler = new VAPLM_Meta_Box_Handler();
                    if ( method_exists( $meta_box_handler, 'render_bom_relationship_table' ) ) {
                        $meta_box_handler->render_bom_relationship_table( $post_object ); 
                    } else {
                        // Fallback message if integrated directly instead of separate call
                        echo '<p style="padding: 20px;">' . esc_html__( 'BOM structure loaded within General Properties tab matrix.', 'va-plm-admin-suite' ) . '</p>';
                    }
                    ?>
                </div>
            <?php endif; ?>

        </div>

        <div class="vaplm-editor-sticky-submission-footer-drawer" style="margin-top: 20px; background: #fff; border: 1px solid #ccd0d4; padding: 15px; border-radius: 4px; display: flex; justify-content: flex-end; gap: 10px;">
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=vaplm-' . str_replace( '_', '-', $object_type ) . 's-workspace' ) ); ?>" class="button button-large"><?php esc_html_e( '✕ Cancel & Return', 'va-plm-admin-suite' ); ?></a>
            <input type="submit" name="save" id="publish" class="button button-primary button-large" value="<?php esc_attr_e( 'Commit & Vault Engineering Record', 'va-plm-admin-suite' ); ?>" />
        </div>

    </form>
</div>