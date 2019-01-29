import React from 'react'
import PropTypes from 'prop-types'
import MCustomScrollbar from 'js/react/m-custom-scrollbar'

export default class ScheduleScroll extends React.Component
{
    state = {
        width: 0,
        contentWidth: 0,
        scrollingTo: null,
        position: 'left',
        scrollDuration: 2
    };

    static propTypes = {
        className: PropTypes.string
    };

    static defaultProps = {
        className: ''
    };

    rootNode = null;
    scrollbar = null;

    scrollSpeed = 5;

    render()
    {
        let isActive = !!this.scrollbar && this.scrollbar.isHidden();

        return (

            <MCustomScrollbar
                className={this.props.className + ' schedule-scroll'}
                ref={ref => this.scrollbar = ref}
                callbacks={{onScrollStart: this.handleScrollStart.bind(this)}}
                onScrollComplete={this.handleScrollComplete.bind(this)}
                mouseWheel={{enable: false}}
                axis="x"
            >
                {isActive && (this.state.position !== 'left') && (
                    <div className="schedule-scroll__button schedule-scroll__button--left"
                         onMouseEnter={() => this.scrollbar.scrollTo('left')}
                         onMouseLeave={() => this.scrollbar.stop()}
                    />
                )}
                {this.props.children}
                {isActive && (this.state.position !== 'right') && (
                    <div className="schedule-scroll__button schedule-scroll__button--right"
                         onMouseEnter={() => this.scrollbar.scrollTo('right')}
                         onMouseLeave={() => this.scrollbar.stop()}
                    />
                )}
            </MCustomScrollbar>
                /*<div className={this.props.className + ' schedule-scroll'} ref={ref => this.rootNode = ref}>
                    {isActive && (this.state.position !== 'left') && (
                        <div className="schedule-scroll__button schedule-scroll__button--left"
                             onMouseEnter={this.scroll.bind(this, 'left')}/>
                    )}



                    <div className="schedule-scroll__content"
                         ref={ref => this.contentNode = ref}
                         style={style}>
                        {this.props.children}
                    </div>

                    {isActive && (this.state.position !== 'right') && (
                        <div className="schedule-scroll__button schedule-scroll__button--right"
                             onMouseEnter={this.scroll.bind(this, 'right')}/>
                    )}
                </div>*/
        );
    }

    componentDidMount()
    {
        /*this.setState({
            contentWidth: this.contentNode.clientWidth,
            width: this.rootNode.clientWidth
        });*/
    }

    componentDidUpdate(prevProps, prevState, snapshot)
    {
        /*let newState = {};
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
        }*/
    }

    scroll(direction)
    {
        if(this.state.position === direction) return;

        const shift = parseFloat(window.getComputedStyle(this.contentNode).right);
        let distance;

        if(direction === 'left')
        {
            distance = shift;
        }
        else
        {
            distance = (this.state.contentWidth - this.state.width) - shift;
        }

        this.setState({
            scrollingTo: direction,
            position: 'middle',
            scrollDuration: (distance / this.scrollSpeed) / 100
        });
    }

    handleScrollComplete(position)
    {
        this.setState({
            scrollingTo: null,
            position: position
        });
    }

    handleScrollStart()
    {
        console.log('start scroll!');
        //this.setState({position: null});
    }
}