import React from 'react'
import './NavAppt.scss'

const stepNames = [
    {
        id: 1,
        text: 'Первичный осмотр'
    },
    {
        id: 2,
        text: 'План лечения'
    },
    {
        id: 3,
        text: 'Процесс лечения'
    },
    {
        id: 4,
        text: 'Наряд'
    },
    {
        id: 5,
        text: 'Курс лечения'
    },
    {
        id: 6,
        text: 'Гарантии'
    },
    {
        id: 7,
        text: 'V'
    }

]

class ApptStep extends React.Component {
    render() {
        const stepsTemplate = this.props.data.map(function (item, index) {
            return (
                <div key={index}>
                    <div className="appt-nav__item">
                        {item.text}
                    </div>
                </div>
            )
        })

        return (
            <div className="appt-nav">
                {stepsTemplate}
            </div>
        )

    }
}

export default class HeaderAppt extends React.Component {
    render() {
        return (
            <ApptStep data={stepNames}/>
        )
    }
}