import React from 'react'
import './ToothRoll.scss'
import {IconCheck, IconTooth} from '../../../common/Icons'

export default class ToothRoll extends React.Component {
    render() {
        return (
            <div className="tooth-roll">
                <div className="row">
                    <div className="tooth-roll__item tooth-roll__item--r">
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
                        <div className="tooth-roll__item tooth-roll__item--rt">
                            <IconTooth fill="#ff4261"/>
                        </div>
                        <div className="tooth-roll__item tooth-roll__item--p">
                            <IconTooth fill="#ff4261"/>
                        </div>
                    </div>
                    <div className="tooth-roll__select">
                        <IconTooth/>
                    </div>
                    <div className="column">
                        <div className="tooth-roll__item tooth-roll__item--tst1">
                            <IconTooth/>
                        </div>
                        <div className="tooth-roll__item tooth-roll__item--tst2">
                            <IconTooth/>
                        </div>
                    </div>

                </div>
                <div className="row">
                    <div className="tooth-roll__item tooth-roll__item--c">
                        <IconTooth />
                    </div>
                    <div className="tooth-roll__item tooth-roll__item--implant">
                        <IconTooth />
                    </div>
                </div>
            </div>
        )
    }
}