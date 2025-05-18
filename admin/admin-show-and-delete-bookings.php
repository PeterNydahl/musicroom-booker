<?php

class AdminShowAndDeleteBookings {
    public function __construct() {
        add_action('admin_menu', array($this, 'add_show_and_delete_bookings_menu'));
        add_action('admin_post_tontid_show_bookings', array($this, 'handle_room_filter_submit'));
        add_action('admin_post_tontid_delete_booking', array($this, 'handle_delete_booking'));
    }

    /**
     * Lägg till menyflik i admin
     */
    public function add_show_and_delete_bookings_menu() {
        add_submenu_page(
            'tontid',
            'Visa och ta bort bokningar',
            'Visa/ta bort bokningar',
            'manage_options',
            'tontid-show-and-delete-bookings',
            array($this, 'display_show_and_delete_bookings_page')
        );
    }

    public function display_show_and_delete_bookings_page() {
        $this->display_select_room();
        $this->display_filtered_bookings();
    }

    public function message_displayer(){
        if(isset($_GET['message']) && $_GET['message']=='booking_deleted')
            TonTidUtils::show_notice_booking_was_deleted();
        if(isset($_GET['error']) && $_GET['error']=='delete_booking_failed')
            TonTidUtils::show_notice_delete_booking_failed();
    }

    /**
     * Visa alla bokningar + ta bort-knappar + rum-filtrering
     */
    public function display_select_room() { 

        
        //admin UI - visa alla bokningar alternativt ett valt rum
        //hämta alla rums id från databasen
        global $wpdb;
        $table_name = $wpdb->prefix . 'tontid_music_rooms';
        $rooms = $wpdb->get_results("SELECT room_id FROM $table_name");
        ?>
        
        <div class="wrap">

            <h1>Visa/ta bort bokningar</h1>
            <form action="<?php echo esc_url(admin_url('admin-post.php'));?>" method="get">
                
                <?php
            $this->message_displayer();
            ?>
            <input type="hidden" name="action" value="tontid_show_bookings">
            <table class="form-table select-room-for-booking-filtering" style="width:auto">
                <tr>
                    <td>
                        <!-- <label for="filter_by_room"><strong>Välj rum</strong></label>         -->
                        <select name="selected_room" id="filter_by_room">
                            <option value="">Välj rum</option>
                            <option value="alla_rum">- alla rum -</option>
                            <?php
                                        foreach ($rooms as $room) {
                                            echo "<option value='{$room->room_id}'>{$room->room_id}</option>";
                                        }
                                        ?>                            
                                    </select>
                                </td>
                                <td style="vertical-align: bottom;">
                                    <?php submit_button('Visa bokningar', 'primary', 'submit', false); ?>
                                </td>
                            </tr>
                        </table>
                    </form>
        </div>
        <?php



}

public function handle_room_filter_submit() {
    if (isset($_GET['selected_room'])) {
        $selected_room = sanitize_text_field($_GET['selected_room']);
    } else {
        $selected_room = 'alla_rum';
    }
    
    // Skicka tillbaka till admin-sidan med rum som parameter
    $redirect_url = add_query_arg(array(
            'page' => 'tontid-show-and-delete-bookings',
            'selected_room' => $selected_room,
        ), admin_url('admin.php'));

        wp_redirect($redirect_url);
        exit;
    }

    public function display_filtered_bookings(){
                // Om ett rum är valt, hämta bokningar för det rummet
        if(isset($_GET['selected_room'])){
            $selected_room = sanitize_text_field($_GET['selected_room']);
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'tontid_bookings';
            if ($selected_room === 'alla_rum') {
                $bookings = $wpdb->get_results(
                "SELECT * FROM $table_name ORDER BY booking_start"
            );
        } else {
            $bookings = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM $table_name WHERE room_id = %s ORDER BY booking_start",
                    $selected_room
                )
            );
        }

        if($bookings){
            echo "<h2>Visar bokningar för rum: $selected_room</h2>";
            echo "<table class='form-table delete-bookings-table'>";
            echo "<tr>
                    <th>Rum</th>
                    <th>Lektion</th>
                    <th>Starttid</th>
                    <th>Sluttid</th>
                    <th>Ta bort</th>
                </tr>";
            foreach($bookings as $booking){
                echo "<tr>
                    <td>{$booking->room_id}</td>
                    <td>{$booking->lesson}</td>
                    <td>{$booking->booking_start}</td>
                    <td>{$booking->booking_end}</td>
                    <td>
                        <form action='" . esc_url(admin_url('admin-post.php')) . "' method='post'>
                            <input type='hidden' name='action' value='tontid_delete_booking'>
                            <input type='hidden' name='booking_to_delete_id' value='{$booking->booking_id}' />
                            <input type='submit' value='Ta bort' class='delete-button'/>
                        </form>
                    </td>
                </tr>";
            }
            echo "</table>";
        }
    }

    public function handle_delete_booking(){
        global $wpdb;
        $table_name = $wpdb->prefix . "tontid_bookings";
        if(isset($_POST["booking_to_delete_id"])){
            $booking_id = intval($_POST["booking_to_delete_id"]);
        };
        $result = $wpdb->delete(
            $table_name,
            array( 'booking_id'=>$booking_id ),
            array( '%d')
        );
        if($result){
            $url = add_query_arg(
                'message',
                'booking_deleted',
                admin_url('admin.php?page=tontid-show-and-delete-bookings')
            );

            wp_redirect($url);
            exit;
        } else {
            $url = add_query_arg(
                'error',
                'delete_booking_failed',
                admin_url('admin.php?page=tontid-show-and-delete-bookings')
            );
        };
    }
}
