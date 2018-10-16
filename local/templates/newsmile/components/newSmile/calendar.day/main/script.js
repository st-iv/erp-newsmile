var CalendarDay = function(params, selector)
{
    this.params = params;
    this.$element = $(selector);

    $(document).ready(this.init.bind(this));
};

$.extend(CalendarDay.prototype, {
    init: function()
    {
        this.$element.dayCalendar({
            curDate: '2018-05-29',
            curTime: '15:08',
            startTime: '09:00',
            endTime: '19:30',
            timeStep: '30',
            mintimeStep: '15',
            cData :[
                {
                    name: 'Кресло 1',
                    doctors:[
                        {
                            id: 123,
                            name: 'Рудзит Ю.Ф.',
                            color: '16aeed',
                            workTime:[
                                {
                                    startTime: '10:00',
                                    endTime: '15:30'
                                },
                                {
                                    startTime: '17:00',
                                    endTime: '19:00'
                                }
                            ]
                        },
                        {
                            id: 123,
                            name: 'Рудзит Ю.Ф.',
                            color: '16aeed',
                            workTime:[
                                {
                                    startTime: '10:00',
                                    endTime: '15:30'
                                },
                                {
                                    startTime: '17:00',
                                    endTime: '19:00'
                                }
                            ]
                        }
                    ],
                    freeDoctors:[
                        {
                            id: 124,
                            name: 'Панов А.А.',
                            color: 'cee646',
                            workTime:[
                                {
                                    startTime: '15:30',
                                    endTime: '17:00'
                                }
                            ]
                        }
                    ],
                    patients:[
                        {
                            timeFrom: '10:00',
                            timeTo: '12:00',
                            doctorId: 123,
                            name: 'Шпилевая А.Ю.',
                            info: {
                                statuses: ['perv','decl'],
                                fullName: 'Шпилевая Анастасия Юрьевна',
                                age: 32,
                                cardNumber: 4015,
                                phone: '8 (950) 10 555 22'
                            }
                        },
                        {
                            timeFrom: '12:00',
                            timeTo: '13:00',
                            doctorId: 123,
                            name: 'Свершникова К.А.',
                            info: {
                                statuses: ['perv','paid'],
                                fullName: 'Шпилевая Анастасия Юрьевна',
                                age: 32,
                                cardNumber: 4016,
                                phone: '8 (950) 10 555 22'
                            }
                        },
                        {
                            timeFrom: '13:00',
                            timeTo: '13:30',
                            doctorId: 123,
                            name: 'Трубкина М.Е.',
                            info: {
                                statuses: ['perv','paid'],
                                fullName: 'Шпилевая Анастасия Юрьевна',
                                age: 32,
                                cardNumber: 4017,
                                phone: '8 (950) 10 555 22'
                            }
                        },
                        {
                            timeFrom: '15:30',
                            timeTo: '16:00',
                            doctorId: 124,
                            name: 'Аверьянов М.',
                            info: {
                                statuses: ['perv','paid'],
                                fullName: 'Шпилевая Анастасия Юрьевна',
                                age: 32,
                                cardNumber: 4018,
                                phone: '8 (950) 10 555 22'
                            }
                        },
                        {
                            timeFrom: '18:00',
                            timeTo: '19:00',
                            doctorId: 123,
                            name: 'Банатова О.А.',
                            info: {
                                statuses: ['perv','paid'],
                                fullName: 'Шпилевая Анастасия Юрьевна',
                                age: 32,
                                cardNumber: 4019,
                                phone: '8 (950) 10 555 22'
                            }
                        }
                    ]
                },
                {
                    name: 'Кресло 2',
                    doctors:[
                        {
                            id: 125,
                            name: 'Груничев В.А.',
                            color: 'ffba61',
                            workTime:[
                                {
                                    startTime: '09:00',
                                    endTime: '18:00'
                                }
                            ]
                        }
                    ],
                    patients:[
                        {
                            timeFrom: '09:30',
                            timeTo: '10:30',
                            doctorId: 125,
                            name: 'Ефремова Н.А.',
                            info: {
                                statuses: ['perv','paid'],
                                fullName: 'Шпилевая Анастасия Юрьевна',
                                age: 32,
                                cardNumber: 4020,
                                phone: '8 (950) 10 555 22'
                            }
                        },
                        {
                            timeFrom: '10:30',
                            timeTo: '10:45',
                            doctorId: 125,
                            name: 'Банатова О.А.',
                            info: {
                                statuses: ['perv','paid'],
                                fullName: 'Шпилевая Анастасия Юрьевна',
                                age: 32,
                                cardNumber: 4021,
                                phone: '8 (950) 10 555 22'
                            }
                        },
                        {
                            timeFrom: '12:30',
                            timeTo: '13:00',
                            doctorId: 125,
                            name: 'Смирнова С.К.',
                            info: {
                                statuses: ['perv','paid'],
                                fullName: 'Шпилевая Анастасия Юрьевна',
                                age: 32,
                                cardNumber: 4022,
                                phone: '8 (950) 10 555 22'
                            }
                        },
                        {
                            timeFrom: '13:00',
                            timeTo: '14:00',
                            doctorId: 125,
                            name: 'Табаков Е.В.',
                            info: {
                                statuses: ['perv','paid'],
                                fullName: 'Шпилевая Анастасия Юрьевна',
                                age: 32,
                                cardNumber: 4023,
                                phone: '8 (950) 10 555 22'
                            }
                        },
                        {
                            timeFrom: '15:00',
                            timeTo: '15:30',
                            doctorId: 125,
                            name: 'Бесов Р.',
                            info: {
                                statuses: ['perv','decl'],
                                fullName: 'Шпилевая Анастасия Юрьевна',
                                age: 32,
                                cardNumber: 4024,
                                phone: '8 (950) 10 555 22'
                            }
                        },
                        {
                            timeFrom: '17:00',
                            timeTo: '17:45',
                            doctorId: 125,
                            name: 'Тимергалиева С.',
                            info: {
                                statuses: ['perv','decl'],
                                fullName: 'Шпилевая Анастасия Юрьевна',
                                age: 32,
                                cardNumber: 4025,
                                phone: '8 (950) 10 555 22'
                            }
                        }
                    ]
                },
                {
                    name: 'Кресло 3',
                    doctors:[
                        {
                            id: 126,
                            name: 'Виноградова И.Б.',
                            color: '713fd6',
                            workTime:[
                                {
                                    startTime: '09:00',
                                    endTime: '14:00'
                                }
                            ]
                        },
                        {
                            id: 127,
                            name: 'Дмитриева Е.В.',
                            color: 'da3b1b',
                            workTime:[
                                {
                                    startTime: '15:00',
                                    endTime: '19:30'
                                }
                            ]
                        }
                    ],
                    patients:[
                        {
                            timeFrom: '10:30',
                            timeTo: '11:00',
                            doctorId: 126,
                            name: 'Степанов В.',
                            info: {
                                statuses: ['perv','paid'],
                                fullName: 'Шпилевая Анастасия Юрьевна',
                                age: 32,
                                cardNumber: 4026,
                                phone: '8 (950) 10 555 22'
                            }
                        },
                        {
                            timeFrom: '11:30',
                            timeTo: '12:00',
                            doctorId: 126,
                            name: 'Рей О.',
                            info: {
                                statuses: ['perv','decl'],
                                fullName: 'Шпилевая Анастасия Юрьевна',
                                age: 32,
                                cardNumber: 4027,
                                phone: '8 (950) 10 555 22'
                            }
                        },
                        {
                            timeFrom: '15:15',
                            timeTo: '17:45',
                            doctorId: 127,
                            name: 'Семёнов М.А.',
                            info: {
                                statuses: ['perv','paid'],
                                fullName: 'Шпилевая Анастасия Юрьевна',
                                age: 32,
                                cardNumber: 4028,
                                phone: '8 (950) 10 555 22'
                            }
                        },
                        {
                            timeFrom: '17:45',
                            timeTo: '18:00',
                            doctorId: 127,
                            name: 'Наумова М.П.',
                            info: {
                                statuses: ['perv','paid'],
                                fullName: 'Шпилевая Анастасия Юрьевна',
                                age: 32,
                                cardNumber: 4029,
                                phone: '8 (950) 10 555 22'
                            }
                        }
                    ]
                }
            ]
        });
    }
});