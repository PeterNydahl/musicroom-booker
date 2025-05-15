<?php

class TonTidUtils {
    /*************************************************************************************
                                        AVISERINGSSÄNDARE
    *************************************************************************************/
    /* VARNINGSMEDDELANDEN ⚠️☢️⚡
    ---------------------------------------------------- */
    public static function show_notice_missing_id(){
        echo '<div class="notice notice-warning is-dismissible"><p><strong>Varning:</strong> Det verkar som att du glömde fylla i ett ID!</p></div>';
    }
    
    /* FELMEDDELANDEN 🤦‍♂️🤬
    ---------------------------------------------------- */
    
    public static function show_notice_error(){
        echo '<div class="notice notice-error is-dismissible"><p><strong>Fel:</strong> Något gick fel.</p></div>';
    }
    public static function show_notice_id_already_exists() {
        echo '<div class="notice notice-error is-dismissible"><p><strong>Fel:</strong> Rums-ID:t du angav finns redan. Välj ett annat ID.</p></div>';
    }
    
    public static function show_notice_room_not_added(){
        echo '<div class="notice notice-error is-dismissible"><p><strong>Fel:</strong> Rummet kunde tyvärr inte läggas till.</p></div>';
    }
    
    public static function show_notice_missing_fields(){
        echo '<div class="notice notice-error is-dismissible"><p><strong>Fel:</strong> Du verkar inte ha fyllt i alla fält! Var vänlig försök igen.</p></div>';
    }

    public static function show_notice_end_before_start(){
        echo '<div class="notice notice-error is-dismissible"><p><strong>Fel:</strong> Sluttid får inte vara före starttid! Var vänlig försök igen.</p></div>';
    }

    public static function show_notice_no_bookings_found(){
        echo '<div class="notice notice-error is-dismissible"><p><strong>Fel:</strong> Inga bokningar hittades! Vänligen försök igen. </p></div>';
    }

    /* MEDDELANDEN OM LYCKA & FRAMGÅNG 🖖
    ---------------------------------------------------- */
    public static function show_notice_room_was_added(){
        echo '<div class="notice notice-success is-dismissible"><p><strong> Ett nytt rum har lagts till!</strong></p></div>';
    }

    public static function show_notice_room_was_deleted(){
        echo '<div class="notice notice-success is-dismissible"><p><strong> Rummet har tagits bort!</strong></p></div>';
    }

    public static function show_notice_booking_added(){
        echo '<div class="notice notice-success is-dismissible"><p><strong> Din bokning har lagts till!</strong></p></div>';
    }

    
    /*************************************************************************************
                                     KONTROLLFUNKTIONER 🧐
    *************************************************************************************/
    public static function abspath_required(){
        if ( ! defined( 'ABSPATH' ) ) {
            wp_die( esc_html__('Direktåtkomst till denna fil är inte tillåten.', 'tontid-booking') );
        }
    }

}


