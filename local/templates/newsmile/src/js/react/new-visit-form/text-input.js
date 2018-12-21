import React from 'react'
import PropTypes from 'prop-types'
import InputMask from 'react-input-mask'

class TextInput extends React.Component
{
    static propTypes = {
        name: PropTypes.string.isRequired,
        title: PropTypes.string.isRequired,
        defaultValue: PropTypes.string,
        value: PropTypes.string,
        required: PropTypes.bool,
        placeholder: PropTypes.string,

        mask: PropTypes.string,
        alwaysShowMask: PropTypes.bool,
        maskChar: PropTypes.string,
    };

    static defaultProps = {
        defaultValue: '',
        required: false,
        mask: '',
        maskChar: '_'
    };

    state = {
        value: this.props.value && this.props.defaultValue,
        isActive: !this.isEmpty(this.props.value) || !this.isEmpty(this.props.defaultValue)
    };

    render()
    {
        const inputClass = 'form__input' + (this.props.required ? ' form__input--required' : '');
        const wrapperClass = 'form__wrapper' + (this.props.required ? ' form__wrapper--required' : '');
        const inputProps = $.extend({}, this.props, {
            className: inputClass,
            type: 'text',
            value: this.state.value,
            onFocus: () => this.setState({isActive: true}),
            onBlur: this.handleBlur.bind(this),
            onChange: e => this.setState({value: e.target.value}),
        });

        delete inputProps.defaultValue;


        let labelClassName = 'form__label' + (this.state.isActive ? ' form__label--focus' : '');

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
        if(this.isEmpty(this.state.value))
        {
            this.setState({
                isActive: false
            })
        }
    }

    isEmpty(value)
    {
        return (!value || (value === this.props.mask.replace(/[*9a]/g, this.props.maskChar)));
    }


    handleLabelClick(e)
    {
        $(e.target).next($('.form__input')).focus();
    }
}

export default TextInput