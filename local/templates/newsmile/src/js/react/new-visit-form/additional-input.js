import React from 'react'
import PropTypes from 'prop-types'

export default class AdditionalInput extends React.PureComponent
{
    static propTypes = {
        buttonTitle: PropTypes.string.isRequired,
        updateKey: PropTypes.any
    };

    state = {
        active: false
    };

    componentWillReceiveProps(newProps, newContext)
    {
        if(newProps.updateKey !== this.props.updateKey)
        {
            this.setState({active: false});
        }
    }

    render()
    {
        return (
            this.state.active
            ? this.props.children
            : (
                <button className="form__add-field-btn" onClick={() => this.setState({active: true})}>
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
            )

        );
    }
}