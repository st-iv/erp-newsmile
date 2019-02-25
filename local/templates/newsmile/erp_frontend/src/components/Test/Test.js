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

class StarRating extends React.Component {
    constructor(props) {
        super(props)
        this.state = {
            starsSelected: 0
        }
        this.change = this.change.bind(this)
    }

    change(starsSelected) {
        this.setState({starsSelected})
    }

    render() {
        const {totalStars} = this.props
        const {starSelected} = this.state
        return (
            <div className="star-rating">

            </div>
        )
    }
}
export default class Test extends React.Component {

    render() {
        return (
            <div>
                <span>rating</span>
                <Star></Star>
            </div>
        )
    }
}