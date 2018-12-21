import React from 'react'
import {default as ReactInputMask} from 'react-input-mask'

export default class InputMask extends React.Component
{
    state = {
        value: this.props.defaultValue || ''
    };

    static defaultProps = {
        maskChar: '_'
    };

    render()
    {
        let props = $.extend({}, this.props);
        delete props.name;
        delete props.defaultValue;

        const hiddenValue = (this.isEmpty(this.state.value) ? '' : this.state.value);

        return (
            <div>
                <ReactInputMask {...this.props} value={this.state.value} onChange={value => this.setState({value})}/>
                <input type="hidden" name={this.props.name} value={hiddenValue}/>
            </div>
        );
    }

    isEmpty(value)
    {
        return (!value || (value.indexOf(this.props.maskChar) !== -1));
    }
}