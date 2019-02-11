import React from 'react'
import Moment from 'react-moment'
import './HeaderAppt.scss'
import Button from './../../common/Button/Button'

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
                        <div className="header-appt__time-item header-appt__time-appt">
                            <div className="text">
                                Время
                            </div>
                             <div className="val">
                                 10:34 - 12:00
                             </div>
                        </div>
                        <div className="header-appt__time-item header-appt__time-current">
                            <div className="text">
                                Текущее
                            </div>
                            <div className="val">
                                <Moment format="HH:mm:ss" interval={1000}/>
                            </div>
                        </div>
                    </div>
                    <Button size="sm" variant="secondary" text="Открыть карту" />
                </div>
            </div>
        )
    }
}