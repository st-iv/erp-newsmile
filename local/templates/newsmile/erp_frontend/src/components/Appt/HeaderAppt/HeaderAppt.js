import React from 'react'
import './HeaderAppt.scss'
import Moment from 'react-moment'

export default class HeaderAppt extends React.Component {
    render() {
        return (
            <div className="header-appt">
                <div className="header-appt__left">
                    <div className="header-appt__title">Прием пациента</div>
                    <div className="header-appt__user header-appt__user--online">Константинов Владимир</div>
                </div>
                <div className="header-appt__right">
                    <div className="header-appt__time-wrap">
                        <div className="header-appt__time-appt"></div>
                        <div className="header-appt__time-current"><Moment format = "HH:mm" interval = {1000}/></div>

                    </div>
                </div>
            </div>
        )
    }
}