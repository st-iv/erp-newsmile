import React from 'react'
import './ToothRoll.scss'
import {IconCheck, IconTooth} from '../../../common/Icons'

export default class ToothRoll extends React.Component {
    render() {
        return (
            <div className="tooth-roll">
                <div className="row">
                    <div className="tooth-roll__item tooth-roll__item--variant-danger tooth-roll__item--action-r">
                        <IconTooth/>
                    </div>
                    <div className="tooth-roll__item tooth-roll__item--none">
                        <IconTooth/>
                    </div>
                    <div className="tooth-roll__item tooth-roll__item--good">
                        <IconTooth/>
                    </div>
                </div>
                <div className="row row-md">
                    <div className="column">
                        <div className="tooth-roll__item tooth-roll__item--variant-danger tooth-roll__item--action-rt">
                            <IconTooth fill="#ff4261"/>
                        </div>
                        <div className="tooth-roll__item tooth-roll__item--variant-danger tooth-roll__item--action-p">
                            <IconTooth fill="#ff4261"/>
                        </div>
                    </div>
                    <div className="tooth-roll__select">
                        <IconTooth/>
                    </div>
                    <div className="column">
                        <div className="tooth-roll__item tooth-roll__item--variant-cured tooth-roll__item--prosthesis">
                            <IconTooth/>
                        </div>
                        <div className="tooth-roll__item tooth-roll__item--variant-cured tooth-roll__item--crown">
                            <IconTooth/>
                        </div>
                    </div>

                </div>
                <div className="row">
                    <div className="tooth-roll__item tooth-roll__item--variant-danger tooth-roll__item--action-c">
                        <IconTooth />
                    </div>
                    <div className="tooth-roll__item tooth-roll__item--variant-cured tooth-roll__item--action-implant">
                        <IconTooth />
                    </div>
                </div>
            </div>
        )
    }
}