<?php

require_once plugin_dir_path(__FILE__) . 'admin-handle-rooms.php';
require_once plugin_dir_path(__FILE__) . 'admin-handle-bookings.php';
require_once plugin_dir_path(__FILE__) . 'admin-show-schedule.php';
require_once plugin_dir_path(__FILE__) . 'admin-show-and-delete-bookings.php';


class AdminUI{
    public function __construct(){
        add_action('admin_menu', array($this, 'add_admin_menus'));
        $this->adminShowSchedule = new AdminShowShedule();
        new AdminHandleBookings();
        new AdminShowAndDeleteBookings();
        new AdminHandleRooms();
    }

    //Lägger till huvudmenyn
    public function add_admin_menus(){
            add_menu_page(
                $plugin_name, // Sidtitel för title tags
                'TonTid', //Menynamn
                'manage_options', //Capability 
                'tontid',
                array($this->adminShowSchedule, 'display_handle_booking_page'), // Callback
                'dashicons-format-audio', // Dashicon
                2, // position
        );
    }
}
