
<?php

function tontid_enqueue_flatpickr( $hook ) {
    // Visa bara på adminsidan för hantera bokningar
    if ( strpos( $hook, 'tontid-handle-bookings' ) === false ) {
        return;
    }

    // Ladda Flatpickr CSS
    wp_enqueue_style(
        'flatpickr-css',
        'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css'
    );

    // Ladda Flatpickr JavaScript
    wp_enqueue_script(
        'flatpickr-js',
        'https://cdn.jsdelivr.net/npm/flatpickr',
        array(), null, true
    );

    // Ladda svensk översättning (så att dagar och månader blir på svenska)
    wp_enqueue_script(
        'flatpickr-sv',
        'https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/sv.js',
        array( 'flatpickr-js' ), null, true
    );

    // Initiera Flatpickr med inställningar och lägga till en custom  "Välj tid"-knapp
    wp_add_inline_script( 'flatpickr-sv', "
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.tontid-flatpickr-time').forEach(function(elem) {
                const picker = flatpickr(elem, {
                    enableTime: true,
                    dateFormat: 'Y-m-d H:i',
                    time_24hr: true,
                    minTime: '08:30',
                    maxTime: '20:00',
                    locale: 'sv',
                    appendTo: document.body,
                    onOpen: function(selectedDates, dateStr, instance) {
                        if (!instance.calendarContainer.querySelector('.flatpickr-select-btn')) {
                            const selectButton = document.createElement('button');
                            selectButton.textContent = 'Välj tid';
                            selectButton.classList.add('flatpickr-select-btn');
                            instance.calendarContainer.appendChild(selectButton);

                            selectButton.addEventListener('click', function() {
                                instance.close();
                            });
                        }
                    }
                });
            });
        });
    ", true );


    // Lägg till CSS för knappen och input-fältet etc
    wp_add_inline_style( 'flatpickr-css', "
    
    /* Inputfältets styling */
    .tontid-flatpickr-time {
        background-color: white!important;
        padding: 6px 12px;
        border: 1px solid #8c8f94;
        border-radius: 5px;
        height: 30px;
    }
    
    .flatpickr-select-btn {
        background-color: #0073aa; /* WordPress-blå */
        color: white;
        border: none;
        padding: 8px 16px;
        margin-top: 10px;
        margin-bottom: 10px;
        cursor: pointer;
        font-size: 14px;
        border-radius: 5px;
        text-align: center;
        width: 90%; /* Fullbredd */
    }

    .flatpickr-select-btn:hover {
        background-color: #005f7f; /* Mörkare blå vid hover */
    }

");
}