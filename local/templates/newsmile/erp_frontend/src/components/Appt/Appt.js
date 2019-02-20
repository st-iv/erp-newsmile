import React from 'react'
import './Appt.scss'
import HeaderAppt from './HeaderAppt/HeaderAppt'
import NavAppt from './NavAppt/NavAppt'
import Checkup from "./Steps/Checkup/Checkup"
import ToothCard from "./Steps/ToothCard/ToothCard"
import ToothRoll from "./Steps/ToothCard/ToothRoll"

export default class Appt extends React.Component {
    render() {
        return (
            <div className="appt">
                <HeaderAppt />
                <NavAppt />
                {/*<Checkup />*/}
                <ToothCard />
                <ToothRoll />
            </div>
        )
    }
}