class Schedule extends React.Component
{
    state = {
        selectedDate: this.props.initialDate,
        timeFrom: this.props.scheduleDay.startTime,
        timeTo: this.props.scheduleDay.endTime,
        scheduleDay: Object.assign({}, this.props.scheduleDay)
    };



    render()
    {
        console.log(this.props, 'schedule!');
        return (
            <div className="row main_content">
                <div className="main_content_left">
                    <div className="left_calendar_cont">
                        <Calendar {...this.props.calendar} setSelectedDate={this.setSelectedDate.bind(this)} curDate={this.state.selectedDate}/>
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
    }
}