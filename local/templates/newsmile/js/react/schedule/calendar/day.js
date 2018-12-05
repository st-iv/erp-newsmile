function CalendarDay(props)
{
    const defaultDayData = {
        generalTime: '00:00',
        engagedTime: '00:00',
        patientCount: 0
    };

    let dayData = Object.assign({}, defaultDayData, props.dayData);
    let dayClassName = 'custCalendar_day';

    const color = props.getColor(dayData.generalTime - dayData.engagedTime);

    let dayStyle = {
        backgroundColor: '#' + color.background,
        color: '#' + color.text
    };

    if(props.isSelected)
    {
        dayClassName += ' day_current active';
    }

    return (
        <div className={dayClassName} key={props.date} onClick={() => props.selectDay(props.date)}>
            <div className="custCalendar_day_content" style={dayStyle}>
                <div className="custCalendar_day_d">{props.day}</div>
                <div className="custCalendar_day_m">{props.curMonth}</div>
            </div>
        </div>
    );
}