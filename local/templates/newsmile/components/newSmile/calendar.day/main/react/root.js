//import React from 'react'

class CalendarDayMain extends React.Component
{
    constructor(props)
    {
        super(props);

        this.timeLimits = {
            start: this.getMoment(props.timeLimits.start),
            end: this.getMoment(props.timeLimits.end),
        };

        this.state = {
            schedule: this.props.schedule
        };

        this.timeLimits.half = this.getHalfTime(this.timeLimits.start, this.timeLimits.end);

        console.log(props);

        this.setData(this.props, true);
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

    getTimeLine()
    {
        let timeLine = {};
        let iterTime = this.getMoment(this.props.startTime);
        let endTime = this.getMoment(this.props.endTime);

        /* сначала таймлайн стандартными интервалами в 30 минут */
        while(iterTime.isBefore(endTime))
        {
            timeLine[iterTime.format('HH:mm')] = 'standard';
            iterTime.add(30, 'minutes');
        }

        /* затем по расписанию врачей (intervals) и приемам (visits) добавляем половинные интервалы (15 минут) */
        this.state.schedule.forEach(chairSchedule =>
        {
            chairSchedule.intervals.concat(Object.values(chairSchedule.visits)).forEach(interval =>
            {
                let startTime = this.getMoment(interval.TIME_START);
                if(startTime.get('minutes') % 30 === 15)
                {
                    timeLine[interval.TIME_START] = 'half';
                }


                let endTime = this.getMoment(interval.TIME_END);
                if(endTime.get('minutes') % 30 === 15)
                {
                    timeLine[interval.TIME_END] = 'half'
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

    render()
    {
        const timeLine = this.getTimeLine();
        const timeLineNode = React.createRef();

        return (
            <div className="dayCalendar_cont" onContextMenu={this.blockEvent}>
                <div className="dayCalendar_header">
                    <span>{this.props.dateTitle}</span>
                </div>

                <div className="dayCalendar_body">
                    {this.state.schedule.map(chairSchedule =>
                        <CalendarDayColumn schedule={chairSchedule} doctors={this.doctors} patients={this.patients} key={chairSchedule.chair.id}
                                           getMoment={this.getMoment.bind(this)} timeLimits={this.timeLimits}
                                           startTime={this.props.startTime} endTime={this.props.endTime}
                                           commands={this.props.commands}
                                           date={this.props.date}
                                           chairId={chairSchedule.chair.id}
                                           timeLine={timeLine}
                                           updateData={this.updateData.bind(this)}
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
                        <CalendarDayCurTime serverTimestamp={this.props.curServerTimestamp}
                                            timeLine={timeLine}
                                            timeLineNode={timeLineNode}
                                            getMoment={this.getMoment.bind(this)}
                        />
                    )}
                </div>
            </div>
        )
    }

    blockEvent(e)
    {
        e.stopPropagation();
        e.preventDefault();
    }

    updateData()
    {
        let data = {
            date: this.props.date,
            timeFrom: this.props.startTime,
            timeTo: this.props.endTime
        };

        let command = new ServerCommand('schedule/get-day-info', data, response =>
        {
            this.setData(response);

            console.log(response, 'updateData!');
        });

        command.exec();
    }

    setData(data, bInitial = false)
    {
        this.doctors = data.doctors;
        this.patients = data.patients;

        if(!bInitial)
        {
            this.setState({
                schedule: data.schedule
            });
        }
    }

    getCurServer

}