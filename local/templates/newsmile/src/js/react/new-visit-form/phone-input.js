import React from 'react'
import ReactDOM from 'react-dom'
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
        addButtonContainerRef: PropTypes.any
    };

    static defaultProps = {
        defaultValue: '',
        value: '',
        required: false,
        mask: '',
        inputOnly: false
    };

    state = {
        value: this.props.value && this.props.defaultValue,
        additionalInputsCount: 0
    };

    render()
    {
        const inputClass = 'form__input' + (this.props.required ? ' form__input--required' : '');
        const wrapperClass = 'form__wrapper' + (this.props.required ? ' form__wrapper--required' : '');
        const inputProps = $.extend({}, this.props, {
            className: inputClass,
            type: 'text',
            value: this.state.value,
        });

        console.log(this.props.addButtonContainerRef, ' this.props.addButtonContainerRef!');

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

                {this.props.addButtonContainerRef
                    ? ReactDOM.createPortal(
                        this.renderAddButton(),
                        this.props.addButtonContainerRef.current
                    )
                    : this.renderAddButton()
                }
            </label>
        );
    }

    renderAdditionalInputs(mainInputProps)
    {
        let result = [];
        let additionalInputsProps = $.extend({}, mainInputProps);
        additionalInputsProps.name = this.props.additionalInputsName + '[]';

        for(let i = 0; i < this.state.additionalInputsCount; i++)
        {
            result.push(
                this.props.mask ? (
                    <InputMask {...additionalInputsProps}/>
                ) : (
                    <input {...additionalInputsProps}/>
                )
            );
        }
    }

    renderAddButton()
    {
        return (
            <button className="form__add-field-btn" onClick={this.addPhoneInput.bind(this)}>
                <span className="form__btn-label">
                    <svg className="form__btn-icon" id="Layer_1" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg"
                         viewBox="0 0 19.16 19.16">
                        <title>plus</title>
                        <line x1="2" y1="2" x2="17.16" y2="17.16" fill="none" strokeLinecap="round" strokeMiterlimit="10"
                              strokeWidth="2"/>
                        <line x1="17.16" y1="2" x2="2" y2="17.16" fill="none" strokeLinecap="round" strokeMiterlimit="10" strokeWidth="2"/>
                    </svg>
                    Добавить телефон
                </span>
            </button>
        );
    }

    addPhoneInput(e)
    {
        this.setState({
            additionalInputsCount: this.state.additionalInputsCount + 1
        });

        e.preventDefault();
    }
}

export default PhoneInput