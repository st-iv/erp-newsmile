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
            timeLine[time] = ((timeDiff === 15) ? 'half' : 'standard');

            iterTime.add(timeDiff, 'minutes');
            lastIntervalTime = time
        }

        if(endTime.get('minutes') % 30 === 15)
        {
            // последний интервал - половинный, если конечное время из фильтра кратно 15 минутам
            timeLine[lastIntervalTime] = 'half';
        }

        /* затем по расписанию врачей (intervals) и приемам (visits) добавляем половинные интервалы (15 минут) */
        schedule.forEach(chairSchedule =>
        {
            chairSchedule.intervals.concat(Object.values(chairSchedule.visits)).forEach(interval =>
            {
                let startTime = this.getMoment(interval.TIME_START);
                if(startTime.get('minutes') % 30 === 15)
                {
                    timeLine[interval.TIME_START] = 'half';
                }

                if(timeLine[interval.TIME_END])
                {
                    let endTime = this.getMoment(interval.TIME_END);
                    if(endTime.get('minutes') % 30 === 15)
                    {
                        timeLine[interval.TIME_END] = 'half'
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
     * по времени. Иначе верхней границей будет минимальное начальное время незаблокированного (прошедшего по фильтру) интервала,
     * а нижней границей - максимальное конечное время незаблокированного интервала.
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

        let timeLineLimits = this.getTimeLineLimits(schedule);
        schedule = this.filterSchedule(schedule, timeLineLimits);

        const timeLine = this.getTimeLine(schedule, timeLineLimits);
        const timeLineNode = React.createRef();

        let timeLimits = {
            start: this.getMoment(this.props.timeLimits.start),
            end: this.getMoment(this.props.timeLimits.end),
        };

        let filter = Object.assign({}, this.props.filter);
        filter.timeFrom = timeLineLimits.startTime.format('HH:mm');
        filter.timeTo = timeLineLimits.endTime.format('HH:mm');

        timeLimits.half = this.getHalfTime(timeLimits.start, timeLimits.end);

        return (
            <div className="dayCalendar_cont" onContextMenu={this.blockEvent}>
                <div className="dayCalendar_header">
                    <span>{this.props.dateTitle}</span>
                </div>

                <div className="dayCalendar_body">
                    {schedule.map(chairSchedule =>
                        <Column schedule={chairSchedule}
                                doctors={this.doctors}
                                patients={this.props.patients}
                                key={chairSchedule.chair.id}
                                getMoment={this.getMoment.bind(this)}
                                timeLimits={timeLimits}
                                commands={this.props.commands}
                                date={this.props.date}
                                chairId={chairSchedule.chair.id}
                                timeLine={timeLine}
                                update={this.props.update}
                                filter={filter}
                        />
                    )}

                    {['dayCalendar_leftTl', 'dayCalendar_rightTl'].map(className =>
                        <div ref={timeLineNode} className={className} key={className}>
                            {Object.keys(timeLine).map(time =>
                                <div className={'dayCalendar_timeItem ' + (timeLine[time] === 'half' ? 'littleTI' : '')}
                                     key={time}>
                                    <span>{time}</span>
                                </div>
                            )}
                        </div>
                    )}

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

    blockEvent(e)
    {
        e.stopPropagation();
        e.preventDefault();
    }
}

export default ScheduleDay