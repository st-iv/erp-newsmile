import React from 'react';
import ReactDOM from 'react-dom'
import PropTypes from 'prop-types'
import ReactCSSTransitionGroup from 'react-addons-css-transition-group'

class Popup extends React.Component
{
    popup = null;

    static propTypes = {
        onClose: PropTypes.func,
        freezeTimeout: PropTypes.number //время, в течение которого после отображения компонент не должен обновляться (чтобы не лагала анимация)
    };

    render()
    {
        return (
            <div className="popup">

                <div className="popup__wrapper">
                    <button className="popup__close-btn" type="btn" onClick={this.props.onClose}>
                        <svg className="popup__close-icon" xmlns="http://www.w3.org/2000/svg"
                             viewBox="0 0 19.16 19.16">
                            <title>close</title>
                            <line x1="2" y1="2" x2="17.16" y2="17.16" fill="none" strokeLinecap="round"
                                  strokeMiterlimit="10" strokeWidth="3"/>
                            <line x1="17.16" y1="2" x2="2" y2="17.16" fill="none" strokeLinecap="round"
                                  strokeMiterlimit="10" strokeWidth="3"/>
                        </svg>
                    </button>

                    {React.cloneElement(this.props.children, {freezeTimeout: this.props.freezeTimeout})}
                </div>

            </div>
        );
    }

}

export default Popup