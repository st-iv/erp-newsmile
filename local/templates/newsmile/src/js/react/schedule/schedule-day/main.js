import React from 'react'
import CurTime from './cur-time'
import Column from './column'

class ScheduleDay extends React.Component
{
    constructor(props)
    {
        super(props);
    }

    getHalfTime(startTime, endTime)
    {
        let halfDiffSeconds = Math.round(endTime.diff(startTime) / 2000);
        let halfTime = startTime.clone();
        halfTime = halfTime.add(halfDiffSeconds, 'seconds');

        halfTime.set({
            'second': 0,
            'millisecond': 0
        });

        return halfTime;
    }

    getMoment(time)
    {
        return moment(this.props.date + ' ' + time);
    }

    getTimeLine(schedule, limits)
    {
        let timeLine = {};
        let iterTime = limits.startTime.clone();
        let endTime = limits.endTime;
        let lastIntervalTime;

        /*
        сначала заполняем таймлайн стандартными интервалами в 30 минут, первый и последний интервал обрезаются до половинного
        в зависимости от фильтра
         */
        while(iterTime.isBefore(endTime))
        {
            let timeDiff = (iterTime.get('minutes') % 30 === 15) ? 15 : 30;

            let time = iterTime.format('HH:mm');
            timeLine[time] = {
                type: ((timeDiff === 15) ? 'half' : 'standard'),
                height: null
            };

            iterTime.add(timeDiff, 'minutes');
            lastIntervalTime = time
        }

        if(endTime.get('minutes') % 30 === 15)
        {
            // последний интервал - половинный, если конечное время из фильтра кратно 15 минутам
            timeLine[lastIntervalTime].type = 'half';
        }

        /* затем по расписанию врачей (intervals) и приемам (visits) добавляем половинные интервалы (15 минут) */
        schedule.forEach(chairSchedule =>
        {
            chairSchedule.intervals.concat(Object.values(chairSchedule.visits)).forEach(interval =>
            {
                let startTime = this.getMoment(interval.TIME_START);
                if(startTime.get('minutes') % 30 === 15)
                {
                    timeLine[interval.TIME_START].type = 'half';
                }

                if(timeLine[interval.TIME_END])
                {
                    let endTime = this.getMoment(interval.TIME_END);
                    if(endTime.get('minutes') % 30 === 15)
                    {
                        timeLine[interval.TIME_END].type = 'half'
                    }
                }
            });
        });

        let sortedTimeLine = {};

        Object.keys(timeLine).sort().forEach(time =>
        {
            sortedTimeLine[time] = timeLine[time];
        });

        return sortedTimeLine;
    }

    /**
     * Вычисляет границы таймлайна. Если не указаны фильтры по врачу и специальности, в качестве границ таймлайна будут взяты границы фильтра
     * по времени. Иначе верхней границей будет минимальное начальное время непустого незаблокированного (прошедшего по фильтру) интервала,
     * а нижней границей - максимальное конечное время непустого незаблокированного интервала.
     *
     * @param schedule
     * @returns {{startTime, endTime}}
     */
    getTimeLineLimits(schedule)
    {
        let startTime = this.getMoment(this.props.filter.timeFrom);
        let endTime = this.getMoment(this.props.filter.timeTo);

        if(this.props.filter.doctor || this.props.filter.specialization)
        {
            /* обрезаем таймлайн, чтобы убрать лишние пустые и блокированные ячейки по краям */

            let minTimeStart = null;
            let maxTimeEnd = null;

            schedule.forEach(chairSchedule =>
            {
                let firstNotBlockedInterval = null;
                let lastNotBlockedInterval = null;

                chairSchedule.intervals.forEach(interval =>
                {
                    if(!this.isBlockedInterval(interval))
                    {
                        if(!firstNotBlockedInterval)
                        {
                            firstNotBlockedInterval = interval;
                        }

                        lastNotBlockedInterval = interval;
                    }
                });

                if(firstNotBlockedInterval !== null)
                {
                    let firstTimeStart = this.getMoment(firstNotBlockedInterval.TIME_START);

                    if((minTimeStart === null) || firstTimeStart.isBefore(minTimeStart))
                    {
                        minTimeStart = firstTimeStart;
                    }
                }

                if(lastNotBlockedInterval !== null)
                {
                    let lastTimeEnd = this.getMoment(lastNotBlockedInterval.TIME_END);

                    if((maxTimeEnd === null) || lastTimeEnd.isAfter(maxTimeEnd))
                    {
                        maxTimeEnd = lastTimeEnd;
                    }
                }
            });

            if(minTimeStart && startTime.isBefore(minTimeStart))
            {
                startTime = minTimeStart;
            }

            if(maxTimeEnd && endTime.isAfter(maxTimeEnd))
            {
                endTime = maxTimeEnd;
            }
        }

        return {startTime, endTime};
    }

    render()
    {
        this.doctors = this.getDoctors();

        let schedule = General.clone(this.props.schedule);

        /* фильтрация расписания */
        let timeLineLimits = this.getTimeLineLimits(schedule);
        schedule = this.filterSchedule(schedule, timeLineLimits);

        /*
        формирование timeLine - массива значений времени, задающего сетку для всего расписания.
        Визуально timeLine отображается в виде боковых колонок расписания.
        */
        const timeLine = this.getTimeLine(schedule, timeLineLimits);
        const timeLineNode = React.createRef();

        /* ограничения расписания по времени - время начала и время окончания работы клиники, а также середина рабочего дня */
        let timeLimits = {
            start: this.getMoment(this.props.timeLimits.start),
            end: this.getMoment(this.props.timeLimits.end),
        };

        timeLimits.half = this.getHalfTime(timeLimits.start, timeLimits.end);

        /*
        переопределение в фильтре ограничений по времени на основе timeLineLimits (фактическое ограничение по времени может быть строже, чем
        изначально заданный фильтр по времени, тк удаляются крайние строки с пустыми и заблокированными ячейками)
         */

        let filter = Object.assign({}, this.props.filter);
        filter.timeFrom = timeLineLimits.startTime.format('HH:mm');
        filter.timeTo = timeLineLimits.endTime.format('HH:mm');


        /* подготовка производной информации по расписанию - массивов ячеек (cells) и id основных врачей (mainDoctors) */
        schedule.filter(chairSchedule =>
        {
            this.addEmptyIntervals(chairSchedule.intervals, filter);
            chairSchedule.cells = this.getCells(chairSchedule, timeLine);
            chairSchedule.mainDoctors = this.defineMainDoctors(chairSchedule.cells, timeLimits);
            this.defineHeight(timeLine, chairSchedule.cells, chairSchedule.mainDoctors);

            return this.isEmptyCells(chairSchedule.cells);
        });
        /* */

        return (
            <div className="dayCalendar_cont" onContextMenu={this.blockEvent}>
                <div className="dayCalendar_header">
                    <span>{this.props.dateTitle}</span>
                </div>

                <div className="dayCalendar_body">
                    {schedule.map(chairSchedule =>
                        <Column cells={chairSchedule.cells}
                                chair={chairSchedule.chair}
                                mainDoctors={chairSchedule.mainDoctors}
                                doctors={this.doctors}
                                patients={this.props.patients}
                                key={chairSchedule.chair.id}
                                getMoment={this.getMoment.bind(this)}
                                commands={this.props.commands}
                                date={this.props.date}
                                chairId={chairSchedule.chair.id}
                                timeLine={timeLine}
                                update={this.props.update}
                        />
                    )}

                    {this.renderTimeLine(timeLine, null, 'dayCalendar_leftTl')}
                    {this.renderTimeLine(timeLine, timeLineNode, 'dayCalendar_rightTl')}

                    {this.props.isCurDay && (
                        <CurTime serverTimestamp={this.props.curServerTimestamp}
                                            timeLine={timeLine}
                                            timeLineNode={timeLineNode}
                                            getMoment={this.getMoment.bind(this)}
                        />
                    )}
                </div>
            </div>
        )
    }

    renderTimeLine(timeLine, ref, className)
    {
        let timeLineItems = General.mapObj(timeLine, (timeLineItem, time) =>
        {
            let style = {};

            if(timeLineItem.height !== null)
            {
                style.height = timeLineItem.height;
            }

            return (
                <div className={'dayCalendar_timeItem ' + (timeLine[time].type === 'half' ? 'littleTI' : '')}
                     key={time}
                     style={style}>
                    <span>{time}</span>
                </div>
            );
        });

        let timeLineProps = {className};
        if(ref !== null)
        {
            timeLineProps.ref = ref;
        }

        return (
            <div {...timeLineProps}>
                {timeLineItems}
            </div>
        );
    }

    /**
     * Фильтрует расписание - отбрасывает интервалы, которые не подходят по времени (ограничения по времени принимает в аргументе timeLineLimits),
     * нтервалы на границе при необходимости делит пополам (если в фильтре по времени указаны 15 или 45 минут). Помечает заблокированные интервалы
     * @param filteredSchedule
     * @param timeLineLimits
     * @returns {*}
     */
    filterSchedule(filteredSchedule, timeLineLimits)
    {
        let timeFrom = timeLineLimits.startTime;
        let timeTo = timeLineLimits.endTime;

        filteredSchedule.forEach(chairSchedule =>
        {
            let intervals = [];
            let visits = {};

            chairSchedule.intervals.concat(Object.values(chairSchedule.visits)).forEach(interval =>
            {
                /* фильтрация по времени */

                let intervalStartTime = this.getMoment(interval.TIME_START);
                let intervalEndTime = this.getMoment(interval.TIME_END);
                let isSuitable = true;
                let isVisit = !!interval.STATUS;

                /**
                 * время, которое используется для выбора позиции в таблице
                 */
                let shownTimeStart = interval.TIME_START;

                if(intervalStartTime.isBefore(timeFrom))
                {

                    if(intervalEndTime.isAfter(timeFrom))
                    {
                        // значит начальным временем из фильтра распилили интервал на части, нужно подменить начальное время интервала
                        shownTimeStart = timeFrom.format('HH:mm');
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
                        if(!isVisit)
                        {
                            // значит конечным временем из фильтра распилили интервал на части, нужно подменить конечное время интервала
                            interval.TIME_END = timeTo.format('HH:mm');
                        }
                    }
                    else
                    {
                        isSuitable = false;
                    }
                }

                if(isSuitable)
                {
                    /* вычисляем, блокирован ли интервал (фильтрация по врачу и специальности) */

                    interval.isBlocked = this.isBlockedInterval(interval);
                    if(isVisit)
                    {
                        visits[shownTimeStart] = interval;
                    }
                    else
                    {
                        interval.TIME_START = shownTimeStart;
                        intervals.push(interval);
                    }
                }
            });

            chairSchedule.intervals = intervals;
            chairSchedule.visits = visits;
        });

        return filteredSchedule;
    }

    /**
     * Проверяет, является ли массив ячеек пустым (не содержит ячеек вообще, либо содержит ячейки с неназначенными врачами,
     * либо заблокированные)
     * @param cells
     * @returns {boolean}
     */
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

    /**
     * Проверяет, является ли интервал заблокированным. Заблокированный интервал - это интервал, не подходящий под фильтр по врачу,
     * либо по специальности
     * @param interval
     * @returns {boolean}
     */
    isBlockedInterval(interval)
    {
        let doctor = this.doctors[interval.DOCTOR_ID];
        if(!doctor || !Object.keys(doctor).length) return false;

        let isBlockedByDoctor = !!this.props.filter.doctor && (this.props.filter.doctor !== interval.DOCTOR_ID);
        let isBlockedBySpec = !!this.props.filter.specialization && (this.props.filter.specialization !== doctor.specialization_code);

        return  !!interval.DOCTOR_ID && (isBlockedByDoctor || isBlockedBySpec);
    }

    /**
     * Получает объект врачей из массива (указывает id врачей в качестве ключей)
     */
    getDoctors()
    {
        let result = {};

        this.props.doctors.forEach(doctor =>
        {
            result[doctor.id] = doctor;
        });

        return result;
    }



    /**
     * Добавляет пустые интервалы (интервалы расписания, для которых не назначен врач).
     * @param intervals
     * @returns {Array}
     */
    addEmptyIntervals(intervals, filter)
    {
        let result = [];
        let prevEndTime = filter.timeFrom;
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

            if((index === count - 1) && (filter.timeTo !== interval.TIME_END))
            {
                result.push({
                    TIME_START: interval.TIME_END,
                    TIME_END: filter.timeTo,
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
    getCells(schedule, timeLine)
    {
        let intervals = schedule.intervals.slice();

        let interval;
        let intervalEndTime;

        let result = {};

        /*
        Массив ячеек - это объединенные массивы интервалов(расписания врачей) и приемов.
        Сначала в массив ячеек добавляем интервалы
        */

        for(let time in timeLine)
        {
            if(!timeLine.hasOwnProperty(time)) continue;

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
                let duration = ((timeLine[time].type === 'half') ? 15 : 30);

                result[time] = {
                    timeStart: time,
                    timeEnd: moment.add(duration, 'minute').format('HH:mm'),
                    doctorId: interval.DOCTOR_ID,
                    halfDayNum: interval.halfDayNum,
                    isBlocked: interval.isBlocked
                };
            }
        }

        this.writeVisitsSize(schedule, timeLine);


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
                cell.timeStart = visit.TIME_START;
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
    writeVisitsSize(schedule, timeLine)
    {
        timeLine = Object.keys(timeLine);

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

    /**
     * Возвращает массив mainDoctors (основных врачей) для указанного массива ячеек.
     * @param cells
     * @param timeLimits
     * @returns {number[]}
     */
    defineMainDoctors(cells, timeLimits)
    {
        let votes = [];

        for(let time in cells)
        {
            if(!cells.hasOwnProperty(time)) continue;

            let cell = cells[time];

            if(!cell.doctorId || cell.isBlocked) continue;

            let timeMoment = this.getMoment(cell.timeStart);
            cell.halfDayNum = timeMoment.isBefore(timeLimits.half) ? 0 : 1;

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

        let mainDoctors = [0, 0];

        votes.forEach((doctorsVotes, halfDayNum) =>
        {
            let maxVotes = 0;

            for(let doctorId in doctorsVotes)
            {
                if(doctorsVotes[doctorId] > maxVotes)
                {
                    maxVotes = doctorsVotes[doctorId];
                    mainDoctors[halfDayNum] = Number(doctorId);
                }
            }
        });

        return mainDoctors;
    }

    defineHeight(timeLine, cells, mainDoctors)
    {
        General.forEachObj(timeLine, (timeLineItem, time) =>
        {
            let cell = cells[time];
            let height;

            if(cell.patientId && (cell.doctorId !== mainDoctors[cell.halfDayNum]))
            {
                height = 46;
            }
            else
            {
                height = null;
            }

            if(height > timeLineItem.height)
            {
                timeLineItem.height = height;
            }
        })
    }

    blockEvent(e)
    {
        e.stopPropagation();
        e.preventDefault();
    }
}

export default ScheduleDay