import React from 'react'
import CurTime from './cur-time'
import Column from './column'
import GeneralHelper from 'js/helpers/general-helper'
import CookieHelper from 'js/helpers/cookie-helper';


class ScheduleDay extends React.Component
{
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


    render()
    {
        this.doctors = this.getDoctors();

        const timeLineNode = React.createRef();
        const timeLine = this.props.timeLine;

        /* ограничения расписания по времени - время начала и время окончания работы клиники, а также середина рабочего дня */
        let timeLimits = {
            start: this.getMoment(this.props.timeLimits.start),
            end: this.getMoment(this.props.timeLimits.end),
        };

        timeLimits.half = this.getHalfTime(timeLimits.start, timeLimits.end);

        /* подготовка производной информации по расписанию - массивов ячеек (cells) и id основных врачей (mainDoctors) */
        const schedule = this.props.schedule.filter(chairSchedule =>
        {
            this.addEmptyIntervals(chairSchedule.intervals, this.props.filter);
            chairSchedule.cells = this.getCells(chairSchedule, timeLine);
            chairSchedule.mainDoctors = this.defineMainDoctors(chairSchedule.cells, timeLimits);
            this.defineHeight(timeLine, chairSchedule.cells, chairSchedule.mainDoctors);

            return !this.isEmptyCells(chairSchedule.cells);
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
                                splitInterval={this.props.splitInterval}
                                uniteInterval={this.props.uniteInterval}
                                availableTimeUnite={this.props.availableTimeUnite}
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
        let timeLineItems = GeneralHelper.mapObj(timeLine, (timeLineItem, time) =>
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
                    isBlocked: interval.isBlocked,
                    isHalf: (timeLine[time].type === 'half')
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

        console.log(resultWithVisits);

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
        GeneralHelper.forEachObj(timeLine, (timeLineItem, time) =>
        {
            let cell = cells[time];
            if(!cell) return;

            let height;

            if(cell.patientId && (cell.doctorId !== mainDoctors[cell.halfDayNum]) && (cell.size === 1))
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