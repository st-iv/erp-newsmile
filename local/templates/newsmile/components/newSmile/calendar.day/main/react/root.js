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

        this.timeLimits.half = this.getHalfTime(this.timeLimits.start, this.timeLimits.end);

        this.timeLine = this.getTimeLine();
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
        return moment(this.props.curDate + ' ' + time);
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
        this.props.schedule.forEach(chairSchedule =>
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
        const schedule = this.props.schedule;
        const doctors = this.props.doctors;
        const patients = this.props.patients;

        return (
            <div className="dayCalendar_cont">
                <div className="dayCalendar_header">
                    <span>{this.props.curDateTitle}</span>
                </div>

                <div className="dayCalendar_body">
                    {schedule.map(chairSchedule =>
                        <CalendarDayColumn schedule={chairSchedule} doctors={doctors} patients={patients} key={chairSchedule.chair.id}
                                           getMoment={this.getMoment.bind(this)} timeLimits={this.timeLimits}
                                           startTime={this.props.startTime} endTime={this.props.endTime}
                                           timeLine={this.timeLine}/>
                    )}

                    {['dayCalendar_leftTl', 'dayCalendar_rightTl'].map(className =>
                        <div className={className} key={className}>
                            {Object.keys(this.timeLine).map(time =>
                                <div className={'dayCalendar_timeItem ' + (this.timeLine[time] === 'half' ? 'littleTI' : '')}
                                     key={time}>
                                    <span>{time}</span>
                                </div>
                            )}
                        </div>
                    )}
                </div>
            </div>
        )
    }
}