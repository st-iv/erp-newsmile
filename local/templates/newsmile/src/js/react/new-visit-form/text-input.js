import React from 'react'
import PropTypes from 'prop-types'
import InputMask from '../input-mask'

class TextInput extends React.Component
{
    static propTypes = {
        name: PropTypes.string.isRequired,
        title: PropTypes.string.isRequired,
        value: PropTypes.oneOfType([PropTypes.string, PropTypes.number]),
        required: PropTypes.bool,
        placeholder: PropTypes.string,

        mask: PropTypes.string,
        alwaysShowMask: PropTypes.bool,
        maskChar: PropTypes.string,

        onChange: PropTypes.func
    };

    static defaultProps = {
        required: false,
        mask: '',
        maskChar: '_'
    };

    state = {
        isFocused: !!this.props.value
    };

    rawValue = this.props.value || this.props.defaultValue;

    render()
    {
        const inputClass = 'form__input' + (this.props.required ? ' form__input--required' : '');
        let wrapperClass = 'form__wrapper' + (this.props.required ? ' form__wrapper--required' : '');
        wrapperClass += (this.props.disabled ? ' form__wrapper--disabled' : '');

        let inputProps = $.extend({}, this.props, {
            className: inputClass,
            type: 'text',
            onFocus: () => this.setState({isFocused: true}),
            onBlur: this.handleBlur.bind(this),
        });


        if(this.props.mask)
        {
            inputProps.onChange = this.handleMaskedChange.bind(this);
        }
        else
        {
            inputProps.onChange = this.handleChange.bind(this);
            delete inputProps.mask;
            delete inputProps.maskChar;
        }


        let labelClassName = 'form__label' + ((this.props.value || this.state.isFocused) ? ' form__label--focus' : '');

        return (
            <label className={wrapperClass} htmlFor={this.props.name}>
                    <span className={labelClassName}
                          onClick={this.handleLabelClick.bind(this)}>
                        {this.props.title}
                    </span>

                {this.props.mask ? (
                    <InputMask {...inputProps}/>
                ) : (
                    <input {...inputProps}/>
                )}
            </label>
        );
    }

    handleBlur()
    {
        this.setState({
            isFocused: false
        })
    }


    handleLabelClick(e)
    {
        $(e.target).next($('.form__input')).focus();
    }

    handleMaskedChange(value, rawValue)
    {
        this.rawValue = rawValue;
        this.props.onChange(value);
    }

    handleChange(e)
    {
        this.rawValue = e.target.value;
        this.props.onChange(e.target.value);
    }

}

export default TextInput