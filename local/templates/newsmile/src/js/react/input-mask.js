import React from 'react'
import {default as ReactInputMask} from 'react-input-mask'

export default class InputMask extends React.PureComponent
{
    static defaultProps = {
        maskChar: '_'
    };

    rawValue = this.props.value;

    render()
    {
        let props = $.extend({}, this.props);

        props.value = (props.value === '') ? this.rawValue : props.value;
        delete props.name;

        return (
            <ReactInputMask {...props} onChange={this.handleChange.bind(this)}/>
        );
    }

    isEmpty(value)
    {
        return (!value || (value.indexOf(this.props.maskChar) !== -1));
    }

    handleChange(e)
    {
        this.rawValue = e.target.value;

        // если значение введено не полностью для родительского компонента оно должно быть пустым, чтобы с форм не
        // отправлялась незаполненная маска
        this.props.onChange(this.isEmpty(e.target.value) ? '' : e.target.value, e.target.value);
    }
}