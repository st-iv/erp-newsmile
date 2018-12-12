import React from 'react'

class Day extends React.Component
{
    render()
    {
        let dayData = Object.assign({}, this.props.dayData);
        let dayClassName = 'custCalendar_day day_tooltip';
        let self = this.constructor;

        const color = this.props.getColor(dayData.generalTime - dayData.engagedTime);

        let dayStyle = {
            backgroundColor: '#' + color.background,
            color: '#' + color.text
        };

        if(this.props.isSelected)
        {
            dayClassName += ' day_current active';
        }

        return (
            <div className={dayClassName} key={this.props.date}
                 onClick={() => this.props.selectDay(this.props.date)} {...self.getTooltipData(dayData)}>
                <div className="custCalendar_day_content" style={dayStyle}>
                    <div className="custCalendar_day_d">{this.props.day}</div>
                    <div className="custCalendar_day_m">{this.props.curMonth}</div>
                </div>
            </div>
        );
    }

    static getTooltipData(dayData)
    {
        let title = '';

        if(!$.isEmptyObject(dayData))
        {
            let freeTime = General.Date.formatMinutes(dayData.generalTime - dayData.engagedTime);
            let generalTime = General.Date.formatMinutes(dayData.generalTime);

            title += '<div>Пациентов - ' + Number(dayData.patientsCount) + '</div>';
            title += '<div>Свободно - ' + freeTime + ' из ' + generalTime + '</div>';
        }
        else
        {
            title = 'Расписание на день не составлено';
        }

        return {
            'data-tip': title
        };
    }
}

export default Day