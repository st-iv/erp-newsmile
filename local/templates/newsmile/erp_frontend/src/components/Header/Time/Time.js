import React from 'react'
import Moment from 'react-moment'

export default class Time extends React.Component {
    render () {
        return (
                <div className=" header_clock">
                    <Moment format = "HH:mm" interval = {1000}/>
                </div>

        )
    }
}