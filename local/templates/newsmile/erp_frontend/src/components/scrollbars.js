import React from 'react'
import PropTypes from 'prop-types'
import $ from 'jquery'
import { Scrollbars as CustomScrollbars } from 'react-custom-scrollbars'

export default class Scrollbars extends React.Component
{
    static propTypes = {
        verticalScrollSide: PropTypes.oneOf(['left', 'right']),
        className: PropTypes.string
    };

    static defaultProps = {
        verticalScrollSide: 'right',
        className: ''
    };

    constructor(props)
    {
        super(props);
        this.renderThumb = this.renderThumb.bind(this);
    }

    render()
    {
        let props = $.extend({
            hideTracksWhenNotNeeded: true
        }, this.props);

        props.renderThumbVertical = this.renderThumb;
        props.renderThumbHorizontal = this.renderThumb;

        props.renderTrackVertical = this.renderTrackVertical.bind(this);
        props.renderTrackHorizontal = this.renderTrackHorizontal.bind(this);
        props.className = 'custom-scroll__container ' + this.props.className;

        delete props.verticalScrollSide;

        return (
            <CustomScrollbars {...props}/>
        );
    }

    renderThumb({ style, ...props })
    {
        return (
            <div style={{ ...style }} className="custom-scroll__thumb"
                {...props}/>
        );
    }

    renderTrackVertical({ style, ...props })
    {
        let className = 'custom-scroll__track custom-scroll__track--vertical';
        className += (this.props.verticalScrollSide === 'right') ? ' custom-scroll__track--right' : ' custom-scroll__track--left';

        return (
            <div style={{ ...style }} className={className}
                 {...props}/>
        );
    }

    renderTrackHorizontal({ style, ...props })
    {
        const trackStyle = {
            left: '2px',
            right: '2px',
            bottom: '6px',
        };

        return (
            <div style={{ ...style, ...trackStyle }} className="custom-scroll__track custom-scroll__track--horizontal"
                 {...props}/>
        );
    }
}