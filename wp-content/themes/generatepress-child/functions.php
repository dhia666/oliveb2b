<?php
/**
 * GeneratePress Child theme setup.
 */

add_action( 'wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'generatepress-child',
        get_stylesheet_uri(),
        array( 'generatepress-style' ),
        '0.1.0'
    );
} );
