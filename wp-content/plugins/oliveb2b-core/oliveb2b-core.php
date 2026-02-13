<?php
/**
 * Plugin Name: OliveB2B Core
 * Description: Core functionality for OliveB2B (CPTs, taxonomies, search UI, language switcher).
 * Version: 0.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

const OLIVEB2B_VERSION = '0.3.0';
const OLIVEB2B_ROLE_SYNC_VERSION = '1';

register_activation_hook( __FILE__, 'oliveb2b_activate_plugin' );
register_deactivation_hook( __FILE__, 'oliveb2b_deactivate_plugin' );

add_action( 'init', 'oliveb2b_register_cpts' );
add_action( 'init', 'oliveb2b_register_taxonomies' );
add_action( 'init', 'oliveb2b_register_shortcodes' );
add_action( 'init', 'oliveb2b_register_cli_commands' );
add_action( 'init', 'oliveb2b_maybe_sync_roles' );
add_action( 'wp_enqueue_scripts', 'oliveb2b_enqueue_assets' );
add_action( 'generate_before_header', 'oliveb2b_maybe_render_language_switcher' );
add_filter( 'the_content', 'oliveb2b_gate_single_content_for_guests', 20 );
add_filter( 'the_title', 'oliveb2b_gate_single_supplier_title_for_guests', 20, 2 );

function oliveb2b_register_cpts() {
    register_post_type(
        'olive_supplier',
        array(
            'labels' => array(
                'name' => 'Suppliers',
                'singular_name' => 'Supplier',
            ),
            'public' => true,
            'has_archive' => true,
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-building',
            'supports' => array( 'title', 'editor', 'excerpt', 'thumbnail', 'author' ),
            'rewrite' => array( 'slug' => 'suppliers' ),
            'capability_type' => array( 'olive_supplier', 'olive_suppliers' ),
            'capabilities' => oliveb2b_get_post_type_capabilities( 'olive_supplier', 'olive_suppliers' ),
            'map_meta_cap' => true,
        )
    );

    register_post_type(
        'olive_offer',
        array(
            'labels' => array(
                'name' => 'Offers',
                'singular_name' => 'Offer',
            ),
            'public' => true,
            'has_archive' => true,
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-megaphone',
            'supports' => array( 'title', 'editor', 'excerpt', 'thumbnail', 'author' ),
            'rewrite' => array( 'slug' => 'offers' ),
            'capability_type' => array( 'olive_offer', 'olive_offers' ),
            'capabilities' => oliveb2b_get_post_type_capabilities( 'olive_offer', 'olive_offers' ),
            'map_meta_cap' => true,
        )
    );

    register_post_type(
        'olive_rfq',
        array(
            'labels' => array(
                'name' => 'RFQs',
                'singular_name' => 'RFQ',
            ),
            'public' => true,
            'has_archive' => true,
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-clipboard',
            'supports' => array( 'title', 'editor', 'excerpt', 'author' ),
            'rewrite' => array( 'slug' => 'rfq' ),
            'capability_type' => array( 'olive_rfq', 'olive_rfqs' ),
            'capabilities' => oliveb2b_get_post_type_capabilities( 'olive_rfq', 'olive_rfqs' ),
            'map_meta_cap' => true,
        )
    );
}

function oliveb2b_register_taxonomies() {
    $shared = array(
        'public' => true,
        'show_in_rest' => true,
        'hierarchical' => true,
    );

    register_taxonomy(
        'olive_country',
        array( 'olive_supplier', 'olive_offer', 'olive_rfq' ),
        array_merge(
            $shared,
            array(
                'labels' => array(
                    'name' => 'Countries',
                    'singular_name' => 'Country',
                ),
                'rewrite' => array( 'slug' => 'country' ),
            )
        )
    );

    register_taxonomy(
        'olive_supplier_type',
        array( 'olive_supplier', 'olive_offer' ),
        array_merge(
            $shared,
            array(
                'labels' => array(
                    'name' => 'Supplier Types',
                    'singular_name' => 'Supplier Type',
                ),
                'rewrite' => array( 'slug' => 'supplier-type' ),
            )
        )
    );

    register_taxonomy(
        'olive_employees',
        array( 'olive_supplier' ),
        array_merge(
            $shared,
            array(
                'labels' => array(
                    'name' => 'Employees',
                    'singular_name' => 'Employees',
                ),
                'rewrite' => array( 'slug' => 'employees' ),
            )
        )
    );
}

function oliveb2b_enqueue_assets() {
    wp_enqueue_style(
        'oliveb2b-core',
        plugins_url( 'assets/oliveb2b.css', __FILE__ ),
        array(),
        OLIVEB2B_VERSION
    );

    wp_enqueue_script(
        'oliveb2b-core',
        plugins_url( 'assets/oliveb2b.js', __FILE__ ),
        array(),
        OLIVEB2B_VERSION,
        true
    );
}

function oliveb2b_register_shortcodes() {
    add_shortcode( 'oliveb2b_language_switcher', 'oliveb2b_language_switcher_shortcode' );
    add_shortcode( 'oliveb2b_search_results', 'oliveb2b_search_results_shortcode' );
}

function oliveb2b_register_cli_commands() {
    if ( defined( 'WP_CLI' ) && WP_CLI ) {
        WP_CLI::add_command( 'oliveb2b seed', 'oliveb2b_cli_seed_data' );
    }
}

function oliveb2b_activate_plugin() {
    oliveb2b_register_cpts();
    oliveb2b_register_taxonomies();
    oliveb2b_sync_roles_and_caps();
    flush_rewrite_rules();
}

function oliveb2b_deactivate_plugin() {
    flush_rewrite_rules();
}

function oliveb2b_maybe_sync_roles() {
    $synced_version = get_option( 'oliveb2b_role_sync_version', '' );
    if ( OLIVEB2B_ROLE_SYNC_VERSION !== $synced_version ) {
        oliveb2b_sync_roles_and_caps();
        update_option( 'oliveb2b_role_sync_version', OLIVEB2B_ROLE_SYNC_VERSION );
    }
}

function oliveb2b_sync_roles_and_caps() {
    add_role(
        'olive_supplier_enterprise',
        'Supplier (Enterprise)',
        array(
            'read' => true,
            'upload_files' => true,
        )
    );
    add_role(
        'olive_supplier_private',
        'Supplier (Private)',
        array(
            'read' => true,
            'upload_files' => true,
        )
    );
    add_role(
        'olive_professional',
        'Professional',
        array(
            'read' => true,
            'upload_files' => true,
        )
    );
    add_role(
        'olive_buyer',
        'Buyer',
        array(
            'read' => true,
        )
    );

    $all_caps = array_merge(
        oliveb2b_get_post_type_capability_values( 'olive_supplier', 'olive_suppliers' ),
        oliveb2b_get_post_type_capability_values( 'olive_offer', 'olive_offers' ),
        oliveb2b_get_post_type_capability_values( 'olive_rfq', 'olive_rfqs' )
    );

    $rfq_caps   = oliveb2b_get_post_type_capability_values( 'olive_rfq', 'olive_rfqs' );
    $offer_caps = oliveb2b_get_post_type_capability_values( 'olive_offer', 'olive_offers' );

    oliveb2b_grant_caps_to_role( 'administrator', $all_caps );
    oliveb2b_grant_caps_to_role( 'olive_buyer', $rfq_caps );
    oliveb2b_grant_caps_to_role( 'olive_supplier_enterprise', $offer_caps );
    oliveb2b_grant_caps_to_role( 'olive_supplier_private', $offer_caps );
    oliveb2b_grant_caps_to_role( 'olive_professional', $offer_caps );
}

function oliveb2b_grant_caps_to_role( $role_name, $caps ) {
    $role = get_role( $role_name );
    if ( ! $role ) {
        return;
    }

    foreach ( $caps as $cap ) {
        $role->add_cap( $cap );
    }
}

function oliveb2b_get_post_type_capabilities( $singular, $plural ) {
    return array(
        'edit_post'              => 'edit_' . $singular,
        'read_post'              => 'read_' . $singular,
        'delete_post'            => 'delete_' . $singular,
        'edit_posts'             => 'edit_' . $plural,
        'edit_others_posts'      => 'edit_others_' . $plural,
        'publish_posts'          => 'publish_' . $plural,
        'read_private_posts'     => 'read_private_' . $plural,
        'delete_posts'           => 'delete_' . $plural,
        'delete_private_posts'   => 'delete_private_' . $plural,
        'delete_published_posts' => 'delete_published_' . $plural,
        'delete_others_posts'    => 'delete_others_' . $plural,
        'edit_private_posts'     => 'edit_private_' . $plural,
        'edit_published_posts'   => 'edit_published_' . $plural,
        'create_posts'           => 'create_' . $plural,
    );
}

function oliveb2b_get_post_type_capability_values( $singular, $plural ) {
    return array_values( oliveb2b_get_post_type_capabilities( $singular, $plural ) );
}

function oliveb2b_maybe_render_language_switcher() {
    $enabled = apply_filters( 'oliveb2b_auto_language_switcher', true );
    if ( ! $enabled ) {
        return;
    }
    echo oliveb2b_language_switcher_markup();
}

function oliveb2b_language_switcher_shortcode() {
    return oliveb2b_language_switcher_markup();
}

function oliveb2b_language_switcher_markup() {
    $languages = oliveb2b_get_languages();
    $current   = oliveb2b_get_current_language_code();

    $items = array();
    foreach ( $languages as $lang ) {
        $is_current = $current === $lang['code'];
        $label      = $lang['label'];
        $url        = apply_filters( 'oliveb2b_language_url', $lang['url'], $lang );
        $dir        = isset( $lang['dir'] ) ? $lang['dir'] : 'ltr';
        $items[]    = sprintf(
            '<li class="oliveb2b-lang-item%1$s"><a href="%2$s" lang="%3$s" dir="%4$s">%5$s</a></li>',
            $is_current ? ' is-current' : '',
            esc_url( $url ),
            esc_attr( $lang['code'] ),
            esc_attr( $dir ),
            esc_html( $label )
        );
    }

    return sprintf(
        '<nav class="oliveb2b-lang-switcher" aria-label="Language switcher"><ul>%s</ul></nav>',
        implode( '', $items )
    );
}

function oliveb2b_get_languages() {
    if ( function_exists( 'pll_the_languages' ) ) {
        $raw = pll_the_languages( array( 'raw' => 1 ) );
        if ( is_array( $raw ) ) {
            $languages = array();
            foreach ( $raw as $data ) {
                $languages[] = array(
                    'code'  => isset( $data['slug'] ) ? $data['slug'] : '',
                    'label' => isset( $data['name'] ) ? $data['name'] : '',
                    'url'   => isset( $data['url'] ) ? $data['url'] : home_url( '/' ),
                    'dir'   => ! empty( $data['rtl'] ) ? 'rtl' : 'ltr',
                );
            }
            return apply_filters( 'oliveb2b_language_list', $languages );
        }
    }

    $languages = array(
        array( 'code' => 'en', 'label' => 'English' ),
        array( 'code' => 'fr', 'label' => 'Français' ),
        array( 'code' => 'de', 'label' => 'Deutsch' ),
        array( 'code' => 'es', 'label' => 'Español' ),
        array( 'code' => 'it', 'label' => 'Italiano' ),
        array( 'code' => 'nl', 'label' => 'Nederlands' ),
        array( 'code' => 'tr', 'label' => 'Türkçe' ),
        array( 'code' => 'cs', 'label' => 'Čeština' ),
        array( 'code' => 'da', 'label' => 'Dansk' ),
        array( 'code' => 'et', 'label' => 'Eesti keel' ),
        array( 'code' => 'el', 'label' => 'Eλληνική' ),
        array( 'code' => 'lt', 'label' => 'Lietuvių' ),
        array( 'code' => 'hu', 'label' => 'Magyar' ),
        array( 'code' => 'no', 'label' => 'Norsk' ),
        array( 'code' => 'pl', 'label' => 'Polski' ),
        array( 'code' => 'pt', 'label' => 'Português' ),
        array( 'code' => 'ro', 'label' => 'Română' ),
        array( 'code' => 'sl', 'label' => 'Slovenščina' ),
        array( 'code' => 'fi', 'label' => 'Suomi' ),
        array( 'code' => 'sv', 'label' => 'Svenska' ),
        array( 'code' => 'bg', 'label' => 'Български' ),
        array( 'code' => 'ar', 'label' => 'العربية', 'dir' => 'rtl' ),
    );

    $languages = array_map(
        function ( $lang ) {
            $lang['url'] = home_url( '/' . $lang['code'] . '/' );
            return $lang;
        },
        $languages
    );

    return apply_filters( 'oliveb2b_language_list', $languages );
}

function oliveb2b_get_current_language_code() {
    if ( function_exists( 'pll_current_language' ) ) {
        $lang = pll_current_language( 'slug' );
        if ( $lang ) {
            return $lang;
        }
    }

    $path      = trim( wp_parse_url( home_url( add_query_arg( array() ) ), PHP_URL_PATH ), '/' );
    $segments  = explode( '/', $path );
    $candidate = isset( $segments[0] ) ? $segments[0] : 'en';
    $codes     = array_map(
        function ( $lang ) {
            return $lang['code'];
        },
        oliveb2b_get_languages()
    );

    return in_array( $candidate, $codes, true ) ? $candidate : 'en';
}

function oliveb2b_gate_single_content_for_guests( $content ) {
    if ( is_admin() || is_user_logged_in() ) {
        return $content;
    }

    if ( ! is_singular( array( 'olive_supplier', 'olive_offer', 'olive_rfq' ) ) || ! in_the_loop() || ! is_main_query() ) {
        return $content;
    }

    $post_id        = get_the_ID();
    $summary_source = has_excerpt( $post_id ) ? get_the_excerpt( $post_id ) : wp_trim_words( wp_strip_all_tags( $content ), 45 );
    $login_url      = wp_login_url( get_permalink( $post_id ) );

    return sprintf(
        '<p>%1$s</p><p><a href="%2$s">Login to view full details and contact information.</a></p>',
        esc_html( $summary_source ),
        esc_url( $login_url )
    );
}

function oliveb2b_gate_single_supplier_title_for_guests( $title, $post_id ) {
    if ( is_admin() || is_user_logged_in() ) {
        return $title;
    }

    if ( (int) $post_id !== (int) get_queried_object_id() ) {
        return $title;
    }

    if ( ! is_singular( 'olive_supplier' ) ) {
        return $title;
    }

    return 'Supplier profile (login to view)';
}

function oliveb2b_search_results_shortcode() {
    $filters = array(
        'search_term'   => isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '',
        'country'       => isset( $_GET['country'] ) ? sanitize_text_field( wp_unslash( $_GET['country'] ) ) : '',
        'supplier_type' => isset( $_GET['supplier_type'] ) ? sanitize_text_field( wp_unslash( $_GET['supplier_type'] ) ) : '',
        'employees'     => isset( $_GET['employees'] ) ? sanitize_text_field( wp_unslash( $_GET['employees'] ) ) : '',
        'verified'      => isset( $_GET['verified'] ) ? sanitize_text_field( wp_unslash( $_GET['verified'] ) ) : '',
        'radius'        => isset( $_GET['radius'] ) ? sanitize_text_field( wp_unslash( $_GET['radius'] ) ) : '',
        'user_lat'      => isset( $_GET['user_lat'] ) ? sanitize_text_field( wp_unslash( $_GET['user_lat'] ) ) : '',
        'user_lng'      => isset( $_GET['user_lng'] ) ? sanitize_text_field( wp_unslash( $_GET['user_lng'] ) ) : '',
    );

    $offers_filters = $filters;
    unset( $offers_filters['employees'] );

    $offers    = oliveb2b_run_search_query( 'olive_offer', $offers_filters );
    $suppliers = oliveb2b_run_search_query( 'olive_supplier', $filters );

    ob_start();
    ?>
    <section class="oliveb2b-search">
        <form method="get" class="oliveb2b-search-form">
            <div class="oliveb2b-search-bar">
                <input type="search" name="s" placeholder="Search suppliers or offers" value="<?php echo esc_attr( $filters['search_term'] ); ?>" />
                <button type="submit">Search</button>
            </div>
            <div class="oliveb2b-search-grid">
                <aside class="oliveb2b-filters">
                    <h3>Filters</h3>
                    <label>
                        Country
                        <?php echo oliveb2b_taxonomy_select( 'olive_country', 'country', $filters['country'] ); ?>
                    </label>
                    <label>
                        Supplier type
                        <?php echo oliveb2b_taxonomy_select( 'olive_supplier_type', 'supplier_type', $filters['supplier_type'] ); ?>
                    </label>
                    <label>
                        Employees
                        <?php echo oliveb2b_taxonomy_select( 'olive_employees', 'employees', $filters['employees'] ); ?>
                    </label>
                    <label>
                        Near me radius (km)
                        <div class="oliveb2b-near-me-row">
                            <input type="number" name="radius" min="1" value="<?php echo esc_attr( $filters['radius'] ); ?>" placeholder="e.g. 50" />
                            <button class="oliveb2b-geolocate" type="button">Use my location</button>
                        </div>
                        <input type="hidden" name="user_lat" value="<?php echo esc_attr( $filters['user_lat'] ); ?>" />
                        <input type="hidden" name="user_lng" value="<?php echo esc_attr( $filters['user_lng'] ); ?>" />
                        <small class="oliveb2b-near-me-note">Uses supplier/offer coordinates when available.</small>
                    </label>
                    <label class="oliveb2b-checkbox">
                        <input type="checkbox" name="verified" value="1" <?php checked( $filters['verified'], '1' ); ?> />
                        Verified only
                    </label>
                    <button type="submit">Apply filters</button>
                </aside>
                <div class="oliveb2b-results">
                    <div class="oliveb2b-tabs" role="tablist">
                        <button class="oliveb2b-tab is-active" data-target="offers" type="button">
                            Offers (<?php echo esc_html( $offers->found_posts ); ?>)
                        </button>
                        <button class="oliveb2b-tab" data-target="suppliers" type="button">
                            Suppliers (<?php echo esc_html( $suppliers->found_posts ); ?>)
                        </button>
                    </div>
                    <div class="oliveb2b-results-panel is-active" data-panel="offers">
                        <?php echo oliveb2b_render_results( $offers, 'offer' ); ?>
                    </div>
                    <div class="oliveb2b-results-panel" data-panel="suppliers">
                        <?php echo oliveb2b_render_results( $suppliers, 'supplier' ); ?>
                    </div>
                </div>
            </div>
        </form>
    </section>
    <?php
    wp_reset_postdata();
    return ob_get_clean();
}

function oliveb2b_run_search_query( $post_type, $filters ) {
    $radius_km = absint( $filters['radius'] );
    $user_lat  = oliveb2b_normalize_float( $filters['user_lat'] );
    $user_lng  = oliveb2b_normalize_float( $filters['user_lng'] );

    $tax_query = array();

    if ( $filters['country'] ) {
        $tax_query[] = array(
            'taxonomy' => 'olive_country',
            'field'    => 'slug',
            'terms'    => array( $filters['country'] ),
        );
    }

    if ( $filters['supplier_type'] ) {
        $tax_query[] = array(
            'taxonomy' => 'olive_supplier_type',
            'field'    => 'slug',
            'terms'    => array( $filters['supplier_type'] ),
        );
    }

    if ( 'olive_supplier' === $post_type && $filters['employees'] ) {
        $tax_query[] = array(
            'taxonomy' => 'olive_employees',
            'field'    => 'slug',
            'terms'    => array( $filters['employees'] ),
        );
    }

    if ( count( $tax_query ) > 1 ) {
        $tax_query['relation'] = 'AND';
    }

    $meta_query = array();
    if ( $filters['verified'] ) {
        $meta_query[] = array(
            'key'     => 'olive_verified',
            'value'   => '1',
            'compare' => '=',
        );
    }

    $args = array(
        'post_type'      => $post_type,
        's'              => $filters['search_term'],
        'posts_per_page' => $radius_km > 0 && null !== $user_lat && null !== $user_lng ? 300 : 20,
    );

    if ( ! empty( $tax_query ) ) {
        $args['tax_query'] = $tax_query;
    }
    if ( ! empty( $meta_query ) ) {
        $args['meta_query'] = $meta_query;
    }

    $query = new WP_Query( $args );

    if ( $radius_km > 0 && null !== $user_lat && null !== $user_lng ) {
        $filtered_posts = array();
        $distances      = array();

        foreach ( $query->posts as $post ) {
            $coords = oliveb2b_get_post_coordinates( $post->ID );
            if ( ! $coords ) {
                continue;
            }

            $distance_km = oliveb2b_distance_km( $user_lat, $user_lng, $coords['lat'], $coords['lng'] );
            if ( $distance_km <= $radius_km ) {
                $distances[ $post->ID ] = round( $distance_km, 1 );
                $filtered_posts[]       = $post;
            }
        }

        usort(
            $filtered_posts,
            function ( $a, $b ) use ( $distances ) {
                return $distances[ $a->ID ] <=> $distances[ $b->ID ];
            }
        );

        $query->posts         = array_slice( $filtered_posts, 0, 20 );
        $query->post_count    = count( $query->posts );
        $query->found_posts   = count( $filtered_posts );
        $query->max_num_pages = $query->found_posts > 0 ? (int) ceil( $query->found_posts / 20 ) : 0;
        $query->olive_distances = $distances;
    }

    return $query;
}

function oliveb2b_normalize_float( $value ) {
    if ( '' === $value || null === $value ) {
        return null;
    }
    $normalized = str_replace( ',', '.', (string) $value );
    if ( ! is_numeric( $normalized ) ) {
        return null;
    }
    return (float) $normalized;
}

function oliveb2b_get_post_coordinates( $post_id ) {
    $lat = oliveb2b_normalize_float( get_post_meta( $post_id, 'olive_lat', true ) );
    $lng = oliveb2b_normalize_float( get_post_meta( $post_id, 'olive_lng', true ) );

    if ( null !== $lat && null !== $lng ) {
        return array(
            'lat' => $lat,
            'lng' => $lng,
        );
    }

    $linked_supplier_id = absint( get_post_meta( $post_id, 'olive_supplier_id', true ) );
    if ( $linked_supplier_id ) {
        $supplier_lat = oliveb2b_normalize_float( get_post_meta( $linked_supplier_id, 'olive_lat', true ) );
        $supplier_lng = oliveb2b_normalize_float( get_post_meta( $linked_supplier_id, 'olive_lng', true ) );
        if ( null !== $supplier_lat && null !== $supplier_lng ) {
            return array(
                'lat' => $supplier_lat,
                'lng' => $supplier_lng,
            );
        }
    }

    return null;
}

function oliveb2b_distance_km( $lat1, $lng1, $lat2, $lng2 ) {
    $earth_radius = 6371;
    $dlat         = deg2rad( $lat2 - $lat1 );
    $dlng         = deg2rad( $lng2 - $lng1 );
    $a            = sin( $dlat / 2 ) * sin( $dlat / 2 ) + cos( deg2rad( $lat1 ) ) * cos( deg2rad( $lat2 ) ) * sin( $dlng / 2 ) * sin( $dlng / 2 );
    $c            = 2 * atan2( sqrt( $a ), sqrt( 1 - $a ) );
    return $earth_radius * $c;
}

function oliveb2b_taxonomy_select( $taxonomy, $name, $selected ) {
    $terms = get_terms(
        array(
            'taxonomy'   => $taxonomy,
            'hide_empty' => false,
        )
    );

    $options = '<option value="">Any</option>';
    foreach ( $terms as $term ) {
        $options .= sprintf(
            '<option value="%1$s"%2$s>%3$s</option>',
            esc_attr( $term->slug ),
            selected( $selected, $term->slug, false ),
            esc_html( $term->name )
        );
    }

    return sprintf( '<select name="%1$s">%2$s</select>', esc_attr( $name ), $options );
}

function oliveb2b_render_results( WP_Query $query, $type ) {
    if ( ! $query->have_posts() ) {
        return '<p class="oliveb2b-empty">No results yet.</p>';
    }

    $cards = '';
    while ( $query->have_posts() ) {
        $query->the_post();

        $post_id      = get_the_ID();
        $is_logged_in = is_user_logged_in();
        $title        = get_the_title();
        $excerpt      = get_the_excerpt();
        $is_verified  = '1' === (string) get_post_meta( $post_id, 'olive_verified', true );
        $distance     = isset( $query->olive_distances[ $post_id ] ) ? $query->olive_distances[ $post_id ] : null;

        if ( ! $is_logged_in && 'supplier' === $type ) {
            $title = 'Supplier profile (login to view)';
        }

        $meta_items = array();
        if ( $is_verified ) {
            $meta_items[] = '<span class="oliveb2b-pill">Verified</span>';
        }
        if ( null !== $distance ) {
            $meta_items[] = '<span class="oliveb2b-pill">' . esc_html( number_format_i18n( $distance, 1 ) ) . ' km</span>';
        }

        $cards .= sprintf(
            '<article class="oliveb2b-card">
                <h4>%1$s</h4>
                <div class="oliveb2b-card-meta">%2$s</div>
                <p>%3$s</p>
                <a href="%4$s">View details</a>
            </article>',
            esc_html( $title ),
            implode( '', $meta_items ),
            esc_html( $excerpt ? $excerpt : 'Summary available after login.' ),
            esc_url( get_permalink() )
        );
    }

    return '<div class="oliveb2b-card-grid">' . $cards . '</div>';
}

function oliveb2b_cli_seed_data( $args, $assoc_args ) {
    $reset = isset( $assoc_args['reset'] );

    $countries = array(
        'france'      => array( 'name' => 'France', 'lat' => 48.8566, 'lng' => 2.3522 ),
        'germany'     => array( 'name' => 'Germany', 'lat' => 52.52, 'lng' => 13.405 ),
        'spain'       => array( 'name' => 'Spain', 'lat' => 40.4168, 'lng' => -3.7038 ),
        'italy'       => array( 'name' => 'Italy', 'lat' => 41.9028, 'lng' => 12.4964 ),
        'netherlands' => array( 'name' => 'Netherlands', 'lat' => 52.3676, 'lng' => 4.9041 ),
        'turkey'      => array( 'name' => 'Turkey', 'lat' => 41.0082, 'lng' => 28.9784 ),
        'poland'      => array( 'name' => 'Poland', 'lat' => 52.2297, 'lng' => 21.0122 ),
        'sweden'      => array( 'name' => 'Sweden', 'lat' => 59.3293, 'lng' => 18.0686 ),
        'romania'     => array( 'name' => 'Romania', 'lat' => 44.4268, 'lng' => 26.1025 ),
        'greece'      => array( 'name' => 'Greece', 'lat' => 37.9838, 'lng' => 23.7275 ),
    );

    $supplier_types = array(
        'enterprise'   => 'Supplier (Enterprise)',
        'private'      => 'Supplier (Private)',
        'professional' => 'Professional',
    );

    $employees_ranges = array(
        '1-10'       => '1-10',
        '11-50'      => '11-50',
        '51-250'     => '51-250',
        '251-1000'   => '251-1000',
        '1000-plus'  => '1000+',
    );

    if ( $reset ) {
        $existing = get_posts(
            array(
                'post_type'      => array( 'olive_supplier', 'olive_offer', 'olive_rfq' ),
                'post_status'    => 'any',
                'posts_per_page' => -1,
                'fields'         => 'ids',
            )
        );
        foreach ( $existing as $post_id ) {
            wp_delete_post( $post_id, true );
        }
    }

    foreach ( $countries as $slug => $country ) {
        wp_insert_term( $country['name'], 'olive_country', array( 'slug' => $slug ) );
    }
    foreach ( $supplier_types as $slug => $label ) {
        wp_insert_term( $label, 'olive_supplier_type', array( 'slug' => $slug ) );
    }
    foreach ( $employees_ranges as $slug => $label ) {
        wp_insert_term( $label, 'olive_employees', array( 'slug' => $slug ) );
    }

    $supplier_ids = array();
    for ( $i = 1; $i <= 36; $i++ ) {
        $country_slugs = array_keys( $countries );
        $country_slug  = $country_slugs[ array_rand( $country_slugs ) ];
        $country       = $countries[ $country_slug ];

        $type_slugs = array_keys( $supplier_types );
        $type_slug  = $type_slugs[ array_rand( $type_slugs ) ];

        $employee_slugs = array_keys( $employees_ranges );
        $employee_slug  = $employee_slugs[ array_rand( $employee_slugs ) ];

        $supplier_id = wp_insert_post(
            array(
                'post_type'    => 'olive_supplier',
                'post_status'  => 'publish',
                'post_title'   => sprintf( 'Supplier %d - %s', $i, $country['name'] ),
                'post_excerpt' => sprintf( 'Industrial supplier in %s. Login for full profile and contact details.', $country['name'] ),
                'post_content' => 'Seeded supplier profile.',
            )
        );

        if ( is_wp_error( $supplier_id ) || ! $supplier_id ) {
            continue;
        }

        wp_set_post_terms( $supplier_id, array( $country_slug ), 'olive_country', false );
        wp_set_post_terms( $supplier_id, array( $type_slug ), 'olive_supplier_type', false );
        wp_set_post_terms( $supplier_id, array( $employee_slug ), 'olive_employees', false );

        update_post_meta( $supplier_id, 'olive_verified', random_int( 0, 1 ) ? '1' : '0' );
        update_post_meta( $supplier_id, 'olive_lat', (string) ( $country['lat'] + ( random_int( -120, 120 ) / 100 ) ) );
        update_post_meta( $supplier_id, 'olive_lng', (string) ( $country['lng'] + ( random_int( -120, 120 ) / 100 ) ) );

        $supplier_ids[] = $supplier_id;
    }

    $offer_titles = array(
        'Bulk Olive Oil',
        'Private Label Packaging',
        'Cold Pressed Oils',
        'Glass Bottle Supply',
        'Organic Certification Support',
        'Export Logistics',
        'Food Service Supply',
    );

    for ( $i = 1; $i <= 60; $i++ ) {
        if ( empty( $supplier_ids ) ) {
            break;
        }

        $supplier_id = $supplier_ids[ array_rand( $supplier_ids ) ];
        $country_ids = wp_get_post_terms( $supplier_id, 'olive_country', array( 'fields' => 'ids' ) );
        $type_ids    = wp_get_post_terms( $supplier_id, 'olive_supplier_type', array( 'fields' => 'ids' ) );
        $title_part  = $offer_titles[ array_rand( $offer_titles ) ];

        $offer_id = wp_insert_post(
            array(
                'post_type'    => 'olive_offer',
                'post_status'  => 'publish',
                'post_title'   => sprintf( '%s #%d', $title_part, $i ),
                'post_excerpt' => 'Offer summary. Login to see full supplier identity and contact.',
                'post_content' => 'Seeded offer.',
            )
        );

        if ( is_wp_error( $offer_id ) || ! $offer_id ) {
            continue;
        }

        if ( ! empty( $country_ids ) ) {
            wp_set_post_terms( $offer_id, $country_ids, 'olive_country', false );
        }
        if ( ! empty( $type_ids ) ) {
            wp_set_post_terms( $offer_id, $type_ids, 'olive_supplier_type', false );
        }

        update_post_meta( $offer_id, 'olive_supplier_id', (string) $supplier_id );
        update_post_meta( $offer_id, 'olive_verified', (string) get_post_meta( $supplier_id, 'olive_verified', true ) );
        update_post_meta( $offer_id, 'olive_lat', (string) get_post_meta( $supplier_id, 'olive_lat', true ) );
        update_post_meta( $offer_id, 'olive_lng', (string) get_post_meta( $supplier_id, 'olive_lng', true ) );
    }

    $rfq_titles = array(
        'Need Bulk EVOO Supplier',
        'Looking for Private Label Partner',
        'Request for Bottling Service',
        'Seeking Certified Organic Producer',
        'Request for Distribution Partner',
    );

    for ( $i = 1; $i <= 18; $i++ ) {
        $country_slugs = array_keys( $countries );
        $country_slug  = $country_slugs[ array_rand( $country_slugs ) ];
        $rfq_title     = $rfq_titles[ array_rand( $rfq_titles ) ];

        $rfq_id = wp_insert_post(
            array(
                'post_type'    => 'olive_rfq',
                'post_status'  => 'publish',
                'post_title'   => sprintf( '%s #%d', $rfq_title, $i ),
                'post_excerpt' => 'Seeded RFQ summary.',
                'post_content' => 'Seeded RFQ content.',
            )
        );

        if ( is_wp_error( $rfq_id ) || ! $rfq_id ) {
            continue;
        }

        wp_set_post_terms( $rfq_id, array( $country_slug ), 'olive_country', false );
    }

    $search_page = get_page_by_path( 'marketplace-search' );
    if ( ! $search_page ) {
        wp_insert_post(
            array(
                'post_type'    => 'page',
                'post_status'  => 'publish',
                'post_title'   => 'Marketplace Search',
                'post_name'    => 'marketplace-search',
                'post_content' => '[oliveb2b_search_results]',
            )
        );
    }

    if ( defined( 'WP_CLI' ) && WP_CLI ) {
        WP_CLI::success( 'Seed complete: suppliers, offers, RFQs, and search page created.' );
    }
}
