import React from 'react'
import './ToothCard.scss'
import {IconTooth} from "../../../common/Icons";
import Tabs from '../../../common/Tabs/Tabs'
import ToothRoll from "./ToothRoll";

const toothData = [
    //1row topleft
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
        id: 48,
    },
    {
        id: 47,
    },
    {
        id: 46,
    },
    {
        id: 45,
    },
    {
        id: 44,
    },
    {
        id: 43,
    },
    {
        id: 42,
    },
    {
        id: 41,
    },
    // 4row bottom-right
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
]

class ToothItem extends React.Component {
    render() {
        const {id} = this.props.data;

        return (
            <React.Fragment>
                <IconTooth/>
                <span>{id}</span>
            </React.Fragment>
        )
    }
}
class ToothList extends React.Component {
    render() {
        const { data } = this.props;

        const rows = data.reduce(
            (prev, el, i) => {
                const subIdx = Math.floor(i / 8);
                prev[subIdx] = [...(prev[subIdx] || []), el];
                return prev;
            },
            []
        );

        return (
            <div className="tooth-list">
                {rows.map((row, i) => (
                    <div key={`row-${i}`} className="tooth-row">
                        {row.map((item, k) => (
                            <div className="tooth-item" key={`row-item-${k}`}>
                                <ToothItem key={item.id} data={item}/>
                            </div>
                        ))}
                    </div>
                ))}
            </div>
        );
    }
}

export default class ToothWrap extends React.Component {
    render() {
        return (
            <div className="tooth-wrap">
                <Tabs />
                <ToothList data={toothData}/>
                <ToothRoll />
            </div>
        )
    }
}