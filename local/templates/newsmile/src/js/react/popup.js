import React from 'react';
import ReactDOM from 'react-dom'
import PropTypes from 'prop-types'
import ReactCSSTransitionGroup from 'react-addons-css-transition-group'

class Popup extends React.Component
{
    popup = null;

    static propTypes = {
        onClose: PropTypes.func
    };

    render()
    {
        return null;
    }

    componentDidMount()
    {
        this.renderPopup();
    }

    componentDidUpdate()
    {
        this.renderPopup();
    }

    componentWillUnmount()
    {
        ReactDOM.unmountComponentAtNode(this.popup);
        document.body.removeChild(this.popup);
    }

    renderPopup()
    {

        if (!this.popup)
        {
            this.popup = document.createElement("div");
            document.body.appendChild(this.popup);
        }

        ReactDOM.render(
            <div className="popup">
                <ReactCSSTransitionGroup transitionName="popup" transitionEnterTimeout={400} transitionLeaveTimeout={400}>
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

                        {this.props.children}
                    </div>
                </ReactCSSTransitionGroup>
            </div>,
            this.popup
        );
    }
}

export default Popup