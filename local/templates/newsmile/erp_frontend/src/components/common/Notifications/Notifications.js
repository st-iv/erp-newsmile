import React from 'react'
import PropTypes from 'prop-types'
import './Notifications.scss'

export default class Notifications extends React.Component {
    render() {
        const {size, variant, text, children} = this.props;
        return (
			<div className="notify notify__list">
				<div className="notify__item notify__item--finish notify__item--open">
					<div className="notify__item-header">
						<span className="notify__doctor-name" style={{backgroundColor: '#16aeed'}}>Рудзит Ю.Ф.</span>
						<div className="notify__signal"></div>
						<span className="notify__status">Закончил прием</span>
					</div>
					<div className="notify__item-content">
						<p className="notify__patient">Пациент - <span className="notify__patient-name">Свешникова К.А.</span></p>
						<p className="notify__cabinet">Кабинет - <span className="notify__cabinet-name">Терапия 1</span></p>
					</div>
					<button type="button" className="notify__close-btn">
						<svg className="notify__close-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 19.16 19.16"><title>close</title><line x1="2" y1="2" x2="17.16" y2="17.16" fill="none" stroke-linecap="round" stroke-miterlimit="10" stroke-width="4"/><line x1="17.16" y1="2" x2="2" y2="17.16" fill="none" stroke-linecap="round" stroke-miterlimit="10" stroke-width="4"/></svg>
					</button>
				</div>
				<div className="notify__item notify__item--ready notify__item--open">
					<div className="notify__item-header">
						<span className="notify__doctor-name" style={{backgroundColor: '#713fd6'}}>Виноградова И.Б.</span>
						<div className="notify__signal"></div>
						<span className="notify__status">Готов принять пациента</span>
					</div>
					<div className="notify__item-content">
						<p className="notify__patient">Пациент - <span className="notify__patient-name">Рей О</span></p>
						<p className="notify__cabinet">Кабинет - <span className="notify__cabinet-name">Кресло 3</span></p>
					</div>
					<button type="button" className="notify__close-btn">
						<svg className="notify__close-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 19.16 19.16"><title>close</title><line x1="2" y1="2" x2="17.16" y2="17.16" fill="none" stroke-linecap="round" stroke-miterlimit="10" stroke-width="4"/><line x1="17.16" y1="2" x2="2" y2="17.16" fill="none" stroke-linecap="round" stroke-miterlimit="10" stroke-width="4"/></svg>
					</button>
				</div>
				<div className="notify__item notify__item--waiting notify__item--open">
					<div className="notify__item-info">
						<div className="notify__item-header">
							<span className="notify__status">Пациент ожидает</span>
						</div>
						<div className="notify__item-content">
							<div className="notify__signal"></div>
							<span className="notify__doctor-name">Свешникова К.А.</span>
							<span className="notify__time">13:00 - 14:00</span>
						</div>
					</div>
					<button type="button" className="notify__write-btn">
						Принять пациента
					</button>
				</div>
			</div>
        )
    }
}