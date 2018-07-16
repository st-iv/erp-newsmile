;(function () {
    window.calendarDay = function (params) {
        this.params = params;
        this.init();
    };
    window.calendarDay.prototype.init = function () {

        var doctors = {};
        $.each(this.params.doctors, function (index, element) {
            doctors[index] = {name: element, callback: selectDoctor}
        });
        var patients = {};
        $.each(this.params.patients, function (index, element) {
            patients[index] = {name: element, callback: selectPatient}
        });

        $(document).ready(function () {

            addSelectable();

            // $('#calendar-day .calendar-visit').draggable({
            //     snap: "#calendar-day .calendar-bottom-work_chair .calendar-bottom-item",
            //     snapMode: 'inner',
            //     grid: [103, 21],
            //     revert: "invalid",
            //     // snapTolerance: 10,
            //     // axis: "y",
            //     cursor: "move",
            //     cursorAt: { top: 0 },
            //     zIndex: 10,
            //     stop: function( event, ui ) {
            //         //console.log(event, ui);
            //
            //     }
            // });

            $( "#calendar-day .calendar-top-work_chair" ).sortable({
                connectWith: "#calendar-day .calendar-top-work_chair",
                placeholder: "ui-state-highlight",
                sort: function( event, ui ) {
                    $('.ui-sortable-placeholder').height($('.ui-sortable-helper').height());
                }
            }).disableSelection();

            $.contextMenu({
                selector: '#calendar-day .calendar-visit',
                items: {
                    start: {
                        name: 'Начать прием',
                        callback: startVisit
                    }
                }
            });

            // $( "#calendar-day .calendar-bottom-work_chair .calendar-bottom-item" ).droppable({
            //     tolerance: 'pointer',
            //     drop: function( event, ui ) {
            //         console.log(ui.helper);
            //         console.log($(this));
            //     }
            // });

        });

        function startVisit(key, opt) {
            var id = $(this).data('visit-id');


            $.post(
                '/local/components/newSmile/calendar.day/ajax.php',
                {
                    action: 'updateStatusVisit',
                    id: id,
                    status: key,
                },
                function () {
                    location.reload();
                },
                'json'
            ).fail(function (data) {
                console.log(data);
            });
        }

        function addSelectable() {
            $('#calendar-day .calendar-bottom-work_chair').selectable({
                filter: ".calendar-bottom-item.active",
                stop: function () {
                    $.contextMenu({
                        // define which elements trigger this menu
                        selector: "#calendar-day .calendar-bottom-item",
                        // define the elements of the menu
                        items: {
                            foo: {name: "Заменить врача",
                                items: doctors
                            },
                            bar: {name: "Записать поциента",
                                items: patients
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
    };

})();