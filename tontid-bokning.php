<?php
/**
 * Plugin Name: Tontid, bokning av musikrum
 * Description: Ett plugin för att boka musikrum.
 * Version: 1.0
 * Author: Peter Nydahl
 */

if ( ! defined( 'ABSPATH' ) ) {
    die( 'You are not allowed to call this page directly.' );
}

// Inkludera filen med admin-klassen
require_once plugin_dir_path( __FILE__ ) . 'admin/admin-functions.php';

// Inkludera databas-hanteringsfilen
require_once plugin_dir_path( __FILE__ ) . 'db/db-functions.php';

//Lägg till tabell för rum och bokningar vid aktivering av pluginet (om dessa inte redan finns sedan innan)
//dessa registreringsmetoder ligger i db-functions.php 
register_activation_hook(__FILE__, 'tontid_create_music_rooms_table'); 
register_activation_hook(__FILE__, 'tontid_create_bookings_table');

// Skapa en instans av admin-klassen för att initiera admin-menyn och hooks
if ( is_admin() ) {
    new TontidAdmin();
}

