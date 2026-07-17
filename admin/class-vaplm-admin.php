<?php
/**
 * Master Administrative Workspace, Sub-Type Topology & Analytical Reporting Engine.
 *
 * @package    VA_PLM_Admin_Suite
 * @subpackage VA_PLM_Admin_Suite/admin
 */

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

class VAPLM_Admin {

    private $plugin_screen_hook = 'vaplm-admin-console';

    public function __construct() {
        $vaplm_types = array( 'vaplm_part', 'vaplm_document', 'vaplm_change_order', 'vaplm_bom' );
        foreach ( $vaplm_types as $type ) {
            add_filter( "manage_{$type}_posts_columns", array( $this, 'inject_compliance_column_headers' ) );
            add_action( "manage_{$type}_posts_custom_column", array( $this, 'render_compliance_column_content' ), 10, 2 );
        }

        add_action( 'admin_init', array( $this, 'process_admin_form_saves' ) );
        add_action( 'admin_menu', array( $this, 'restrict_standard_wordpress_menus' ), 999 );
        add_action( 'admin_post_vaplm_download_lov_template', array( $this, 'generate_and_stream_csv_template' ) );

        // AJAX Channels strictly protected
        add_action( 'wp_ajax_vaplm_execute_advanced_matrix_query', array( $this, 'ajax_execute_advanced_matrix_query' ) );
        add_action( 'wp_ajax_vaplm_save_engineering_report', array( $this, 'ajax_save_engineering_report' ) );
        add_action( 'wp_ajax_vaplm_purge_engineering_report', array( $this, 'ajax_purge_engineering_report' ) );
    }

    public function add_plugin_admin_menu() {
        add_menu_page( 
            __( 'VA PLM Workspace', 'va-plm-admin-suite' ), 
            __( 'VA PLM Workspace', 'va-plm-admin-suite' ), 
            'edit_posts', 
            $this->plugin_screen_hook, 
            array( $this, 'render_admin_console' ), 
            'dashicons-networking', 
            30 
        );

        $vaplm_objects = array(
            'vaplm_part'         => _x( 'Parts Directory', 'menu label', 'va-plm-admin-suite' ),
            'vaplm_document'     => _x( 'Documents Vault', 'menu label', 'va-plm-admin-suite' ),
            'vaplm_bom'          => _x( 'BOM Assemblies', 'menu label', 'va-plm-admin-suite' ),
            'vaplm_change_order' => _x( 'Change Orders', 'menu label', 'va-plm-admin-suite' )
        );

        foreach ( $vaplm_objects as $cpt => $label ) {
            add_submenu_page(
                $this->plugin_screen_hook,
                $label,
                $label,
                'edit_posts',
                'edit.php?post_type=' . $cpt
            );
        }
        
        add_submenu_page( 
            $this->plugin_screen_hook, 
            __( 'Configure Subtypes', 'va-plm-admin-suite' ), 
            __( 'Configure Subtypes', 'va-plm-admin-suite' ), 
            'manage_options', 
            'vaplm-configure-subtypes', 
            array( $this, 'render_subtypes_configuration_menu' ) 
        );

        add_submenu_page( 
            $this->plugin_screen_hook, 
            __( 'Configuration Control', 'va-plm-admin-suite' ), 
            __( '⚙ Configuration Control', 'va-plm-admin-suite' ), 
            'manage_options', 
            'vaplm-configuration-control', 
            array( $this, 'render_configuration_control' ) 
        );
    }

    public function restrict_standard_wordpress_menus() {
        remove_menu_page( 'index.php' );
        remove_menu_page( 'edit.php' );
        remove_menu_page( 'upload.php' );
        remove_menu_page( 'edit.php?post_type=page' );
        remove_menu_page( 'edit-comments.php' );
        remove_menu_page( 'themes.php' );
        remove_menu_page( 'plugins.php' );
        remove_menu_page( 'tools.php' );
    }

    public function enqueue_admin_assets( $hook ) {
        wp_enqueue_media();
        wp_enqueue_style( 'vaplm-admin-css', VAPLM_URL . 'admin/css/vaplm-admin.css', array(), VAPLM_VERSION );
        wp_enqueue_script( 'vaplm-admin-js', VAPLM_URL . 'admin/js/vaplm-admin.js', array( 'jquery' ), VAPLM_VERSION, true );

        global $wpdb;
        $lov_dictionaries = $wpdb->get_col( "SELECT DISTINCT list_slug FROM {$wpdb->prefix}vaplm_lov_entries ORDER BY list_slug ASC" );
        
        wp_localize_script( 'vaplm-admin-js', 'vaplmAdminSuiteL10n', array(
            'editModeActive'   => __( 'Edit Mode Active', 'va-plm-admin-suite' ),
            'editModeWarning'  => __( 'You are modifying a live engineering record context. Save updates to commit changes.', 'va-plm-admin-suite' ),
            'uploadTitle'      => __( 'Vault Management: Select Engineering Documentation', 'va-plm-admin-suite' ),
            'uploadBtnText'    => __( 'Bind Binaries to Object Profile', 'va-plm-admin-suite' ),
            'unknownFormat'    => __( 'UNKNOWN', 'va-plm-admin-suite' ),
            'noAttachments'    => __( 'No associated binary attachments in vault.', 'va-plm-admin-suite' ),
            'noRelationships'  => __( 'No cross-object engineering alignment traces mapped.', 'va-plm-admin-suite' ),
            'queryExecutionErr'=> __( 'Relational Query compilation pipeline failure.', 'va-plm-admin-suite' ),
            'reportSaved'      => __( 'Analytical parameter mapping report persisted inside options registry.', 'va-plm-admin-suite' ),
            'lovDictionaries'  => $lov_dictionaries ?: array(),
            'ajaxUrl'          => admin_url( 'admin-ajax.php' )
        ) );
    }

    public function render_admin_console() {
        $saved_reports = get_option( 'vaplm_saved_engineering_reports', array() );
        $field_definitions = get_option( 'vaplm_dynamic_field_definitions', array() );
        include_once VAPLM_PATH . 'admin/views/workspace-home.php';
    }

    public function render_subtypes_configuration_menu() {
        if ( ! current_user_can( 'manage_options' ) ) { 
            wp_die( esc_html__( 'Access denied. Inadequate clearance profile privileges.', 'va-plm-admin-suite' ) ); 
        }
        ?>
        <div class="wrap vaplm-admin-wrap">
            <h1>🏷️ <?php esc_html_e( 'Classification Sub-Types Topology Engine', 'va-plm-admin-suite' ); ?></h1>
            <p class="description"><?php esc_html_e( 'Select an object category context below to register or edit hierarchical sub-type taxonomy definitions.', 'va-plm-admin-suite' ); ?></p>
            
            <div class="vaplm-card" style="margin-top: 20px;">
                <table class="wp-list-table widefat fixed striped vaplm-data-table" style="max-width: 600px;">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Target Object Post Class', 'va-plm-admin-suite' ); ?></th>
                            <th style="width: 35%; text-align: center;"><?php esc_html_e( 'Action Link Node', 'va-plm-admin-suite' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>📐 <?php esc_html_e( 'Configure Part Subtypes', 'va-plm-admin-suite' ); ?></strong></td>
                            <td style="text-align: center;"><a class="button button-secondary" href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=vaplm_part_type&post_type=vaplm_part' ) ); ?>"><?php esc_html_e( 'Manage Taxonomy', 'va-plm-admin-suite' ); ?></a></td>
                        </tr>
                        <tr>
                            <td><strong>📄 <?php esc_html_e( 'Configure Document Subtypes', 'va-plm-admin-suite' ); ?></strong></td>
                            <td style="text-align: center;"><a class="button button-secondary" href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=vaplm_doc_type&post_type=vaplm_document' ) ); ?>"><?php esc_html_e( 'Manage Taxonomy', 'va-plm-admin-suite' ); ?></a></td>
                        </tr>
                        <tr>
                            <td><strong>📊 <?php esc_html_e( 'Configure BOM Subtypes', 'va-plm-admin-suite' ); ?></strong></td>
                            <td style="text-align: center;"><a class="button button-secondary" href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=vaplm_bom_type&post_type=vaplm_bom' ) ); ?>"><?php esc_html_e( 'Manage Taxonomy', 'va-plm-admin-suite' ); ?></a></td>
                        </tr>
                        <tr>
                            <td><strong>🔄 <?php esc_html_e( 'Configure Change Subtypes', 'va-plm-admin-suite' ); ?></strong></td>
                            <td style="text-align: center;"><a class="button button-secondary" href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=vaplm_change_type&post_type=vaplm_change_order' ) ); ?>"><?php esc_html_e( 'Manage Taxonomy', 'va-plm-admin-suite' ); ?></a></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    public function render_configuration_control() {
        if ( ! current_user_can( 'manage_options' ) ) { 
            wp_die( esc_html__( 'Access denied. Inadequate administrative clearance profile.', 'va-plm-admin-suite' ) ); 
        }
        include_once VAPLM_PATH . 'admin/views/admin-display.php';
    }

    public function process_admin_form_saves() {
        if ( ! isset( $_POST['vaplm_admin_nonce_field'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['vaplm_admin_nonce_field'] ) ), 'vaplm_admin_save_action' ) ) { 
            return; 
        }
        
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        global $wpdb;
        $action_type = sanitize_key( wp_unslash( $_POST['vaplm_action_type'] ?? '' ) );
        $table_lov   = $wpdb->prefix . 'vaplm_lov_entries';

        if ( $action_type === 'manage_roles' ) {
            if ( ! empty( $_POST['vaplm_new_role_slug'] ) ) {
                add_role( sanitize_key( wp_unslash( $_POST['vaplm_new_role_slug'] ) ), sanitize_text_field( wp_unslash( $_POST['vaplm_new_role_label'] ) ), array( 'read' => true, 'edit_posts' => true ) );
            }
        }

        if ( $action_type === 'manage_statuses' ) {
            $target_obj = sanitize_key( wp_unslash( $_POST['vaplm_status_object_scope'] ?? '' ) );
            if ( ! empty( $target_obj ) && ! empty( $_POST['vaplm_new_status'] ) ) {
                $current_pool = get_option( 'vaplm_custom_lifecycle_statuses', array() );
                $new_milestone = sanitize_text_field( wp_unslash( $_POST['vaplm_new_status'] ) );
                if ( ! isset( $current_pool[$target_obj] ) ) {
                    $current_pool[$target_obj] = array( 'Draft', 'In Review', 'Released', 'Obsolete' );
                }
                if ( ! in_array( $new_milestone, $current_pool[$target_obj], true ) ) {
                    $current_pool[$target_obj][] = $new_milestone;
                    update_option( 'vaplm_custom_lifecycle_statuses', $current_pool );
                }
            }
        }

        if ( $action_type === 'manage_fields' ) {
            $existing = get_option( 'vaplm_dynamic_field_definitions', array() );
            $scope = sanitize_text_field( wp_unslash( $_POST['vaplm_field_scope_tab'] ?? '' ) );
            $sub_scope = isset( $_POST['vaplm_field_sub_scope'] ) ? sanitize_text_field( wp_unslash( $_POST['vaplm_field_sub_scope'] ) ) : '';

            if ( ! empty( $scope ) ) {
                foreach ( $existing as $key => $config ) {
                    if ( isset( $config['object_type'] ) && $config['object_type'] === $scope ) {
                        if ( ! empty( $sub_scope ) ) {
                            $current_sub = $config['object_subtype'] ?? 'general';
                            if ( $current_sub === $sub_scope ) {
                                unset( $existing[$key] );
                            }
                        } else {
                            unset( $existing[$key] );
                        }
                    }
                }
            }

            $new_batch = array();
            if ( isset( $_POST['vaplm_dynamic_fields'] ) && is_array( $_POST['vaplm_dynamic_fields'] ) ) {
                $raw_fields = wp_unslash( $_POST['vaplm_dynamic_fields'] );
                foreach ( $raw_fields as $key => $vals ) {
                    $k = sanitize_key( str_replace( ' ', '', $vals['key'] ?? $key ) );
                    if ( ! empty( $k ) ) {
                        $new_batch[$k] = array(
                            'label'          => sanitize_text_field( $vals['label'] ?? '' ),
                            'object_type'    => sanitize_text_field( $vals['object_type'] ?? '' ),
                            'object_subtype' => sanitize_key( $vals['object_subtype'] ?? '' ),
                            'field_type'     => sanitize_text_field( $vals['field_type'] ?? '' ),
                            'lov_target'     => sanitize_key( $vals['lov_target'] ?? '' ),
                            'required'       => isset( $vals['required'] ) ? 1 : 0,
                            'default'        => sanitize_text_field( $vals['default'] ?? '' )
                        );
                    }
                }
            }
            update_option( 'vaplm_dynamic_field_definitions', array_merge( $existing, $new_batch ) );
        }

        if ( $action_type === 'manage_lov' ) {
            if ( ! empty( $_POST['vaplm_new_lov_slug'] ) && ! empty( $_POST['vaplm_new_lov_val'] ) ) {
                $wpdb->insert( $table_lov, array( 
                    'list_slug'    => sanitize_key( wp_unslash( $_POST['vaplm_new_lov_slug'] ) ), 
                    'option_value' => sanitize_key( wp_unslash( $_POST['vaplm_new_lov_val'] ) ), 
                    'option_label' => sanitize_text_field( wp_unslash( $_POST['vaplm_new_lov_lbl'] ) ) 
                ) );
            }
        }
    }

    public function ajax_execute_advanced_matrix_query() {
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'vaplm_workspace_search_action' ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid security token.', 'va-plm-admin-suite' ) ) );
        }

        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( array( 'message' => __( 'Clearance trace mismatch.', 'va-plm-admin-suite' ) ) );
        }

        global $wpdb;
        $target_cpt = sanitize_key( wp_unslash( $_POST['object_type'] ?? 'vaplm_part' ) );
        $rules_chain = isset( $_POST['rules'] ) ? (array) wp_unslash( $_POST['rules'] ) : array();

        $sql_query = "SELECT p.ID, p.post_title, p.post_type FROM {$wpdb->posts} p WHERE p.post_type = %s AND p.post_status = 'publish'";
        $params_buffer = array( $target_cpt );

        $meta_rules_count = 0;
        $meta_sub_conditions = array();

        foreach ( $rules_chain as $rule ) {
            if ( ! is_array( $rule ) ) continue;
            
            $field    = sanitize_key( $rule['field'] ?? '' );
            $operator = sanitize_key( $rule['operator'] ?? 'equals' );
            $value    = sanitize_text_field( $rule['value'] ?? '' );

            if ( empty( $field ) ) continue;

            if ( $field === 'post_title' ) {
                if ( $operator === 'equals' ) {
                    $sql_query .= " AND p.post_title = %s"; $params_buffer[] = $value;
                } elseif ( $operator === 'not_equals' ) {
                    $sql_query .= " AND p.post_title != %s"; $params_buffer[] = $value;
                } elseif ( $operator === 'contains' ) {
                    $sql_query .= " AND p.post_title LIKE %s"; $params_buffer[] = '%' . $wpdb->esc_like( $value ) . '%';
                }
            } else {
                $meta_rules_count++;
                $meta_key_target = ( strpos( $field, 'vaplm_' ) === 0 ) ? '_' . $field : '_' . $field;

                if ( $operator === 'is_blank' ) {
                    $meta_sub_conditions[] = $wpdb->prepare( "(meta_key = %s AND (meta_value IS NULL OR meta_value = ''))", $meta_key_target );
                } else {
                    $value_placeholder = '%s';
                    if ( $operator === 'contains' ) {
                        $value = '%' . $wpdb->esc_like( $value ) . '%';
                        $op_sign = 'LIKE';
                    } else {
                        $op_sign = ( $operator === 'not_equals' ) ? '!=' : '=';
                    }
                    $meta_sub_conditions[] = $wpdb->prepare( "(meta_key = %s AND meta_value {$op_sign} {$value_placeholder})", $meta_key_target, $value );
                }
            }
        }

        if ( $meta_rules_count > 0 ) {
            $inner_meta_clause = implode( ' OR ', $meta_sub_conditions );
            $sql_query .= " AND p.ID IN (
                SELECT post_id FROM {$wpdb->postmeta} 
                WHERE {$inner_meta_clause} 
                GROUP BY post_id 
                HAVING COUNT(DISTINCT meta_key) >= %d
            )";
            $params_buffer[] = $meta_rules_count;
        }

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Dynamic query string explicitly sanitized and protected via $wpdb->prepare variables buffer.
        $results = $wpdb->get_results( $wpdb->prepare( $sql_query, $params_buffer ) );
        
        $formatted_payload = array();

        if ( ! empty( $results ) ) {
            foreach ( $results as $row ) {
                $formatted_payload[] = array(
                    'id'            => $row->ID,
                    'object_number' => get_post_meta( $row->ID, '_vaplm_object_number', true ) ?: '--',
                    'post_title'    => $row->post_title,
                    'status'        => get_post_meta( $row->ID, '_vaplm_lifecycle_status', true ) ?: 'Draft',
                    'created_date'  => get_post_meta( $row->ID, '_vaplm_created_datetime', true ) ?: '--',
                    'modified_date' => get_post_meta( $row->ID, '_vaplm_modified_datetime', true ) ?: '--',
                );
            }
        }

        wp_send_json_success( $formatted_payload );
    }

    public function ajax_save_engineering_report() {
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'vaplm_workspace_search_action' ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid security token.', 'va-plm-admin-suite' ) ) );
        }
        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( array( 'message' => __( 'Clearance privilege violation.', 'va-plm-admin-suite' ) ) );
        }

        $report_name = sanitize_text_field( wp_unslash( $_POST['report_name'] ?? '' ) );
        $target_obj  = sanitize_key( wp_unslash( $_POST['object_type'] ?? 'vaplm_part' ) );
        $raw_rules   = isset( $_POST['rules'] ) ? (array) wp_unslash( $_POST['rules'] ) : array();

        if ( empty( $report_name ) ) {
            wp_send_json_error( array( 'message' => __( 'Report tracking label cannot be blank.', 'va-plm-admin-suite' ) ) );
        }

        // Deep sanitize the nested multi-dimensional parameters array
        $sanitized_rules = array();
        foreach ( $raw_rules as $rule ) {
            if ( is_array( $rule ) ) {
                $sanitized_rules[] = array(
                    'field'    => isset( $rule['field'] ) ? sanitize_key( $rule['field'] ) : '',
                    'operator' => isset( $rule['operator'] ) ? sanitize_key( $rule['operator'] ) : 'equals',
                    'value'    => isset( $rule['value'] ) ? sanitize_text_field( $rule['value'] ) : ''
                );
            }
        }

        $reports_registry = get_option( 'vaplm_saved_engineering_reports', array() );
        $report_id = sanitize_key( strtolower( str_replace( ' ', '_', $report_name ) ) );

        $reports_registry[$report_id] = array(
            'name'        => $report_name,
            'object_type' => $target_obj,
            'rules'       => $sanitized_rules 
        );

        update_option( 'vaplm_saved_engineering_reports', $reports_registry );
        wp_send_json_success( array( 'report_id' => $report_id, 'name' => $report_name ) );
    }

    public function ajax_purge_engineering_report() {
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'vaplm_workspace_search_action' ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid security token.', 'va-plm-admin-suite' ) ) );
        }
        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( array( 'message' => __( 'Insufficient access rights.', 'va-plm-admin-suite' ) ) );
        }

        $target_report_id = sanitize_key( wp_unslash( $_POST['report_id'] ?? '' ) );
        $reports_registry = get_option( 'vaplm_saved_engineering_reports', array() );

        if ( isset( $reports_registry[$target_report_id] ) ) {
            unset( $reports_registry[$target_report_id] );
            update_option( 'vaplm_saved_engineering_reports', $reports_registry );
            wp_send_json_success();
        }

        wp_send_json_error( array( 'message' => __( 'Report configuration node trace unresolvable.', 'va-plm-admin-suite' ) ) );
    }

    public function inject_compliance_column_headers( $columns ) {
        $extended_headers = array(
            'vaplm_object_number' => __( 'Object Number', 'va-plm-admin-suite' ),
            'vaplm_created_date'  => __( 'Create Date (UTC)', 'va-plm-admin-suite' ),
            'vaplm_modified_date' => __( 'Modified Date (UTC)', 'va-plm-admin-suite' ),
            'vaplm_creator_user'  => __( 'Creator User', 'va-plm-admin-suite' )
        );

        $output_columns = array();
        foreach ( $columns as $column_id => $column_label ) {
            $output_columns[$column_id] = $column_label;
            if ( 'title' === $column_id ) {
                $output_columns = array_merge( $output_columns, $extended_headers );
            }
        }
        unset( $output_columns['date'] );
        return $output_columns;
    }

    public function render_compliance_column_content( $column_id, $post_id ) {
        switch ( $column_id ) {
            case 'vaplm_object_number':
                $object_number = get_post_meta( $post_id, '_vaplm_object_number', true );
                echo $object_number ? '<code>' . esc_html( $object_number ) . '</code>' : '--';
                break;
            case 'vaplm_created_date':
                echo esc_html( get_post_meta( $post_id, '_vaplm_created_datetime', true ) ?: '--' );
                break;
            case 'vaplm_modified_date':
                echo esc_html( get_post_meta( $post_id, '_vaplm_modified_datetime', true ) ?: '--' );
                break;
            case 'vaplm_creator_user':
                //$creator_id = get_post_meta( $post_id, '_vaplm_creator_user_id', true );
                //echo $creator_id ? esc_html( get_userdata( $creator_id )->user_login ) : '--';
                $creator_id = get_post_meta( $post_id, '_vaplm_creator_user_id', true );
                $creator_user = $creator_id ? get_userdata( $creator_id ) : false;
                echo $creator_user ? esc_html( $creator_user->user_login ) : 'User Not Found';
				break;
        }
    }

    public function generate_and_stream_csv_template() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Insufficient clearance matrix privileges.', 'va-plm-admin-suite' ) );
        }

        if ( ob_get_level() ) { 
            ob_end_clean(); 
        }

        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=vaplm_lov_template.csv' );
        header( 'Pragma: no-cache' );
        header( 'Expires: 0' );

        // Output CSV data directly via echo to bypass fopen/fclose static filesystem warnings
        echo "list_slug,option_value,option_label\r\n";
        echo "material_grades,al_7075_t6,Aluminum 7075-T6\r\n";
        
        exit;
    }

}