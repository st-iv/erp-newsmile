import React from 'react'
import Day from './day'
import ReactTooltip from 'react-tooltip'
import PropTypes from 'prop-types'

class Calendar extends React.Component
{
    months = ['янв', 'фев', 'мар', 'апр', 'май', 'июн', 'июл', 'авг', 'сен', 'окт', 'ноя', 'дек'];
    scrollPosition = 0;
    ctrlPressed = false;

    static propTypes = {
        data: PropTypes.object.isRequired,
        colorScheme: PropTypes.object.isRequired,
        load: PropTypes.func,
        setSelectedDate: PropTypes.func,
        selectedDates: PropTypes.arrayOf(PropTypes.string)
    };

    constructor(props)
    {
        super(props);
        let _this = this;

        this.scrollbarConfig = {
            autoHideScrollbar: false,
            mouseWheel: {
                scrollAmount: 123
            },
            snapAmount: 41,
            callbacks: {
                onTotalScroll: () => {this.limitPosition = 'bottom'},
                onTotalScrollBack: () => {this.limitPosition = 'top'},
                onScroll: function()
                {
                    _this.limitPosition = '';
                    _this.scrollPosition = -this.mcs.top;
                }
            },
        };

        this.handleKeyUp = this.handleKeyUp.bind(this);
        this.handleKeyDown = this.handleKeyDown.bind(this);
    }

    $body = null;
    $root = null;
    limitPosition = 'top';

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

                    <div className="custCalendar_month_up day_tooltip" onClick={this.monthUpHandler.bind(this)} data-tip="<div>На месяц назад</div>"/>
                    <div className="custCalendar_week_up day_tooltip" onClick={this.weekUpHandler.bind(this)} data-tip="<div>На неделю назад</div>"/>
                    <div className="custCalendar_week_down day_tooltip" onClick={this.weekDownHandler.bind(this)} data-tip="<div>На неделю вперёд</div>"/>
                    <div className="custCalendar_month_down day_tooltip" onClick={this.monthDownHandler.bind(this)} data-tip="<div>На месяц вперёд</div>"/>
                </div>

                <div className="custCalendar_controls_bottom">
                    <div className="custCalendar_frame"/>
                    <div className="custCalendar_more"/>
                    <div className="custCalendar_print"/>
                </div>

                <ReactTooltip html effect="solid" offset={{top: -15}} className="calendar-tooltip"/>
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
            const day = startDate.date();

            const isSelected = this.props.selectedDates.indexOf(date) !== -1;

            result.push(
                <Day date={date}
                     day={day}
                     curMonth={this.months[monthIndex]}
                     dayData={data.dateData[curDateString]}
                     isSelected={isSelected}

                     getColor={this.getColor.bind(this)}
                     onClick={this.handleDayClick.bind(this, date, data.dateData[curDateString], isSelected)}

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
        $(document).keyup(this.handleKeyUp);
        $(document).keydown(this.handleKeyDown);
    }

    componentWillUnmount()
    {
        this.$body.mCustomScrollbar('destroy');
        $(document).off('keyup', this.handleKeyUp);
        $(document).off('keydown', this.handleKeyDown);
    }

    componentWillUpdate()
    {
        this.$body.mCustomScrollbar('destroy');
    }

    componentDidUpdate(prevProps, prevState, snapshot)
    {
        /* создаем скролл таким образом, чтобы он был перемотан на прежнее место */

        let config = Object.assign({}, this.scrollbarConfig);

        let prevStartDate = moment(prevProps.data.dateFrom);
        let prevEndDate = moment(prevProps.data.dateTo);

        let newStartDate = moment(this.props.data.dateFrom);
        let newEndDate = moment(this.props.data.dateTo);

        let prevWeeksCount = Math.abs( Math.ceil(prevStartDate.diff(prevEndDate, 'd') / 7) );
        let newWeeksCount = Math.abs( Math.ceil(newStartDate.diff(newEndDate, 'd') / 7) );
        let weeksDiff = newWeeksCount - prevWeeksCount;

        if(this.limitPosition === 'top' && (weeksDiff > 0))
        {
            this.scrollPosition += weeksDiff * 41;
        }

        config.setTop = -this.scrollPosition + 'px';

        this.$body.mCustomScrollbar(config);

        if(this.limitPosition && (weeksDiff > 0))
        {
            /* а затем плавно и демонстративно проматываем скролл на новые загруженные даты */

            let diffPosition = (this.limitPosition === 'top') ? '+' : '-';
            diffPosition += '=' + weeksDiff * 41;
            this.$body.mCustomScrollbar('scrollTo', diffPosition);
        }

        /* обновляем подсказки */
        ReactTooltip.rebuild();
    }

    getColor(freeMinutes)
    {
        let color = {
            background: 'eaeaea',
            text: '454545'
        };

        if(freeMinutes !== null)
        {
            let minutesBounds = Object.keys(this.props.colorsScheme);

            for(let i=0; i < minutesBounds.length; i++)
            {
                if((i == (minutesBounds.length - 1)) || ((freeMinutes >= minutesBounds[i]) && (freeMinutes < minutesBounds[i+1])))
                {
                    color = $.extend(color, this.props.colorsScheme[minutesBounds[i]]);
                    break;
                }
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


    /**
     * Вызывается, когда пользователь пытается прокрутить календарь дальше загруженных дат
     * @param direction - сторона, в которую пользователь пытается прокрутить календарь: 'top' или 'bottom'
     * @param unit - единица, на которую прокручивается календарь: 'week' или 'month'
     */
    onScrollEnd(direction, unit)
    {
        /* подгружаем в календарь новые даты */
        let loadWeeksCount = ((unit === 'week') ? 1 : 4);
        let loadStartDate, loadEndDate;

        if(direction === 'top')
        {
            loadEndDate = moment(this.props.data.dateTo);
            loadStartDate = moment(this.props.data.dateFrom).add('-' + loadWeeksCount, 'w');
        }
        else
        {
            loadStartDate = moment(this.props.data.dateFrom);
            loadEndDate = moment(this.props.data.dateTo).add('+' + loadWeeksCount, 'w');
        }

        this.props.load(loadStartDate.format('YYYY-MM-DD'), loadEndDate.format('YYYY-MM-DD'));
    }

    handleDayClick(date, dayData, isSelected)
    {
        if(dayData.isEmpty) return;
        let newSelectedDates;

        if(isSelected)
        {
            if(this.ctrlPressed)
            {
                newSelectedDates = this.props.selectedDates.filter(selectedDate =>
                {
                    return selectedDate !== date;
                });
            }
            else
            {
                newSelectedDates = [date];
            }
        }
        else
        {
            if(this.ctrlPressed)
            {
                if(this.props.selectedDates.indexOf(date) === -1)
                {
                    newSelectedDates = this.props.selectedDates.slice();
                    newSelectedDates.push(date);
                }
            }
            else
            {
                newSelectedDates = [date]
            }
        }

        if(newSelectedDates)
        {
           this.props.setSelectedDates(newSelectedDates);
        }
    }

    handleKeyUp(e)
    {
        if(e.which === 17)
        {
            this.ctrlPressed = true;
        }
    }

    handleKeyDown(e)
    {
        if(e.which === 17)
        {
            this.ctrlPressed = false;
        }
    }
}

export default Calendar
