import React from 'react'
import moment from 'moment'
import CurTime from './cur-time'
import Column from './column'
import GeneralHelper from './../../../common/helpers/general-helper'

class ScheduleDay extends React.PureComponent
{
    getMoment(time)
    {
        return moment(this.props.date + ' ' + time);
    }

    render()
    {
        this.doctors = this.getDoctors();

        const timeLineNode = React.createRef();
        const timeLine = this.props.timeLine;

        return (
            <div className="dayCalendar_cont" onContextMenu={this.blockEvent}>
                <div className="dayCalendar_header">
                    <span>{this.props.dateTitle}</span>
                </div>

                <div className="dayCalendar_body">
                    {this.props.schedule.map(chairSchedule =>
                        <Column cells={chairSchedule.cells}
                                chair={chairSchedule.chair}
                                mainDoctors={chairSchedule.mainDoctors}
                                doctors={this.doctors}
                                patients={this.props.patients}
                                key={chairSchedule.chair.id}
                                getMoment={this.getMoment.bind(this)}
                                commands={this.props.commands}
                                date={this.props.date}
                                chairId={chairSchedule.chair.id}
                                timeLine={timeLine}
                                update={this.props.update}
                                splitInterval={this.props.splitInterval}
                                uniteInterval={this.props.uniteInterval}
                                availableTimeUnite={this.props.availableTimeUnite}
                        />
                    )}

                    {this.props.showLeftTimeLine && this.renderTimeLine(timeLine, null, 'dayCalendar_leftTl')}
                    {this.renderTimeLine(timeLine, timeLineNode, 'dayCalendar_rightTl' + (this.props.centerRightTimeLine ? ' dayCalendar_tl--center' : ''))}

                    {this.props.isCurDay && (
                        <CurTime serverTimestamp={this.props.curServerTimestamp}
                                            timeLine={timeLine}
                                            timeLineNode={timeLineNode}
                                            getMoment={this.getMoment.bind(this)}
                        />
                    )}
                </div>
            </div>
        )
    }

    renderTimeLine(timeLine, ref, className)
    {
        let timeLineItems = GeneralHelper.mapObj(timeLine, (timeLineItem, time) =>
        {
            let style = {};

            if(timeLineItem.height !== null)
            {
                style.height = timeLineItem.height;
            }

            return (
                <div className={'dayCalendar_timeItem ' + (timeLine[time].type === 'half' ? 'littleTI' : '')}
                     key={time}
                     style={style}>
                    <span>{time}</span>
                </div>
            );
        });

        className = 'dayCalendar_tl ' + className;

        let timeLineProps = {className};
        if(ref !== null)
        {
            timeLineProps.ref = ref;
        }

        return (
            <div {...timeLineProps}>
                {timeLineItems}
            </div>
        );
    }


    /**
     * Получает объект врачей из массива (указывает id врачей в качестве ключей)
     */
    getDoctors()
    {
        let result = {};

        this.props.doctors.forEach(doctor =>
        {
            result[doctor.id] = doctor;
        });

        return result;
    }

    blockEvent(e)
    {
        e.stopPropagation();
        e.preventDefault();
    }
}

export default ScheduleDay