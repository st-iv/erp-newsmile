import React from 'react'
import PropTypes from 'prop-types'
import './Button.scss'
import {IconArrow, IconPrint} from '../Icons'

export default class Button extends React.Component {
    render() {
        const {size, variant, action, text, children} = this.props;
        return (
            <div className={`btn btn-size-${size} btn-variant-${variant}`}>
                        <span className="btn-text">
                            {text || children}
                        </span>
                {action=='next' && <IconArrow />}
                {action=='reset' && <IconPrint />}
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
    variant: PropTypes.oneOf(["primary", "secondary", "success", "disabled", "default", "outline--secondary"]),
    action: PropTypes.oneOf(["next"]),
    text: PropTypes.string
}