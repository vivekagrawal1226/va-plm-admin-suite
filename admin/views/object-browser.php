<?php
/**
 * Multi-Object High-Density List Viewer & Search Directory.
 *
 * Renders the tabular grid directory for viewing, searching, and filtering
 * parent PLM items. Displays the five unalterable system auditing metrics
 * and encapsulates AJAX trigger anchors to load objects contextually.
 *
 * @package    VA_PLM_Admin_Suite
 * @subpackage VA_PLM_Admin_Suite/admin/views
 */

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

/**
 * Active context determination.
 * $object_type is passed cleanly via include context variables from class-vaplm-admin.php
 */
if ( ! isset( $object_type ) ) {
    $object_type = 'vaplm_part'; 
}

// 1. Resolve localized structural nomenclature parameters for headers
$object_labels = array(
    'vaplm_part'         => array( 'singular' => __( 'Part', 'va-plm-admin-suite' ), 'plural' => 'Parts', 'tax' => 'vaplm_part_type' ),
    'vaplm_document'     => array( 'singular' => __( 'Document', 'va-plm-admin-suite' ), 'plural' => 'Documents', 'tax' => 'vaplm_doc_type' ),
    'vaplm_bom'          => array( 'singular' => __( 'BOM Assembly', 'va-plm-admin-suite' ), 'plural' => 'BOM Assemblies', 'tax' => 'vaplm_bom_type' ),
    'vaplm_change_order' => array( 'singular' => __( 'Change Order', 'va-plm-admin-suite' ), 'plural' => 'Changes', 'tax' => 'vaplm_change_type' )
);

$current_label = $object_labels[$object_type]['plural'];
$current_tax   = $object_labels[$object_type]['tax'];

// 2. Strict Input Sanitization
$current_page   = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
$search_string  = isset( $_GET['vaplm_search'] ) ? sanitize_text_field( wp_unslash( $_GET['vaplm_search'] ) ) : '';
$subtype_filter = isset( $_GET['vaplm_filter_subtype'] ) ? sanitize_key( wp_unslash( $_GET['vaplm_filter_subtype'] ) ) : '';
$page_number    = isset( $_GET['paged'] ) ? max( 1, absint( wp_unslash( $_GET['paged'] ) ) ) : 1;

// 3. Process Search Queries & Taxonomy Scope Filters
$query_args = array(
    'post_type'      => $object_type,
    'post_status'    => 'any',
    'posts_per_page' => 20,
    'paged'          => $page_number,
    's'              => $search_string,
    'orderby'        => 'modified',
    'order'          => 'DESC'
);

// Append classification filter constraints if a specific sub-type taxonomy is active
if ( ! empty( $subtype_filter ) ) {
    $query_args['tax_query'] = array(
        array(
            'taxonomy' => $current_tax,
            'field'    => 'slug',
            'terms'    => $subtype_filter,
        ),
    );
}

$vaplm_query = new WP_Query( $query_args );
$available_subtypes = get_terms( array( 'taxonomy' => $current_tax, 'hide_empty' => false ) );
?>

<div class="wrap vaplm-admin-wrap" id="vaplm-object-browser-canvas-container">
    <div class="vaplm-browser-header-cluster" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h1 class="wp-heading-inline">🔍 
            <?php 
            /* translators: %s: Plural name of the engineering object type (e.g., Parts, BOM Assemblies) */
            printf( esc_html__( 'Engineering Registry: %s Directory', 'va-plm-admin-suite' ), esc_html( $current_label ) ); 
            ?>
        </h1>
        
        <?php 
        // Role Access Control Safeguard: Hide Create Shortcut from read-only PLM Guest accounts
        if ( current_user_can( 'edit_posts' ) ) : 
            $create_page_slug = str_replace( '_', '-', $object_type ) . '-create';
            ?>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=vaplm-' . $create_page_slug ) ); ?>" class="page-title-action" style="background: var(--vaplm-primary, #2271b1); color: #fff; border: none; padding: 6px 14px; border-radius: 4px; font-weight: 600; text-decoration: none;">
                ➕ <?php 
                /* translators: %s: Singular name of the engineering object type (e.g., Part, Document) */
                printf( esc_html__( 'Create New %s', 'va-plm-admin-suite' ), esc_html( $object_labels[$object_type]['singular'] ) ); 
                ?>
            </a>
        <?php endif; ?>
    </div>

    <div class="tablenav top" style="background: #fff; border: 1px solid var(--vaplm-border-color, #ccd0d4); padding: 10px 15px; border-radius: 4px; margin-bottom: 15px; display: flex; gap: 15px; align-items: center; justify-content: space-between;">
        <form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" style="display: flex; gap: 10px; width: 100%;">
            <input type="hidden" name="page" value="<?php echo esc_attr( $current_page ); ?>" />
            
            <input type="search" name="vaplm_search" value="<?php echo esc_attr( $search_string ); ?>" placeholder="<?php esc_attr_e( 'Search by object title, description or code...', 'va-plm-admin-suite' ); ?>" style="flex-grow: 1; max-width: 400px; height: 30px;" />

            <select name="vaplm_filter_subtype" style="height: 30px; min-width: 180px;">
                <option value=""><?php esc_html_e( 'All Classifications / Sub-types', 'va-plm-admin-suite' ); ?></option>
                <?php 
                if ( ! is_wp_error( $available_subtypes ) && ! empty( $available_subtypes ) ) {
                    foreach ( $available_subtypes as $term ) {
                        echo '<option value="' . esc_attr( $term->slug ) . '" ' . selected( $subtype_filter, $term->slug, false ) . '>' . esc_html( $term->name ) . '</option>';
                    }
                }
                ?>
            </select>

            <input type="submit" class="button button-secondary" value="<?php esc_attr_e( 'Apply Filter Matrix', 'va-plm-admin-suite' ); ?>" style="height: 30px;" />
            
            <?php if ( ! empty( $search_string ) || ! empty( $subtype_filter ) ) : ?>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $current_page ) ); ?>" class="button button-link" style="line-height: 28px; color: var(--vaplm-danger, #d63638); text-decoration: none;">✕ <?php esc_html_e( 'Reset Filters', 'va-plm-admin-suite' ); ?></a>
            <?php endif; ?>
        </form>

        <div class="tablenav-pages" style="font-weight: 500; color: var(--vaplm-text-muted, #646970);">
            <span class="displaying-num">
                <?php 
                /* translators: %s: Number of engineering records found */
                printf( esc_html( _n( '%s engineering record found', '%s engineering records listed', $vaplm_query->found_posts, 'va-plm-admin-suite' ) ), esc_html( number_format_i18n( $vaplm_query->found_posts ) ) ); 
                ?>
            </span>
        </div>
    </div>

    <table class="wp-list-table widefat fixed striped vaplm-data-table">
        <thead>
            <tr>
                <th style="width: 14%;"><?php esc_html_e( 'Object Number', 'va-plm-admin-suite' ); ?></th>
                <th style="width: 26%;"><?php esc_html_e( 'Object Core Title', 'va-plm-admin-suite' ); ?></th>
                <th style="width: 12%;"><?php esc_html_e( 'Classification Sub-type', 'va-plm-admin-suite' ); ?></th>
                <th style="width: 14%;"><?php esc_html_e( 'Create Date (UTC)', 'va-plm-admin-suite' ); ?></th>
                <th style="width: 14%;"><?php esc_html_e( 'Modified Date (UTC)', 'va-plm-admin-suite' ); ?></th>
                <th style="width: 10%;"><?php esc_html_e( 'Creator Profile', 'va-plm-admin-suite' ); ?></th>
                <th style="width: 10%; text-align: center;"><?php esc_html_e( 'Operational State', 'va-plm-admin-suite' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( $vaplm_query->have_posts() ) : ?>
                <?php while ( $vaplm_query->have_posts() ) : $vaplm_query->the_post(); 
                    $post_id        = get_the_ID();
                    
                    // Pull immutable automated metrics properties contextually row-by-row
                    $object_num     = get_post_meta( $post_id, '_vaplm_object_number', true ) ?: '--';
                    $created_time   = get_post_meta( $post_id, '_vaplm_created_datetime', true ) ?: '--';
                    $modified_time  = get_post_meta( $post_id, '_vaplm_modified_datetime', true ) ?: '--';
                    $creator_id     = get_post_meta( $post_id, '_vaplm_creator_user_id', true );
                    $lifecycle_state = get_post_meta( $post_id, '_vaplm_lifecycle_status', true ) ?: 'Draft';

                    // Map numeric creator database ids to human login strings
                    $creator_login  = $creator_id ? get_userdata( $creator_id )->user_login : '--';

                    // Extract assigned taxonomy metadata tokens for column layout cells
                    $terms = wp_get_post_terms( $post_id, $current_tax );
                    $subtype_cell = ( ! is_wp_error( $terms ) && ! empty( $terms ) ) ? $terms[0]->name : '--';

                    // Setup clean state-dependent CSS variable classes for quick tracing visual aids
                    $state_badge_style = 'background: #e0e0e0; color: #1d2327;';
                    if ( 'Released' === $lifecycle_state ) {
                        $state_badge_style = 'background: #ecf7ed; color: #1e4620; border: 1px solid #c8e6ca; font-weight: 700;';
                    } elseif ( 'In Review' === $lifecycle_state ) {
                        $state_badge_style = 'background: #fdf6ec; color: #664d03; border: 1px solid #fbe6c4;';
                    } elseif ( 'Obsolete' === $lifecycle_state ) {
                        $state_badge_style = 'background: #fbebeb; color: #842029; border: 1px solid #f5c2c7;';
                    }
                    ?>
                    <tr data-post-id="<?php echo esc_attr( $post_id ); ?>">
                        <td>
                            <code class="vaplm-object-number-accent-link" style="font-weight: 700; font-size: 12px;">
                                <?php echo esc_html( $object_num ); ?>
                            </code>
                        </td>
                        <td>
                            <a href="<?php echo esc_url( admin_url( 'post.php?post=' . $post_id . '&action=edit' ) ); ?>" class="vaplm-file-link-accent row-title" style="display: block; font-weight: 600;">
                                <?php the_title(); ?>
                            </a>
                            <div class="row-actions" style="font-weight: 500;">
                                <span class="view"><a href="<?php echo esc_url( admin_url( 'post.php?post=' . $post_id . '&action=edit' ) ); ?>" title="<?php esc_attr_e( 'Open in safe standard read-only view mode.', 'va-plm-admin-suite' ); ?>"><?php esc_html_e( 'Open Object', 'va-plm-admin-suite' ); ?></a> | </span>
                                <span class="edit"><a href="<?php echo esc_url( admin_url( 'post.php?post=' . $post_id . '&action=edit#vaplm-enable-edit-mode-btn' ) ); ?>" style="color: var(--vaplm-danger, #d63638);" title="<?php esc_attr_e( 'Request write capabilities override to modify parameters.', 'va-plm-admin-suite' ); ?>"><?php esc_html_e( 'Request Edit Mode', 'va-plm-admin-suite' ); ?></a></span>
                            </div>
                        </td>
                        <td>
                            <span class="vaplm-item-type-pill"><?php echo esc_html( $subtype_cell ); ?></span>
                        </td>
                        <td style="font-family: monospace; font-size: 12px; color: #3c434a;">
                            <?php echo esc_html( $created_time ); ?>
                        </td>
                        <td style="font-family: monospace; font-size: 12px; color: #3c434a;">
                            <?php echo esc_html( $modified_time ); ?>
                        </td>
                        <td>
                            <strong><?php echo esc_html( $creator_login ); ?></strong>
                        </td>
                        <td style="text-align: center;">
                            <span class="vaplm-state-pill-badge" style="display: inline-block; padding: 3px 9px; border-radius: 12px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.02em; min-width: 65px; <?php echo esc_attr( $state_badge_style ); ?>">
                                <?php echo esc_html( $lifecycle_state ); ?>
                            </span>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else : ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 30px; color: var(--vaplm-text-muted, #646970); font-weight: 500;">
                        📭 <?php 
                        /* translators: %s: The plural lowercase name of the object type (e.g., parts, documents) */
                        printf( esc_html__( 'No engineering %s matches your active lookup filtering query profiles.', 'va-plm-admin-suite' ), esc_html( strtolower( $current_label ) ) ); 
                        ?>
                    </td>
                </tr>
            <?php endif; wp_reset_postdata(); ?>
        </tbody>
    </table>

    <?php
    $total_pages = $vaplm_query->max_num_pages;
    if ( $total_pages > 1 ) :
        $current_page_url = remove_query_arg( 'paged' );
        ?>
        <div class="tablenav bottom" style="margin-top: 15px; display: flex; justify-content: flex-end;">
            <div class="tablenav-pages" style="display: flex; gap: 4px;">
                <?php
                echo wp_kses_post( paginate_links( array(
                    'base'      => add_query_arg( 'paged', '%#%' ),
                    'format'    => '',
                    'prev_text' => __( '« Previous', 'va-plm-admin-suite' ),
                    'next_text' => __( 'Next »', 'va-plm-admin-suite' ),
                    'total'     => $total_pages,
                    'current'   => $page_number,
                    'type'      => 'plain',
                    'className' => 'button page-numbers'
                ) ) );
                ?>
            </div>
        </div>
    <?php endif; ?>
</div>