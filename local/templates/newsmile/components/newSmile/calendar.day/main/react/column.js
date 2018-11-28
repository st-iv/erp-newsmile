class CalendarDayColumn extends React.Component
{
    constructor(props)
    {
        super(props);

        this.schedule = Object.assign({}, this.props.schedule);

        this.schedule.intervals = this.addEmptyIntervals(this.schedule.intervals);

        this.cells = this.getCells();
        this.defineMainDoctors();
    }

    defineMainDoctors()
    {
        /* определяем массив mainDoctors */

        let votes = [];

        for(let time in this.cells)
        {
            if(!this.cells.hasOwnProperty(time)) continue;

            let cell = this.cells[time];

            if(!cell.doctorId) continue;

            let timeMoment = this.getMoment(cell.timeStart);
            cell.halfDayNum = timeMoment.isBefore(this.props.timeLimits.half) ? 0 : 1;

            if(!votes[cell.halfDayNum])
            {
                votes[cell.halfDayNum] = {};
            }

            if(!votes[cell.halfDayNum][cell.doctorId])
            {
                votes[cell.halfDayNum][cell.doctorId] = 0;
            }

            votes[cell.halfDayNum][cell.doctorId]++;
        }

        this.mainDoctors = [0, 0];

        votes.forEach((doctorsVotes, halfDayNum) =>
        {
            let maxVotes = 0;

            for(let doctorId in doctorsVotes)
            {
                if(doctorsVotes[doctorId] > maxVotes)
                {
                    maxVotes = doctorsVotes[doctorId];
                    this.mainDoctors[halfDayNum] = doctorId;
                }
            }
        });
    }

    /**
     * Добавляет пустые интервалы (интервалы расписания, для которых не назначен врач).
     * @param intervals
     * @returns {Array}
     */
    addEmptyIntervals(intervals)
    {
        let result = [];
        let prevEndTime = this.props.startTime;
        let count = intervals.length;

        intervals.forEach((interval, index) =>
        {
            if(interval.TIME_START !== prevEndTime)
            {
                result.push({
                    TIME_START: prevEndTime,
                    TIME_END: interval.TIME_START,
                    DOCTOR_ID: 0
                });
            }

            result.push(interval);

            if((index === count - 1) && (this.props.endTime !== interval.TIME_END))
            {
                result.push({
                    TIME_START: interval.TIME_END,
                    TIME_END: this.props.endTime,
                    DOCTOR_ID: 0
                });
            }

            prevEndTime = interval.TIME_END;
        });

        return result;
    }

    /**
     * Из посещений и интервалов собирает единый массив - массив ячеек, который используется для вывода
     * @returns object
     */
    getCells()
    {
        let intervals = this.schedule.intervals.slice();

        let interval;
        let intervalEndTime;

        let result = {};

        /*
        Массив ячеек - это объединенные массивы интервалов(расписания врачей) и приемов.
        Сначала в массив ячеек добавляем интервалы
        */

        for(let time in this.props.timeLine)
        {
            if(!this.props.timeLine.hasOwnProperty(time)) continue;

            let moment = this.getMoment(time);

            if(!interval || !moment.isBefore(intervalEndTime))
            {
                if(intervals.length)
                {
                    interval = intervals.shift();
                    intervalEndTime = this.getMoment(interval.TIME_END);
                }
            }

            if(interval)
            {
                result[time] = {
                    timeStart: time,
                    doctorId: interval.DOCTOR_ID,
                    halfDayNum: interval.halfDayNum
                };
            }
        }

        this.writeVisitsSize();


        /* затем добавляем приемы, заменяя интервалы, на которые они приходятся */

        let resultWithVisits = {};
        let visit;
        let skipCellsCounter = 0;

        for(let time in result)
        {
            if (!result.hasOwnProperty(time)) continue;

            if(skipCellsCounter)
            {
                skipCellsCounter--;
                continue;
            }

            visit = this.schedule.visits[time];
            let cell = result[time];

            if(visit)
            {
                skipCellsCounter = (visit.size - 1);
                cell.size = visit.size;
                cell.timeEnd = visit.TIME_END;
                cell.patientId = visit.PATIENT_ID;
            }

            resultWithVisits[time] = cell;
        }

        return resultWithVisits;
    }

    /**
     * Определение размеров приемов (в количестве строк таймлайна)
     */
    writeVisitsSize()
    {
        let timeLine = Object.keys(this.props.timeLine);

        for(let timeStart in this.schedule.visits)
        {
            if(!this.schedule.visits.hasOwnProperty(timeStart)) continue;

            let visit = this.schedule.visits[timeStart];
            let timeEnd = visit.TIME_END;

            let startIndex = timeLine.indexOf(timeStart);
            let endIndex = timeLine.indexOf(timeEnd);
            if(endIndex === -1)
            {
                // такое может быть только когда время окончания приема является временем окончания рабочего дня
                endIndex = undefined;
            }

            visit.size = timeLine.slice(startIndex, endIndex).length;
        }
    }

    getMoment(time)
    {
        return this.props.getMoment(time);
    }

    render()
    {
        const doctors = this.props.doctors;
        const isOneMainDoctor = (this.mainDoctors[0]) && (this.mainDoctors[0] === this.mainDoctors[1]);

        return (
            <div className="dayCalendar_column">
                <div className="dayCalendar_roomName">{this.schedule.chair.name}</div>

                {this.mainDoctors.map((mainDoctorId, index) =>
                    <div className={'dayCalendar_doctor ' + (isOneMainDoctor ? 'sameD' : '') + (mainDoctorId ? '' : ' emptyD')}
                         style={mainDoctorId ? {backgroundColor: doctors[mainDoctorId].color} : {}}
                         key={index}>
                        {mainDoctorId ? doctors[mainDoctorId].fio : ''}
                    </div>
                )}

                {this.renderCells()}

            </div>
        );
    }

    renderCells()
    {
        let result = [];

        for(let time in this.props.timeLine)
        {
            if(!this.props.timeLine.hasOwnProperty(time) || !this.cells[time]) continue;

            let cellProps = Object.assign({}, this.cells[time]);

            cellProps.doctor = (cellProps.doctorId ? this.props.doctors[cellProps.doctorId] : null);
            cellProps.patient = (cellProps.patientId ? this.props.patients[cellProps.patientId] : null);
            cellProps.isMainDoctor = (this.mainDoctors[cellProps.halfDayNum] === cellProps.doctorId);
            cellProps.key = cellProps.timeStart;

            cellProps.actions = [
                {
                    title: 'Изменить врача',
                    code: 'changeDoctor',
                    variants: [
                        {
                            code: 1,
                            title: 'Груничев'
                        },
                        {
                            code: 4,
                            title: 'Иванова'
                        },
                        {
                            code: 5,
                            title: 'Столяров'
                        },
                    ]
                },
                {
                    title: 'Разделить интервал',
                    code: 'splitInterval'
                }
            ];

            result.push(
                <CalendarDayCell {...cellProps}/>
            );
        }

        return result;
    }
}