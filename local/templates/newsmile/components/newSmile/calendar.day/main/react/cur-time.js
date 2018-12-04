class CalendarDayCurTime extends React.Component
{
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
        this.setState({
            top: this.getTopValue(),
            active: this.isActive()
        });

        this.interval = setInterval(() =>
        {
            this.setState({
                top: this.getTopValue(),
                active: this.isActive()
            });
        }, 60000);
    }

    componentWillUnmount()
    {
        clearInterval(this.interval);
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
            (this.props.timeLine[timeEnd] === 'standard' ? 30 : 15),
            'minute'
        );


        let curServerMoment = this.getCurServerMoment();

        return !timeStartMoment.isAfter(curServerMoment) && timeEndMoment.isAfter(curServerMoment)
    }

    getTopValue()
    {
        let curServerMoment = this.getCurServerMoment();
        console.log(Date.now() - this.serverTimeDiff);
        let timeLineIndex = 0;
        let cellPassedPart = 0;
        let isStandardInterval = true;


        for(let time in this.props.timeLine)
        {
            let startMoment = this.props.getMoment(time);
            let endMoment = startMoment.clone();
            endMoment.add(
                (this.props.timeLine[time] === 'standard' ? 30 : 15),
                'minute'
            );


            if(!startMoment.isAfter(curServerMoment) && endMoment.isAfter(curServerMoment))
            {
                isStandardInterval = (this.props.timeLine[time] === 'standard');
                cellPassedPart = startMoment.diff(curServerMoment) / startMoment.diff(endMoment);
                break;
            }

            timeLineIndex++;
        }

        let $timeLine = $(this.props.timeLineNode.current);
        let $curTimeLineCell = $(this.props.timeLineNode.current).children().eq(timeLineIndex);
        let cellHeight = $curTimeLineCell.height();
        let timeLineCellPosition = $curTimeLineCell.position();
        let timeLinePosition = $timeLine.position();

        return timeLinePosition.top + timeLineCellPosition.top + (cellHeight * cellPassedPart);
    }

    getCurServerMoment()
    {
        return moment(Date.now() - this.serverTimeDiff);
    }
}