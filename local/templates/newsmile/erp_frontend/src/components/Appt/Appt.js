import React from 'react'
import './Appt.scss'
import HeaderAppt from './HeaderAppt/HeaderAppt'
import NavAppt from './NavAppt/NavAppt'
import Checkup from "./Steps/Checkup/Checkup"
import ToothCard from "./Steps/ToothCard/ToothCard"
import ToothRoll from "./Steps/ToothCard/ToothRoll"
import Tabs from '../common/Tabs/Tabs'
import ServicesList from '../common/ServicesList/ServicesList';
import Button from '../common/Button/Button';
import { IconCheck } from '../common/Icons';

export default class Appt extends React.Component {
    render() {
        return (
            <div className="appt">
                <HeaderAppt />
                <NavAppt />
                <Checkup />
                <ToothCard />
                <ToothRoll />
                <Tabs />
                <div className="check-group">
                    <div className="check-group__title-group">
                        <IconCheck width="10" height="7"/>
                        <span className="check-group__title">Отметить здоровыми</span>
                    </div>
                    <div className="check-group__btns">
                        <Button variant="outline--secondary" text="все" size="sm"/>
                        <Button variant="outline--secondary" text="в.ч." size="sm"/>
                        <Button variant="outline--secondary" text="н.ч." size="sm"/>
                    </div>
                    <Button variant="outline--secondary" text="Сбросить" size="sm"/>
                </div>
                <ServicesList />
            </div>
        )
    }
}