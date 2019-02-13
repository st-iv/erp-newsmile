import React from 'react'
import Select from './select'
import {components} from 'react-select'

class ColoredSelect extends React.Component
{
    option = (props) =>
    {
        const valueColorClass = 'value-color' + (this.isWhiteColor(props.data.color) ? ' value-color--white' : '');

        return (
            <components.Option {...props}>
                <div className={valueColorClass} style={{backgroundColor: props.data.color}}/>
                {props.children}
            </components.Option>
        );
    };

    valueContainer = (props) =>
    {
        const value = props.getValue()[0];
        const valueColorClass = 'value-color' + (this.isWhiteColor(value.color) ? ' value-color--white' : '');

        return (
            <components.ValueContainer {...props}>
                {!!value && (
                    <div className={valueColorClass} style={{backgroundColor: value.color}}/>
                )}
                {props.children}
            </components.ValueContainer>
        );
    };

    render()
    {
        return (
            <Select {...this.props} className={this.props.className + ' colored-select'} components={{Option: this.option, ValueContainer: this.valueContainer}}/>
        );
    }

    isWhiteColor(color)
    {
        return !color || !!color.match(/#fff(fff)?/i)
    }
}

export default ColoredSelect