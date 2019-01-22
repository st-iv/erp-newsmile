import React from 'react'
import PropTypes from 'prop-types'

export default class MCustomScrollbar extends React.Component
{
    static propTypes = {
        className: PropTypes.string,
    };

    static defaultProps = {
        className: ''
    };

    $root = null;

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
        return config;
    }
}