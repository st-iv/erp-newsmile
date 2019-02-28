import React from 'react'
import PropTypes from 'prop-types'
import './Tag.scss'

export default class Tag extends React.Component {
	render() {
		const {variant, text} = this.props;
		return (
			<span className={`tag tag-variant-${variant}`}>
				{text}
			</span>
		)
	}
}

Tag.defaultProps = {
    variant: "default"
}

Tag.propTypes = {
    variant: PropTypes.oneOf(["outline--mango", "outline--grape", "outline--success"]),
    text: PropTypes.string
}
