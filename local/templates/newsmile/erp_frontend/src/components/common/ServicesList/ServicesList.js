import React from 'react'
import PropTypes from 'prop-types'

export default class ServicesList extends React.Component {
    render() {
        return (
			<ul className="services-list">
				<li className="services-list__item">
					<span className="services-list__item-title">Терапия</span>
					<ul className="services-list__sublist">
						<li className = "services-list__sublist-item">
							<span className="services-list__sublist-item-title">Подпапка 1</span>
						</li>
						<li className = "services-list__sublist-item">
							<span className="services-list__sublist-item-title">Подпапка 2</span>
						</li>
					</ul>
				</li>
				<li className="services-list__item">
					<span className="services-list__item-title">Хирургия</span>
					<ul className="services-list__sublist">
						<li className = "services-list__sublist-item">
							<span className="services-list__sublist-item-title">Подпапка 1</span>
						</li>
						<li className = "services-list__sublist-item">
							<span className="services-list__sublist-item-title">Подпапка 2</span>
						</li>
					</ul>
				</li>
				<li className="services-list__item">
					<span className="services-list__item-title">Парадонтология</span>
					<ul className="services-list__sublist">
						<li className = "services-list__sublist-item">
							<span className="services-list__sublist-item-title">Подпапка 1</span>
						</li>
						<li className = "services-list__sublist-item">
							<span className="services-list__sublist-item-title">Подпапка 1</span>
						</li>
					</ul>
				</li>
				<li className="services-list__item">
					<span className="services-list__item-title">Имплантология</span>
					<ul className="services-list__sublist">
						<li className = "services-list__sublist-item">
							<span className="services-list__sublist-item-title">Еще одна подпапка</span>
							<ul className="services-list__sublist">
								<li className="services-list__sublist-item">
									<span className="services-list__sublist-item-title">Подпапка 1 внутри еще одной подпапки</span>
								</li>
								<li className="services-list__sublist-item">
									<span className="services-list__sublist-item-title">Подпапка 2 внутри еще одной подпапки</span>
								</li>
							</ul>
						</li>
						<li className = "services-list__sublist-item">
							<span className="services-list__sublist-item-title">Установка ипланта ADIN</span>
						</li>
						<li className = "services-list__sublist-item">
							<span className="services-list__sublist-item-title">Операции + костный материал</span>
						</li>
						<li className = "services-list__sublist-item">
							<span className="services-list__sublist-item-title">Установка импланта MIS</span>
						</li>
						<li className = "services-list__sublist-item">
							<span className="services-list__sublist-item-title">Установка ипланта Nobel</span>
						</li>
					</ul>
				</li>
				<li className="services-list__item">
					<span className="services-list__item-title">Несъемное протезирование</span>
				</li>
				<li className="services-list__item">
					<span className="services-list__item-title">Съемное протезирование</span>
				</li>
				<li className="services-list__item">
					<span className="services-list__item-title">Детство</span>
				</li>
				<li className="services-list__item">
					<span className="services-list__item-title">Ортодонтия</span>
				</li>
			</ul>
		)
	}
}