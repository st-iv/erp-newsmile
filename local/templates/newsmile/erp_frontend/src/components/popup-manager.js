import React from 'react'
import ReactCSSTransitionGroup from 'react-addons-css-transition-group'
import Popup from './popup'

class PopupManager extends React.Component
{
    static instance = null;
    state = {
        popups: []
    };

    constructor(props)
    {
        super(props);

        if(this.constructor.instance)
        {
            new Error('Разрешено подключение только одного экземпляра PopupManager');
        }
        else
        {
            // костыльное решение до ввода global store
            this.constructor.instance = this;
        }
    }

    render()
    {
        return (
            <div>
                <ReactCSSTransitionGroup transitionName="popup" transitionEnterTimeout={400} transitionLeaveTimeout={400}>
                    {this.state.popups.map( (popupContent, index) =>
                        <Popup key={index} onClose={this.removePopup.bind(this, index)} freezeTimeout={400}>
                            {popupContent}
                        </Popup>//
                    )}
                </ReactCSSTransitionGroup>
            </div>
        );
    }

    static showPopup(popup)
    {
        return this.instance.addPopup(popup);
    }

    static closePopup(popupIndex)
    {
        this.instance.setState({
            popups: this.instance.state.popups.slice(0, popupIndex)
        })
    }

    addPopup(popupContent)
    {
        let popups = this.state.popups.slice();
        popups.push(popupContent);
        this.setState({popups});

        return this.state.popups.length;
    }

    removePopup(index)
    {
        let popups = this.state.popups.slice();
        popups.splice(index, 1);
        this.setState({popups});//
    }
}

export default PopupManager