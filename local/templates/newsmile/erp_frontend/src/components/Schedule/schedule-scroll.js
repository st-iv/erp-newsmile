import React from 'react'
import PropTypes from 'prop-types'
import $ from 'jquery'
import Animation from './../../common/animation/animation'
import Helper from './../../common/helpers/main'

export default class ScheduleScroll extends React.Component
{
    state = {
        width: 0,
        contentWidth: 0,
        position: 'left',
        shift: 0
    };

    static propTypes = {
        className: PropTypes.string,
        contentStamp: PropTypes.any
    };

    static defaultProps = {
        className: ''
    };

    rootNode = null;
    contentNode = null;

    scrollSpeed = 500; // скорость скролла при наведении - количество пикселей в секунду
    toEndSpeed = 1200; // скорость скролла при клике
    bToEnd = false;

    scrollAnimation = new Animation((timePassed, stepSize, params) =>
    {
        const speed = this.bToEnd ? this.toEndSpeed : this.scrollSpeed;
        let shiftDif = speed * stepSize / 1000;
        let stop = false;

        shiftDif *= (params.direction === 'left') ? -1 : 1;

        let rawShift = this.state.shift + shiftDif;
        let newState = {};

        const isCompleted = ((params.direction === 'left') && (rawShift <= params.endShift)) || ((params.direction === 'right') && (rawShift >= params.endShift));

        newState.shift = isCompleted ? params.endShift : (this.state.shift + shiftDif);

        if(isCompleted)
        {
            newState.position = params.direction;
            this.bToEnd = false;
            stop = true;
        }
        else
        {
            newState.position = 'middle';
        }

        this.setState(newState);

        return !stop;
    });

    constructor(props)
    {
        super(props);
        this.handleUnhover = this.handleUnhover.bind(this);
    }

    render()
    {
        let isActive = !!this.state.width && !!this.state.contentWidth && (this.state.contentWidth > this.state.width);

        return (
            <div className={this.props.className + ' schedule-scroll'} ref={ref => this.rootNode = ref}>
                {isActive && (this.state.position !== 'left') && (
                    <div className="schedule-scroll__button-container schedule-scroll__button-container--left">
                        <div className="schedule-scroll__button schedule-scroll__button--left"
                             onMouseEnter={this.scroll.bind(this, 'left')}
                             onMouseLeave={this.handleUnhover}
                             onClick={() => this.bToEnd = true}
                             /*style={{left: $(this.rootNode).offset().left}}*/
                        />
                    </div>
                )}

                <div className="schedule-scroll__content"
                     ref={ref => this.contentNode = ref}
                     style={{right: this.state.shift}}>
                    {this.props.children}
                </div>

                {isActive && (this.state.position !== 'right') && (
                    <div className="schedule-scroll__button-container schedule-scroll__button-container--right">
                        <div className="schedule-scroll__button schedule-scroll__button--right"
                             onMouseEnter={this.scroll.bind(this, 'right')}
                             onMouseLeave={this.handleUnhover}
                             onClick={() => this.bToEnd = true}/>
                    </div>
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

        if(!Helper.isEqual(this.props.contentStamp, prevProps.contentStamp))
        {
            this.scrollAnimation.stop();
            newState.shift = 0;
            newState.position = 'left';
        }

        if(!$.isEmptyObject(newState))
        {
            this.setState(newState);
        }
    }

    scroll(direction)
    {
        if(this.state.position === direction) return;

        const animationParams = {
            direction,
            startShift: this.state.shift,
            endShift: (direction === 'left') ? 0 : (this.state.contentWidth - this.state.width)
        };

        this.scrollAnimation.animate(null, animationParams);
    }

    stopScroll()
    {
        if(!this.bToEnd)
        {
            this.scrollAnimation.stop();
        }
    }

    handleUnhover()
    {
        this.stopScroll();
    }
}