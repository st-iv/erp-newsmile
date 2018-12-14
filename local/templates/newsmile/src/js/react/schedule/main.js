import React from 'react'
import Calendar from './calendar/main'
import ScheduleDay from './schedule-day/main'
import Filter from './filter/main'


class Schedule extends React.Component
{
    defaultFilter = {
        timeFrom: this.props.scheduleDay.startTime,
        timeTo: this.props.scheduleDay.endTime,
        doctor: 0,
        specialization: ''
    };

    state = {
        selectedDate: this.props.initialDate,
        scheduleDay: Object.assign({}, this.props.scheduleDay),
        calendarData: Object.assign({}, this.props.calendar.data),
        filter: Object.assign({}, this.defaultFilter),
    };

    render()
    {
        console.log(this.props, 'schedule props!');

        return (
            <div>
                <Filter doctors={this.props.doctors.list}
                        setFilter={this.setFilter.bind(this)}
                        startTime={this.props.scheduleDay.startTime}
                        endTime={this.props.scheduleDay.endTime}
                        defaultFilter={this.defaultFilter}
                />

                <div className="row main_content">
                    <div className="main_content_left">
                        <div className="left_calendar_cont">
                            <Calendar colorsScheme={this.props.calendar.colorsScheme}
                                      data={this.state.calendarData}
                                      setSelectedDate={this.setSelectedDate.bind(this)}
                                      load={this.loadCalendar.bind(this)}
                                      curDate={this.state.selectedDate}/>
                        </div>
                    </div>

                    <div className="main_content_center">
                        <ScheduleDay {...this.state.scheduleDay}
                                     date={this.state.selectedDate}
                                     update={this.updateDaySchedule.bind(this)}
                                     filter={this.state.filter}
                                     doctors={this.props.doctors.list}
                        />
                    </div>
                </div>
            </div>
        );
    }

    setSelectedDate(date)
    {
        if(date !== this.state.selectedDate)
        {
            this.updateDaySchedule(date);
        }
    }

    updateDaySchedule(date = null)
    {
        if(!date)
        {
            date = this.state.selectedDate;
        }

        let data = {
            date: date
        };

        data = Object.assign(data, this.filter);

        let command = new ServerCommand('schedule/get-day-info', data, response =>
        {
            this.setState({
                selectedDate: date,
                scheduleDay: response
            });
        });

        command.exec();
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
}

export default Schedule