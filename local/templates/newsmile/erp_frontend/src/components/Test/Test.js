import React from 'react'
import PropTypes from 'prop-types'
import './Test.scss'

const Star =({selected=false, onClick=f=>f}) => {
    return (
        <div className={(selected)? "star selected" : "star"} onClick={onClick}>
        </div>
    )
}

Star.propTypes = {
    selected: PropTypes.bool,
    onClick: PropTypes.func,
}
export default class Test extends React.Component {

    render() {
        return (
            <div>
                <p>test</p>
                <Star></Star>
            </div>
        )
    }
}