import React from 'react'
import PropTypes from 'prop-types'
import './Test.scss'

const TodoList = (props) => {
    return (
        <ul>
            <li><TodoListItem cap="coffee"/></li>
            <li><TodoListItem cap="tea"/></li>
        </ul>
    )
}

class TodoListItem extends React.Component {
    state = {
        done: false,
        important: false,
    }
    onDone = () => {
        this.setState(({done})=>{
            return {
                done: !done
            }
        })
    }
    onMarkImp = () => {
        this.setState(({important})=>{
            return {
                important: !important
            }
        })
    }

    render() {
        const {important, done} = this.state
        let className = 'test-item'

        if(important) {
            className += ' important'
        }

        if (done) {
            className += ' done'
        }

        return (
            <div className={className}>
                <span onClick={this.onDone}>{this.props.cap}</span>
                <button onClick={this.onMarkImp}> !</button>
            </div>
        )
    }
}

export default class Test extends React.Component {

    render() {
        return (
            <div>
                <TodoList/>
            </div>
        )
    }
}