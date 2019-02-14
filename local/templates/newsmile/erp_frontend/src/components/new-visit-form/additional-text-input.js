import React from 'react'
import ReactDOM from 'react-dom'
import PropTypes from 'prop-types'
import TextInput from './text-input'
import $ from 'jquery'

export default class AdditionalTextInput extends React.PureComponent
{
    static propTypes = {
        buttonTitle: PropTypes.string.isRequired,
        updateKey: PropTypes.any,
        value: PropTypes.any
    };

    state = {
        active: this.isActive()
    };

    inputRef = null;

    setFocus = false;

    componentWillReceiveProps(newProps, newContext)
    {
        // обновление активности при изменении updateKey или значения свойства
        if((newProps.updateKey !== this.props.updateKey) || (!this.isActive() && this.isActive(newProps)))
        {
            this.setState({active: this.isActive(newProps)});
        }
    }

    isActive(props = null)
    {
        if(props === null)
        {
            props = this.props;
        }

        return !!String(props.value).length;
    }

    componentDidUpdate(prevProps, prevState, snapshot)
    {
        if(this.setFocus)
        {
            this.inputRef.focus();
            this.setFocus = false;
        }
    }

    render()
    {
        let props = $.extend({}, this.props);

        if(this.state.active)
        {
            delete props.buttonTitle;
            delete props.updateKey;

            return (
                <TextInput {...props} inputRef={ref => this.inputRef = ref}/>
            );
        }
        else
        {
            return (
                <button className="form__add-field-btn" onClick={this.handleButtonClick.bind(this)}>
                    <span className="form__btn-label">
                        <svg className="form__btn-icon" id="Layer_1" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg"
                             viewBox="0 0 19.16 19.16">
                            <title>plus</title>
                            <line x1="2" y1="2" x2="17.16" y2="17.16" fill="none" strokeLinecap="round" strokeMiterlimit="10"
                                  strokeWidth="2"/>
                             <line x1="17.16" y1="2" x2="2" y2="17.16" fill="none" strokeLinecap="round" strokeMiterlimit="10" strokeWidth="2"/>
                        </svg>
                        {this.props.buttonTitle}
                    </span>
                </button>
            );
        }
    }

    handleButtonClick()
    {
        this.setState({active: true});
        this.setFocus = true;
    }
}