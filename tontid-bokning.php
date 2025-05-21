<?php
/**
 * Plugin Name: Tontid, bokning av musikrum
 * Description: Ett plugin för att boka musikrum.
 * Version: 1.0
 * Author: Peter Nydahl
 */

// INKLUDERAR API ENDPOINTS     
add_action('plugins_loaded', function () {
    require_once plugin_dir_path(__FILE__) . 'api/get-bookings.php';
    require_once plugin_dir_path(__FILE__) . 'api/get-rooms.php';
});


/* LADDA IN MEDDELANDEN & KONTROLLFUNKTIONER
-----------------------------------------------------*/
require_once plugin_dir_path(__FILE__) . 'includes/messages-and-checks.php';
// (funktionen nedan hindrar användaren att ha direkt tillgång till filen om inte ABSPATH är definierad)
TonTidUtils::abspath_required();



/*SKAPA ADMIN UI
-----------------------------------------------------*/
require_once plugin_dir_path(__FILE__) . 'admin/admin-ui.php';
// Om användare är admin - skapa en instans av admin-klassen för att initiera admin-menyn och hooks
if ( is_admin() ) {
    new AdminUI();
}

/* IMPORTERA FILER SOM HANTERAR RUM OCH BOKNINGAR
* Så att de blir del av detta scope och kan kommunicera med messages-and-checks.php
-----------------------------------------------------*/
require_once plugin_dir_path(__FILE__) . 'admin/admin-handle-rooms.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin-functions.php';

/*SKAPA TABELLER NÄR PLUGINET AKTIVERAS
-----------------------------------------------------*/
require_once plugin_dir_path(__FILE__) . 'includes/create-tables.php';
register_activation_hook(__FILE__, 'tontid_create_music_rooms_table'); 
register_activation_hook(__FILE__, 'tontid_create_bookings_table');
register_activation_hook(__FILE__, 'tontid_create_mock_rooms');
register_activation_hook(__FILE__, 'tontid_create_mock_bookings');

/* LADDA IN JACASCRIPT-BIBLIOTEK FÖR VISNING AV KALENDER FÖR BOKNING I ADMINPANELEN
-----------------------------------------------------*/
require_once plugin_dir_path( __FILE__ ) . 'includes/flatpickr-calender-setup.php';
add_action( 'admin_enqueue_scripts', 'tontid_enqueue_flatpickr' );

/* LADDA IN STYLE-FIL MED CSS TILL ADMIN UI */
add_action('admin_enqueue_scripts', 'tontid_enqueue_admin_styles');

function tontid_enqueue_admin_styles() {
    wp_enqueue_style(
        'tontid-admin-style',
        plugin_dir_url(__FILE__) . 'assets/admin/admin-style.css',
        array(),
        '1.0.0'
    );
}
