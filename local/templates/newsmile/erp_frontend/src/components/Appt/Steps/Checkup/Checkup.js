import React from 'react'
import './Checkup.scss'
import Button from "../../../common/Button/Button"
import {IconPrint} from "./../../../common/Icons"
import CheckupForm from './CheckupForm'

export default class Checkup extends React.Component {
    render() {
        return (
            <div className="checkup">
                <CheckupHeader />
                <CheckupForm />
            </div>
        )
    }
}

class CheckupHeader extends React.Component {
    render() {
        return (
            <React.Fragment>
                <div className="checkup-header">
                    <div className="checkup-header__desc">
                        Детально опишите осмотр пациента
                    </div>
                    <div className="checkup-steps">
                        <a href="/tooth-card" className="checkup-steps__item">
                           Зубная карта
                        </a>
                        <a href="/checkup-form" className="checkup-steps__item">
                            Осмотр
                        </a>
                        <a href="/print" className="checkup-steps__item">
                            <IconPrint width="15" height="18"/>
                        </a>
                    </div>
                    <Button variant="success" text="Сохранить и продолжить" action="next"/>
                </div>
            </React.Fragment>
        )
    }
}