<?php
/**
 * Plugin Name: OliveB2B Core
 * Description: Core functionality for OliveB2B (CPTs, taxonomies, search UI, language switcher).
 * Version: 0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

const OLIVEB2B_VERSION = '0.1.0';

add_action( 'init', 'oliveb2b_register_cpts' );
add_action( 'init', 'oliveb2b_register_taxonomies' );
add_action( 'init', 'oliveb2b_register_shortcodes' );
add_action( 'wp_enqueue_scripts', 'oliveb2b_enqueue_assets' );
add_action( 'generate_before_header', 'oliveb2b_maybe_render_language_switcher' );

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
        array( 'olive_supplier' ),
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
    $current = oliveb2b_get_current_language_code();

    $items = array();
    foreach ( $languages as $lang ) {
        $is_current = $current === $lang['code'];
        $label = $lang['label'];
        $url = apply_filters( 'oliveb2b_language_url', home_url( '/' . $lang['code'] . '/' ), $lang );
        $dir = isset( $lang['dir'] ) ? $lang['dir'] : 'ltr';
        $items[] = sprintf(
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

    return apply_filters( 'oliveb2b_language_list', $languages );
}

function oliveb2b_get_current_language_code() {
    $path = trim( wp_parse_url( home_url( add_query_arg( array() ) ), PHP_URL_PATH ), '/' );
    $segments = explode( '/', $path );
    $candidate = isset( $segments[0] ) ? $segments[0] : 'en';
    $codes = array_map(
        function ( $lang ) {
            return $lang['code'];
        },
        oliveb2b_get_languages()
    );

    return in_array( $candidate, $codes, true ) ? $candidate : 'en';
}

function oliveb2b_search_results_shortcode() {
    $search_term = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
    $country = isset( $_GET['country'] ) ? sanitize_text_field( wp_unslash( $_GET['country'] ) ) : '';
    $supplier_type = isset( $_GET['supplier_type'] ) ? sanitize_text_field( wp_unslash( $_GET['supplier_type'] ) ) : '';
    $employees = isset( $_GET['employees'] ) ? sanitize_text_field( wp_unslash( $_GET['employees'] ) ) : '';
    $verified = isset( $_GET['verified'] ) ? sanitize_text_field( wp_unslash( $_GET['verified'] ) ) : '';
    $radius = isset( $_GET['radius'] ) ? sanitize_text_field( wp_unslash( $_GET['radius'] ) ) : '';

    $offers = oliveb2b_run_search_query( 'olive_offer', $search_term, $country, $verified );
    $suppliers = oliveb2b_run_search_query( 'olive_supplier', $search_term, $country, $verified, $supplier_type, $employees );

    ob_start();
    ?>
    <section class="oliveb2b-search">
        <div class="oliveb2b-search-bar">
            <form method="get">
                <input type="search" name="s" placeholder="Search suppliers or offers" value="<?php echo esc_attr( $search_term ); ?>" />
                <button type="submit">Search</button>
            </form>
        </div>
        <div class="oliveb2b-search-grid">
            <aside class="oliveb2b-filters">
                <h3>Filters</h3>
                <label>
                    Country
                    <?php echo oliveb2b_taxonomy_select( 'olive_country', 'country', $country ); ?>
                </label>
                <label>
                    Near me radius (km)
                    <input type="number" name="radius" value="<?php echo esc_attr( $radius ); ?>" placeholder="e.g. 25" />
                </label>
                <label>
                    Supplier type
                    <?php echo oliveb2b_taxonomy_select( 'olive_supplier_type', 'supplier_type', $supplier_type ); ?>
                </label>
                <label>
                    Employees
                    <?php echo oliveb2b_taxonomy_select( 'olive_employees', 'employees', $employees ); ?>
                </label>
                <label class="oliveb2b-checkbox">
                    <input type="checkbox" name="verified" value="1" <?php checked( $verified, '1' ); ?> />
                    Verified
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
    </section>
    <?php
    wp_reset_postdata();
    return ob_get_clean();
}

function oliveb2b_run_search_query( $post_type, $search_term, $country, $verified, $supplier_type = '', $employees = '' ) {
    $tax_query = array();
    if ( $country ) {
        $tax_query[] = array(
            'taxonomy' => 'olive_country',
            'field' => 'slug',
            'terms' => array( $country ),
        );
    }
    if ( $supplier_type ) {
        $tax_query[] = array(
            'taxonomy' => 'olive_supplier_type',
            'field' => 'slug',
            'terms' => array( $supplier_type ),
        );
    }
    if ( $employees ) {
        $tax_query[] = array(
            'taxonomy' => 'olive_employees',
            'field' => 'slug',
            'terms' => array( $employees ),
        );
    }
    if ( count( $tax_query ) > 1 ) {
        $tax_query['relation'] = 'AND';
    }

    $meta_query = array();
    if ( $verified ) {
        $meta_query[] = array(
            'key' => 'olive_verified',
            'value' => '1',
            'compare' => '=',
        );
    }

    return new WP_Query(
        array(
            'post_type' => $post_type,
            's' => $search_term,
            'posts_per_page' => 20,
            'tax_query' => $tax_query,
            'meta_query' => $meta_query,
        )
    );
}

function oliveb2b_taxonomy_select( $taxonomy, $name, $selected ) {
    $terms = get_terms(
        array(
            'taxonomy' => $taxonomy,
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
        $is_logged_in = is_user_logged_in();
        $title = get_the_title();
        $excerpt = get_the_excerpt();

        if ( ! $is_logged_in && $type === 'supplier' ) {
            $title = 'Supplier profile (login to view)';
        }

        $cards .= sprintf(
            '<article class="oliveb2b-card">
                <h4>%1$s</h4>
                <p>%2$s</p>
                <a href="%3$s">View details</a>
            </article>',
            esc_html( $title ),
            esc_html( $excerpt ? $excerpt : 'Summary available after login.' ),
            esc_url( get_permalink() )
        );
    }

    return '<div class="oliveb2b-card-grid">' . $cards . '</div>';
}
