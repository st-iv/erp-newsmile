import React from 'react'
import './Appt.scss'
import HeaderAppt from './HeaderAppt/HeaderAppt'
import NavAppt from './NavAppt/NavAppt'

export default class Appt extends React.Component {
    render() {
        return (
            <div className="appt">
                <HeaderAppt />
                <NavAppt />
            </div>
        )
    }
}