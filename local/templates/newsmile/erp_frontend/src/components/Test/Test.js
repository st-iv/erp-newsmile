import React from 'react'
import './Test.scss'

export default class Test extends React.Component {
    render() {
        return (
            <div className="test">
                <p>
                    {this.props.name}
                </p>
            </div>
            )
    }
}