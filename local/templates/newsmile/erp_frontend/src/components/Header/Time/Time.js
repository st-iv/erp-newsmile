import React from 'react'
import Moment from 'react-moment'

export default class Time extends React.Component {
    state={
        date: new Date()
    };
    callMe(){
        setInterval(()=>{
            this.setState({date:new Date()})
        }, 1000);
    }
    render () {
        return (
                <div className="header_clock">
                    <Moment format = "HH:mm" interval = {1000}/>
                </div>

        )
    }
}