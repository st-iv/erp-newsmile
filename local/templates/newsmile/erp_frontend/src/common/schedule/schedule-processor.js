import Helper from './../../common/helpers/main'
import GeneralHelper from "./../../common/helpers/general-helper"
import moment from 'moment'

export default class ScheduleProcessor
{
    constructor(schedule, filter, doctors, splittedTime)
    {
        this.schedule = Helper.clone(schedule);
        this.filter = Helper.clone(filter);
        this.doctors = doctors;
        this.splittedTime = splittedTime;
    }

    process()
    {
        /* собираем расписание на все дни в единый массив */

        let unitedSchedule = [];
        let scheduleDateMap = [];

        Helper.forEachObj(this.schedule.days, (dayInfo, date) =>
        {
            dayInfo.schedule.forEach(() =>
            {
                scheduleDateMap.push(date);
            });
            unitedSchedule = unitedSchedule.concat(dayInfo.schedule);
        });

        /* фильтрация расписания */
        let timeLineLimits = this._getTimeLineLimits(unitedSchedule);
        unitedSchedule = this._filterSchedule(unitedSchedule, timeLineLimits);

        /*
        формирование timeLine - массива значений времени, задающего сетку для всего расписания.
        */
        let timeLine = this._getTimeLine(unitedSchedule, timeLineLimits);

        // делим timeLine в зависимости от разделённых пользователем интервалов

        let availableTimeUnite = this._getAvailableTimeUnite(timeLine);

        !!availableTimeUnite && availableTimeUnite.forEach(time =>
        {
            this._splitTimeLine(timeLine, time);
        });

        // сортировка timeLine по возрастанию времени
        timeLine = this._sortTimeLine(timeLine);

        /* разбиение интервалов на ячейки таблицы, определение главных врачей */

        // ограничения расписания по времени - время начала и время окончания работы клиники, а также середина рабочего дня
        let timeLimits = {
            start: this._getMoment(this.schedule.timeLimits.start),
            end: this._getMoment(this.schedule.timeLimits.end),
        };

        timeLimits.half = this._getHalfTime(timeLimits.start, timeLimits.end);

        unitedSchedule.filter(chairSchedule =>
        {
            this._addEmptyIntervals(chairSchedule.intervals, this.filter);
            chairSchedule.cells = this._getCells(chairSchedule, timeLine);
            chairSchedule.mainDoctors = this._defineMainDoctors(chairSchedule.cells, timeLimits);
            this._defineHeight(timeLine, chairSchedule.cells, chairSchedule.mainDoctors);
            return !this._isEmptyCells(chairSchedule.cells);
        });


        /*
        переопределение в фильтре ограничений по времени на основе timeLineLimits (фактическое ограничение по времени может быть строже, чем
        изначально заданный фильтр по времени, тк удаляются крайние строки с пустыми и заблокированными ячейками)
         */

        this.filter.timeFrom = timeLineLimits.startTime.format('HH:mm');
        this.filter.timeTo = timeLineLimits.endTime.format('HH:mm');

        /* распределяем расписание из единого массива обратно по дням */

        Helper.forEachObj(this.schedule.days, dayInfo =>
        {
            dayInfo.schedule = [];
        });

        unitedSchedule.forEach((chairSchedule, index) =>
        {
            let date = scheduleDateMap[index];
            this.schedule.days[date].schedule.push(chairSchedule);
        });

        this.timeLine = timeLine;
        this.availableTimeUnite = availableTimeUnite;
    }


    /**
     * Вычисляет границы таймлайна. Если не указаны фильтры по врачу и специальности, в качестве границ таймлайна будут взяты границы фильтра
     * по времени. Иначе верхней границей будет минимальное начальное время непустого незаблокированного (прошедшего по фильтру) интервала,
     * а нижней границей - максимальное конечное время непустого незаблокированного интервала.
     *
     * @param schedule
     * @returns {{startTime, endTime}}
     */
    _getTimeLineLimits(schedule)
    {
        let startTime = this._getMoment(this.filter.timeFrom);
        let endTime = this._getMoment(this.filter.timeTo);

        if(this.filter.doctor || this.filter.specialization)
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
                    if(!this._isBlockedInterval(interval))
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
                    let firstTimeStart = this._getMoment(firstNotBlockedInterval.TIME_START);

                    if((minTimeStart === null) || firstTimeStart.isBefore(minTimeStart))
                    {
                        minTimeStart = firstTimeStart;
                    }
                }

                if(lastNotBlockedInterval !== null)
                {
                    let lastTimeEnd = this._getMoment(lastNotBlockedInterval.TIME_END);

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

    _getTimeLine(schedule, limits)
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
                let startTime = this._getMoment(interval.TIME_START);
                if(startTime.get('minutes') % 30 === 15)
                {
                    startTime.add(-15, 'm');
                    this._splitTimeLine(timeLine, startTime.format('HH:mm'));
                }

                let endTime = this._getMoment(interval.TIME_END);
                if(endTime.get('minutes') % 30 === 15)
                {
                    endTime.add(-15, 'm');
                    this._splitTimeLine(timeLine, endTime.format('HH:mm'));
                }
            });
        });

        return timeLine;
    }
    
    _getMoment(time)
    {
        return moment(time, 'HH:mm');
    }

    /**
     * Проверяет, является ли интервал заблокированным. Заблокированный интервал - это интервал, не подходящий под фильтр по врачу,
     * либо по специальности
     * @param interval
     * @returns {boolean}
     */
    _isBlockedInterval(interval)
    {
        let doctor = this.doctors[interval.DOCTOR_ID];
        if(!doctor || !Object.keys(doctor).length) return false;

        let isBlockedByDoctor = !!this.filter.doctor && (this.filter.doctor !== interval.DOCTOR_ID);
        let isBlockedBySpec = !!this.filter.specialization && (this.filter.specialization !== doctor.specialization_code);

        return  !!interval.DOCTOR_ID && (isBlockedByDoctor || isBlockedBySpec);
    }

    /**
     * Фильтрует расписание - отбрасывает интервалы, которые не подходят по времени (ограничения по времени принимает в аргументе timeLineLimits),
     * нтервалы на границе при необходимости делит пополам (если в фильтре по времени указаны 15 или 45 минут). Помечает заблокированные интервалы
     * @param schedule
     * @param timeLineLimits
     * @returns {*}
     */
    _filterSchedule(schedule, timeLineLimits)
    {
        let timeFrom = timeLineLimits.startTime;
        let timeTo = timeLineLimits.endTime;

        schedule.forEach(chairSchedule =>
        {
            let intervals = [];
            let visits = {};

            chairSchedule.intervals.concat(Object.values(chairSchedule.visits)).forEach(interval =>
            {
                /* фильтрация по времени */

                let intervalStartTime = this._getMoment(interval.TIME_START);
                let intervalEndTime = this._getMoment(interval.TIME_END);
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

                    interval.isBlocked = this._isBlockedInterval(interval);
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

        return schedule;
    }

    /**
     * Делит определённый элемент таймлайна
     * @param timeLine
     * @param time - начальное время разделяемого элемента
     */
    _splitTimeLine(timeLine, time)
    {
        if((time in timeLine) && (timeLine[time].type === 'standard'))
        {
            const nextTime = this._getMoment(time).add(15, 'm').format('HH:mm');
            timeLine[nextTime] = {
                type: 'half',
                height: null
            };

            timeLine[time].type = 'half';
        }
    }

    /**
     * Получает массив времени, которое можно объединить
     * @param timeLine
     * @returns {*}
     */
    _getAvailableTimeUnite(timeLine)
    {
        let result = [];
        Helper.mapObj(this.splittedTime, (dateSplittedTime, date) =>
        {
            if(!!this.schedule.days[date])
            {
                result = result.concat(dateSplittedTime);
            }
        });

        return result.filter(splittedTime =>
        {
            return !!timeLine[splittedTime] && (timeLine[splittedTime].type === 'standard');
        })
    }

    _sortTimeLine(timeLine)
    {
        let sortedTimeLine = {};

        Object.keys(timeLine).sort().forEach(time =>
        {
            sortedTimeLine[time] = timeLine[time];
        });

        return sortedTimeLine;
    }

    /**
     * Добавляет пустые интервалы (интервалы расписания, для которых не назначен врач).
     * @param intervals
     * @param filter
     * @returns {Array}
     */
    _addEmptyIntervals(intervals, filter)
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
    _getCells(schedule, timeLine)
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

            let moment = this._getMoment(time);

            if(!interval || !moment.isBefore(intervalEndTime))
            {
                if(intervals.length)
                {
                    interval = intervals.shift();
                    intervalEndTime = this._getMoment(interval.TIME_END);
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

        this._writeVisitsSize(schedule, timeLine);


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
    _writeVisitsSize(schedule, timeLine)
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

    _getHalfTime(startTime, endTime)
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

    /**
     * Возвращает массив mainDoctors (основных врачей) для указанного массива ячеек.
     * @param cells
     * @param timeLimits
     * @returns {number[]}
     */
    _defineMainDoctors(cells, timeLimits)
    {
        let votes = [];

        for(let time in cells)
        {
            if(!cells.hasOwnProperty(time)) continue;

            let cell = cells[time];

            if(!cell.doctorId || cell.isBlocked) continue;

            let timeMoment = this._getMoment(cell.timeStart);
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

    /**
     * Проверяет, является ли массив ячеек пустым (не содержит ячеек вообще, либо содержит ячейки с неназначенными врачами,
     * либо заблокированные)
     * @param cells
     * @returns {boolean}
     */
    _isEmptyCells(cells)
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

    _defineHeight(timeLine, cells, mainDoctors)
    {
        GeneralHelper.forEachObj(timeLine, (timeLineItem, time) =>
        {
            if(timeLineItem.height) return;

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

    getTimeLine()
    {
        return this.timeLine;
    }

    getFilter()
    {
        return this.filter;
    }

    getAvailableTimeUnite()
    {
        return this.availableTimeUnite;
    }

    getSchedule()
    {
        return this.schedule;
    }
}