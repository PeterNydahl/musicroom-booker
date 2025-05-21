<?php

// Regga action hook för ny REST API endpoint 
add_action('rest_api_init', function() {
    register_rest_route('tontid/v1', '/rooms', [
        'methods' => 'GET',
        'callback' => 'tontid_get_rooms', 
        'permission_callback' => '__return_true'
    ]);
});

// Callback funktion för anrop mot db och hantering av svar
function tontid_get_rooms($request){
    global $wpdb;
    
    $table_name = $wpdb->prefix . "tontid_music_rooms";
    // $query = "SELECT room_id FROM $table_name ORDER BY room_id ASC";
    $query = "SELECT * FROM $table_name";
    
    $rooms = $wpdb->get_results($query, ARRAY_A);
    
    if(empty($rooms)){
        return new WP_REST_Response([
            'status' => 'error',
            'message' => "Det finns inte några rum!"
        ], 400);
    };

    return new WP_REST_Response([
        'status' => 'success',
        'data' => $rooms
    ], 200);
}