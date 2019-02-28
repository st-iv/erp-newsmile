import React from 'react'
import PropTypes from 'prop-types'
import './ToothRoll.scss'
import {IconCheck, IconTooth} from '../../../common/Icons'

const statusList = [
    {"id": 1, "code": "G", "decode": "Здоров"},
    {"id": 2, "code": "S", "decode": "Пломба"},
    {
        "id": 3,
        "code": "C",
        "decode": "Коронка"
    },
    {"id": 4, "code": "I", "decode": "Искусственный зуб"}, {"id": 5, "code": "R", "decode": "Радикс"},
    {
        "id": 6,
        "code": "Pt",
        "decode": "Периодонтит"
    },
    {"id": 7, "code": "P", "decode": "Пульпит"},
    {"id": 8, "code": "K", "decode": "Кариес"},
    {
        "id": 9,
        "code": "E",
        "decode": "Отсутствует"
    },
    {"id": 10, "code": "N", "decode": "Не определен"}
]

class ToothItem extends React.Component {
    static propTypes = {
        variant: PropTypes.oneOf(["default", "missing", "sick", "cured", "healthy"]),
        code: PropTypes.oneOf(["r", "pt", "sick", "p", "c"]),
    }
    static defaultProps = {
        code: "default"
    }

    render() {
        const {code} = this.props
        return (
            <div className={`tooth-roll__item tooth-roll__item--variant-${code} tooth-roll__item--action-r`}>
                <IconTooth/>
            </div>
        )
    }
}

export default class ToothRoll extends React.Component {
    render() {
        return (
            <div className="tooth-roll">
                <div className="row">
                    {/*<div className="tooth-roll__item tooth-roll__item--variant-danger tooth-roll__item--action-r">
                        <IconTooth/>
                    </div>*/}
                    <ToothItem/>
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
                        <IconTooth/>
                    </div>
                    <div className="tooth-roll__item tooth-roll__item--variant-cured tooth-roll__item--action-implant">
                        <IconTooth/>
                    </div>
                </div>
            </div>
        )
    }
}