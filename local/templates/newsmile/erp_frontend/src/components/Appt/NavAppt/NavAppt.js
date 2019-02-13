import React from 'react'
import './NavAppt.scss'

export default class HeaderAppt extends React.Component {
    render() {
        return (
            <div className="appt-nav">
                <div className="appt-nav__item appt-nav__item--active">
                    Первичный осмотр
                </div>
                <div className="appt-nav__item">
                    План лечения
                </div>
                <div className="appt-nav__item">
                    Процесс лечения
                </div>
                <div className="appt-nav__item">
                    Наряд
                </div>
                <div className="appt-nav__item">
                    Курс лечения
                </div>
                <div className="appt-nav__item">
                    Гарантии
                </div>
                <div className="appt-nav__item">
                    V
                </div>
            </div>
        )
    }
}