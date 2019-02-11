import React from 'react'
import './Button.scss'

export default class Button extends React.Component {
    render() {
        return (
            <div className={['btn-'+this.props.size + ' ' + 'btn-'+this.props.variant]}>
                <span className="btn-text">
                    {this.props.text}
                </span>
            </div>
        )
    }
}