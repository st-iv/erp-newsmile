import React from 'react'
import './Contacts.css'

export default class Contacts extends React.Component {
    state = {
        visible: false,
    }

    handleReadMoreClck = (e) => {
        e.preventDefault()
        this.setState({
            visible: true
        })
    }

    render () {
        const { visible } = this.state
        const contactsMore = this.props.data.map(function(item, index){
            return (
                <div className ="place__item" key={item.id}>
                    <div className="place__city">{item.city}</div>
                    <div className="place__address">{item.address}</div>
                    <div className="place__patients">Cегодня пациентов - {item.patients}</div>
                    <div className="place__hours">Свободно – {item.freeHours} из {item.totalHours}</div>
                </div>
            )
        })

        return (
            <div className="place__list">
                <div className="header_place">
                    <div className="place_current">
                        <div className="place_current_city">Иркутск</div>
                        <div className="place_current_adrs">Донская, 24/3</div>
                    </div>
                </div>
                {
                    !visible && <div className="header_drwnarr" onClick={this.handleReadMoreClck}></div>
                }
                {
                    visible && <div className="place__listMore">{contactsMore}</div>
                }
            </div>
        )
    }
}