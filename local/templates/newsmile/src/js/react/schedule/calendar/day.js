import React from 'react'
import DateHelper from 'js/helpers/date-helper'

class Day extends React.Component
{
    render()
    {
        let dayData = Object.assign({}, this.props.dayData);

        let dayClassName = 'custCalendar_day day_tooltip';
        let self = this.constructor;
        let freeTime = (dayData.isEmpty ? null : (dayData.generalTime - dayData.engagedTime));

        const color = this.props.getColor(freeTime);

        let dayStyle = {
            backgroundColor: '#' + color.background,
            color: '#' + color.text
        };

        if(dayData.isCurrent)
        {
            dayClassName += ' day_current';
        }

        if(this.props.isSelected)
        {
            dayClassName += ' active';
        }

        if(dayData.isEmpty)
        {
            dayClassName += ' blocked';
        }

        return (
            <div className={dayClassName} key={this.props.date}
                 onClick={this.props.onClick} {...self.getTooltipData(dayData)}>
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

        if(!dayData.isEmpty)
        {
            let freeTime = DateHelper.formatMinutes(dayData.generalTime - dayData.engagedTime);
            let generalTime = DateHelper.formatMinutes(dayData.generalTime);

            title += '<div>Пациентов - ' + Number(dayData.patientsCount) + '</div>';
            title += '<div>Свободно - ' + freeTime + ' из ' + generalTime + '</div>';
        }
        else
        {
            title = dayData.isAvailable ? 'Нет доступного времени' : 'Расписание на день не составлено';
        }

        return {
            'data-tip': title
        };
    }
}

export default Day