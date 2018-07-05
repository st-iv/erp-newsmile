;(function () {
    window.scheduleSettings = function (params) {
        this.params = params;
        this.init();
    };
    window.scheduleSettings.prototype.init = function () {

        var doctors = {};
        $.each(this.params.doctors, function (index, element) {
            doctors[index] = {name: element, callback: selectDoctor}
        });

        $(document).ready(function () {

            addSelectable();

            $('.visit-block').draggable({
                snap: "#calendar-template table tr td",
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

            $( "#calendar-template table tr td" ).droppable({
                tolerance: 'pointer',
                drop: function( event, ui ) {
                    console.log(ui.helper);
                    console.log($(this));
                }
            });

        });

        function addSelectable() {
            $('#calendar-template table').selectable({
                filter: "tr td",
                stop: function () {
                    $.contextMenu({
                        // define which elements trigger this menu
                        selector: "#calendar-template .visit",
                        // define the elements of the menu
                        items: {
                            foo: {name: "Заменить врача",
                                items: doctors
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
            if ($('.ui-selected').parent().is('[data-start-time]')) {
                $.post(
                    '/local/components/newSmile/calendar.schedule.settings/ajax.php',
                    {
                        action: 'selectDoctorDay',
                        time: $('.ui-selected').parent().data('start-time'),
                        work_chair: $('.ui-selected').data('work-chair'),
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
                    '/local/components/newSmile/calendar.schedule.settings/ajax.php',
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
                '/local/components/newSmile/calendar.schedule.settings/ajax.php',
                {
                    action: 'addVisit',
                    TIME_START: $('.ui-selected').first().parent().data('time-visit'),
                    TIME_END: $('.ui-selected').last().parent().data('time-visit'),
                    PATIENT_ID: key,
                    DOCTOR_ID: $('.ui-selected').first().data('doctor-id'),
                    WORK_CHAIR_ID: $('.ui-selected').first().data('work-chair')
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