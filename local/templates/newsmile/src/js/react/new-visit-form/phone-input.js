import React from 'react'
import PropTypes from 'prop-types'
import InputMask from 'react-input-mask'

class PhoneInput extends React.Component
{
    static propTypes = {
        name: PropTypes.string.isRequired,
        title: PropTypes.string.isRequired,
        defaultValue: PropTypes.string,
        required: PropTypes.bool,
        placeholder: PropTypes.string,

        mask: PropTypes.string,
        alwaysShowMask: PropTypes.bool,
        maskChar: PropTypes.string,

        additionalInputsName: PropTypes.string.isRequired,
        additionalInputsCount: PropTypes.number
    };

    static defaultProps = {
        defaultValue: '',
        required: false,
        mask: '',
        additionalInputsCount: 0
    };

    render()
    {
        const inputClass = 'form__input form__input--phone' + (this.props.required ? ' form__input--required' : '');
        const wrapperClass = 'form__wrapper' + (this.props.required ? ' form__wrapper--required' : '');
        const inputProps = $.extend({}, this.props, {
            className: inputClass,
            type: 'text'
        });

        delete inputProps.additionalInputsName;
        delete inputProps.additionalInputsCount;

        return (
            <label className={wrapperClass} htmlFor={this.props.name}>
                <span className="form__label form__label--phones">
                    {this.props.title}
                </span>

                {this.props.mask ? (
                    <InputMask {...inputProps}/>
                ) : (
                    <input {...inputProps}/>
                )}

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

        for(let i = 0; i < this.props.additionalInputsCount; i++)
        {
            result.push(
                this.props.mask ? (
                    <InputMask {...additionalInputsProps} key={i}/>
                ) : (
                    <input {...additionalInputsProps} key={i}/>
                )
            );
        }

        return result;
    }
}

export default PhoneInput