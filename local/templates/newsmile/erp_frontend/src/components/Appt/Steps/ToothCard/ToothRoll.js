import React from 'react'
import './ToothRoll.scss'
import {IconTooth} from '../../../common/Icons'

export default class ToothRoll extends React.Component {
    render() {
        return (
            <div className="tooth-roll">
                <IconTooth fill="#ff4261"/>
                <IconTooth fill="#ff4261"/>
                <IconTooth fill="#ff4261"/>
                <IconTooth fill="#ff4261"/>
                <IconTooth fill="#f9d905"/>
                <IconTooth fill="#f9d905"/>
                <IconTooth fill="#f9d905"/>
                <IconTooth fill="#8fca00"/>
                <div className="tooth-main">
                    <IconTooth />
                </div>
            </div>
        )
    }
}