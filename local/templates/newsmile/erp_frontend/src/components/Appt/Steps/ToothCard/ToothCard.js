import React from 'react'
import './ToothCard.scss'
import {IconTooth} from "../../../common/Icons";

const toothData = [
    {
        id: 18,
    },
    {
        id: 17,
    },
    {
        id: 16,
    },
    {
        id: 15,
    },
    {
        id: 14,
    },
    {
        id: 13,
    },
    {
        id: 12,
    },
    {
        id: 11,
    }

]
class ToothList extends React.Component {
    render() {
        const toothTemplate = this.props.data.map(function (item, index) {
            return (
                <div className="tooth-item" key={index}>
                    <IconTooth/>
                    <span>
                        {item.id}
                    </span>
                </div>
            )
        })

        return (
            <div className="tooth-list">
                {toothTemplate}
            </div>
        )
    }
}

export default class ToothWrap extends React.Component {
    render() {
        return (
            <ToothList data={toothData}/>
        )
    }
}