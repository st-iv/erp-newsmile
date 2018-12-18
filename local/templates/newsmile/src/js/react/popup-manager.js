import React from 'react'
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

                    {this.state.popups.map( (popupContent, index) =>
                        <Popup key={index} onClose={this.removePopup.bind(this, index)}>
                            {popupContent}
                        </Popup>
                    )}
            </div>
        );
    }

    static showPopup(popup)
    {
        this.instance.addPopup(popup);
    }

    addPopup(popupContent)
    {
        let popups = this.state.popups.slice();
        popups.push(popupContent);
        this.setState({popups});
    }

    removePopup(index)
    {
        let popups = this.state.popups.slice();
        popups.splice(index, 1);
        this.setState({popups});//
    }
}

export default PopupManager