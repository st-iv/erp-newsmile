import React from 'react'
import Cell from './cell'

class Column extends React.Component
{
    constructor(props)
    {
        super(props);
    }

    defineMainDoctors(cells)
    {
        /* определяем массив mainDoctors */

        let votes = [];

        for(let time in cells)
        {
            if(!cells.hasOwnProperty(time)) continue;

            let cell = cells[time];

            if(!cell.doctorId || cell.isBlocked) continue;

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
                    this.mainDoctors[halfDayNum] = Number(doctorId);
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
        let prevEndTime = this.props.filter.timeFrom;
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

            if((index === count - 1) && (this.props.filter.timeTo !== interval.TIME_END))
            {
                result.push({
                    TIME_START: interval.TIME_END,
                    TIME_END: this.props.filter.timeTo,
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
    getCells(schedule)
    {
        let intervals = schedule.intervals.slice();

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
                let duration = ((this.props.timeLine[time] === 'half') ? 15 : 30);

                result[time] = {
                    timeStart: time,
                    timeEnd: moment.add(duration, 'minute').format('HH:mm'),
                    doctorId: interval.DOCTOR_ID,
                    halfDayNum: interval.halfDayNum,
                    isBlocked: this.isBlockedInterval(interval)
                };
            }
        }

        this.writeVisitsSize(schedule);


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

            visit = schedule.visits[time];
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

    cutSchedule(schedule)
    {
        let result = Object.assign({}, schedule);
        result.intervals = [];

        let timeFrom = this.getMoment(this.props.filter.timeFrom);
        let timeTo = this.getMoment(this.props.filter.timeTo);

        schedule.intervals.forEach(interval =>
        {
            interval = Object.assign({}, interval);
            let intervalStartTime = this.getMoment(interval.TIME_START);
            let intervalEndTime = this.getMoment(interval.TIME_END);
            let isSuitable = true;

            if(intervalStartTime.isBefore(timeFrom))
            {
                if(intervalEndTime.isAfter(timeFrom))
                {
                    // значит начальным временем из фильтра распилили интервал на части, нужно подменить начальное время интервала
                    interval.TIME_START = timeFrom.format('HH:mm');
                }
                else
                {
                    isSuitable = false;
                }
            }

            if(intervalEndTime.isAfter(timeTo))
            {
                if(intervalStartTime.isBefore(timeTo))
                {
                    // значит начальным временем из фильтра распилили интервал на части, нужно подменить начальное время интервала
                    interval.TIME_END = timeTo.format('HH:mm');
                }
                else
                {
                    isSuitable = false;
                }
            }

            if(isSuitable)
            {
                result.intervals.push(interval);
            }
        });

        return result;
    }

    /**
     * Определение размеров приемов (в количестве строк таймлайна)
     */
    writeVisitsSize(schedule)
    {
        let timeLine = Object.keys(this.props.timeLine);

        for(let timeStart in schedule.visits)
        {
            if(!schedule.visits.hasOwnProperty(timeStart)) continue;

            let visit = schedule.visits[timeStart];
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
        // get cells from schedule

        this.doctors = this.getDoctors();
        
        let schedule = this.cutSchedule(this.props.schedule);
        console.log(Object.assign({}, schedule), 'schedule snap!');

        schedule.intervals = this.addEmptyIntervals(schedule.intervals);

        let cells = this.getCells(schedule);
        if(this.isEmptyCells(cells))
        {
            return null;
        }

        this.defineMainDoctors(cells);

        const doctors = this.doctors;
        const isOneMainDoctor = (this.mainDoctors[0]) && (this.mainDoctors[0] === this.mainDoctors[1]);

        return (
            <div className="dayCalendar_column">
                <div className="dayCalendar_roomName">{this.props.schedule.chair.name}</div>

                {this.mainDoctors.map((mainDoctorId, index) =>
                    <div className={'dayCalendar_doctor ' + (isOneMainDoctor ? 'sameD' : '') + (mainDoctorId ? '' : ' emptyD')}
                         style={mainDoctorId ? {backgroundColor: doctors[mainDoctorId].color} : {}}
                         key={index}>
                        {mainDoctorId ? doctors[mainDoctorId].fio : ''}
                    </div>
                )}

                {this.renderCells(cells)}

            </div>
        );
    }

    renderCells(cells)
    {
        let result = [];

        for(let time in this.props.timeLine)
        {
            if(!this.props.timeLine.hasOwnProperty(time) || !cells[time]) continue;

            let cellProps = Object.assign({}, cells[time]);

            cellProps.doctor = (cellProps.doctorId ? this.doctors[cellProps.doctorId] : null);
            cellProps.patient = (cellProps.patientId ? this.props.patients[cellProps.patientId] : null);
            cellProps.isMainDoctor = (this.mainDoctors[cellProps.halfDayNum] === cellProps.doctorId);

            cellProps.key = cellProps.timeStart;
            cellProps.commands = this.props.commands;
            cellProps.date = this.props.date;
            cellProps.chairId = this.props.chairId;
            cellProps.onUpdate = this.props.update;

            result.push(
                <Cell {...cellProps}/>
            );
        }

        return result;
    }

    isBlockedInterval(interval)
    {
        let doctor = this.doctors[interval.DOCTOR_ID];
        if(!doctor || !Object.keys(doctor).length) return false;

        let isBlockedByDoctor = !!this.props.filter.doctor && (this.props.filter.doctor !== interval.DOCTOR_ID);
        let isBlockedBySpec = !!this.props.filter.specialization && (this.props.filter.specialization !== doctor.specialization_code);

        return  !!interval.DOCTOR_ID && (isBlockedByDoctor || isBlockedBySpec);
    }
    
    getDoctors()
    {
        let result = {};
        
        this.props.doctors.forEach(doctor =>
        {
            result[doctor.id] = doctor;
        });
        
        return result;
    }

    isEmptyCells(cells)
    {
        let isEmpty = true;

        for(let time in cells)
        {
            let cell = cells[time];
            if (cell.doctorId && !cell.isBlocked) {
                isEmpty = false;
                break;
            }
        }

        return isEmpty;
    }
}

export default Column