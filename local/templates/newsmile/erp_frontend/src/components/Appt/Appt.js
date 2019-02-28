import React from 'react'
import './Appt.scss'
import HeaderAppt from './HeaderAppt/HeaderAppt'
import NavAppt from './NavAppt/NavAppt'
import Checkup from "./Steps/Checkup/Checkup"
import Tabs from '../common/Tabs/Tabs'
import ServicesList from '../common/ServicesList/ServicesList';
import Button from '../common/Button/Button';
import { IconCheck } from '../common/Icons';
import ResultTable from './ResultTable/ResultTable';

export default class Appt extends React.Component {
    render() {
        return (
            <div className="appt">
                <HeaderAppt />
                <NavAppt />
                <Checkup />
                <ServicesList />
                <Button variant="outline--secondary" text="Верхняя челюсть"/>
                <Button variant="outline--secondary" text="Нижняя челюсть"/>
                <ResultTable />
            </div>
        )
    }
}