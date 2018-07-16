;(function () {
    window.calendarDay = function (params) {
        this.params = params;
        this.init();


    };
    window.calendarDay.prototype.init = function () {

        this.initSelectable();
        this.initSortable();
        this.initVisitAction();

    };

    window.calendarDay.prototype.initVisitAction = function () {
        $.contextMenu({
            selector: '#calendar-day .calendar-visit',
            items: {
                start: {
                    name: 'Начать прием',
                    callback: startVisit
                },
                end: {
                    name: 'Закончить прием',
                    callback: endVisit
                }
            }
        });

        function startVisit(key, opt) {
            var id = $(this).data('visit-id');

            window.calendarDay.sendPost(
                {
                    id: id,
                    status: key,
                },
                'updateStatusVisit',
                function () {
                    location.reload();
                }
            );
        }

        function endVisit(key, opt) {
            var id = $(this).data('visit-id');

            window.calendarDay.sendPost(
                {
                    id: id,
                    status: key,
                },
                'updateStatusVisit',
                function () {
                    location.reload();
                }
            );
        }
    };

    window.calendarDay.prototype.initSortable = function () {
        $( "#calendar-day .calendar-top-work_chair" ).sortable({
            connectWith: "#calendar-day .calendar-top-work_chair",
            placeholder: "ui-state-highlight",
            sort: function( event, ui ) {
                $('.ui-sortable-placeholder').height($('.ui-sortable-helper').height());
            }
        }).disableSelection();
    };

    window.calendarDay.prototype.initSelectable = function () {

        selectDoctor = function(key, opt) {
            console.log(key, opt);
            console.log($(this));
            var id = [];
            var newSchedule = [];
            if ($('.ui-selected').is('[data-start-time]')) {
                window.calendarDay.sendPost(
                    {
                        time: $('.ui-selected').data('start-time'),
                        work_chair: $('.ui-selected').parent().data('work-chair'),
                        doctor_id: key
                    },
                    'selectDoctorDay',
                    function () {
                        location.reload();
                    }
                );
            } else {
                $('.ui-selected').each(function () {
                    if ($(this).data('schedule-id')) {
                        id.push($(this).data('schedule-id'));
                    } else {
                        newSchedule.push({TIME: $(this).parents('tr').data('time-visit'), WORK_CHAIR: $(this).data('work-chair')})
                    }
                });
                window.calendarDay.sendPost(
                    {
                        schedule_id: id,
                        new_schedule: newSchedule,
                        doctor_id: key
                    },
                    'selectDoctor',
                    function () {
                        location.reload();
                    }
                );
            }
        };
        selectPatient = function(key, opt) {
            window.calendarDay.sendPost(
                {
                    TIME_START: $('.ui-selected').first().data('schedule-time'),
                    TIME_END: $('.ui-selected').last().data('schedule-time'),
                    PATIENT_ID: key,
                    DOCTOR_ID: $('.ui-selected').first().data('doctor-id'),
                    WORK_CHAIR_ID: $('.ui-selected').first().parent().data('work-chair')
                },
                'addVisit',
                function () {
                    location.reload();
                }
            );
        };

        var doctors = {};
        $.each(this.params.doctors, function (index, element) {
            doctors[index] = {name: element, callback: selectDoctor}
        });
        var patients = {};
        $.each(this.params.patients, function (index, element) {
            patients[index] = {name: element, callback: selectPatient}
        });

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

    };

    window.calendarDay.prototype.sendPost = function (data, action, callback) {
        if (!action || !callback) {
            console.log('No found action or callback');
            return false;
        }
        data.action = action;
        $.post(
            '/local/components/newSmile/calendar.day/ajax.php',
            data,
            callback,
            'json'
        ).fail(function (data) {
            console.log(data);
        });
    };

})();