import React from 'react'
import './CheckupForm.scss'
import TextArea from "../../../common/TextArea/TextArea"
import AccordionList from "../../../common/Accordion/Accordion"

const AccordionData = [
    {
        id: 1,
        text: 'Перенесенные и сопутствующие заболевания',
        content: 'контент аккордеона'
    },
    {
        id: 2,
        text: 'Развитие настоящего заболевания',
        content: 'контент аккордеона'
    },
    {
        id: 3,
        text: 'Данные объективного обследования, внешний осмотр',
        content: 'контент аккордеона'
    },
    {
        id: 4,
        text: 'Прикус',
        content: 'контент аккордеона'
    },
    {
        id: 5,
        text: 'Состояние слизистой оболочки полости рта, десен, альвеолярных отростков и неба',
        content: 'контент аккордеона'
    },
    {
        id: 6,
        text: 'Данные рентгеновских и лабораторных исследований',
        content: 'контент аккордеона'
    },
]

export  default class CheckupForm extends React.Component {
    render() {
        return (
            <div className="checkup-content">
                <div className="appt-form">
                    <TextArea title="Диагноз" placeholder="Опишите состояние пациента"/>
                    <TextArea title="Жалобы" placeholder="Опишите жалобы"/>
                    <AccordionList data={AccordionData} />
                </div>
                <div className="appt-explorer">
                    <div className="appt-explorer__title">
                        Диагноз
                    </div>
                    <p className="appt-explorer__desc">
                        частичная потеря зубов на нижней челюсти (1-ый класс по
                        Е.И. Гаврилову)
                    </p>
                </div>
            </div>
        )
    }
}