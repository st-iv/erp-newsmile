import React from 'react'
import './Checkup.scss'
import Button from "../../../common/Button/Button"
import {IconPrint} from "./../../../common/Icons"
import CheckupForm from './CheckupForm'
import ToothCard from './ToothCard'
import Print from './Print'
import {BrowserRouter, Route, NavLink} from 'react-router-dom'

export default class Checkup extends React.Component {
    render() {
        return (
                <BrowserRouter>
                    <div className="checkup">
                    <React.Fragment>
                        <div className="checkup-header">
                            <div className="checkup-header__desc">
                                Детально опишите осмотр пациента
                            </div>
                            <div className="checkup-steps">
                                <NavLink to="/tooth-card" className="checkup-steps__item">
                                    Зубная карта
                                </NavLink>
                                <NavLink to="/checkup-form" className="checkup-steps__item">
                                    Осмотр
                                </NavLink>
                                <NavLink to="/print" className="checkup-steps__item">
                                    <IconPrint width="15" height="18"/>
                                </NavLink>
                            </div>
                            <Button variant="success" text="Сохранить и продолжить" action="next"/>
                        </div>
                    </React.Fragment>
                        <Route path="/tooth-card" component={ToothCard}/>
                        <Route path="/checkup-form" component={CheckupForm}/>
                        <Route path="/print" component={Print}/>
                    </div>
                </BrowserRouter>
        )
    }
}