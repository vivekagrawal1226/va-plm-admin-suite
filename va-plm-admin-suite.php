<?php
/**
 * Plugin Name: VA PLM Admin Suite
 * Plugin URI:  https://agrawalvivek.com/apps
 * Description: An enterprise-grade Product Lifecycle Management workspace built natively inside the /wp-admin dashboard context.
 * Version:     1.5.4
 * Author:      Vivek Agrawal
 * Author URI:  https://agrawalvivek.com
 * Text Domain: va-plm-admin-suite
 * License:     GPLv2 or later
 * * @package VA_PLM_Admin_Suite
 */

if ( ! defined( 'ABSPATH' ) ) {
    die; 
}

// Define Plugin Constants
define( 'VAPLM_VERSION', '1.5.3' );
define( 'VAPLM_PATH', plugin_dir_path( __FILE__ ) );
define( 'VAPLM_URL', plugin_dir_url( __FILE__ ) );

// Include Core Dependencies
if ( file_exists( VAPLM_PATH . 'admin/class-vaplm-admin.php' ) ) {
    require_once VAPLM_PATH . 'admin/class-vaplm-admin.php';
}
if ( file_exists( VAPLM_PATH . 'admin/class-vaplm-meta-box-handler.php' ) ) {
    require_once VAPLM_PATH . 'admin/class-vaplm-meta-box-handler.php';
}

/**
 * Initialize Plugin Core Classes
 */
function vaplm_initialize_plugin() {
    if ( class_exists( 'VAPLM_Admin' ) ) {
        $vaplm_admin = new VAPLM_Admin();
        add_action( 'admin_menu', array( $vaplm_admin, 'add_plugin_admin_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $vaplm_admin, 'enqueue_admin_assets' ) );
    }

    // FIX: Boot up the Meta Box Handler so it injects the fields into the Classic Editor!
    if ( class_exists( 'VAPLM_Meta_Box_Handler' ) ) {
        new VAPLM_Meta_Box_Handler();
    }
}
add_action( 'plugins_loaded', 'vaplm_initialize_plugin' );

/**
 * Register Custom Post Types & Taxonomies
 */
function vaplm_register_engineering_objects() {
    $objects = array(
        'vaplm_part'         => array( 'singular' => __( 'Part', 'va-plm-admin-suite' ), 'plural' => __( 'Parts Directory', 'va-plm-admin-suite' ), 'icon' => 'dashicons-admin-generic' ),
        'vaplm_document'     => array( 'singular' => __( 'Document', 'va-plm-admin-suite' ), 'plural' => __( 'Documents Vault', 'va-plm-admin-suite' ), 'icon' => 'dashicons-media-document' ),
        'vaplm_bom'          => array( 'singular' => __( 'BOM Assembly', 'va-plm-admin-suite' ), 'plural' => __( 'BOM Assemblies', 'va-plm-admin-suite' ), 'icon' => 'dashicons-networking' ),
        'vaplm_change_order' => array( 'singular' => __( 'Change Order', 'va-plm-admin-suite' ), 'plural' => __( 'Change Orders', 'va-plm-admin-suite' ), 'icon' => 'dashicons-update' )
    );

    foreach ( $objects as $post_type => $labels ) {
        register_post_type( $post_type, array(
            'labels' => array(
                'name'          => $labels['plural'],
                'singular_name' => $labels['singular'],
                'menu_name'     => $labels['plural'],
                'add_new'       => __( 'Add New', 'va-plm-admin-suite' ),
                'add_new_item'  => sprintf( __( 'Add New %s', 'va-plm-admin-suite' ), $labels['singular'] ),
                'edit_item'     => sprintf( __( 'Edit %s', 'va-plm-admin-suite' ), $labels['singular'] ),
            ),
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => false, // Handled via our custom workspace dashboard menu
            'supports'            => array( 'title' ),
            'menu_icon'           => $labels['icon'],
            'has_archive'         => false,
            'exclude_from_search' => true,
            'publicly_queryable'  => false,
            'rewrite'             => false,
        ) );
    }

    $taxonomies = array(
        'vaplm_part_type'   => array( 'singular' => __( 'Part Subtype', 'va-plm-admin-suite' ), 'plural' => __( 'Part Subtypes', 'va-plm-admin-suite' ), 'post_type' => 'vaplm_part' ),
        'vaplm_doc_type'    => array( 'singular' => __( 'Document Subtype', 'va-plm-admin-suite' ), 'plural' => __( 'Document Subtypes', 'va-plm-admin-suite' ), 'post_type' => 'vaplm_document' ),
        'vaplm_bom_type'    => array( 'singular' => __( 'BOM Subtype', 'va-plm-admin-suite' ), 'plural' => __( 'BOM Subtypes', 'va-plm-admin-suite' ), 'post_type' => 'vaplm_bom' ),
        'vaplm_change_type' => array( 'singular' => __( 'Change Subtype', 'va-plm-admin-suite' ), 'plural' => __( 'Change Subtypes', 'va-plm-admin-suite' ), 'post_type' => 'vaplm_change_order' )
    );

    foreach ( $taxonomies as $tax_slug => $tax_args ) {
        register_taxonomy( $tax_slug, $tax_args['post_type'], array(
            'labels' => array(
                'name'          => $tax_args['plural'],
                'singular_name' => $tax_args['singular'],
            ),
            'hierarchical'      => true,
            'public'            => false,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => false,
            'show_tagcloud'     => false,
        ) );
    }
}
add_action( 'init', 'vaplm_register_engineering_objects' );

/**
 * Enforce Immutable Database Audit Trail & Core Object Properties
 * Security Fix: Intercepts save_post to enforce capability & CSRF nonce protection.
 *
 * @param int $post_id The ID of the post being saved.
 */
function vaplm_enforce_immutable_database_audit_trail( $post_id ) {
    // 1. Bail out immediately on autosave routines
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    $post_type = get_post_type( $post_id );
    $vaplm_types = array( 'vaplm_part', 'vaplm_document', 'vaplm_bom', 'vaplm_change_order' );
    
    if ( ! in_array( $post_type, $vaplm_types, true ) ) {
        return;
    }

    // 2. Strict Capability Check: Does this user have permission to edit this specific post?
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    // 3. Strict Nonce Verification against CSRF
    $nonce_valid = false;
    
    // Check custom meta box submission nonce
    if ( isset( $_POST['vaplm_meta_box_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['vaplm_meta_box_nonce'] ) ), 'vaplm_save_meta_action' ) ) {
        $nonce_valid = true;
    } 
    // Check custom URL-appended nonce (for taxonomy writes on quick-creation redirects)
    elseif ( isset( $_GET['_vaplm_url_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_vaplm_url_nonce'] ) ), 'vaplm_url_action' ) ) {
        $nonce_valid = true;
    }
    // Check standard WP post edit screen nonce fallback
    elseif ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'update-post_' . $post_id ) ) {
        $nonce_valid = true;
    }

    // 4. Bail if the security tokens are missing or invalid
    if ( ! $nonce_valid ) {
        return;
    }

    // --- Core Audit & Taxonomy Logic Below ---

    // 5. Handle the Taxonomy Write Operation Safely
    if ( isset( $_GET['vaplm_subtype'] ) ) {
        $subtype = sanitize_key( wp_unslash( $_GET['vaplm_subtype'] ) );
        
        $tax_map = array(
            'vaplm_part'         => 'vaplm_part_type',
            'vaplm_document'     => 'vaplm_doc_type',
            'vaplm_bom'          => 'vaplm_bom_type',
            'vaplm_change_order' => 'vaplm_change_type'
        );

        if ( isset( $tax_map[$post_type] ) && ! empty( $subtype ) ) {
            wp_set_object_terms( $post_id, $subtype, $tax_map[$post_type] );
        }
    }

    // 6. Assign System Object Number
    if ( ! get_post_meta( $post_id, '_vaplm_object_number', true ) ) {
        $schemas = get_option( 'vaplm_object_numbering_schemas', array() );
        
        $default_prefixes = array(
            'vaplm_part'         => 'PRT-{SEQ:6}',
            'vaplm_document'     => 'DOC-{SEQ:6}',
            'vaplm_bom'          => 'BOM-{SEQ:6}',
            'vaplm_change_order' => 'ECO-{SEQ:6}'
        );
        
        $mask = isset( $schemas[$post_type]['mask'] ) ? $schemas[$post_type]['mask'] : $default_prefixes[$post_type];
        $next_idx = (int) get_option( 'vaplm_next_idx_' . $post_type, 1 );
        
        // Process dynamic timestamp syntax replacements
        $number = str_replace( 
            array( '{YYYY}', '{YY}', '{MM}', '{DD}' ), 
            array( gmdate( 'Y' ), gmdate( 'y' ), gmdate( 'm' ), gmdate( 'd' ) ), 
            $mask 
        );
        
        // Process sequential padding block
        if ( preg_match( '/\{SEQ:(\d+)\}/', $number, $matches ) ) {
            $pad = (int) $matches[1];
            $seq_str = str_pad( $next_idx, $pad, '0', STR_PAD_LEFT );
            $number = str_replace( $matches[0], $seq_str, $number );
        }
        
        update_post_meta( $post_id, '_vaplm_object_number', sanitize_text_field( $number ) );
        update_option( 'vaplm_next_idx_' . $post_type, $next_idx + 1 );
    }

    // 7. Assign Original Creation Immutables
    if ( ! get_post_meta( $post_id, '_vaplm_created_datetime', true ) ) {
        update_post_meta( $post_id, '_vaplm_created_datetime', current_time( 'mysql', 1 ) );
        update_post_meta( $post_id, '_vaplm_creator_user_id', get_current_user_id() );
    }

    // 8. Assign Modification Immutables
    update_post_meta( $post_id, '_vaplm_modified_datetime', current_time( 'mysql', 1 ) );
    update_post_meta( $post_id, '_vaplm_modifier_user_id', get_current_user_id() );
    
    // 9. Assign Baseline Lifecycle Gate
    if ( ! get_post_meta( $post_id, '_vaplm_lifecycle_status', true ) ) {
        update_post_meta( $post_id, '_vaplm_lifecycle_status', 'Draft' );
    }
}
add_action( 'save_post', 'vaplm_enforce_immutable_database_audit_trail', 10, 1 );

/**
 * Plugin Activation Hook
 * Instantiates necessary bespoke database tables required for advanced EBOM structures.
 */
function vaplm_activate_plugin() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    // Matrix: BOM Hierarchical Line Items
    $table_ebom = $wpdb->prefix . 'vaplm_ebom';
    $sql_ebom = "CREATE TABLE $table_ebom (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        parent_id bigint(20) NOT NULL,
        child_id bigint(20) NOT NULL,
        quantity decimal(10,4) NOT NULL DEFAULT '1.0000',
        uom varchar(30) NOT NULL DEFAULT 'ea',
        custom_data longtext DEFAULT NULL,
        PRIMARY KEY  (id),
        KEY parent_id (parent_id),
        KEY child_id (child_id)
    ) $charset_collate;";
    dbDelta( $sql_ebom );

    // Matrix: Polymorphic Traceability Cross-References
    $table_relationships = $wpdb->prefix . 'vaplm_relationships';
    $sql_relationships = "CREATE TABLE $table_relationships (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        from_id bigint(20) NOT NULL,
        to_id bigint(20) NOT NULL,
        rel_type varchar(50) NOT NULL,
        PRIMARY KEY  (id),
        KEY from_id (from_id),
        KEY to_id (to_id),
        KEY rel_type (rel_type)
    ) $charset_collate;";
    dbDelta( $sql_relationships );

    // Matrix: High-Performance Lookup Dictionaries (LOVs)
    $table_lov = $wpdb->prefix . 'vaplm_lov_entries';
    $sql_lov = "CREATE TABLE $table_lov (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        list_slug varchar(100) NOT NULL,
        option_value varchar(100) NOT NULL,
        option_label varchar(255) NOT NULL,
        PRIMARY KEY  (id),
        KEY list_slug (list_slug)
    ) $charset_collate;";
    dbDelta( $sql_lov );

    // Force flush of URL rewrite rules context
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'vaplm_activate_plugin' );