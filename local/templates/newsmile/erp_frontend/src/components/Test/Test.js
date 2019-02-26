import React from 'react'
import PropTypes from 'prop-types'
import './Test.scss'

const TodoList = (props) => {
    return (
        <ul>
            <li><TodoListItem /></li>
            <li><TodoListItem /></li>
        </ul>
    )
}

const TodoListItem = () => {
    return (
        <span>Drink Coffee</span>
    )
}

export default class Test extends React.Component {

    render() {
        return (
            <div>
                <TodoList />
            </div>
        )
    }
}