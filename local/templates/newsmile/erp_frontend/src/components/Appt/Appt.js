import React from 'react'
import './Appt.scss'
import HeaderAppt from './HeaderAppt/HeaderAppt'
import NavAppt from './NavAppt/NavAppt'
import Checkup from "./Steps/Checkup/Checkup"
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
                <ServicesList />
            </div>
        )
    }
}