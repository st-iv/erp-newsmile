import React from 'react'
import { Scrollbars as CustomScrollbars } from 'react-custom-scrollbars'

export default class Scrollbars extends React.Component
{
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
        const trackStyle = {
            bottom: '2px',
            top: '2px',
            left: '6px'
        };

        return (
            <div style={{ ...style, ...trackStyle }} className="custom-scroll__track custom-scroll__track--vertical"
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