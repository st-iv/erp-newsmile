import React from 'react'
import PropTypes from 'prop-types'
import InputMask from '../input-mask'

class PhoneInput extends React.Component {
    static propTypes = {
        name: PropTypes.string.isRequired,
        title: PropTypes.string.isRequired,
        value: PropTypes.array,
        required: PropTypes.bool,
        placeholder: PropTypes.string,

        mask: PropTypes.string,
        alwaysShowMask: PropTypes.bool,
        maskChar: PropTypes.string,

        additionalInputsName: PropTypes.string.isRequired,
        additionalInputsCount: PropTypes.number,

        onChange: PropTypes.func
    };

    static defaultProps = {
        value: '',
        required: false,

        mask: '+7 (999) 999 99 99',
        maskChar: '-',
        alwaysShowMask: true,

        additionalInputsCount: 0
    };

    values = [];

    constructor(props)
    {
        super(props);
        this.handleChange = this.handleChange.bind(this);
    }


    render()
    {
        const inputClass = 'form__input form__input--phone' + (this.props.required ? ' form__input--required' : '');
        const wrapperClass = 'form__wrapper' + (this.props.required ? ' form__wrapper--required' : '');
        const inputProps = $.extend({}, this.props, {
            className: inputClass,
            type: 'text',
            onChange: this.handleChange.bind(this, 0),
            value: this.values[0]
        });

        delete inputProps.additionalInputsName;
        delete inputProps.additionalInputsCount;

        return (
            <label className={wrapperClass} htmlFor={this.props.name}>
                <span className="form__label form__label--phones">
                    {this.props.title}
                </span>

                <InputMask {...inputProps}/>

                {this.renderAdditionalInputs(inputProps)}
            </label>
        );
    }

    renderAdditionalInputs(mainInputProps)
    {
        let result = [];
        let additionalInputsProps = $.extend({}, mainInputProps);
        additionalInputsProps.name = this.props.additionalInputsName + '[]';

        delete additionalInputsProps.required;

        for(let i = 1; i <= this.props.additionalInputsCount; i++)
        {
            result.push(
                <InputMask {...additionalInputsProps} value={this.values[i]} onChange={this.handleChange.bind(this, i)} key={i}/>
            );
        }

        return result;
    }

    handleChange(index, value)
    {
        this.values[index] = value;
        this.props.onChange && this.props.onChange(this.values.slice());
    }
}

export default PhoneInput