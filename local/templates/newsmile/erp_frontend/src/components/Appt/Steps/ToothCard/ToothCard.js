import React from 'react'
import './ToothCard.scss'
import {IconTooth} from "../../../common/Icons";
import Tabs from '../../../common/Tabs/Tabs'

const toothData = [
    //1row topleft
    {
        id: 11,
    },
    {
        id: 12,
    },
    {
        id: 13,
    },
    {
        id: 14,
    },
    {
        id: 15,
    },
    {
        id: 16,
    },
    {
        id: 17,
    },
    {
        id: 18,
    },
    // 2row topright
    {
        id: 21,
    },
    {
        id: 22,
    },
    {
        id: 23,
    },
    {
        id: 24,
    },
    {
        id: 25,
    },
    {
        id: 26,
    },
    {
        id: 27,
    },
    {
        id: 28,
    },
    // 3row bottom-left
    {
        id: 31,
    },
    {
        id: 32,
    },
    {
        id: 33,
    },
    {
        id: 34,
    },
    {
        id: 35,
    },
    {
        id: 36,
    },
    {
        id: 37,
    },
    {
        id: 38,
    },
    // 4row bottom-right
    {
        id: 41,
    },
    {
        id: 42,
    },
    {
        id: 43,
    },
    {
        id: 44,
    },
    {
        id: 45,
    },
    {
        id: 46,
    },
    {
        id: 47,
    },
    {
        id: 48,
    },

]
class ToothList extends React.Component {
    render() {
        const toothTemplate = this.props.data.map(function (item, index) {
            return (
        <React.Fragment>
                <div className="tooth-item" key={index}>
                    <IconTooth/>
                    <span>
                        {item.id}
                    </span>
                </div>
            {index%8===7 ? (<br/>) : null}
        </React.Fragment>
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
            <div>
                <Tabs />
                <ToothList data={toothData}/>
            </div>
        )
    }
}