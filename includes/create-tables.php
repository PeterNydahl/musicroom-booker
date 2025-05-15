<?php

require_once ABSPATH . 'wp-admin/includes/upgrade.php';

/*************************************************************************************
                      SKAPA TABELLER VID AKTIVERING AV PLUGIN
          funktionerna anropas via pluginets huvudfil när pluginet aktiveras 
 ************************************************************************************/

/* Skapa tabell för musiksalar 
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

/* Skapa tabell för bokningar 
-------------------------------------------------------------------------------------*/

function tontid_create_bookings_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . "tontid_bookings";
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        booking_id BIGINT NOT NULL AUTO_INCREMENT,
        room_id VARCHAR(20) NOT NULL,
        lesson VARCHAR(20) NOT NULL,
        user_id BIGINT NOT NULL,
        booking_start DATETIME NOT NULL,
        booking_end DATETIME NOT NULL,
        booking_status VARCHAR(20) NOT NULL DEFAULT 'Pending',
        PRIMARY KEY (booking_id)
    ) $charset_collate;";

    dbDelta($sql);
}


/* Skapa mockdata för rum
-------------------------------------------------------------------------------------*/
function tontid_create_mock_rooms() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'tontid_music_rooms';

    $rooms = [
        ['room_id' => '108', 'room_equipment' => 'datorer, mini-keyboards', 'room_description' => 'gemu-sal'],
        ['room_id' => '115', 'room_equipment' => 'Piano, litet PA', 'room_description' => 'Piano och sång-sal'],
        ['room_id' => '113', 'room_equipment' => 'Piano, litet PA', 'room_description' => 'Piano och sång-sal'],
        ['room_id' => '114', 'room_equipment' => 'Piano, litet PA', 'room_description' => 'Piano och sång-sal'],
        ['room_id' => '15',  'room_equipment' => 'trumset, PA-system, gitarr, bas, keyboards, Digital piano, mikrofoner', 'room_description' => 'ensemble-rum'],
    ];

    foreach ($rooms as $room) {
        $wpdb->insert(
            $table_name,
            [
                'room_id' => $room['room_id'],
                'room_equipment' => $room['room_equipment'],
                'room_description' => $room['room_description'],
            ],
            ['%s', '%s', '%s']
        );
    }
}


/* Skapa mockdata för bokningar
-------------------------------------------------------------------------------------*/

function tontid_create_mock_bookings() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'tontid_bookings';
    $user_id = get_current_user_id() ?: 1;

    // Definiera de veckor du vill skapa bokningar för
    $weeks = [20, 21, 22];

    // Definiera bokningar per rum och vecka
    $hardcoded_bookings = [
        '15' => [ // Rums-ID 15
            20 => [ // Vecka 20
                ['start' => '08:00', 'end' => '10:00', 'day' => 1, 'lesson' => 'Rockensembel'], // Måndag
                ['start' => '13:00', 'end' => '14:30', 'day' => 3, 'lesson' => 'Bandrep'],   // Onsdag
                ['start' => '15:00', 'end' => '17:00', 'day' => 5, 'lesson' => 'Gitarrlektion'], // Fredag
                ['start' => '09:30', 'end' => '11:00', 'day' => 2, 'lesson' => 'Samspel'],    // Tisdag
                ['start' => '11:30', 'end' => '13:30', 'day' => 4, 'lesson' => 'Trumlektion'],  // Torsdag
            ],
            21 => [ // Vecka 21
                ['start' => '08:30', 'end' => '10:30', 'day' => 1, 'lesson' => 'Latinensembel'],
                ['start' => '12:30', 'end' => '14:00', 'day' => 3, 'lesson' => 'Percussion'],
                ['start' => '14:30', 'end' => '16:30', 'day' => 5, 'lesson' => 'Baslektion'],
                ['start' => '10:00', 'end' => '11:30', 'day' => 2, 'lesson' => 'Ensemble'],
                ['start' => '13:00', 'end' => '15:00', 'day' => 4, 'lesson' => 'Keyboard'],
            ],
            22 => [ // Vecka 22
                ['start' => '09:00', 'end' => '11:00', 'day' => 1, 'lesson' => 'Klassisk ensembel'],
                ['start' => '13:30', 'end' => '15:00', 'day' => 3, 'lesson' => 'Teori'],
                ['start' => '15:30', 'end' => '17:00', 'day' => 5, 'lesson' => 'Blås'],
                ['start' => '10:30', 'end' => '12:00', 'day' => 2, 'lesson' => 'Musikhistoria'],
                ['start' => '14:00', 'end' => '16:00', 'day' => 4, 'lesson' => 'Komposition'],
            ],
        ],
        '108' => [ // Rums-ID 108
            20 => [
                ['start' => '09:00', 'end' => '11:00', 'day' => 2, 'lesson' => 'Elektronisk musik'],
                ['start' => '14:00', 'end' => '15:30', 'day' => 4, 'lesson' => 'Synt-labb'],
                ['start' => '08:00', 'end' => '09:30', 'day' => 1, 'lesson' => 'Ljuddesign'],
                ['start' => '11:30', 'end' => '13:00', 'day' => 3, 'lesson' => 'Sampling'],
                ['start' => '15:30', 'end' => '17:00', 'day' => 5, 'lesson' => 'Mixning'],
            ],
            21 => [
                ['start' => '10:00', 'end' => '12:00', 'day' => 2, 'lesson' => 'Synth-lab'],
                ['start' => '15:00', 'end' => '16:30', 'day' => 4, 'lesson' => 'Sequencing'],
                ['start' => '09:00', 'end' => '10:30', 'day' => 1, 'lesson' => 'Effektprocessorer'],
                ['start' => '12:30', 'end' => '14:00', 'day' => 3, 'lesson' => 'Modularsynt'],
                ['start' => '16:30', 'end' => '17:00', 'day' => 5, 'lesson' => 'Mastering'],
            ],
            22 => [
                ['start' => '11:00', 'end' => '13:00', 'day' => 2, 'lesson' => 'Midi-programmering'],
                ['start' => '16:00', 'end' => '17:00', 'day' => 4, 'lesson' => 'Live-elektronik'],
                ['start' => '10:00', 'end' => '11:30', 'day' => 1, 'lesson' => 'Digitala ljudverktyg'],
                ['start' => '13:30', 'end' => '15:00', 'day' => 3, 'lesson' => 'Ljudsyntes'],
                ['start' => '08:00', 'end' => '09:30', 'day' => 5, 'lesson' => 'Studioinspelning'],
            ],
        ],
        '113' => [ // Rums-ID 113
            // Lägg till bokningar för vecka 20, 21, 22 här (minst 5 per vecka)
            20 => [
                ['start' => '14:00', 'end' => '16:00', 'day' => 1, 'lesson' => 'Sånglektion'],
                ['start' => '09:00', 'end' => '10:30', 'day' => 3, 'lesson' => 'Vocal coaching'],
                ['start' => '11:00', 'end' => '12:30', 'day' => 5, 'lesson' => 'Sångensemble'],
                ['start' => '15:00', 'end' => '16:30', 'day' => 2, 'lesson' => 'Röstteknik'],
                ['start' => '08:30', 'end' => '10:00', 'day' => 4, 'lesson' => 'Interpretation'],
            ],
            21 => [
                ['start' => '14:30', 'end' => '16:30', 'day' => 1, 'lesson' => 'Sånglektion'],
                ['start' => '09:30', 'end' => '11:00', 'day' => 3, 'lesson' => 'Vocal coaching'],
                ['start' => '11:30', 'end' => '13:00', 'day' => 5, 'lesson' => 'Kör'],
                ['start' => '15:30', 'end' => '17:00', 'day' => 2, 'lesson' => 'Scennärvaro'],
                ['start' => '09:00', 'end' => '10:30', 'day' => 4, 'lesson' => 'Repertoar'],
            ],
            22 => [
                ['start' => '15:00', 'end' => '17:00', 'day' => 1, 'lesson' => 'Vocal coaching'],
                ['start' => '10:00', 'end' => '11:30', 'day' => 3, 'lesson' => 'Sånginterpretation'],
                ['start' => '12:00', 'end' => '13:30', 'day' => '5', 'lesson' => 'Stämmor'],
                ['start' => '16:00', 'end' => '17:00', 'day' => 2, 'lesson' => 'Mikrofonteknik'],
                ['start' => '09:30', 'end' => '11:00', 'day' => 4, 'lesson' => 'Musikalisk gestaltning'],
            ],
        ],
        '114' => [ // Rums-ID 114
            // Lägg till bokningar för vecka 20, 21, 22 här (minst 5 per vecka)
            20 => [
                ['start' => '08:00', 'end' => '10:00', 'day' => 2, 'lesson' => 'Pianoteknik'],
                ['start' => '10:30', 'end' => '12:00', 'day' => 4, 'lesson' => 'Ackordspel'],
                ['start' => '13:00', 'end' => '14:30', 'day' => 1, 'lesson' => 'Notläsning'],
                ['start' => '15:00', 'end' => '16:30', 'day' => 3, 'lesson' => 'Improvisation'],
                ['start' => '09:30', 'end' => '11:00', 'day' => 5, 'lesson' => 'Repertoarstudier'],
            ],
            21 => [
                ['start' => '08:30', 'end' => '10:30', 'day' => 2, 'lesson' => 'Improvisation'],
                ['start' => '11:00', 'end' => '12:30', 'day' => 4, 'lesson' => 'Harmonilära'],
                ['start' => '13:30', 'end' => '15:00', 'day' => 1, 'lesson' => 'Interpretation'],
                ['start' => '15:30', 'end' => '17:00', 'day' => 3, 'lesson' => 'Komp'],
                ['start' => '10:00', 'end' => '11:30', 'day' => 5, 'lesson' => 'Pianolektion'],
            ],
            22 => [
                ['start' => '09:00', 'end' => '11:00', 'day' => 2, 'lesson' => 'Ackompanjemang'],
                ['start' => '11:30', 'end' => '13:00', 'day' => 4, 'lesson' => 'Kontrapunkt'],
                ['start' => '14:00', 'end' => '15:30', 'day' => 1, 'lesson' => 'Stilar och genrer'],
                ['start' => '16:00', 'end' => '17:00', 'day' => 3, 'lesson' => 'Pedagogik'],
                ['start' => '10:30', 'end' => '12:00', 'day' => 5, 'lesson' => 'Kammarmusik'],
            ],
        ],
        '115' => [ // Rums-ID 115
            // Lägg till bokningar för vecka 20, 21, 22 här (minst 5 per vecka)
            20 => [
                ['start' => '10:00', 'end' => '12:00', 'day' => 2, 'lesson' => 'Interpretation'],
                ['start' => '13:00', 'end' => '14:30', 'day' => 4, 'lesson' => 'Ensembleträning'],
                ['start' => '08:30', 'end' => '10:00', 'day' => 1, 'lesson' => 'Kammarmusik'],
                ['start' => '15:00', 'end' => '16:30', 'day' => 3, 'lesson' => 'Repertoar'],
                ['start' => '11:30', 'end' => '13:00', 'day' => 5, 'lesson' => 'Instudering'],
            ],
            21 => [
                ['start' => '10:30', 'end' => '12:30', 'day' => 2, 'lesson' => 'Kammarmusik'],
                ['start' => '13:30', 'end' => '15:00', 'day' => 4, 'lesson' => 'Gruppspel'],
                ['start' => '09:00', 'end' => '10:30', 'day' => 1, 'lesson' => 'Interpretation'],
                ['start' => '15:30', 'end' => '17:00', 'day' => 3, 'lesson' => 'Konsertförberedelse'],
                ['start' => '12:00', 'end' => '13:30', 'day' => 5, 'lesson' => 'Notanalys'],
            ],
            22 => [
                ['start' => '11:00', 'end' => '13:00', 'day' => 2, 'lesson' => 'Ensemble'],
                ['start' => '14:00', 'end' => '15:30', 'day' => 4, 'lesson' => 'Framförande'],
                ['start' => '09:30', 'end' => '11:00', 'day' => 1, 'lesson' => 'Kammarmusik'],
                ['start' => '16:00', 'end' => '17:00', 'day' => 3, 'lesson' => 'Teknikövningar'],
                ['start' => '12:30', 'end' => '14:00', 'day' => 5, 'lesson' => 'Musikteori i praktik'],
            ],
        ],
        // Lägg till bokningar för fler rum här om du har dem
    ];

    foreach ($weeks as $week) {
        foreach ($hardcoded_bookings as $room_id => $week_bookings) {
            if (isset($week_bookings[$week])) {
                $year = date('Y', strtotime(sprintf('+%d weeks', $week - intval(date('W', strtotime('today'))))));
                if ($week < intval(date('W', strtotime('today')))) {
                    $year = date('Y', strtotime('+1 year', strtotime(sprintf('+%d weeks', $week - intval(date('W', strtotime('today')))))));
                }

                foreach ($week_bookings[$week] as $booking) {
                    // Skapa DateTime-objekt för bokningens start och slut
                    $date = new DateTime();
                    $date->setISODate($year, $week, $booking['day']);
                    $start_datetime_str = $date->format('Y-m-d') . ' ' . $booking['start'] . ':00';
                    $end_datetime_str = $date->format('Y-m-d') . ' ' . $booking['end'] . ':00';

                    $wpdb->insert(
                        $table_name,
                        [
                            'room_id' => $room_id,
                            'lesson' => $booking['lesson'],
                            'user_id' => $user_id,
                            'booking_start' => $start_datetime_str,
                            'booking_end' => $end_datetime_str,
                            'booking_status' => 'Pending',
                        ],
                        ['%s', '%s', '%d', '%s', '%s', '%s']
                    );
                }
            }
        }
    }
}