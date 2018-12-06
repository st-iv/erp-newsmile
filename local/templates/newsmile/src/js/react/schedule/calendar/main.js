import React from 'react'
import Day from './day'

class Calendar extends React.Component
{
    months = ['янв', 'фев', 'мар', 'апр', 'май', 'июн', 'июл', 'авг', 'сен', 'окт', 'ноя', 'дек'];

    scrollbarConfig = {
        autoHideScrollbar: false,
        mouseWheel: {
            scrollAmount: 123
        },
        snapAmount: 41,
        callbacks: {
            onTotalScroll: () => {this.limitPosition = 'bottom'},
            onTotalScrollBack: () => {this.limitPosition = 'top'},
            onScroll: () => {this.limitPosition = ''}
        }
    };

    $body = null;
    $root = null;
    limitPosition = '';

    render()
    {
        return (
            <div id="left-calendar" ref={ref => {this.$root = $(ref)}}>
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
                    <div className="custCalendar_month_up day_tooltip" onClick={this.monthUpHandler.bind(this)} data-toggle="tooltip" data-html="true" title="<div>На месяц назад</div>"/>
                    <div className="custCalendar_week_up day_tooltip" onClick={this.weekUpHandler.bind(this)} data-toggle="tooltip" data-html="true" title="<div>На неделю назад</div>"/>
                    <div className="custCalendar_week_down day_tooltip" onClick={this.weekDownHandler.bind(this)} data-toggle="tooltip" data-html="true" title="<div>На неделю вперёд</div>"/>
                    <div className="custCalendar_month_down day_tooltip" onClick={this.monthDownHandler.bind(this)} data-toggle="tooltip" data-html="true" title="<div>На месяц вперёд</div>"/>
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
                <Day date={date}
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

    componentDidMount()
    {
        this.$body.mCustomScrollbar(this.scrollbarConfig);
        this.$root.tooltip({
            selector: '.day_tooltip'
        });
    }

    componentDidUpdate()
    {
        this.$body.mCustomScrollbar('update');
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

    monthUpHandler()
    {
        if(this.limitPosition === 'top')
        {
            this.onScrollEnd(this.limitPosition, 'month');
        }
        else
        {
            this.$body.mCustomScrollbar('scrollTo', '+=205');
        }
    }

    monthDownHandler()
    {
        if(this.limitPosition === 'bottom')
        {
            this.onScrollEnd(this.limitPosition, 'month');
        }
        else
        {
            this.$body.mCustomScrollbar('scrollTo', '-=205');
        }
    }

    weekUpHandler()
    {
        if(this.limitPosition === 'top')
        {
            this.onScrollEnd(this.limitPosition, 'week');
        }
        else
        {
            this.$body.mCustomScrollbar('scrollTo', '+=41');
        }
    }

    weekDownHandler()
    {
        if(this.limitPosition === 'bottom')
        {
            this.onScrollEnd(this.limitPosition, 'week');
        }
        else
        {
            this.$body.mCustomScrollbar('scrollTo', '-=41');
        }
    }


    onScrollEnd(direction, unit)
    {
        /*let loadWeeksCount = ((unit === 'week') ? 1 : 4);
        let loadStartDate, loadEndDate;

        if(direction === 'top')
        {
            loadEndDate = moment(this.props.data.dateTo);
            loadStartDate = moment(this.props.data.dateFrom).add('-' + loadWeeksCount, 'w');
        }
        else
        {
            loadStartDate = moment(this.props.data.dateFrom);
            loadEndDate = moment(this.props.data.dateTo).add('+' + loadWeeksCount - 1, 'w');
        }

        this.props.load(loadStartDate.format('YYYY-MM-DD'), loadEndDate.format('YYYY-MM-DD'));*/
    }
}

export default Calendar
