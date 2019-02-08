import React from 'react'
import Scrollbar from 'react-scrollbars-custom'
import './Test.scss'

export default class Test extends React.Component {
    render() {
        return (
            <div>
                <a href="https://github.com/st-iv/erp-newsmile">erp new-smile</a>
                <Scrollbar style={{width: '200px', height: '200px', minHeight: 80}}>
                    <p>Text text text text text text text text text text text text text text text text text text text
                        text text text text text text text text text text text text text text text text text text text
                        text text text text text text text text text text text text text text text text text text text
                        text text text text text text text text text text text text text text text text text text text
                        text text text text text text text text text text text text text text text text text text text
                        text text text text text text text text text text text text text text text text text text text
                        text text text text text text text text text text text text text text </p>
                </Scrollbar>
            </div>
        )
    }
}