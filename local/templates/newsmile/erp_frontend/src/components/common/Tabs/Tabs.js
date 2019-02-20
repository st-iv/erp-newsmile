import React from 'react'
import PropTypes from 'prop-types'
import './Tabs.scss'

export default class Tabs extends React.Component {
	render() {
		return (
			<div className="tabs">
				<div className="tabs__item">Взрослый</div>
				<div className="tabs__item">Ребенок</div>
			</div>
		)
	}
}