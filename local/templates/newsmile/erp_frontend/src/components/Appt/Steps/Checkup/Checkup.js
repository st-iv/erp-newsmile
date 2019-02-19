import React from 'react'
import './Checkup.scss'
import Button from "../../../common/Button/Button"
import TextArea from "../../../common/TextArea/TextArea"
import AccordionList from "../../../common/Accordion/Accordion"
import {IconPrint, IconArrow} from "./../../../common/Icons";

const AccordionData = [
    {
        id: 1,
        text: 'Перенесенные и сопутствующие заболевания'
    },
    {
        id: 2,
        text: 'Развитие настоящего заболевания'
    },
    {
        id: 3,
        text: 'Данные объективного обследования, внешний осмотр'
    },
    {
        id: 4,
        text: 'Прикус'
    },
    {
        id: 5,
        text: 'Состояние слизистой оболочки полости рта, десен, альвеолярных отростков и неба'
    },
    {
        id: 6,
        text: 'Данные рентгеновских и лабораторных исследований'
    },
]

export default class Checkup extends React.Component {
    render() {
        return (
            <div className="checkup">
                <CheckupHeader />
                <CheckupContent />
            </div>
        )
    }
}

class CheckupHeader extends React.Component {
    render() {
        return (
            <React.Fragment>
                <div className="checkup-header">
                    <div className="checkup-header__desc">
                        Детально опишите осмотр пациента
                    </div>
                    <div className="checkup-steps">
                        <div className="checkup-steps__item">
                            Зубная карта
                        </div>
                        <div className="checkup-steps__item">
                            Осмотр
                        </div>
                        <div className="checkup-steps__item">
                            <IconPrint width="15" height="18"/>
                        </div>
                    </div>
                    <Button variant="success" text="Сохранить и продолжить"/>
                </div>
            </React.Fragment>
        )
    }
}

class CheckupContent extends React.Component {
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