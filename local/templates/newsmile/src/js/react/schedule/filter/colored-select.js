import React from 'react'
import Select from './select'
import {components} from 'react-select'

class ColoredSelect extends React.Component
{
    option = (props) =>
    {
        return (
            <components.Option {...props}>
                <div className="value-color" style={{backgroundColor: props.data.color}}/>
                {props.children}
            </components.Option>
        );
    };

    valueContainer = (props) =>
    {
        const value = props.getValue()[0];

        return (
            <components.ValueContainer {...props}>
                {!!value && (
                    <div className="value-color" style={{backgroundColor: value.color}}/>
                )}
                {props.children}
            </components.ValueContainer>
        );
    };

    render()
    {
        return (
            <Select {...this.props} components={{Option: this.option, ValueContainer: this.valueContainer}}/>
        );
    }
}

export default ColoredSelect