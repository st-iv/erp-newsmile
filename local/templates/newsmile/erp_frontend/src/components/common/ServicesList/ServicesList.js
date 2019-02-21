import React from 'react'
import PropTypes from 'prop-types'
import './ServicesLists.scss'
import { IconFolder } from './../Icons';

export default class ServicesList extends React.Component {
    render() {
        return (
			<ul className="services-list services-list--lvl1">
				<li className="services-list__item">
					<div className="services-list__item-title-group services-list__item-title-group--full">
						<IconFolder width="13" height="11" />
						<a href="" className="services-list__item-title">Терапия</a>
					</div>
				</li>
				<li className="services-list__item">
					<div className="services-list__item-title-group">
						<IconFolder width="13" height="11" />
						<a href="" className="services-list__item-title">Хирургия</a>
					</div>
				</li>
				<li className="services-list__item">
					<div className="services-list__item-title-group">
						<IconFolder width="13" height="11" />
						<a href="" className="services-list__item-title">Парадонтология</a>
					</div>
				</li>
				<li className="services-list__item">
					<div className="services-list__item-title-group services-list__item-title-group--full services-list__item-title-group--active">
						<IconFolder width="13" height="11" />
						<a href="" className="services-list__item-title">Имплантология</a>
					</div>
					<ul className="services-list services-list--lvl2">
						<li className = "services-list__item">
							<div className="services-list__item-title-group services-list__item-title-group--full">
								<IconFolder width="13" height="11" />
								<a href="" className="services-list__item-title">Еще одна подпапка</a>
							</div>
						</li>
						<li className = "services-list__item">
							<div className="services-list__item-title-group">
								<IconFolder width="13" height="11" />
								<a href="" className="services-list__item-title">Установка ипланта ADIN</a>
							</div>
						</li>
						<li className = "services-list__item">
							<div className="services-list__item-title-group">
								<IconFolder width="13" height="11" />
								<a href="" className="services-list__item-title">Операции + костный материал</a>
							</div>
						</li>
						<li className = "services-list__item">
							<div className="services-list__item-title-group">
								<IconFolder width="13" height="11" />
								<a href="" className="services-list__item-title">Установка импланта MIS</a>
							</div>
						</li>
						<li className = "services-list__item">
							<div className="services-list__item-title-group">
								<IconFolder width="13" height="11" />
								<a href="" className="services-list__item-title">Установка ипланта Nobel</a>
							</div>
						</li>
					</ul>
				</li>
				<li className="services-list__item">
					<div className="services-list__item-title-group services-list__item-title-group--full">
						<IconFolder width="13" height="11" />
						<a href="" className="services-list__item-title">Несъемное протезирование</a>
					</div>
				</li>
				<li className="services-list__item">
					<div className="services-list__item-title-group services-list__item-title-group--full">
						<IconFolder width="13" height="11" />
						<a href="" className="services-list__item-title">Съемное протезирование</a>
					</div>
				</li>
				<li className="services-list__item">
					<div className="services-list__item-title-group">
						<IconFolder width="13" height="11" />
						<a href="" className="services-list__item-title">Детство</a>
					</div>
				</li>
				<li className="services-list__item">
					<div className="services-list__item-title-group services-list__item-title-group--full">
						<IconFolder width="13" height="11" />
						<a href="" className="services-list__item-title">Ортодонтия</a>
					</div>
				</li>
			</ul>
		)
	}
}