import React from 'react'
import Calendar from './calendar/main'
import ScheduleDay from './schedule-day/main'


class Schedule extends React.Component
{
    state = {
        selectedDate: this.props.initialDate,
        timeFrom: this.props.scheduleDay.startTime,
        timeTo: this.props.scheduleDay.endTime,
        scheduleDay: Object.assign({}, this.props.scheduleDay),
        calendarData: Object.assign({}, this.props.calendar.data),
    };

    render()
    {
        return (
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
                    <ScheduleDay {...this.state.scheduleDay} date={this.state.selectedDate} update={this.updateDaySchedule.bind(this)}/>
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
            date: date,
            timeFrom: this.state.timeFrom,
            timeTo: this.state.timeTo
        };

        let command = new ServerCommand('schedule/get-day-info', data, response =>
        {
            this.setState({
                selectedDate: date,
                scheduleDay: response
            });
        });

        command.exec();

        console.log('update!');
    }

    loadCalendar(startDate, endDate)
    {
        let data = {
            dateFrom: startDate,
            dateTo: endDate
        };

        let command = new ServerCommand('schedule/get-calendar', data, response =>
        {
            this.setState({
                calendarData: response
            });
        });

        command.exec();
    }
}

export default Schedule