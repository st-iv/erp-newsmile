import React from 'react'
import PropTypes from 'prop-types'
import './Button.scss'

export default class Button extends React.Component {
    render() {
        const {size, variant, text, children} = this.props;
        return (
            <div className={`btn btn-size-${size} btn-variant-${variant}`}>
                <span className="btn-text">
                    {text || children}
                </span>
            </div>
        )
    }
}

Button.defaultProps = {
    size: "md",
    variant: "default"
}

Button.propTypes = {
    size: PropTypes.oneOf(["sm", "md", "lg"]),
    variant: PropTypes.oneOf(["primary", "secondary", "disabled", "default"]),
    text: PropTypes.string
}