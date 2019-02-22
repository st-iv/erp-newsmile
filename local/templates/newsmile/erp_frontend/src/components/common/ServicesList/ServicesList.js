import React from 'react'
import PropTypes from 'prop-types'
import './ServicesLists.scss'
import { IconFolder } from './../Icons';

const ServicesItems = [
    {
        id: 1,
		name: 'Терапия',
		filling: 'full',
    },
    {
        id: 2,
		name: 'Хирургия',
		filling: 'empty',
    },
    {
        id: 3,
		name: 'Парадонтология',
		filling: 'empty',
    },
    {
        id: 4,
		name: 'Имплантология',
		filling: 'full',
		values: [
			{
				id: 1,
				name: 'Еще одна подпапка',
				filling: 'full',
			},
			{
				id: 2,
				name: 'Установка ипланта ADIN',
				filling: 'empty',
			},
			{
				id: 3,
				name: 'Операции + костный материал',
				filling: 'empty',
			},
			{
				id: 4,
				name: 'Установка импланта MIS',
				filling: 'empty',
			},
			{
				id: 5,
				name: 'Установка ипланта Nobel',
				filling: 'empty',
			},
		]
    },
    {
        id: 5,
		name: 'Несъемное протезирование',
		filling: 'full',
	},
    {
        id: 6,
        name: 'Съемное протезирование',
		filling: 'full',
    },
    {
        id: 7,
		name: 'Детство',
		filling: 'empty'
	},
	{
        id: 8,
		name: 'Ортодонтия',
		filling: 'full',
    }

]

function ListItem({ item }) {
	let children = null;
	if (item.values && item.values.length) {
		children = (
			<ul className="services-list">
				{item.values.map(i => (
					<ListItem className="services-list__item" item={i} key={i.id} />
				))}
			</ul>
		);
	}

	return (
		<li className="services-list__item">
			<div className={`services-list__item-title-group services-list__item-title-group--${item.filling}`}>
				<IconFolder width="13" height="11" />
				<a href="" className="services-list__item-title">{item.name}</a>
			</div>
			{children}
		</li>
	);
}

export default class ServicesList extends React.Component {
	render() {
		return (
			<ul className="services-list">
			  {ServicesItems.map(i => (
				<ListItem item={i} key={i.id} />
			  ))}
			</ul>
		);
	}
}