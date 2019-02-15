import React from 'react'
import './Checkup.scss'
import Button from "../../../common/Button/Button"

export default class Checkup extends React.Component {
    render() {
        return (
            <div className="checkup">
                <CheckupHeader />
            </div>
        )
    }
}

class CheckupHeader extends React.Component {
    render() {
        return (
            <div className="checkup-header">
                <div className="checkup-header__desc">
                    Детально опишите осмотр пациента
                </div>
                <div className="checkup-steps">
                    <div className="checkup-steps__item">
                        Зубная карта
                    </div>
                    <div className="checkup-steps__item">
                        Осмотр
                    </div>
                    <div className="checkup-steps__item">
                        Печать
                    </div>
                </div>
                <Button variant="success" text="Сохранить и продолжить"/>
            </div>
        )
    }
}