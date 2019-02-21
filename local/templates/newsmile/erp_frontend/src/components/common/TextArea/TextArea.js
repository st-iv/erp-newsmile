import React from 'react'
import PropTypes from 'prop-types'
import './TextArea.scss'

export default class TextArea extends React.Component {
    state = {
        desc: '',
    }

    onBtnClickHandler = (e) => {
        e.preventDefault()
    }

    handleTextChange = (e) => {
        this.setState({ desc: e.currentTarget.value })
    }

    render() {
        const {variant, text, title, children, placeholder} = this.props;
        const {desc} = this.state;
        return (
            <div className="textarea-wrap">
            <label className="textarea-title">{title}</label>
                <textarea name="txt" placeholder={placeholder} className="form__control" onChange={this.handleTextChange} value={desc}>
                </textarea>
            </div>
        )
    }
}

TextArea.defaultProps = {
    placeholder: ""
}