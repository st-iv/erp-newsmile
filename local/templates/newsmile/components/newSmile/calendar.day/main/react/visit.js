function CalendarDayVisit(props)
{
    return (
        <div className="dayCalendar_interval" style={{backgroundColor: General.Color.lighten(props.doctor.color, 30)}}>
            {props.patient.fio}
        </div>
    )
}