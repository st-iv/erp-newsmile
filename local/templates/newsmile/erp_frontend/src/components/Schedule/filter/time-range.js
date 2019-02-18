import React from 'react'
import PropTypes from 'prop-types'
import Slider, { Range } from 'rc-slider'
import DateHelper from './../../../common/helpers/date-helper'

class TimeRange extends React.Component
{
    static propTypes = {
        timeStart: PropTypes.string.isRequired,
        timeEnd: PropTypes.string.isRequired,
        step: PropTypes.number,
        value: PropTypes.array.isRequired,
        onChange: PropTypes.func.isRequired
    };

    static defaultProps = {
        step: 15
    };

    min = DateHelper.getMinutesByTime(this.props.timeStart);
    max = DateHelper.getMinutesByTime(this.props.timeEnd);

    render()
    {
        let value = this.props.value.map(time =>
        {
            return DateHelper.getMinutesByTime(time);
        });

        return (
            <div className="time-range_cont">
                <Range min={this.min}
                       max={this.max}
                       step={this.props.step}
                       value={value}
                       onChange={this.handleChange.bind(this)}
                       allowCross={false}
                       className="filter-time-range"
                />

                <div className="time-range_label-from">
                    c {this.props.value[0]}
                </div>
                <div className="time-range_label-to">
                    до {this.props.value[1]}
                </div>
            </div>
        );
    }

    handleChange(value)
    {
        value = value.map(minutes =>
        {
            return DateHelper.formatMinutes(minutes);
        });

        this.props.onChange(value);
    }
}

export default TimeRange