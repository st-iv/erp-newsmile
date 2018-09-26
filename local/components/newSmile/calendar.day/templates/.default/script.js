;(function () {
    window.calendarDay = function (params) {

        var _this = this;

        this.params = params;

        this.init = function () {

            this.initSelectable();
            this.initSortable();
            this.initVisitAction();

        };

        this.initVisitAction = function () {
            $.contextMenu({
                selector: '#calendar-day .calendar-visit',
                items: {
                    start: {
                        name: 'Начать прием',
                        callback: _this.startVisit
                    },
                    end: {
                        name: 'Закончить прием',
                        callback: _this.endVisit
                    }
                }
            });

        };

        this.startVisit = function(key, opt) {
            var id = $(this).data('visit-id');

            _this.sendPost(
                {
                    id: id,
                    status: key,
                },
                'updateStatusVisit',
                function () {
                    location.reload();
                }
            );
        };

        this.endVisit = function(key, opt) {
            var id = $(this).data('visit-id');

            location.href = '/invoice/?CREATE_INVOICE=Y&VISIT_ID=' + id;

            // window.calendarDay.sendPost(
            //     {
            //         id: id,
            //         status: key,
            //     },
            //     'updateStatusVisit',
            //     function () {
            //         location.reload();
            //     }
            // );
        };

        this.initSortable = function () {
            $( "#calendar-day .calendar-top-work_chair" ).sortable({
                connectWith: "#calendar-day .calendar-top-work_chair",
                placeholder: "ui-state-highlight",
                sort: function( event, ui ) {
                    $('.ui-sortable-placeholder').height($('.ui-sortable-helper').height());
                }
            }).disableSelection();
        };

        this.initSelectable = function () {



            var doctors = {};
            $.each(this.params.doctors, function (index, element) {
                doctors[index] = {name: element, callback: _this.selectDoctor}
            });
            var patients = {};
            $.each(this.params.patients, function (index, element) {
                patients[index] = {name: element, callback: _this.selectPatient}
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

        this.selectDoctor = function(key, opt) {
            console.log(key, opt);
            console.log($(this));
            var id = [];
            var newSchedule = [];
            if ($('.calendar-bottom-item.ui-selected').is('[data-start-time]')) {
                _this.sendPost(
                    {
                        time: $('.calendar-bottom-item.ui-selected').data('start-time'),
                        work_chair: $('.calendar-bottom-item.ui-selected').parent().data('work-chair'),
                        doctor_id: key
                    },
                    'selectDoctorDay',
                    function () {
                        location.reload();
                    }
                );
            } else {
                $('.calendar-bottom-item.ui-selected').each(function () {
                    if ($(this).data('schedule-id')) {
                        id.push($(this).data('schedule-id'));
                    } else {
                        newSchedule.push({TIME: $(this).parents('tr').data('time-visit'), WORK_CHAIR: $(this).data('work-chair')})
                    }
                });
                _this.sendPost(
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

        this.selectPatient = function(key, opt) {
            _this.sendPost(
                {
                    TIME_START: $('.calendar-bottom-item.ui-selected').first().data('schedule-time'),
                    TIME_END: $('.calendar-bottom-item.ui-selected').last().data('schedule-time'),
                    PATIENT_ID: key,
                    DOCTOR_ID: $('.calendar-bottom-item.ui-selected').first().data('doctor-id'),
                    WORK_CHAIR_ID: $('.calendar-bottom-item.ui-selected').first().parent().data('work-chair')
                },
                'addVisit',
                function () {
                    location.reload();
                }
            );
        };

        this.sendPost = function (data, action, callback) {
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

        this.init();
    };

})();