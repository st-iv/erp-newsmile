import React from 'react'
import $ from 'jquery'
import Calendar from './calendar/main'
import ScheduleDay from './schedule-day/main'
import Filter from './filter/main'
import PopupManager from './../popup-manager'
import ServerCommand from './../../common/server/server-command'
import Helper from './../../common/helpers/main'
import ScheduleProcessor from './../../common/schedule/schedule-processor'
import CookieHelper from "./../../common/helpers/cookie-helper"
import GeneralHelper from "./../../common/helpers/general-helper"
import ScheduleScroll from './schedule-scroll'

class Schedule extends React.PureComponent
{
    defaultFilter = {
        timeFrom: this.props.schedule.timeLimits.start,
        timeTo: this.props.schedule.timeLimits.end,
        doctor: 0,
        specialization: ''
    };

    state = {
        schedule: Helper.clone(this.props.schedule),
        calendarData: Object.assign({}, this.props.calendar.data),
        filter: Object.assign({}, this.defaultFilter),
        splittedTime: this.loadSplittedTime()
    };

    constructor(props)
    {
        super(props);
        this.finalize = this.finalize.bind(this);
    }

    render()
    {
        let scheduleProcessor = new ScheduleProcessor(
            this.state.schedule,
            this.state.filter,
            this.props.doctors.list,
            this.state.splittedTime
        );

        scheduleProcessor.process();

        const schedule = scheduleProcessor.getSchedule();
        const timeLine = scheduleProcessor.getTimeLine();
        const filter = scheduleProcessor.getFilter();
        const availableTimeUnite = scheduleProcessor.getAvailableTimeUnite();
        let dayCounter = 0;
        let daysCount = Object.keys(schedule.days).length;

        const scheduleDates = Object.keys(schedule.days);

        return (
            <div>
                <Filter doctors={this.props.doctors.list}
                        setFilter={this.setFilter.bind(this)}
                        startTime={this.state.schedule.timeLimits.start}
                        endTime={this.state.schedule.timeLimits.end}
                        defaultFilter={this.defaultFilter}
                />

                <div className="row main_content">
                    <div className="main_content_left">
                        <div className="left_calendar_cont">
                            <Calendar colorsScheme={this.props.calendar.colorsScheme}
                                      data={this.state.calendarData}
                                      initialDate={this.props.initialDate}
                                      onSelect={this.handleSelectedDates.bind(this)}
                                      load={this.loadCalendar.bind(this)}
                            />
                        </div>
                    </div>

                    <ScheduleScroll className="main_content_center" contentStamp={Object.keys(schedule.days)}>
                        {Helper.mapObj(schedule.days, (daySchedule, date) =>
                        {
                            dayCounter++;

                            return (
                                <ScheduleDay {...daySchedule}
                                             timeLimits={schedule.timeLimits}
                                             timeLine={timeLine}
                                             availableTimeUnite={availableTimeUnite}
                                             curServerTimestamp={schedule.curServerTimestamp}
                                             commands={schedule.commands}
                                             date={date}
                                             update={this.updateDaySchedule.bind(this, [date])}
                                             splitInterval={this.splitInterval.bind(this, scheduleDates)}
                                             uniteInterval={this.uniteInterval.bind(this, scheduleDates)}
                                             filter={filter}
                                             doctors={this.props.doctors.list}
                                             patients={schedule.patients}

                                             showLeftTimeLine={dayCounter === 1}
                                             centerRightTimeLine={daysCount !== dayCounter}

                                             key={date}
                                />
                            );
                        })}
                    </ScheduleScroll>

                </div>

                <PopupManager />
            </div>
        );
    }

    handleSelectedDates(selectedDates)
    {
        let loadedDates = Object.keys(this.state.schedule.days);
        let newDates = selectedDates.diff(loadedDates);

        if(newDates.length)
        {
            this.updateDaySchedule(newDates, selectedDates);
        }
        else
        {
            let newSchedule = Helper.clone(this.state.schedule);
            newSchedule.days = this.deleteExcessDays(selectedDates, this.state.schedule.days);
            this.setState({schedule: newSchedule});
        }
    }

    deleteExcessDays(necessaryDates, days)
    {
        return Helper.filterObj(days, (daySchedule, date) =>
        {
            return (necessaryDates.indexOf(date) !== -1);
        });
    }

    /**
     * Загружает расписание за определённые дни (при этом загруженное расписание за остальные дни не затрагивается)
     * @param dates - даты для загрузки
     * @param allDates - дни, которые нужно отображать после загрузки. По умолчанию отображаются все дни, в ином случае
     * будут удалены те дни, которые не указаны в этом параметре.
     */
    updateDaySchedule(dates = null, allDates = null)
    {
        if(!dates)
        {
            dates = Object.keys(this.state.schedule.days);
        }

        let command = new ServerCommand('schedule/get-days-info', {dates});

        command.exec().then(response =>
        {
            let newSchedule = Helper.clone(this.state.schedule);
            Object.assign(newSchedule.days, response.days);
            Object.assign(newSchedule.patients, response.patients);

            if(allDates)
            {
                newSchedule.days = this.deleteExcessDays(allDates, newSchedule.days);
            }

            newSchedule.days = Helper.sortObj(newSchedule.days);

            /* resolve может модифицировать новое состояние */
            this.setState({schedule: newSchedule});
        });
    }

    loadCalendar(startDate = null, endDate = null, filter = null)
    {
        filter = filter || this.state.filter;

        let data = Object.assign({}, filter);
        data.dateFrom = startDate || this.state.calendarData.dateFrom;
        data.dateTo = endDate || this.state.calendarData.dateTo;

        let command = new ServerCommand('schedule/get-calendar', data, response =>
        {
            this.setState({
                calendarData: response
            });
        });

        command.exec();
    }

    setFilter(filter)
    {
        this.setState({filter});
        this.loadCalendar(null, null, filter);
    }

    /**
     * Грузит разделённое время из куков
     */
    loadSplittedTime()
    {
        let splittedTime = CookieHelper.getCookie('scheduleSplittedTime');
        return splittedTime ? JSON.parse(splittedTime) : {};
    }

    componentWillMount()
    {
        $(window).on('unload', this.finalize);
    }

    componentWillUnmount()
    {
        this.finalize();
        $(window).off('unload', this.finalize);
    }

    finalize()
    {
        CookieHelper.setCookie('scheduleSplittedTime', JSON.stringify(this.state.splittedTime));
    }

    splitInterval(dates, time)
    {
        let splittedTime = GeneralHelper.clone(this.state.splittedTime);

        dates.map(date =>
        {
            if(!splittedTime[date])
            {
                splittedTime[date] = [];
            }

            if(splittedTime[date].indexOf(time) === -1)
            {
                splittedTime[date].push(time);
            }
        });

        this.setState({splittedTime});
    }

    uniteInterval(dates, time)
    {
        let splittedTime = GeneralHelper.clone(this.state.splittedTime);

        dates.map(date =>
        {
            if(!splittedTime[date]) return;

            time = Helper.Date.getStandardIntervalTime(time);

            splittedTime[date].splice(splittedTime[date].indexOf(time), 1);
        });

        this.setState({splittedTime});
    }
}

export default Schedule