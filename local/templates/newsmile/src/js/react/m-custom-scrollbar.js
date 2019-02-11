import React from 'react'
import PropTypes from 'prop-types'

export default class MCustomScrollbar extends React.Component
{
    static propTypes = {
        className: PropTypes.string,
        onScrollComplete: PropTypes.func
    };

    static defaultProps = {
        className: ''
    };

    $root = null;
    scrolledTo = null;

    render()
    {
        return (
            <div className={this.props.className} ref={ref => this.$root = $(ref)}>
                {this.props.children}
            </div>
        );
    }

    componentDidMount()
    {
        this.$root.mCustomScrollbar(this.getScrollbarConfig());
    }

    componentWillUnmount()
    {
        this.$root.mCustomScrollbar('destroy');
    }

    componentWillUpdate()
    {
        this.$root.mCustomScrollbar('destroy');
    }

    componentDidUpdate()
    {
        this.$root.mCustomScrollbar(this.getScrollbarConfig());
    }

    getScrollbarConfig()
    {
        let config = $.extend({}, this.props);
        delete config.className;

        if(!!config.callbacks)
        {
            delete config.callbacks.onScroll;
        }
        else
        {
            config.callbacks = {};
        }

        config.callbacks.onScroll = this.handleScroll.bind(this);
        return config;
    }

    stop()
    {
        this.$root.mCustomScrollbar('stop');
    }

    scrollTo(position, options)
    {
        this.$root.mCustomScrollbar('scrollTo', position, options);
        this.scrolledTo = position;
    }

    getNode()
    {
        return this.$root[0];
    }

    isHidden()
    {
        return this.$root.hasClass('mCS_no_scrollbar');
    }

    handleScroll()
    {
        !!this.props.onScrollComplete && this.props.onScrollComplete(this.scrolledTo);
    }
}