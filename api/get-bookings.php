<?php

add_action('rest_api_init', function(){
    register_rest_route('tontid/v1', '/bookings', [
        'methods' => 'GET',
        'callback' => 'tontid_get_bookings', 
        'permission_callback' => '__return_true',
    ]);
});

function tontid_get_bookings($request) {
   global $wpdb;
   $table_name = $wpdb->prefix . 'tontid_bookings';

    $room_id = $request->get_param('room');
    $selected_week = $request->get_param('week');
    $year = date('Y');
    
    if (!$room_id) {
        return new WP_REST_Response([
            'status' => 'error',
            'message' => 'Det fanns inget rum i query parametern!'
        ], 400);
    }
    
    // Hämta alla bokningar för rummet
    $bookings = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE room_id = %s", $room_id));

    // generera meddelande om inga bokningar hittades
    if (empty($bookings)) {
        return new WP_REST_Response([
            'status' => 'error',
            'message' => 'Inga bokningar hittades för det angivna rummet.'
        ], 400);
    }
    
    //returnera samtliga rum om ingen vecka specificerats
    if(!$selected_week) {
        return new WP_REST_Response([
            'status' => 'success',
            'data' => $bookings
        ], 200);
    }


    // sortera ut bokningar av rummet baserat på vald vecka
        $monday_first_hour = (new DateTime())->setISODate($year, (int)$selected_week, 1)->setTime(8, 0)->getTimestamp();
        $friday_last_hour = (new DateTime())->setISODate($year, (int)$selected_week, 5)->setTime(20, 0)->getTimestamp();

        $selected_week_bookings = [];
        foreach($bookings as $booking){
            $booking_timestamp = (new DateTime($booking->booking_start))->getTimestamp();
            if ($booking_timestamp >= $monday_first_hour && $booking_timestamp <= $friday_last_hour)
                $selected_week_bookings[] = $booking;
        }
   
   /* sista potentiella responerna!
   -----------------------------------------*/
    if (empty($selected_week_bookings)){
        return new WP_REST_Response([
            'status' => 'error',
            'message' => 'Inga bokningar gjorda för rummet denna vecka!'
        ], 400);
    }

    return new WP_REST_Response([
        'status' => 'success',
        'data' => $selected_week_bookings
    ], 200);


}
