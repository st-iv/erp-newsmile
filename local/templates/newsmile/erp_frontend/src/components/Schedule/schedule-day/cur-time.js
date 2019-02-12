import React from 'react'
import $ from 'jquery'
import moment from 'moment'
class CurTime extends React.Component
{
    refreshInterval = 20;

    constructor(props)
    {
        super(props);
        this.serverTimeDiff = Date.now() - (props.serverTimestamp * 1000);

        this.state = {
            top: 0,
            active: false
        };
    }

    componentDidMount()
    {
        this.updatePosition();
        this.interval = setInterval(this.updatePosition.bind(this), this.refreshInterval * 1000);
    }

    updatePosition()
    {
        if(this.props.timeLineNode && this.props.timeLineNode.current)
        {
            let topPosition = this.getTopValue();

            this.setState({
                top: topPosition,
                active: (topPosition > 0)
            });
        }
    }

    componentWillUnmount()
    {
        clearInterval(this.interval);
    }

    componentDidUpdate(prevProps, prevState, snapshot)
    {
        if(JSON.stringify(prevProps.timeLine) !== JSON.stringify(this.props.timeLine))
        {
            // если был обновлен timeLine - нужно обновить позицию линии текущего времени
            this.updatePosition();
        }
    }

    render()
    {
        if(this.state.active)
        {
            return (
                <div className="dayCalendar_curTime" style={{top: this.state.top + 'px'}}></div>
            );
        }
        else
        {
            return null;
        }
    }

    isActive()
    {
        let timeList = Object.keys(this.props.timeLine);
        let timeStartMoment = this.props.getMoment(timeList[0]);
        let timeEnd = timeList.pop();
        let timeEndMoment = this.props.getMoment(timeEnd);
        timeEndMoment.add(
            (this.props.timeLine[timeEnd].type === 'standard' ? 30 : 15),
            'minute'
        );


        let curServerMoment = this.getCurServerMoment();

        return !timeStartMoment.isAfter(curServerMoment) && timeEndMoment.isAfter(curServerMoment)
    }

    getTopValue()
    {
        let curServerMoment = this.getCurServerMoment();
        let timeLineIndex = 0;
        let cellPassedPart = 0;
        let isStandardInterval = true;
        let isTimeLineHit = false;

        for(let time in this.props.timeLine)
        {
            let startMoment = this.props.getMoment(time);
            let endMoment = startMoment.clone();
            endMoment.add(
                (this.props.timeLine[time].type === 'standard' ? 30 : 15),
                'minute'
            );


            if(!startMoment.isAfter(curServerMoment) && endMoment.isAfter(curServerMoment))
            {
                isStandardInterval = (this.props.timeLine[time].type === 'standard');
                isTimeLineHit = true;
                cellPassedPart = startMoment.diff(curServerMoment) / startMoment.diff(endMoment);
                break;
            }

            timeLineIndex++;
        }

        if(isTimeLineHit)
        {
            // если текущее время попало в таймлайн, определяем позицию компонента на таймлайне
            let $timeLine = $(this.props.timeLineNode.current);
            let $curTimeLineCell = $(this.props.timeLineNode.current).children().eq(timeLineIndex);
            let cellHeight = $curTimeLineCell.height();
            let timeLineCellPosition = $curTimeLineCell.position();
            let timeLinePosition = $timeLine.position();

            return timeLinePosition.top + timeLineCellPosition.top + (cellHeight * cellPassedPart) + 2;
        }
        else
        {
            return 0;
        }
    }

    getCurServerMoment()
    {
        return moment(Date.now() - this.serverTimeDiff);
    }
}

export default CurTime