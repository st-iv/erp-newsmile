import Helper from 'js/helpers/main'

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
            this._splitTimeLine(this.timeLine, time);
        });

        // сортировка timeLine по возрастанию времени
        timeLine = this._sortTimeLine(timeLine);

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
                result.concat(dateSplittedTime);
            }
        });

        return result.filter(splittedTime =>
        {
            return (splittedTime in timeLine) && (timeLine[splittedTime].type === 'standard');
        })
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

    _sortTimeLine(timeLine)
    {
        let sortedTimeLine = {};

        Object.keys(timeLine).sort().forEach(time =>
        {
            sortedTimeLine[time] = timeLine[time];
        });

        return sortedTimeLine;
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