import React from 'react'
import PropTypes from 'prop-types'
import $ from 'jquery'
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

        inputRef: PropTypes.any,

        onChange: PropTypes.func
    };

    static defaultProps = {
        required: false,
        mask: '',
        maskChar: '_'
    };

    state = {
        isActive: !!this.props.value
    };

    inputRef = null;
    inputComponent = null;

    constructor(props)
    {
        super(props);
        this.setInputRef = this.setInputRef.bind(this);
    }

    render()
    {
        const inputClass = 'form__input' + (this.props.required ? ' form__input--required' : '');
        let wrapperClass = 'form__wrapper' + (this.props.required ? ' form__wrapper--required' : '');
        wrapperClass += (this.props.disabled ? ' form__wrapper--disabled' : '');

        let inputProps = $.extend({}, this.props, {
            className: inputClass,
            type: 'text',
            onFocus: () => this.setState({isActive: true}),
            onBlur: this.handleBlur.bind(this)
        });


        if(this.props.mask)
        {
            inputProps.onChange = this.handleMaskedChange.bind(this);
        }
        else
        {
            inputProps.onChange = this.handleChange.bind(this);
            inputProps.ref = inputProps.inputRef;

            delete inputProps.mask;
            delete inputProps.maskChar;
            delete inputProps.inputRef;
        }


        let labelClassName = 'form__label' + ((this.props.value || this.state.isActive) ? ' form__label--focus' : '');

        return (
            <label className={wrapperClass} htmlFor={this.props.name}>
                    <span className={labelClassName}
                          onClick={this.handleLabelClick.bind(this)}>
                        {this.props.title}
                    </span>

                {this.props.mask ? (
                    <InputMask {...inputProps} inputRef={this.setInputRef} ref={ref => this.inputComponent = ref}/>
                ) : (
                    <input {...inputProps} ref={this.setInputRef()}/>
                )}
            </label>
        );
    }

    handleBlur()
    {
        if(this.inputComponent && this.inputComponent.isEmpty)
        {
            if(this.inputComponent.isEmpty())
            {
                this.setState({
                    isActive: false
                });
            }
        }
        else
        {
            this.setState({
                isActive: false
            });
        }
    }


    handleLabelClick(e)
    {
        $(e.target).next($('.form__input')).focus();
    }

    handleMaskedChange(value)
    {
        this.props.onChange(value);
    }

    handleChange(e)
    {
        this.props.onChange(e.target.value);
    }

    setInputRef(ref)
    {
        this.inputRef = ref;

        if(this.props.inputRef)
        {
            this.props.inputRef(ref);
        }
    }

}

export default TextInput