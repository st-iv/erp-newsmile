import React from 'react'
import PropTypes from 'prop-types'
import './Tabs.scss'

const tabNames = [
	{
        id: 1,
        text: 'Взрослый'
    },
    {
        id: 2,
        text: 'Ребенок'
    }
]

class Tabs extends React.Component {
	render() {
		const tabsTemplate = this.props.data.map(function (item, index) {
            return (
                <div className="tabs__item" key={index}>
                    {item.text}
                </div>
            )
		})
		
		return (
            <div className="tabs">
                {tabsTemplate}
            </div>
        )
	}
}

export default class TabsAppt extends React.Component {
    render() {
        return (
            <Tabs data={tabNames}/>
        )
    }
}