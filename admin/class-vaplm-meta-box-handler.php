<?php
/**
 * Dynamic Form Metadata Layout, Tabbed Interface & Split Vault Attachment Controller.
 *
 * @package    VA_PLM_Admin_Suite
 * @subpackage VA_PLM_Admin_Suite/admin
 */

if ( ! defined( 'ABSPATH' ) ) {
    die; 
}

class VAPLM_Meta_Box_Handler {

    /**
     * Registers contextual administrative panels against core engineering post type classes.
     */
    public function register_dynamic_meta_panels() {
        $vaplm_types = array( 'vaplm_part', 'vaplm_document', 'vaplm_bom', 'vaplm_change_order' );
        foreach ( $vaplm_types as $type ) {
            add_meta_box(
                'vaplm_core_engineering_workspace_panel',
                __( 'VA PLM Engineering Object Workspace Canvas', 'va-plm-admin-suite' ),
                array( $this, 'render_integrated_meta_box_tabs_layout' ),
                $type,
                'normal',
                'high'
            );
        }
    }

    /**
     * Orchestrates high-density horizontal sub-tab views inside the post editor framework.
     */
    public function render_integrated_meta_box_tabs_layout( $post ) {
        wp_nonce_field( 'vaplm_meta_box_save_action', 'vaplm_meta_box_nonce' );

        $object_number = get_post_meta( $post->ID, '_vaplm_object_number', true ) ?: '--';
        $lifecycle     = get_post_meta( $post->ID, '_vaplm_lifecycle_status', true ) ?: 'Draft';
        $created_dt    = get_post_meta( $post->ID, '_vaplm_created_datetime', true ) ?: '--';
        $modified_dt   = get_post_meta( $post->ID, '_vaplm_modified_datetime', true ) ?: '--';
        
        $c_user_id     = get_post_meta( $post->ID, '_vaplm_creator_user_id', true );
        $m_user_id     = get_post_meta( $post->ID, '_vaplm_modifier_user_id', true );
        
        $creator_name  = $c_user_id ? get_userdata( $c_user_id )->user_login : '--';
        $modifier_name = $m_user_id ? get_userdata( $m_user_id )->user_login : '--';

        $is_editable_context = current_user_can( 'edit_post', $post->ID );
        ?>
        <div class="vaplm-tabbed-app-container">
            
            <div class="vaplm-workspace-toolbar-accelerator" style="display: flex; align-items: center; justify-content: space-between; background: #fafafa; border: 1px solid #ccd0d4; padding: 10px 15px; border-radius: 4px; margin-bottom: 15px;">
                <div>
                    <span class="vaplm-item-type-pill vaplm-status-<?php echo esc_attr( strtolower( str_replace( ' ', '', $lifecycle ) ) ); ?>">
                        <?php echo esc_html( $lifecycle ); ?>
                    </span>
                    <strong style="font-family: monospace; font-size: 14px; margin-left: 10px;">
                        <?php echo esc_html( $object_number ); ?>
                    </strong>
                </div>
                <div>
                    <?php if ( $is_editable_context ) : ?>
                        <button type="button" class="button button-primary" id="vaplm-custom-toggle-edit-btn" style="font-weight: 600;">
                            🔓 <?php esc_html_e( 'Promote to Edit Lock Mode', 'va-plm-admin-suite' ); ?>
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="nav-tab-wrapper vaplm-nav-tab-wrapper">
                <a href="#vaplm-tab-general-properties" class="nav-tab nav-tab-active">📋 <?php esc_html_e( 'General Properties', 'va-plm-admin-suite' ); ?></a>
                
                <?php if ( 'vaplm_bom' === $post->post_type ) : ?>
                    <a href="#vaplm-tab-bom-structure" class="nav-tab">📊 <?php esc_html_e( 'BOM Structure Rows', 'va-plm-admin-suite' ); ?></a>
                <?php endif; ?>
                
                <?php if ( 'vaplm_bom' === $post->post_type || 'vaplm_part' === $post->post_type ) : ?>
                    <a href="#vaplm-tab-object-relationships" class="nav-tab">🔗 <?php esc_html_e( 'Relationships', 'va-plm-admin-suite' ); ?></a>
                <?php endif; ?>
                
                <a href="#vaplm-tab-attachments-vault" class="nav-tab">📁 <?php esc_html_e( 'Secure Vault Attachments', 'va-plm-admin-suite' ); ?></a>
                <a href="#vaplm-tab-compliance-audit" class="nav-tab">🛡️ <?php esc_html_e( 'Forensics Compliance Audit', 'va-plm-admin-suite' ); ?></a>
            </div>

            <div id="vaplm-tab-general-properties" class="vaplm-tab-panel vaplm-tab-panel-active">
                <table class="form-table vaplm-vertical-stack">
                    <tbody>
                        <tr>
                            <th scope="row"><label><?php esc_html_e( 'Object Description Descriptor Title *', 'va-plm-admin-suite' ); ?></label></th>
                            <td>
                                <p class="description" style="margin: 0 0 5px 0; font-size: 12px; color: #646970;">
                                    <?php esc_html_e( 'Provide the standard nomenclature title used on production manifests routing files.', 'va-plm-admin-suite' ); ?>
                                </p>
                            </td>
                        </tr>
                        <?php
                        $fields_registry = get_option( 'vaplm_dynamic_field_definitions', array() );
                        foreach ( $fields_registry as $key => $config ) {
                            if ( isset( $config['object_type'] ) && $config['object_type'] === $post->post_type ) {
                                if ( isset( $config['object_subtype'] ) && $config['object_subtype'] === 'bom_relationship_column' ) {
                                    continue;
                                }
                                
                                $meta_value = get_post_meta( $post->ID, '_' . $key, true ) ?: ( $config['default'] ?? '' );
                                $required_attribute = empty( $config['required'] ) ? '' : 'required';
                                ?>
                                <tr>
                                    <th scope="row">
                                        <label for="vaplm_custom_f_<?php echo esc_attr( $key ); ?>">
                                            <?php echo esc_html( $config['label'] ); ?>
                                            <?php if ( ! empty( $required_attribute ) ) echo '<span style="color:#d63638;">*</span>'; ?>
                                        </label>
                                    </th>
                                    <td>
                                        <?php if ( 'lov_dropdown' === $config['field_type'] && ! empty( $config['lov_target'] ) ) : ?>
                                            <select id="vaplm_custom_f_<?php echo esc_attr( $key ); ?>" name="vaplm_dynamic_meta[<?php echo esc_attr( $key ); ?>]" <?php echo esc_attr( $required_attribute ); ?>>
                                                <option value=""><?php printf( esc_html__( '-- Select %s Option --', 'va-plm-admin-suite' ), esc_html( $config['label'] ) ); ?></option>
                                                <?php
                                                global $wpdb;
                                                $lov_rows = $wpdb->get_results( $wpdb->prepare( "SELECT option_value, option_label FROM {$wpdb->prefix}vaplm_lov_entries WHERE list_slug = %s ORDER BY id ASC", sanitize_key( $config['lov_target'] ) ) );
                                                foreach ( $lov_rows as $lov ) {
                                                    echo '<option value="' . esc_attr( $lov->option_value ) . '" ' . selected( $meta_value, $lov->option_value, false ) . '>' . esc_html( $lov->option_label ) . '</option>';
                                                }
                                                ?>
                                            </select>
                                        <?php elseif ( 'textarea' === $config['field_type'] ) : ?>
                                            <textarea id="vaplm_custom_f_<?php echo esc_attr( $key ); ?>" name="vaplm_dynamic_meta[<?php echo esc_attr( $key ); ?>]" <?php echo esc_attr( $required_attribute ); ?>><?php echo esc_textarea( $meta_value ); ?></textarea>
                                        <?php else : ?>
                                            <input type="text" id="vaplm_custom_f_<?php echo esc_attr( $key ); ?>" name="vaplm_dynamic_meta[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $meta_value ); ?>" <?php echo esc_attr( $required_attribute ); ?> />
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <?php if ( 'vaplm_bom' === $post->post_type ) : ?>
                <?php
                $bom_table_columns_schema = array_filter( $fields_registry, function( $field ) {
                    return isset( $field['object_type'] ) && $field['object_type'] === 'vaplm_bom' && isset( $field['object_subtype'] ) && $field['object_subtype'] === 'bom_relationship_column';
                });
                ?>
                <div id="vaplm-tab-bom-structure" class="vaplm-tab-panel">
                    <p class="description" style="margin-bottom: 12px;">
                        <?php esc_html_e( 'Map parent-child structural hierarchies. Columns feature customizable horizontal resizer handles. Click 🔗 next to a child part to review its parameters in a new window.', 'va-plm-admin-suite' ); ?>
                    </p>

                    <div class="vaplm-bom-rows-scroll-viewport">
                        <table class="wp-list-table widefat fixed striped vaplm-data-table" id="vaplm-bom-assembly-lines-matrix-table">
                            <thead>
                                <tr>
                                    <th style="width: 30%; min-width: 180px; padding: 10px; position: relative;">
                                        <?php esc_html_e( 'Component Child Part Number', 'va-plm-admin-suite' ); ?>
                                        <div class="vaplm-th-resizer-handle"></div>
                                    </th>
                                    <th style="width: 15%; min-width: 90px; padding: 10px; position: relative;">
                                        <?php esc_html_e( 'Quantity Required', 'va-plm-admin-suite' ); ?>
                                        <div class="vaplm-th-resizer-handle"></div>
                                    </th>
                                    <th style="width: 15%; min-width: 120px; padding: 10px; position: relative;">
                                        <?php esc_html_e( 'Unit of Measure (UOM)', 'va-plm-admin-suite' ); ?>
                                        <div class="vaplm-th-resizer-handle"></div>
                                    </th>
                                    
                                    <?php foreach ( $bom_table_columns_schema as $col_key => $col_config ) : ?>
                                        <th style="width: 15%; min-width: 100px; padding: 10px; position: relative;">
                                            <?php echo esc_html( $col_config['label'] ); ?>
                                            <div class="vaplm-th-resizer-handle"></div>
                                        </th>
                                    <?php endforeach; ?>

                                    <th style="width: 10%; min-width: 70px; padding: 10px; text-align: center;">
                                        <?php esc_html_e( 'Actions', 'va-plm-admin-suite' ); ?>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="vaplm-bom-assembly-rows-injection-body">
                                <?php
                                global $wpdb;
                                $bom_lines = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}vaplm_ebom WHERE parent_part_id = %d ORDER BY id ASC", $post->ID ) );
                                
                                if ( ! empty( $bom_lines ) ) {
                                    foreach ( $bom_lines as $line ) {
                                        $row_id = intval( $line->id );
                                        $row_meta_payload = ! empty( $line->meta_data ) ? json_decode( $line->meta_data, true ) : array();
                                        $child_part_edit_url = admin_url( 'post.php?post=' . intval( $line->child_part_id ) . '&action=edit' );
                                        ?>
                                        <tr data-row-index="<?php echo esc_attr( $row_id ); ?>">
                                            <td style="vertical-align: middle;">
                                                <div style="display: flex; align-items: center; gap: 8px;">
                                                    <a href="<?php echo esc_url( $child_part_edit_url ); ?>" target="_blank" class="vaplm-bom-live-component-link vaplm-file-link-accent" title="<?php esc_attr_e( 'Open Part Specification Sheet in New Tab', 'va-plm-admin-suite' ); ?>" style="font-size: 14px; text-decoration: none; line-height: 1;">🔗</a>
                                                    
                                                    <select name="vaplm_bom_components[<?php echo esc_attr( $row_id ); ?>][child_id]" class="vaplm-bom-child-part-selector-dropdown" style="flex-grow: 1; height: 32px;" required>
                                                        <?php
                                                        $all_parts = get_posts( array( 'post_type' => 'vaplm_part', 'posts_per_page' => -1, 'post_status' => 'publish' ) );
                                                        foreach ( $all_parts as $p_item ) {
                                                            $p_num = get_post_meta( $p_item->ID, '_vaplm_object_number', true ) ?: '--';
                                                            echo '<option value="' . esc_attr( $p_item->ID ) . '" ' . selected( $line->child_part_id, $p_item->ID, false ) . '>' . esc_html( $p_num . ' - ' . $p_item->post_title ) . '</option>';
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            </td>
                                            <td>
                                                <input type="number" name="vaplm_bom_components[<?php echo esc_attr( $row_id ); ?>][quantity]" value="<?php echo esc_attr( $line->quantity ); ?>" min="0.0001" step="0.0001" style="width: 100%; height: 32px;" required />
                                            </td>
                                            <td>
                                                <select name="vaplm_bom_components[<?php echo esc_attr( $row_id ); ?>][uom]" style="width: 100%; height: 32px;" required>
                                                    <?php
                                                    $uom_rows = $wpdb->get_results( "SELECT option_value, option_label FROM {$wpdb->prefix}vaplm_lov_entries WHERE list_slug = 'uom_codes' ORDER BY id ASC" );
                                                    foreach ( $uom_rows as $uom ) {
                                                        echo '<option value="' . esc_attr( $uom->option_value ) . '" ' . selected( $line->uom, $uom->option_value, false ) . '>' . esc_html( $uom->option_label ) . '</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </td>

                                            <?php foreach ( $bom_table_columns_schema as $col_key => $col_config ) : ?>
                                                <?php $cell_value = isset( $row_meta_payload[$col_key] ) ? $row_meta_payload[$col_key] : ( $col_config['default'] ?? '' ); ?>
                                                <td>
                                                    <?php if ( 'lov_dropdown' === $col_config['field_type'] && ! empty( $col_config['lov_target'] ) ) : ?>
                                                        <select name="vaplm_bom_components[<?php echo esc_attr( $row_id ); ?>][<?php echo esc_attr( $col_key ); ?>]" style="width: 100%; height: 32px;">
                                                            <option value=""><?php esc_html_e( '-- Select --', 'va-plm-admin-suite' ); ?></option>
                                                            <?php
                                                            $col_lovs = $wpdb->get_results( $wpdb->prepare( "SELECT option_value, option_label FROM {$wpdb->prefix}vaplm_lov_entries WHERE list_slug = %s ORDER BY id ASC", sanitize_key( $col_config['lov_target'] ) ) );
                                                            foreach ( $col_lovs as $c_lov ) {
                                                                echo '<option value="' . esc_attr( $c_lov->option_value ) . '" ' . selected( $cell_value, $c_lov->option_value, false ) . '>' . esc_html( $c_lov->option_label ) . '</option>';
                                                            }
                                                            ?>
                                                        </select>
                                                    <?php else : ?>
                                                        <input type="text" name="vaplm_bom_components[<?php echo esc_attr( $row_id ); ?>][<?php echo esc_attr( $col_key ); ?>]" value="<?php echo esc_attr( $cell_value ); ?>" style="width: 100%; height: 32px;" placeholder="<?php echo esc_attr( $col_config['label'] ); ?>..." />
                                                    <?php endif; ?>
                                                </td>
                                            <?php endforeach; ?>

                                            <td style="text-align: center;">
                                                <button type="button" class="button vaplm-bom-delete-component-row-action-btn" style="color: #d63638; border-color: #ccd0d4; height: 32px; line-height: 30px;">✕ <?php esc_html_e( 'Drop', 'va-plm-admin-suite' ); ?></button>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="vaplm-editable-only-element" style="padding-top: 5px;">
                        <button type="button" class="button button-secondary" id="vaplm-bom-add-component-line-item-trigger-btn">
                            ➕ <?php esc_html_e( 'Add Component Line Item', 'va-plm-admin-suite' ); ?>
                        </button>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ( 'vaplm_bom' === $post->post_type || 'vaplm_part' === $post->post_type ) : ?>
                <div id="vaplm-tab-object-relationships" class="vaplm-tab-panel">
                    <p class="description" style="margin-bottom: 15px;">
                        <?php esc_html_e( 'Query and append reference documentation validation files from the Documents Vault directly against this master specification context routing.', 'va-plm-admin-suite' ); ?>
                    </p>

                    <div class="vaplm-editable-only-element vaplm-relationship-search-container" style="position: relative; margin-bottom: 20px; max-width: 500px;">
                        <input type="text" id="vaplm-relationship-typeahead-search-box" class="widefat" placeholder="<?php esc_attr_e( '🔍 Search documents by explicit title, Object ID code, or custom definitions...', 'va-plm-admin-suite' ); ?>" style="height: 36px; padding-left: 10px;" />
                        <div id="vaplm-relationship-search-results-floating-flyout" class="vaplm-typeahead-flyout" style="display: none; position: absolute; left: 0; right: 0; background: #fff; border: 1px solid #ccd0d4; z-index: 9999; max-height: 250px; overflow-y: auto; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border-radius: 0 0 4px 4px;"></div>
                    </div>

                    <table class="wp-list-table widefat fixed striped vaplm-data-table" id="vaplm-object-relationships-grid-table">
                        <thead>
                            <tr>
                                <th style="width: 25%;"><?php esc_html_e( 'Unique Document ID', 'va-plm-admin-suite' ); ?></th>
                                <th style="width: 45%;"><?php esc_html_e( 'Linked Reference Document Title Nomenclature', 'va-plm-admin-suite' ); ?></th>
                                <th style="width: 15%;"><?php esc_html_e( 'Lifecycle Milestone', 'va-plm-admin-suite' ); ?></th>
                                <th style="width: 15%; text-align: center;" class="vaplm-editable-only-element-cell"><?php esc_html_e( 'Action', 'va-plm-admin-suite' ); ?></th>
                            </tr>
                        </thead>
                        <tbody id="vaplm-object-relationships-rows-injection-target">
                            <?php
                            $linked_docs_pool = get_post_meta( $post->ID, '_vaplm_linked_documents', true ) ?: array();
                            if ( ! empty( $linked_docs_pool ) && is_array( $linked_docs_pool ) ) {
                                foreach ( $linked_docs_pool as $doc_id ) {
                                    $doc_id = intval( $doc_id );
                                    if ( 'vaplm_document' === get_post_type( $doc_id ) ) {
                                        $d_num   = get_post_meta( $doc_id, '_vaplm_object_number', true ) ?: 'DOC--';
                                        $d_state = get_post_meta( $doc_id, '_vaplm_lifecycle_status', true ) ?: 'Draft';
                                        $d_title = get_the_title( $doc_id );
                                        $d_url   = admin_url( 'post.php?post=' . $doc_id . '&action=edit' );
                                        ?>
                                        <tr data-linked-doc-id="<?php echo esc_attr( $doc_id ); ?>">
                                            <td><code><?php echo esc_html( $d_num ); ?></code></td>
                                            <td><a href="<?php echo esc_url( $d_url ); ?>" target="_blank" class="vaplm-file-link-accent">📄 <?php echo esc_html( $d_title ); ?></a></td>
                                            <td><span class="vaplm-item-type-pill"><?php echo esc_html( $d_state ); ?></span></td>
                                            <td style="text-align: center;" class="vaplm-editable-only-element-cell">
                                                <button type="button" class="button vaplm-drop-relationship-row-btn" style="color: #d63638;">✕ <?php esc_html_e( 'Sever Link', 'va-plm-admin-suite' ); ?></button>
                                                <input type="hidden" name="vaplm_linked_documents[]" value="<?php echo esc_attr( $doc_id ); ?>" />
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                }
                            } else {
                                echo '<tr class="vaplm-empty-relationships-fallback-row"><td colspan="4" style="text-align: center; color: #646970; padding: 20px;">' . esc_html__( 'No operational alignments mapped against active specification contexts.', 'va-plm-admin-suite' ) . '</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <div id="vaplm-tab-attachments-vault" class="vaplm-tab-panel">
                <div class="vaplm-attachment-uploader-dropzone vaplm-editable-only-element" id="vaplm-add-multi-binary-btn" style="margin-bottom: 20px;">
                    <span class="dashicons dashicons-upload" style="font-size: 40px; width: 40px; height: 40px; color: #2271b1;"></span>
                    <p><?php esc_html_e( 'Click to bind active production documentation to this object specification routing file context.', 'va-plm-admin-suite' ); ?></p>
                </div>

                <table class="wp-list-table widefat fixed striped vaplm-data-table" id="vaplm-attachments-grid-table">
                    <thead>
                        <tr>
                            <th style="width: 55%;"><?php esc_html_e( 'Vault Linked Production Binary Artifact File Name', 'va-plm-admin-suite' ); ?></th>
                            <th style="width: 25%;"><?php esc_html_e( 'MIME File Ext Extension', 'va-plm-admin-suite' ); ?></th>
                            <th style="width: 20%; text-align: center;" class="vaplm-editable-only-element-cell"><?php esc_html_e( 'Action', 'va-plm-admin-suite' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $attachments_pool = get_post_meta( $post->ID, '_vaplm_vault_attachments', true ) ?: array();
                        if ( ! empty( $attachments_pool ) && is_array( $attachments_pool ) ) {
                            foreach ( $attachments_pool as $attachment_id ) {
                                $attachment_id = intval( $attachment_id );
                                $url = wp_get_attachment_url( $attachment_id );
                                $name = basename( get_attached_file( $attachment_id ) );
                                $mime = strtoupper( str_replace( 'application/', '', get_post_mime_type( $attachment_id ) ) );
                                ?>
                                <tr data-attachment-id="<?php echo esc_attr( $attachment_id ); ?>">
                                    <td><a href="<?php echo esc_url( $url ); ?>" target="_blank" class="vaplm-file-link-accent">📁 <?php echo esc_html( $name ); ?></a></td>
                                    <td><code><?php echo esc_html( $mime ); ?></code></td>
                                    <td style="text-align: center;" class="vaplm-editable-only-element-cell">
                                        <button type="button" class="button vaplm-remove-attachment-row-btn" style="color: #d63638;">✕ <?php esc_html_e( 'Detach', 'va-plm-admin-suite' ); ?></button>
                                        <input type="hidden" name="vaplm_attachments[]" value="<?php echo esc_attr( $attachment_id ); ?>" />
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo '<tr class="vaplm-empty-attachments-row"><td colspan="3" style="text-align: center; color: #646970;">' . esc_html__( 'No associated binary attachments in vault.', 'va-plm-admin-suite' ) . '</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <div id="vaplm-tab-compliance-audit" class="vaplm-tab-panel">
                <table class="vaplm-audit-table form-table vaplm-vertical-stack" style="width: 100%;">
                    <tbody>
                        <tr>
                            <th scope="row"><label><?php esc_html_e( 'Unique Identity Sequence Marker ID', 'va-plm-admin-suite' ); ?></label></th>
                            <td><input type="text" class="vaplm-readonly-token-field vaplm-object-number-accent" value="<?php echo esc_attr( $object_number ); ?>" readonly /></td>
                        </tr>
                        <tr>
                            <th scope="row"><label><?php esc_html_e( 'Creation Date-Time Stamp (UTC)', 'va-plm-admin-suite' ); ?></label></th>
                            <td><input type="text" class="vaplm-readonly-token-field" value="<?php echo esc_attr( $created_dt ); ?>" readonly /></td>
                        </tr>
                        <tr>
                            <th scope="row"><label><?php esc_html_e( 'Authoring Engineering Creator User', 'va-plm-admin-suite' ); ?></label></th>
                            <td><input type="text" class="vaplm-readonly-token-field" value="<?php echo esc_attr( $creator_name ); ?>" readonly /></td>
                        </tr>
                        <tr>
                            <th scope="row"><label><?php esc_html_e( 'Last Modification Change Date-Time Stamp (UTC)', 'va-plm-admin-suite' ); ?></label></th>
                            <td><input type="text" class="vaplm-readonly-token-field" value="<?php echo esc_attr( $modified_dt ); ?>" readonly /></td>
                        </tr>
                        <tr>
                            <th scope="row"><label><?php esc_html_e( 'Revising Committing Modifier User', 'va-plm-admin-suite' ); ?></label></th>
                            <td><input type="text" class="vaplm-readonly-token-field" value="<?php echo esc_attr( $modifier_name ); ?>" readonly /></td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>

        <?php
        // Prepare localized data for JS without inline scripts.
        $client_side_column_cache = array();
        if ( ! empty( $bom_table_columns_schema ) ) {
            foreach ( $bom_table_columns_schema as $c_key => $c_conf ) {
                $options_buffer = array();
                if ( 'lov_dropdown' === $c_conf['field_type'] && ! empty( $c_conf['lov_target'] ) ) {
                    global $wpdb;
                    $lov_items = $wpdb->get_results( $wpdb->prepare( "SELECT option_value, option_label FROM {$wpdb->prefix}vaplm_lov_entries WHERE list_slug = %s ORDER BY id ASC", sanitize_key( $c_conf['lov_target'] ) ) );
                    foreach ( $lov_items as $li ) {
                        $options_buffer[] = array( 'value' => $li->option_value, 'label' => $li->option_label );
                    }
                }
                $client_side_column_cache[$c_key] = array(
                    'label'   => $c_conf['label'],
                    'type'    => $c_conf['field_type'],
                    'default' => $c_conf['default'] ?? '',
                    'options' => $options_buffer
                );
            }
        }

        $parts_catalog_query = get_posts( array( 'post_type' => 'vaplm_part', 'posts_per_page' => -1, 'post_status' => 'publish' ) );
        $serialized_cache_buffer = array();
        if ( ! empty( $parts_catalog_query ) && ! is_wp_error( $parts_catalog_query ) ) {
            foreach ( $parts_catalog_query as $part_post_node ) {
                $object_id_code = get_post_meta( $part_post_node->ID, '_vaplm_object_number', true ) ?: '--';
                $serialized_cache_buffer[ intval( $part_post_node->ID ) ] = array(
                    'number' => $object_id_code,
                    'title'  => $part_post_node->post_title
                );
            }
        }
        ?>
        <div id="vaplm-meta-box-data-store" style="display:none;" 
             data-bom-columns="<?php echo esc_attr( wp_json_encode( $client_side_column_cache ) ); ?>" 
             data-parts-inventory="<?php echo esc_attr( wp_json_encode( $serialized_cache_buffer ) ); ?>">
        </div>
        <?php
    }

    public function save_dynamic_meta_panels( $post_id, $post ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( wp_is_post_revision( $post_id ) || 'revision' === $post->post_type ) {
            return;
        }

        // Strict Nonce and Sanitization on incoming fields
        if ( ! isset( $_POST['vaplm_meta_box_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['vaplm_meta_box_nonce'] ) ), 'vaplm_meta_box_save_action' ) ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        if ( isset( $_POST['vaplm_dynamic_meta'] ) && is_array( $_POST['vaplm_dynamic_meta'] ) ) {
            $dynamic_meta = wp_unslash( $_POST['vaplm_dynamic_meta'] );
            foreach ( $dynamic_meta as $meta_key => $value ) {
                update_post_meta( $post_id, '_' . sanitize_key( $meta_key ), sanitize_textarea_field( $value ) );
            }
        }

        if ( 'vaplm_bom' === $post->post_type || 'vaplm_part' === $post->post_type ) {
            $cross_references = isset( $_POST['vaplm_linked_documents'] ) ? array_map( 'absint', wp_unslash( $_POST['vaplm_linked_documents'] ) ) : array();
            update_post_meta( $post_id, '_vaplm_linked_documents', $cross_references );
        }

        if ( isset( $_POST['vaplm_attachments'] ) && is_array( $_POST['vaplm_attachments'] ) ) {
            $attachments_pool = array_map( 'absint', wp_unslash( $_POST['vaplm_attachments'] ) );
            update_post_meta( $post_id, '_vaplm_vault_attachments', $attachments_pool );
        } else {
            update_post_meta( $post_id, '_vaplm_vault_attachments', array() );
        }

        if ( 'vaplm_bom' === $post->post_type ) {
            $raw_components_payload = isset( $_POST['vaplm_bom_components'] ) ? (array) wp_unslash( $_POST['vaplm_bom_components'] ) : array();
            
            if ( ! empty( $raw_components_payload ) ) {
                global $wpdb;
                $table_ebom = $wpdb->prefix . 'vaplm_ebom';

                $wpdb->delete( $table_ebom, array( 'parent_part_id' => $post_id ), array( '%d' ) );

                $fields_registry = get_option( 'vaplm_dynamic_field_definitions', array() );
                $relational_columns_keys = array_filter( $fields_registry, function( $field ) {
                    return isset( $field['object_type'] ) && $field['object_type'] === 'vaplm_bom' && isset( $field['object_subtype'] ) && $field['object_subtype'] === 'bom_relationship_column';
                });

                foreach ( $raw_components_payload as $index => $row_data ) {
                    $child_id = isset( $row_data['child_id'] ) ? absint( $row_data['child_id'] ) : 0;
                    $quantity = isset( $row_data['quantity'] ) ? floatval( $row_data['quantity'] ) : 1.0000;
                    $uom_slug = isset( $row_data['uom'] ) ? sanitize_key( $row_data['uom'] ) : 'ea';

                    if ( $child_id > 0 ) {
                        $row_custom_meta_payload = array();
                        if ( ! empty( $relational_columns_keys ) ) {
                            foreach ( $relational_columns_keys as $col_key => $config ) {
                                if ( isset( $row_data[$col_key] ) && '' !== $row_data[$col_key] ) {
                                    $row_custom_meta_payload[$col_key] = sanitize_text_field( $row_data[$col_key] );
                                } else {
                                    $row_custom_meta_payload[$col_key] = isset( $config['default'] ) ? sanitize_text_field( $config['default'] ) : '';
                                }
                            }
                        }

                        $json_string_payload = ! empty( $row_custom_meta_payload ) ? wp_json_encode( $row_custom_meta_payload ) : '';

                        $wpdb->insert(
                            $table_ebom,
                            array(
                                'parent_part_id' => $post_id,
                                'child_part_id'  => $child_id,
                                'quantity'       => $quantity,
                                'uom'            => $uom_slug,
                                'meta_data'      => $json_string_payload
                            ),
                            array( '%d', '%d', '%f', '%s', '%s' )
                        );
                    }
                }
            }
        }
    }
}