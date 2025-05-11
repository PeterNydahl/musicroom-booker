<?php
// Förhindra direkt åtkomst till filen
if ( ! defined( 'ABSPATH' ) ) {
    die( 'Direct access forbidden.' );
}

require_once ABSPATH . 'wp-admin/includes/upgrade.php';

/*************************************************************************************
                    SKAPA TABELLER VID AKTIVERING AV PLUGIN
 ************************************************************************************/

/* Skapa tabell för musiksalar (anropas när pluginet aktiveras via tontid-bokning.php)
-------------------------------------------------------------------------------------*/
function tontid_create_music_rooms_table() {
    global $wpdb;
    
    // Tabellnamn (inklusive prefix från WordPress)
    $table_name = $wpdb->prefix . 'tontid_music_rooms';
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        room_id VARCHAR(20) NOT NULL,    
        room_description VARCHAR(100) DEFAULT NULL,
        room_equipment TEXT DEFAULT NULL,
        PRIMARY KEY (room_id)
    ) $charset_collate;";
    dbDelta($sql); // Skapar tabellen om den inte finns
}

/* Skapa tabell för bokningar (anropas när pluginet aktiveras via tontid-bokning.php)
-------------------------------------------------------------------------------------*/

function tontid_create_bookings_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . "tontid_bookings";
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        booking_id BIGINT NOT NULL AUTO_INCREMENT,
        room_id VARCHAR(20) NOT NULL,
        user_id BIGINT NOT NULL,
        booking_start DATETIME NOT NULL,
        booking_end DATETIME NOT NULL,
        booking_status VARCHAR(20) NOT NULL DEFAULT 'Pending',
        PRIMARY KEY (booking_id)
    ) $charset_collate;";

    dbDelta($sql);
}




/*************************************************************************************
              Lägga till ny bokning baserad på input från admin dashboard
 ************************************************************************************/
function tontid_handle_add_booking() {
    // Avbryter om formuläret saknar värden eller om nonce är ogiltig (säkerhetsåtgärd i WordPress)
    if ( ! isset( $_POST['tontid_add_booking_nonce'] ) || ! wp_verify_nonce( $_POST['tontid_add_booking_nonce'], 'tontid_add_booking_nonce' ) ) {
        wp_die('Ogiltig eller utgången säkerhetsnyckel! Försök igen.');
    }

    // Om formuläret har värden
    if (
        isset( $_POST['room_id'] ) && ! empty( $_POST['room_id'] ) &&
        isset( $_POST['booking_start'] ) && ! empty( $_POST['booking_start'] ) &&
        isset( $_POST['booking_end'] ) && ! empty( $_POST['booking_end'] )
    ) {
        // Sanera användardata
        $room_id = sanitize_text_field( $_POST['room_id'] );
        $booking_start = sanitize_text_field( $_POST['booking_start'] );
        $booking_end = sanitize_text_field( $_POST['booking_end'] );

        // Ogiltigt datum/tid, hantera fel
        if ( strtotime( $booking_start ) === false || strtotime( $booking_end ) === false ) {
            wp_die( 'Ogiltigt värde för datum/tid' );
        }

        // Sätt upp global WP-databasanslutning
        global $wpdb;

        // hämta aktuell anvädares id nummer i databasen
        $user_id = get_current_user_id();

        // Förbered SQL-frågan och anropa databasen
        $result = $wpdb->insert(
            $wpdb->prefix . 'tontid_bookings',
            array(
                'room_id' => $room_id,
                'user_id' => $user_id,
                'booking_start' => $booking_start,
                'booking_end' => $booking_end
            ),
            array(
                '%s',  // room_id
                '%d',  // user_id
                '%s',  // booking_start
                '%s'   // booking_end
            )
        );

        // Kontrollera om insättningen lyckades
        if ( false === $result ) {
            wp_die( 'Kunde inte lägga till bokningen i databasen' );
        }
        // Återkoppla till en annan sida eller visa ett meddelande
    //     wp_redirect( admin_url( 'admin.php?page=tontid-handle-bookings' ) );  // Exempel på omdirigering
    //     exit;
    // } else {
        // Om några värden saknas
    //     wp_die( 'Alla fält måste fyllas i.' );
    // }
}


/*************************************************************************************
               Lägga till nytt rum baserat på input från admin dashboard
 ************************************************************************************/

function tontid_handle_add_room_form() {
    // Verifiera säkerhetsnyckeln som genererats av nonce funktionen
    if ( ! isset( $_POST['tontid_add_room_nonce'] ) || ! wp_verify_nonce( $_POST['tontid_add_room_nonce'], 'tontid_add_room_nonce' ) ) {
        wp_nonce_ays( 'tontid_add_room_nonce' );
        exit;
    }

    if ( isset( $_POST['room_id'] ) && ! empty( $_POST['room_id'] ) ) {
        $room_id = sanitize_text_field( $_POST['room_id'] );
        $equipment = isset( $_POST['equipment'] ) ? array_map( 'sanitize_text_field', $_POST['equipment'] ) : array();
        $room_description = sanitize_text_field( $_POST['room_description'] );

        // kontrollera om rummet redan existerar
        if ( tontid_check_if_room_id_exists( $room_id ) ) {
            // Om ID:t redan finns, skicka tillbaka användaren med ett felmeddelande
            $redirect_url = add_query_arg( 'error', 'duplicate_id', admin_url( 'admin.php?page=tontid-manage-rooms' ) );
            wp_safe_redirect( $redirect_url );
            exit;
        } else {
            // Om ID:t är unikt, lägg till rummet i databasen
            $equipment_string = implode( ', ', $equipment ); // Konvertera utrustnings-array till en sträng
            global $wpdb;
            $table_name = $wpdb->prefix . 'tontid_music_rooms';

            $insert_result = $wpdb->insert(
                $table_name,
                array(
                    'room_id' => $room_id,
                    'room_equipment' => $equipment_string,
                    'room_description' => $room_description
                ),
                array(
                    '%s',
                    '%s',
                    '%s'
                )
            );

            if ( $insert_result ) { // Kontrollera om insert returnerade ett "truthy" värde (antal rader påverkade > 0 vid lyckad insert)
                // Om insättningen lyckades, skicka tillbaka användaren med ett success-meddelande
                $redirect_url = admin_url( 'admin.php?page=tontid-manage-rooms&message=room_added' );
                wp_safe_redirect( $redirect_url );
                exit;
            } else {
                // Om något gick fel med insättningen
                $redirect_url = admin_url( 'admin.php?page=tontid-manage-rooms&error=database_error' );
                wp_safe_redirect( $redirect_url );
                exit;
            }
        }
    } else {
        // Om inget rum-ID skickades
        $redirect_url = admin_url( 'admin.php?page=tontid-manage-rooms&error=missing_id' );
        wp_safe_redirect( $redirect_url );
        exit;
    }
}

/**
 * Checks if a given room ID already exists in the database.
 *
 * @param string $room_id The room ID to check.
 * @return bool True if the ID exists, false otherwise.
 */
function tontid_check_if_room_id_exists( $room_id ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'tontid_music_rooms';
    $result = $wpdb->get_var( $wpdb->prepare( "SELECT room_id FROM $table_name WHERE room_id = %s", $room_id ) );
    return ( $result !== null );
}


/*************************************************************************************
                   Ta bort rum baserat på input från admin dashboard
************************************************************************************/
function tontid_handle_delete_room() {
    // Verifiera säkerhetsnyckeln från nonce
    if ( ! isset( $_POST['tontid_delete_room_nonce'] ) || ! wp_verify_nonce( $_POST['tontid_delete_room_nonce'], 'tontid_delete_room_nonce' ) ) {
        wp_nonce_ays( 'tontid_delete_room_nonce' ); // Visar ett felmeddelande om noncen inte är korrekt
        exit; // Avsluta skriptet
    }

    // Kontrollera om ett rums-ID har skickats med POST-metoden
    if ( isset( $_POST['room_id_to_delete'] ) && ! empty( $_POST['room_id_to_delete'] ) ) {
        // "Sanera" det inkommande rums-ID:t för att förhindra en potentiell SQL-injektion
        $room_id_to_delete = sanitize_text_field( $_POST['room_id_to_delete'] );

        global $wpdb;

        // Ange namnet på databastabellen för musiksalar
        $table_name = $wpdb->prefix . 'tontid_music_rooms';

        $deleted = $wpdb->delete(
            $table_name, // Tabellens namn
            array( 'room_id' => $room_id_to_delete ), // array med de kolumner och värden som ska matchas (WHERE-klausulen) 
            array( '%s' ) //formaten för värdena i WHERE-klausulen ('%s' för sträng)
        );

        // ontrollera om borttagningen lyckades
        if ( $deleted ) {
            // Om borttagningen lyckades, skapa en omdirigerings-URL med ett success-meddelande (meddelandet ligger i admin-functions.php)
            $redirect_url = admin_url( 'admin.php?page=tontid-manage-rooms&message=room_deleted' );
        } else {
            // Om borttagningen misslyckades, skapa en omdirigerings-URL med ett felmeddelande (meddelandet ligger i admin-functions.php)
            $redirect_url = admin_url( 'admin.php?page=tontid-manage-rooms&error=delete_failed' );
        }

        // Utför omdirigeringen tillbaka till sidan för att ta bort rum
        wp_safe_redirect( $redirect_url );
        exit; // Avsluta skriptet efter omdirigeringen

    } else {
        // Om inget rums-ID valdes, skicka tillbaka användaren med ett varningsmeddelande
        $redirect_url = admin_url( 'admin.php?page=tontid-manage-rooms&error=missing_id' );
        wp_safe_redirect( $redirect_url );
        exit; // Avsluta skriptet
    }
}

}