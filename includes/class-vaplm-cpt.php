<?php
/**
 * Custom Post Type & Hierarchical Taxonomy Topology Registration Engine.
 *
 * @package    VA_PLM_Admin_Suite
 * @subpackage VA_PLM_Admin_Suite/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
    die; 
}

class VAPLM_Cpt {

    public function register_core_post_types_and_taxonomies() {
        
        // --------------------------------------------------------------------------
        // PHASE 1: TAXONOMY REGISTRATIONS
        // --------------------------------------------------------------------------
        
        register_taxonomy( 'vaplm_part_type', array( 'vaplm_part' ), array(
            'hierarchical'      => true,
            'labels'            => array(
                'name'              => _x( 'Part Subtypes', 'taxonomy general name', 'va-plm-admin-suite' ),
                'singular_name'     => _x( 'Part Subtype', 'taxonomy singular name', 'va-plm-admin-suite' ),
                'search_items'      => __( 'Search Part Subtypes', 'va-plm-admin-suite' ),
                'all_items'         => __( 'All Part Subtypes', 'va-plm-admin-suite' ),
                'parent_item'       => __( 'Parent Subtype', 'va-plm-admin-suite' ),
                'parent_item_colon' => __( 'Parent Subtype:', 'va-plm-admin-suite' ),
                'edit_item'         => __( 'Edit Part Subtype', 'va-plm-admin-suite' ),
                'update_item'       => __( 'Update Part Subtype', 'va-plm-admin-suite' ),
                'add_new_item'      => __( 'Add New Part Subtype', 'va-plm-admin-suite' ),
                'new_item_name'     => __( 'New Part Subtype Name', 'va-plm-admin-suite' ),
                'menu_name'         => __( 'Subtype Configurations', 'va-plm-admin-suite' ),
            ),
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array( 'slug' => 'part-type' ),
            'show_in_rest'      => false, 
        ) );

        register_taxonomy( 'vaplm_doc_type', array( 'vaplm_document' ), array(
            'hierarchical'      => true,
            'labels'            => array(
                'name'              => _x( 'Document Subtypes', 'taxonomy general name', 'va-plm-admin-suite' ),
                'singular_name'     => _x( 'Document Subtype', 'taxonomy singular name', 'va-plm-admin-suite' ),
                'search_items'      => __( 'Search Document Subtypes', 'va-plm-admin-suite' ),
                'all_items'         => __( 'All Document Subtypes', 'va-plm-admin-suite' ),
                'edit_item'         => __( 'Edit Document Subtype', 'va-plm-admin-suite' ),
                'add_new_item'      => __( 'Add New Document Subtype', 'va-plm-admin-suite' ),
                'menu_name'         => __( 'Subtype Configurations', 'va-plm-admin-suite' ),
            ),
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array( 'slug' => 'doc-type' ),
            'show_in_rest'      => false,
        ) );

        register_taxonomy( 'vaplm_bom_type', array( 'vaplm_bom' ), array(
            'hierarchical'      => true,
            'labels'            => array(
                'name'              => _x( 'BOM Subtypes', 'taxonomy general name', 'va-plm-admin-suite' ),
                'singular_name'     => _x( 'BOM Subtype', 'taxonomy singular name', 'va-plm-admin-suite' ),
                'add_new_item'      => __( 'Add New BOM Subtype', 'va-plm-admin-suite' ),
                'menu_name'         => __( 'Subtype Configurations', 'va-plm-admin-suite' ),
            ),
            'show_ui'           => true,
            'show_admin_column' => true,
            'rewrite'           => array( 'slug' => 'bom-type' ),
            'show_in_rest'      => false,
        ) );

        register_taxonomy( 'vaplm_change_type', array( 'vaplm_change_order' ), array(
            'hierarchical'      => true,
            'labels'            => array(
                'name'              => _x( 'Change Subtypes', 'taxonomy general name', 'va-plm-admin-suite' ),
                'singular_name'     => _x( 'Change Subtype', 'taxonomy singular name', 'va-plm-admin-suite' ),
                'add_new_item'      => __( 'Add New Change Subtype', 'va-plm-admin-suite' ),
                'menu_name'         => __( 'Subtype Configurations', 'va-plm-admin-suite' ),
            ),
            'show_ui'           => true,
            'show_admin_column' => true,
            'rewrite'           => array( 'slug' => 'change-type' ),
            'show_in_rest'      => false,
        ) );

        // --------------------------------------------------------------------------
        // PHASE 2: CUSTOM POST TYPES
        // --------------------------------------------------------------------------

        $base_cpt_args = array(
            'public'             => false, 
            'publicly_queryable' => false, 
            'show_ui'            => true,  
            'show_in_menu'       => false, 
            'query_var'          => true,
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array( 'title' ), 
            'show_in_rest'       => false, 
        );

        $part_args = array_merge( $base_cpt_args, array(
            'labels'  => array(
                'name'               => _x( 'Parts', 'post type general name', 'va-plm-admin-suite' ),
                'singular_name'      => _x( 'Part', 'post type singular name', 'va-plm-admin-suite' ),
                'menu_name'          => _x( 'Parts', 'admin menu', 'va-plm-admin-suite' ),
                'add_new'            => _x( 'Create Engineering Part', 'add new button label', 'va-plm-admin-suite' ),
                'add_new_item'       => __( 'Instantiate New Part Profile', 'va-plm-admin-suite' ),
                'edit_item'          => __( 'Modify Part Definition Context', 'va-plm-admin-suite' ),
                'new_item'           => __( 'New Part Profile', 'va-plm-admin-suite' ),
                'view_item'          => __( 'Review Part Specification', 'va-plm-admin-suite' ),
                'search_items'       => __( 'Search Parts Vault', 'va-plm-admin-suite' ),
                'not_found'          => __( 'No product parts records matching search filters compiled.', 'va-plm-admin-suite' ),
                'all_items'          => __( 'Parts Directory Layout', 'va-plm-admin-suite' )
            ),
            'rewrite' => array( 'slug' => 'engineering-part' )
        ) );
        register_post_type( 'vaplm_part', $part_args );

        $doc_args = array_merge( $base_cpt_args, array(
            'labels'  => array(
                'name'               => _x( 'Documents', 'post type general name', 'va-plm-admin-suite' ),
                'singular_name'      => _x( 'Document', 'post type singular name', 'va-plm-admin-suite' ),
                'add_new'            => _x( 'Vault New File', 'add new button label', 'va-plm-admin-suite' ),
                'add_new_item'       => __( 'Instantiate Specification Document', 'va-plm-admin-suite' ),
                'edit_item'          => __( 'Edit Document Metadata Trace', 'va-plm-admin-suite' ),
                'all_items'          => __( 'Documents Vault Layout', 'va-plm-admin-suite' )
            ),
            'rewrite' => array( 'slug' => 'vault-document' )
        ) );
        register_post_type( 'vaplm_document', $doc_args );

        $bom_args = array_merge( $base_cpt_args, array(
            'labels'  => array(
                'name'               => _x( 'BOM Assemblies', 'post type general name', 'va-plm-admin-suite' ),
                'singular_name'      => _x( 'BOM Assembly', 'post type singular name', 'va-plm-admin-suite' ),
                'add_new'            => _x( 'Create BOM Configuration', 'add new button label', 'va-plm-admin-suite' ),
                'add_new_item'       => __( 'Instantiate Parent Assembly Blueprint', 'va-plm-admin-suite' ),
                'all_items'          => __( 'BOM Center Assembly Views', 'va-plm-admin-suite' )
            ),
            'rewrite' => array( 'slug' => 'assembly-bom' )
        ) );
        register_post_type( 'vaplm_bom', $bom_args );

        $change_args = array_merge( $base_cpt_args, array(
            'labels'  => array(
                'name'               => _x( 'Change Orders', 'post type general name', 'va-plm-admin-suite' ),
                'singular_name'      => _x( 'Change Order', 'post type singular name', 'va-plm-admin-suite' ),
                'add_new'            => _x( 'Initiate ECO Variant', 'add new button label', 'va-plm-admin-suite' ),
                'add_new_item'       => __( 'Draft Engineering Change Notice Order', 'va-plm-admin-suite' ),
                'all_items'          => __( 'Change Control Log Workspace', 'va-plm-admin-suite' )
            ),
            'rewrite' => array( 'slug' => 'workflow-eco' )
        ) );
        register_post_type( 'vaplm_change_order', $change_args );
    }
}