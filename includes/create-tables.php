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

    // Definiera de veckor du vill skapa bokningar för (vecka 21 och 22)
    $start_week = intval(date('W', strtotime('+1 week')));
    $weeks = [$start_week, $start_week + 1];

    // Definiera hårdkodade bokningar per rum och vecka (utökad för fler bokningar och 1-2 timmars längd)
    $hardcoded_bookings = [
        '15' => [ // Rums-ID 15
            21 => [ // Vecka 21
                ['start' => '08:00', 'end' => '09:30', 'day' => 1, 'lesson' => 'Rockensembel'],
                ['start' => '10:00', 'end' => '11:30', 'day' => 1, 'lesson' => 'Bandcoachning'],
                ['start' => '13:00', 'end' => '14:00', 'day' => 3, 'lesson' => 'Bandrep'],
                ['start' => '14:30', 'end' => '16:00', 'day' => 3, 'lesson' => 'Låtskriveri'],
                ['start' => '15:00', 'end' => '17:00', 'day' => 5, 'lesson' => 'Gitarrlektion'],
                ['start' => '09:30', 'end' => '10:30', 'day' => 2, 'lesson' => 'Samspel'],
                ['start' => '11:00', 'end' => '12:30', 'day' => 2, 'lesson' => 'Improvisation'],
                ['start' => '11:30', 'end' => '13:00', 'day' => 4, 'lesson' => 'Trumlektion'],
                ['start' => '13:30', 'end' => '15:30', 'day' => 4, 'lesson' => 'Rytmik'],
            ],
            22 => [ // Vecka 22
                ['start' => '08:30', 'end' => '10:00', 'day' => 1, 'lesson' => 'Latinensembel'],
                ['start' => '10:30', 'end' => '12:00', 'day' => 1, 'lesson' => 'Musikhistoria'],
                ['start' => '12:30', 'end' => '13:30', 'day' => 3, 'lesson' => 'Percussion'],
                ['start' => '14:00', 'end' => '15:30', 'day' => 3, 'lesson' => 'Arrangering'],
                ['start' => '14:30', 'end' => '16:30', 'day' => 5, 'lesson' => 'Baslektion'],
                ['start' => '10:00', 'end' => '11:00', 'day' => 2, 'lesson' => 'Ensemble'],
                ['start' => '11:30', 'end' => '13:00', 'day' => 2, 'lesson' => 'Gehörsträning'],
                ['start' => '13:00', 'end' => '14:30', 'day' => 4, 'lesson' => 'Keyboard'],
                ['start' => '15:00', 'end' => '16:30', 'day' => 4, 'lesson' => 'Syntintroduktion'],
            ],
        ],
        '108' => [ // Rums-ID 108
            21 => [
                ['start' => '09:00', 'end' => '11:00', 'day' => 2, 'lesson' => 'Elektronisk musik'],
                ['start' => '11:30', 'end' => '13:00', 'day' => 2, 'lesson' => 'Syntdesign'],
                ['start' => '14:00', 'end' => '15:30', 'day' => 4, 'lesson' => 'Synt-labb'],
                ['start' => '16:00', 'end' => '17:00', 'day' => 4, 'lesson' => 'Modulärsynt'],
                ['start' => '08:00', 'end' => '09:00', 'day' => 1, 'lesson' => 'Ljuddesign'],
                ['start' => '09:30', 'end' => '11:30', 'day' => 1, 'lesson' => 'Akustik'],
                ['start' => '11:30', 'end' => '13:00', 'day' => 3, 'lesson' => 'Sampling'],
                ['start' => '13:30', 'end' => '15:00', 'day' => 3, 'lesson' => 'Sequencing'],
                ['start' => '15:30', 'end' => '17:00', 'day' => 5, 'lesson' => 'Mixning'],
                ['start' => '17:30', 'end' => '18:30', 'day' => 5, 'lesson' => 'Mastering'],
            ],
            22 => [
                ['start' => '10:00', 'end' => '11:30', 'day' => 2, 'lesson' => 'Synth-lab'],
                ['start' => '12:00', 'end' => '14:00', 'day' => 2, 'lesson' => 'Ljudsyntes'],
                ['start' => '15:00', 'end' => '16:00', 'day' => 4, 'lesson' => 'Sequencing'],
                ['start' => '16:30', 'end' => '18:00', 'day' => 4, 'lesson' => 'Live-elektronik'],
                ['start' => '09:00', 'end' => '10:00', 'day' => 1, 'lesson' => 'Effektprocessorer'],
                ['start' => '10:30', 'end' => '12:30', 'day' => 1, 'lesson' => 'Digitala ljudverktyg'],
                ['start' => '12:30', 'end' => '14:00', 'day' => 3, 'lesson' => 'Modularsynt'],
                ['start' => '14:30', 'end' => '16:00', 'day' => 3, 'lesson' => 'Midi-programmering'],
                ['start' => '16:30', 'end' => '17:30', 'day' => 5, 'lesson' => 'Mastering'],
                ['start' => '18:00', 'end' => '19:00', 'day' => 5, 'lesson' => 'Ljudinstallationer'],
            ],
        ],
        '113' => [ // Rums-ID 113
            21 => [
                ['start' => '14:00', 'end' => '15:30', 'day' => 1, 'lesson' => 'Sånglektion'],
                ['start' => '16:00', 'end' => '17:00', 'day' => 1, 'lesson' => 'Röstvård'],
                ['start' => '09:00', 'end' => '10:30', 'day' => 3, 'lesson' => 'Vocal coaching'],
                ['start' => '11:00', 'end' => '12:00', 'day' => 3, 'lesson' => 'Interpretation'],
                ['start' => '11:00', 'end' => '12:30', 'day' => 5, 'lesson' => 'Sångensemble'],
                ['start' => '13:00', 'end' => '14:00', 'day' => 5, 'lesson' => 'Scennärvaro'],
                ['start' => '15:00', 'end' => '16:30', 'day' => 2, 'lesson' => 'Röstteknik'],
                ['start' => '17:00', 'end' => '18:00', 'day' => 2, 'lesson' => 'Mikrofonteknik'],
                ['start' => '08:30', 'end' => '10:00', 'day' => 4, 'lesson' => 'Repertoarstudier'],
                ['start' => '10:30', 'end' => '11:30', 'day' => 4, 'lesson' => 'Musikalisk gestaltning'],
                ['start' => '12:00', 'end' => '13:00', 'day' => 4, 'lesson' => 'Improvisation'],
            ],
            22 => [
                ['start' => '14:30', 'end' => '16:30', 'day' => 1, 'lesson' => 'Sånglektion'],
                ['start' => '17:00', 'end' => '18:00', 'day' => 1, 'lesson' => 'Textinterpretation'],
                ['start' => '09:30', 'end' => '11:00', 'day' => 3, 'lesson' => 'Vocal coaching'],
                ['start' => '11:30', 'end' => '12:30', 'day' => 3, 'lesson' => 'Gehör och rytmik'],
                ['start' => '11:30', 'end' => '13:00', 'day' => 5, 'lesson' => 'Kör'],
                ['start' => '13:30', 'end' => '14:30', 'day' => 5, 'lesson' => 'Stämmor'],
                ['start' => '15:30', 'end' => '17:00', 'day' => 2, 'lesson' => 'Scennärvaro'],
                ['start' => '17:30', 'end' => '18:30', 'day' => 2, 'lesson' => 'Rörelse och uttryck'],
                ['start' => '09:00', 'end' => '10:30', 'day' => 4, 'lesson' => 'Repertoar'],
                ['start' => '11:00', 'end' => '12:00', 'day' => 4, 'lesson' => 'Konsertförberedelse'],
            ],
        ],
        '114' => [ // Rums-ID 114
            21 => [
                ['start' => '08:00', 'end' => '09:30', 'day' => 2, 'lesson' => 'Pianoteknik'],
                ['start' => '10:00', 'end' => '11:00', 'day' => 2, 'lesson' => 'Skalor och arpeggion'],
                ['start' => '10:30', 'end' => '12:00', 'day' => 4, 'lesson' => 'Ackordspel'],
                ['start' => '12:30', 'end' => '13:30', 'day' => 4, 'lesson' => 'Ackordföljder'],
                ['start' => '13:00', 'end' => '14:30', 'day' => 1, 'lesson' => 'Notläsning'],
                ['start' => '15:00', 'end' => '16:00', 'day' => 1, 'lesson' => 'Rytm och timing'],
                ['start' => '15:00', 'end' => '16:30', 'day' => 3, 'lesson' => 'Improvisation'],
                ['start' => '17:00', 'end' => '18:00', 'day' => 3, 'lesson' => 'Bluespiano'],
                ['start' => '09:30', 'end' => '11:00', 'day' => 5, 'lesson' => 'Repertoarstudier'],
                ['start' => '11:30', 'end' => '12:30', 'day' => 5, 'lesson' => 'Interpretation'],
            ],
            22 => [
                ['start' => '08:30', 'end' => '10:00', 'day' => 2, 'lesson' => 'Improvisation'],
                ['start' => '10:30', 'end' => '11:30', 'day' => 2, 'lesson' => 'Jazzpiano'],
                ['start' => '11:00', 'end' => '12:30', 'day' => 4, 'lesson' => 'Harmonilära'],
                ['start' => '13:00', 'end' => '14:00', 'day' => 4, 'lesson' => 'Funktionsanalys'],
                ['start' => '13:30', 'end' => '15:00', 'day' => 1, 'lesson' => 'Interpretation'],
                ['start' => '15:30', 'end' => '16:30', 'day' => 1, 'lesson' => 'Stilar och genrer'],
                ['start' => '15:30', 'end' => '17:00', 'day' => 3, 'lesson' => 'Komp'],
                ['start' => '17:30', 'end' => '18:30', 'day' => 3, 'lesson' => 'Ackompanjemang'],
                ['start' => '10:00', 'end' => '11:30', 'day' => 5, 'lesson' => 'Pianolektion'],
                ['start' => '12:00', 'end' => '13:00', 'day' => 5, 'lesson' => 'Pedagogik'],
            ],
        ],
        '115' => [ // Rums-ID 115
            21 => [
                ['start' => '10:00', 'end' => '11:30', 'day' => 2, 'lesson' => 'Interpretation'],
                ['start' => '12:00', 'end' => '13:00', 'day' => 2, 'lesson' => 'Notläsning och analys'],
                ['start' => '13:00', 'end' => '14:30', 'day' => 4, 'lesson' => 'Ensembleträning'],
                ['start' => '15:00', 'end' => '16:00', 'day' => 4, 'lesson' => 'Stämspel'],
                ['start' => '08:30', 'end' => '10:00', 'day' => 1, 'lesson' => 'Kammarmusik'],
                ['start' => '10:30', 'end' => '11:30', 'day' => 1, 'lesson' => 'Stilkunskap'],
                ['start' => '15:00', 'end' => '16:30', 'day' => 3, 'lesson' => 'Repertoar'],
                ['start' => '17:00', 'end' => '18:00', 'day' => 3, 'lesson' => 'Instuderingsteknik'],
                ['start' => '11:30', 'end' => '13:00', 'day' => 5, 'lesson' => 'Instudering'],
                ['start' => '13:30', 'end' => '14:30', 'day' => 5, 'lesson' => 'Framförandepraxis'],
            ],
            22 => [
                ['start' => '10:30', 'end' => '12:00', 'day' => 2, 'lesson' => 'Kammarmusik'],
                ['start' => '12:30', 'end' => '13:30', 'day' => 2, 'lesson' => 'Gehörsspel'],
                ['start' => '13:30', 'end' => '15:00', 'day' => 4, 'lesson' => 'Gruppspel'],
                ['start' => '15:30', 'end' => '16:30', 'day' => 4, 'lesson' => 'Improvisation i ensemble'],
                ['start' => '09:00', 'end' => '10:30', 'day' => 1, 'lesson' => 'Interpretation'],
                ['start' => '11:00', 'end' => '12:00', 'day' => 1, 'lesson' => 'Musikteori i ensemble'],
                ['start' => '15:30', 'end' => '17:00', 'day' => 3, 'lesson' => 'Konsertförberedelse'],
                ['start' => '17:30', 'end' => '18:30', 'day' => 3, 'lesson' => 'Scennärvaro i ensemble'],
                ['start' => '12:00', 'end' => '13:30', 'day' => 5, 'lesson' => 'Notanalys'],
                ['start' => '14:00', 'end' => '15:00', 'day' => 5, 'lesson' => 'Repertoarval'],
            ],
        ],
        // Lägg till bokningar för fler rum här om du har dem för vecka 21 och 22
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
                        ['%d', '%s', '%d', '%s', '%s', '%s']
                    );
                }
            }
        }
    }
}
