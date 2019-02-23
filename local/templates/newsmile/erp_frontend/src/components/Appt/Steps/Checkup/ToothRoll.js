import React from 'react'
import './ToothRoll.scss'
import {IconTooth} from '../../../common/Icons'

export default class ToothRoll extends React.Component {
    render() {
        return (
            <div className="tooth-roll">
                <div className="row">
                    <IconTooth fill="#ff4261"/>
                    <IconTooth fill="#f4f4f4"/>
                    <IconTooth fill="#8fca00"/>
                </div>
                <div className="row row-md">
                    <div className="column">
                        <IconTooth fill="#ff4261"/>
                        <IconTooth fill="#ff4261"/>
                    </div>
                    <div className="tooth-roll__select">
                        <IconTooth/>
                    </div>
                    <div className="column">
                        <IconTooth fill="#f9d905"/>
                        <IconTooth fill="#f9d905"/>
                    </div>

                </div>
                <div className="row">
                    <IconTooth fill="#ff4261" />
                    <IconTooth fill="#f9d905"/>
                </div>
            </div>
        )
    }
}