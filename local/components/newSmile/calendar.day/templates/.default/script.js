;(function () {
    $(document).ready(function () {

        addSelectable();

        $('#calendar-day .calendar-visit').draggable({
            snap: "#calendar-day .calendar-bottom-work_chair .calendar-bottom-item",
            snapMode: 'inner',
            grid: [103, 21],
            revert: "invalid",
            // snapTolerance: 10,
            // axis: "y",
            cursor: "move",
            cursorAt: { top: 0 },
            zIndex: 10,
            stop: function( event, ui ) {
                //console.log(event, ui);

            }
        });

        // $( "#calendar-day .calendar-top-work_chair" ).sortable({
        // }).disableSelection();

        $( "#calendar-day .calendar-bottom-work_chair .calendar-bottom-item" ).droppable({
            tolerance: 'pointer',
            drop: function( event, ui ) {
                console.log(ui.helper);
                console.log($(this));
            }
        });

    });

    function addSelectable() {
        $('#calendar-day .calendar-bottom-work_chair').selectable({
            filter: ".calendar-bottom-item",
            stop: function () {
                $.contextMenu({
                    // define which elements trigger this menu
                    selector: "#calendar-day .calendar-bottom-item",
                    // define the elements of the menu
                    items: {
                        foo: {name: "Заменить врача",
                            items: {
                                '1': {name: 'Doctor 1', callback: selectDoctor},
                                '2': {name: 'Doctor 2', callback: selectDoctor},
                                '3': {name: 'Doctor 3', callback: selectDoctor},
                            }
                        },
                        bar: {name: "Записать поциента",
                            items: {
                                '1': {name: 'Patient 1', callback: selectPatient},
                                '2': {name: 'Patient 2', callback: selectPatient},
                                '3': {name: 'Patient 3', callback: selectPatient},
                            }
                        }
                    }
                    // there's more, have a look at the demos and docs...
                });
            }
        });
    }

    function selectDoctor(key, opt) {
        console.log(key, opt);
        console.log($(this));
        var id = [];
        var newSchedule = [];
        if ($('.ui-selected').is('[data-start-time]')) {
            $.post(
                '/local/components/newSmile/calendar.day/ajax.php',
                {
                    action: 'selectDoctorDay',
                    time: $('.ui-selected').data('start-time'),
                    work_chair: $('.ui-selected').parent().data('work-chair'),
                    doctor_id: key
                },
                function () {
                    location.reload();
                },
                'json'
            ).fail(function (data) {
                console.log(data);
            });
        } else {
            $('.ui-selected').each(function () {
                if ($(this).data('schedule-id')) {
                    id.push($(this).data('schedule-id'));
                } else {
                    newSchedule.push({TIME: $(this).parents('tr').data('time-visit'), WORK_CHAIR: $(this).data('work-chair')})
                }
            });
            $.post(
                '/local/components/newSmile/calendar.day/ajax.php',
                {
                    action: 'selectDoctor',
                    schedule_id: id,
                    new_schedule: newSchedule,
                    doctor_id: key
                },
                function () {
                    location.reload();
                },
                'json'
            ).fail(function (data) {
                console.log(data);
            });
        }
    }

    function selectPatient(key, opt) {
        $.post(
            '/local/components/newSmile/calendar.day/ajax.php',
            {
                action: 'addVisit',
                TIME_START: $('.ui-selected').first().data('schedule-time'),
                TIME_END: $('.ui-selected').last().data('schedule-time'),
                PATIENT_ID: key,
                DOCTOR_ID: $('.ui-selected').first().data('doctor-id'),
                WORK_CHAIR_ID: $('.ui-selected').first().parent().data('work-chair')
            },
            function () {
                location.reload();
            },
            'json'
        ).fail(function (data) {
            console.log(data);
        });
    }
})();