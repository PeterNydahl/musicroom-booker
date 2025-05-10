<?php

class TontidAdmin {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'registerAdminMenus' ) );
        add_action( 'admin_post_tontid_add_room', 'tontid_handle_add_room_form' );
        add_action( 'admin_post_tontid_delete_room', 'tontid_handle_delete_room' );
    }

/*************************************************************************************
                          Meny & UI för pluginet i dashboard
************************************************************************************/

public function registerAdminMenus() {
    $plugin_name = 'tontid';

    add_menu_page(
        $plugin_name, // Sidtitel för title tags
        'TonTid', //Menynamn
        'manage_options', //Capability 
        'tontid', //slug - samma som första undermenyn
        array($this, 'displayAllRooms'), // Callback
        'dashicons-format-audio', // Dashicon
        2, // position
    );

    add_submenu_page(
        'tontid', // slug till parent
        'Alla rum', // title (för browser tab)
        'Alla rum', // namn i menyn 
        'manage_options', // En såkallad "capability" av kraftig sort som tex administratör besitter
        'tontid-see-all-rooms', // slug
        array($this, 'displayAllRooms'), // Callback funktion för att generera UI
        '0' // plats i menyn (valfritt)
    );

    add_submenu_page(
        'tontid',
        'Hantera rum',
        'Hantera rum',
        'manage_options',
        'tontid-manage-rooms',
        array($this, 'displayManageRooms'),
    );

    add_submenu_page(
        'tontid',                     // Parent slug
        'Skapa bokning',             // Sidtitel
        'Skapa bokning',             // Menynamn
        'manage_options',             // Capability
        'tontid-createbooking',         // Slug
        array( $this, 'displayCreateBookingPage' ) // Callback
    );
    // FÖrhindra att huvudmenyn dyker upp som submeny (vilket sker per default)
    remove_submenu_page( 'tontid', 'tontid' );

}

/* admin UI för att lägga till ett rum
------------------------------------------------------------------------------------*/
    public function displayAddRoomPage() {
        ?>
        <div class="wrap">
            <h1>Lägg till ett rum</h1>

            <?php
            if ( isset( $_GET['error'] ) && $_GET['error'] === 'duplicate_id' ) {
                echo '<div class="notice notice-error is-dismissible"><p><strong>Fel:</strong> Rums-ID:t du angav finns redan. Välj ett annat ID.</p></div>';
            } elseif ( isset( $_GET['message'] ) && $_GET['message'] === 'room_added' ) {
                echo '<div class="notice notice-success is-dismissible"><p><strong>Ett nytt rum har lagts till!</strong></p></div>';
            } elseif ( isset( $_GET['error'] ) && $_GET['error'] === 'database_error' ) {
                echo '<div class="notice notice-error is-dismissible"><p><strong>Fel:</strong> Rummet kunde tyvärr inte läggas till.</p></div>';
            } elseif ( isset( $_GET['error'] ) && $_GET['error'] === 'missing_id' ) {
                echo '<div class="notice notice-warning is-dismissible"><p><strong>Varning:</strong> Det verkar som att du glömde fylla i ett ID!</p></div>';
            }
            ?>

            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <input type="hidden" name="action" value="tontid_add_room">
                <?php wp_nonce_field( 'tontid_add_room_nonce', 'tontid_add_room_nonce' ); ?>

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="room_id">Rums-ID</label>
                        </th>
                        <td>
                            <input type="text" id="room_id" name="room_id" value="" class="regular-text" required>
                            <p class="description">Ange ett unikt ID för musiksalen.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="equipment">Utrustning</label>
                        </th>
                        <td>
                            <input type="checkbox" id="pa_system" name="equipment[]" value="pa system">
                            <label for="pa_system">PA System</label><br>
                            <input type="checkbox" id="monitors" name="equipment[]" value="monitors">
                            <label for="monitors">Monitorer</label><br>
                            <input type="checkbox" id="piano" name="equipment[]" value="piano">
                            <label for="piano">Piano</label><br>
                            <input type="checkbox" id="synth" name="equipment[]" value="synth">
                            <label for="synth">Synth</label><br>
                            <p class="description">Välj den tillgängliga utrustningen i detta rum.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="room_description">Beskrivning</label>
                        </th>
                        <td>
                            <input type="text" id="room_description" name="room_description" value="" class="regular-text" required>
                            <p class="description">Här kan du ange extra information om rummet!</p>
                        </td>
                    </tr>
                
                </table>
                

                <?php submit_button( 'Lägg till rum' ); ?>
            </form>
        </div>
        <?php
    }

    public function displayCreateBookingPage() {
        echo '<h1>' . esc_html( get_admin_page_title() ) . '</h1>';
        echo '<p>Här kan du registrera en ny bokning.</p>';
    }



/* UI för att ta bort ett rum
------------------------------------------------------------------------------------*/
public function displayDeleteRoomPage() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'tontid_music_rooms';
    $rooms = $wpdb->get_results( "SELECT room_id FROM $table_name" ); // Hämta ID och namn på rum
    ?>
    <div class="wrap">
        <h1>Ta bort ett rum</h1>
        <?php if ( isset( $_GET['message'] ) && $_GET['message'] === 'room_deleted' ) : ?>
            <div class="notice notice-success is-dismissible">
                <p><strong>Rummet har tagits bort!</strong></p>
            </div>
        <?php elseif ( isset( $_GET['error'] ) && $_GET['error'] === 'delete_failed' ) : ?>
            <div class="notice notice-error is-dismissible">
                <p><strong>Fel:</strong> Kunde inte ta bort rummet.</p>
            </div>
        <?php elseif ( isset( $_GET['error'] ) && $_GET['error'] === 'missing_id' ) : ?>
            <div class="notice notice-warning is-dismissible">
                <p><strong>Varning:</strong> Välj ett rum att ta bort.</p>
            </div>
        <?php endif; ?>

        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <input type="hidden" name="action" value="tontid_delete_room">
            <?php wp_nonce_field( 'tontid_delete_room_nonce', 'tontid_delete_room_nonce' ); ?>

            <table class="form-table">
                <tr class="row-room-to-delete">
                    <th scope="row">
                        <label for="room_id_to_delete">Välj rum att ta bort</label>
                    </th>
                    <td>
                        <select name="room_id_to_delete" id="room_id_to_delete" required>
                            <option value="">Välj ett rum</option>
                            <?php if ( ! empty( $rooms ) ) : ?>
                                <?php foreach ( $rooms as $room ) : ?>
                                    <option value="<?php echo esc_attr( $room->room_id ); ?>">
                                        <?php echo esc_html( $room->room_id ) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <option value="" disabled>Inga rum har lagts till ännu</option>
                            <?php endif; ?>
                        </select>
                        <p class="description">Välj det rum du vill ta bort.</p>
                    </td>
                </tr>
            </table>

            <?php if ( ! empty( $rooms ) ) : ?>
                <?php submit_button( 'Ta bort valt rum', 'delete', 'submit', false ); ?>
            <?php endif; ?>
        </form>
    </div>
    <?php
}

/* admin UI för att hantera rum (lägga till rum + ta bort rum)
------------------------------------------------------------------------------------*/
public function displayManageRooms(){
    $this->displayAddRoomPage();
    $this->displayDeleteRoomPage();
} 


/* admin UI för att se alla rum 
------------------------------------------------------------------------------------*/
public function displayAllRooms() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'tontid_music_rooms';
    $rooms = $wpdb->get_results("SELECT * FROM $table_name");

    echo '<div class="wrap">';
    echo '<h1>' . esc_html(get_admin_page_title()) . '</h1>';
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>Rums-ID</th>';
    echo '<th>Utrustning</th>';
    echo '<th>Beskrivning</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    if (!empty($rooms)) {
        foreach ($rooms as $room) {
            echo '<tr>';
            echo '<td>' . esc_html($room->room_id) . '</td>';
            echo '<td>' . esc_html($room->room_equipment) . '</td>';
            echo '<td>' . esc_html($room->room_description) . '</td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="2">Inga rum har lagts till ännu.</td></tr>';
    }

    // echo '</tbody>';
    // echo '<tfoot>';
    // echo '<tr>';
    // echo '<th>Rums-ID</th>';
    // echo '<th>Utrustning</th>';
    // echo '<th>Beskrivning</th>';
    // echo '</tr>';
    // echo '</tfoot>';
    // echo '</table>';
    // echo '</div>';
}


}

