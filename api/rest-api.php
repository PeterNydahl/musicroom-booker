<?php

add_action('rest_api_init', function () {
    register_rest_route('tontid/v1', '/bokningar', array(
        'methods' => 'GET',
        'callback' => 'tontid_rest_get_bookings',
        'permission_callback' => '__return_true' // OBS: öppen för alla! Justera sen för säkerhet.
    ));
});

function tontid_rest_get_bookings() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'tontid_bokningar';

    $results = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);

    return rest_ensure_response($results);
}
