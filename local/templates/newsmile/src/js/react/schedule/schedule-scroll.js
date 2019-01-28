import React from 'react'
import PropTypes from 'prop-types'
import MCustomScrollbar from 'js/react/m-custom-scrollbar'

export default class ScheduleScroll extends React.Component
{
    state = {
        width: 0,
        contentWidth: 0,
        shift: 0
    };

    static propTypes = {
        className: PropTypes.string
    };

    static defaultProps = {
        className: ''
    };

    rootNode = null;
    contentNode = null;

    render()
    {


        return (
            <div className={this.props.className + ' schedule-scroll'} ref={ref => this.rootNode = ref}>
                {(this.state.shift > 0) && (
                    <div className="schedule-scroll__button schedule-scroll__button--left" onMouseEnter={this.scroll.bind(this, 'left')}/>
                )}

                <div className="schedule-scroll__content"
                     ref={ref => this.contentNode = ref} style={{right: this.state.shift}}>
                    {this.props.children}
                </div>

                {((this.state.shift + this.state.width) < this.state.contentWidth) && (
                    <div className="schedule-scroll__button schedule-scroll__button--right" onMouseEnter={this.scroll.bind(this, 'right')}/>
                )}
            </div>
        );
    }

    componentDidMount()
    {
        this.setState({
            contentWidth: this.contentNode.clientWidth,
            width: this.rootNode.clientWidth
        });
    }

    componentDidUpdate(prevProps, prevState, snapshot)
    {
        let newState = {};
        if(this.state.contentWidth !== this.contentNode.clientWidth)
        {
            newState.contentWidth = this.contentNode.clientWidth;
        }

        if(this.state.width !== this.rootNode.clientWidth)
        {
            newState.width = this.rootNode.clientWidth;
        }

        if(!$.isEmptyObject(newState))
        {
            this.setState(newState);
        }
    }

    scroll(direction)
    {
        let shiftSign = (direction === 'left') ? -1 : 1;
        let newShift = this.state.shift + shiftSign * 10;
        if(newShift < 0)
        {
            newShift = 0;
        }

        this.setState({
            shift: newShift
        });
    }
}