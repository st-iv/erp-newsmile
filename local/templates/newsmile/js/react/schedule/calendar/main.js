class Calendar extends React.Component
{
    months = ['янв', 'фев', 'мар', 'апр', 'май', 'июн', 'июл', 'авг', 'сен', 'окт', 'ноя', 'дек'];

    scrollbarConfig = {
        autoHideScrollbar: false,
        mouseWheel: {
            scrollAmount: 123
        },
        snapAmount: 41,
        callbacks: {}
    };

    render()
    {
        return (
            <div id="left-calendar">
                <div className="custCalendar_header">
                    <div>Пн</div>
                    <div>Вт</div>
                    <div>Ср</div>
                    <div>Чт</div>
                    <div>Пт</div>
                    <div>Сб</div>
                    <div>Вс</div>
                </div>

                <div className="custCalendar_body clearfix" ref={ref =>
                {
                    this.$body = $(ref);
                }}>
                     {this.renderBody()}
                </div>

                <div className="custCalendar_controls_right">
                    <div className="custCalendar_month_up day_tooltip"/>
                    <div className="custCalendar_week_up day_tooltip"/>
                    <div className="custCalendar_week_down day_tooltip"/>
                    <div className="custCalendar_month_down day_tooltip"/>
                </div>

                <div className="custCalendar_controls_bottom">
                    <div className="custCalendar_frame"/>
                    <div className="custCalendar_more"/>
                    <div className="custCalendar_print"/>
                </div>
            </div>
        )
    }

    renderBody()
    {
        let result = [];
        let data = this.props.data;

        let startDate = moment(data.dateFrom);
        let endDate = moment(data.dateTo);

        while(endDate.diff(startDate, 'days') >= 0)
        {
            let curDateString = startDate.format('YYYY-MM-DD');
            let monthIndex = startDate.month();
            let date = startDate.format('YYYY-MM-DD');

            result.push(
                <CalendarDay date={date}
                             day={startDate.date()}
                             curMonth={this.months[monthIndex]}
                             dayData={data.dateData[curDateString]}
                             isSelected={this.props.curDate === date}

                             getColor={this.getColor.bind(this)}
                             selectDay={this.props.setSelectedDate}

                             key={date}
                />
            );

            startDate.add(1, 'd');
        }

        return result;
    }

    renderDay(date, day, month, dayData)
    {
        const defaultDayData = {
            generalTime: '00:00',
            engagedTime: '00:00',
            patientCount: 0
        };

        dayData = Object.assign({}, defaultDayData, dayData);

        let dayClassName = 'custCalendar_day';

        const color = this.getColor(dayData.generalTime - dayData.engagedTime);

        let dayStyle = {
            backgroundColor: '#' + color.background,
            color: '#' + color.text
        };

        if(this.state.selectedDate === date)
        {
            dayClassName += ' day_current active';
        }

        return (
            <div className={dayClassName} key={date} onClick={this.selectDay.bind(this)}>
                <div className="custCalendar_day_content" style={dayStyle}>
                    <div className="custCalendar_day_d">{day}</div>
                    <div className="custCalendar_day_m">{this.months[month]}</div>
                </div>
            </div>
        );
    }

    componentDidMount()
    {
        console.log('test!');
        this.$body.mCustomScrollbar(this.scrollbarConfig);
    }

    getColor(freeMinutes)
    {
        let color = {
            background: 'eaeaea',
            text: '454545'
        };

        let minutesBounds = Object.keys(this.props.colorsScheme);

        for(let i=0; i < minutesBounds.length; i++)
        {
            if((i == (minutesBounds.length - 1)) || ((freeMinutes > minutesBounds[i]) && (freeMinutes <= minutesBounds[i+1])))
            {
                color = $.extend(color, this.props.colorsScheme[minutesBounds[i]]);
                break;
            }
        }

        return color;
    };
}

