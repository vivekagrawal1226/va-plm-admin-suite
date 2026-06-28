<?php
/**
 * Plugin Name: VA PLM Admin Suite
 * Plugin URI:  https://agrawalvivek.com/apps
 * Description: An enterprise-grade Product Lifecycle Management workspace built natively inside the /wp-admin dashboard context.
 * Version:     1.5.2
 * Author:      Vivek Agrawal
 * Author URI:  https://agrawalvivek.com/about-me/
 * Text Domain: va-plm-admin-suite
 * License:     GPLv2 or later
 */

if ( ! defined( 'ABSPATH' ) ) {
    die; // Protect against raw path execution security drops
}

define( 'VAPLM_VERSION', '1.5.1' );
define( 'VAPLM_PATH', plugin_dir_path( __FILE__ ) );
define( 'VAPLM_URL', plugin_dir_url( __FILE__ ) );

// -------------------------------------------------------------------------
// 1. LIFECYCLE INSTALLATION & RELATIONAL SCHEMA SETUP
// -------------------------------------------------------------------------

/**
 * Initializes optimized custom MySQL tables and seeds core capabilities upon plugin activation.
 */
function vaplm_activate_suite() {
    global $wpdb;
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    $charset_collate = $wpdb->get_charset_collate();

    // Table A: Standalone N-Tier Bill of Materials Relationship Table
    $table_ebom = $wpdb->prefix . 'vaplm_ebom';
    $sql_ebom = "CREATE TABLE $table_ebom (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        parent_part_id bigint(20) NOT NULL,
        child_part_id bigint(20) NOT NULL,
        quantity decimal(10,4) NOT NULL DEFAULT '1.0000',
        uom varchar(50) NOT NULL DEFAULT 'EA',
        eco_id bigint(20) DEFAULT NULL,
        eco_link_id bigint(20) DEFAULT NULL,
        meta_data longtext DEFAULT NULL,
        PRIMARY KEY  (id),
        KEY parent_part_id (parent_part_id),
        KEY child_part_id (child_part_id),
        KEY eco_id (eco_id)
    ) $charset_collate;";

    // Table B: Multi-Object Cross-Alignment Junction Registry
    $table_relationships = $wpdb->prefix . 'vaplm_relationships';
    $sql_relationships = "CREATE TABLE $table_relationships (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        from_id bigint(20) NOT NULL,
        to_id bigint(20) NOT NULL,
        rel_type varchar(50) NOT NULL,
        PRIMARY KEY  (id),
        KEY from_id (from_id),
        KEY rel_type (rel_type)
    ) $charset_collate;";

    // Table C: Dynamic Parameterized Data Dictionary Lookup Registry (LOVs)
    $table_lov = $wpdb->prefix . 'vaplm_lov_entries';
    $sql_lov = "CREATE TABLE $table_lov (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        list_slug varchar(100) NOT NULL,
        option_value varchar(255) NOT NULL,
        option_label varchar(255) NOT NULL,
        PRIMARY KEY  (id),
        KEY list_slug (list_slug)
    ) $charset_collate;";

    dbDelta( $sql_ebom );
    dbDelta( $sql_relationships );
    dbDelta( $sql_lov );

    // Seed Core Engineering Roles and access capabilities
    add_role( 'vaplm_manager', __( 'PLM Manager', 'va-plm-admin-suite' ), array(
        'read'         => true,
        'edit_posts'   => true,
        'upload_files' => true
    ) );

    add_role( 'vaplm_engineer', __( 'PLM Engineer', 'va-plm-admin-suite' ), array(
        'read'         => true,
        'edit_posts'   => true,
        'upload_files' => true
    ) );

    add_role( 'vaplm_guest', __( 'PLM Guest', 'va-plm-admin-suite' ), array(
        'read'         => true,
        'edit_posts'   => false,
        'upload_files' => false
    ) );

    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'vaplm_activate_suite' );

/**
 * Handles housekeeping on deactivation. Data-preservation guardrail protects engineering tables.
 */
function vaplm_deactivate_suite() {
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'vaplm_deactivate_suite' );

// -------------------------------------------------------------------------
// 2. CORE ENGINE SUBSYSTEM INITIALIZATION LOOPS
// -------------------------------------------------------------------------

/**
 * Bootstraps the administrative components, assets, views controllers, and form templates.
 */
function vaplm_initialize_core_subsystems() {
    // 1. Load and instantiate Core Post Types & Taxonomies
    require_once VAPLM_PATH . 'includes/class-vaplm-cpt.php';
    $vaplm_cpt = new VAPLM_Cpt();
    add_action( 'init', array( $vaplm_cpt, 'register_core_post_types_and_taxonomies' ), 10 );

    // 2. Load and instantiate Admin Controllers
    if ( is_admin() ) {
        require_once VAPLM_PATH . 'admin/class-vaplm-admin.php';
        require_once VAPLM_PATH . 'admin/class-vaplm-meta-box-handler.php';

        $vaplm_admin_controller = new VAPLM_Admin();
        $vaplm_meta_box_handler = new VAPLM_Meta_Box_Handler();

        add_action( 'admin_menu', array( $vaplm_admin_controller, 'add_plugin_admin_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $vaplm_admin_controller, 'enqueue_admin_assets' ) );
        add_action( 'add_meta_boxes', array( $vaplm_meta_box_handler, 'register_dynamic_meta_panels' ) );
        add_action( 'save_post', array( $vaplm_meta_box_handler, 'save_dynamic_meta_panels' ), 11, 2 );
    }
}
add_action( 'plugins_loaded', 'vaplm_initialize_core_subsystems' );

// -------------------------------------------------------------------------
// 3. EDITOR CONTROL & PROGRAMMATIC INTERCEPT HANDLERS
// -------------------------------------------------------------------------

/**
 * Force-deactivates the Gutenberg Block Editor to maintain absolute control over attributes forms layouts.
 */
function vaplm_force_disable_gutenberg_editor( $use_block_editor, $post_type ) {
    $vaplm_types = array( 'vaplm_part', 'vaplm_document', 'vaplm_change_order', 'vaplm_bom' );
    if ( in_array( $post_type, $vaplm_types, true ) ) {
        return false;
    }
    return $use_block_editor;
}
add_filter( 'use_block_editor_for_post_type', 'vaplm_force_disable_gutenberg_editor', 10, 2 );

/**
 * Automates the incremental numbering sequence keys safely.
 */
function vaplm_generate_atomic_object_number( $post_type ) {
    $prefix_map = array(
        'vaplm_part'         => 'PRT-',
        'vaplm_document'     => 'DOC-',
        'vaplm_change_order' => 'ECO-',
        'vaplm_bom'          => 'BOM-'
    );

    if ( ! isset( $prefix_map[ $post_type ] ) ) {
        return '';
    }

    $prefix = $prefix_map[ $post_type ];
    $option_key = 'vaplm_next_idx_' . $post_type;

    $next_index = (int) get_option( $option_key, 1 );
    update_option( $option_key, $next_index + 1 );

    return $prefix . str_pad( $next_index, 8, '0', STR_PAD_LEFT );
}

/**
 * Core Data Pipeline Interceptor: Generates unalterable sequence IDs and records audit timestamps safely.
 */
function vaplm_enforce_immutable_database_audit_trail( $post_id, $post, $update ) {
    $vaplm_types = array( 'vaplm_part', 'vaplm_document', 'vaplm_change_order', 'vaplm_bom' );
    if ( ! in_array( $post->post_type, $vaplm_types, true ) ) {
        return;
    }

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    $current_time = current_time( 'mysql' );
    $current_user = get_current_user_id();

    // Gate 1: Initial System Instantiation Configuration
    if ( ! get_post_meta( $post_id, '_vaplm_object_number', true ) ) {
        $assigned_id = vaplm_generate_atomic_object_number( $post->post_type );
        update_post_meta( $post_id, '_vaplm_object_number', $assigned_id );
        update_post_meta( $post_id, '_vaplm_created_datetime', $current_time );
        update_post_meta( $post_id, '_vaplm_creator_user_id', $current_user );

        // Safely extract and assign URL query params if a sidebar sub-type parameter initiated this post
        $active_tax = array( 
            'vaplm_part'         => 'vaplm_part_type', 
            'vaplm_document'     => 'vaplm_doc_type', 
            'vaplm_change_order' => 'vaplm_change_type', 
            'vaplm_bom'          => 'vaplm_bom_type' 
        );
        
        if ( isset( $active_tax[ $post->post_type ] ) && isset( $_GET['vaplm_subtype'] ) ) {
            // Strict sanitization of the $_GET input
            $subtype_slug = sanitize_key( wp_unslash( $_GET['vaplm_subtype'] ) );
            if ( ! empty( $subtype_slug ) ) {
                wp_set_object_terms( $post_id, $subtype_slug, $active_tax[ $post->post_type ] );
            }
        }
    }

    // Gate 2: Downstream Modification Processing Tracking
    update_post_meta( $post_id, '_vaplm_modified_datetime', $current_time );
    update_post_meta( $post_id, '_vaplm_modifier_user_id', $current_user );
}
add_action( 'save_post', 'vaplm_enforce_immutable_database_audit_trail', 10, 3 );