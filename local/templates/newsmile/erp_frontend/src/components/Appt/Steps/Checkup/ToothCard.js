import React from 'react'
import './ToothCard.scss'
import {IconCheck, IconTooth} from "../../../common/Icons";
import Tabs from '../../../common/Tabs/Tabs'
import ToothRoll from "./ToothRoll";
import Button from "../../../common/Button/Button";

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
    state = {
        isOpen: false,
        // tooth states
        r: false,
    }

    handleReadMoreClick = (e) => {
        e.preventDefault()
        this.setState({
            isOpen: !this.state.isOpen
        })
    }

    // tooth functions
    rSet = () => {
        console.log('clickkk')
        this.setState({
            r: true
        })
    }

    render() {
        const {isOpen, r} = this.state
        const {id} = this.props.data
        let classNames = 'tooth-item'

        // add class on click

        if (r) {
            classNames += ' '+classNames+'--r'
        }
        return (
            <React.Fragment>
                <div onClick={this.handleReadMoreClick} className={classNames}>
                    <IconTooth/>
                    <span>{id}</span>
                </div>
                {
                    isOpen &&
                    /*<div className="tooth-roll__wrap">
                        <div className="tooth-roll">
                            <div className="row">
                                <div className="" onClick={this.rSet}>
                                <IconTooth fill="#ff4261" />
                                </div>
                                <IconTooth fill="#f4f4f4"/>
                                <IconTooth fill="#8fca00"/>
                            </div>
                            <div className="row row-md">
                                <div className="column">
                                    <IconTooth fill="#ff4261"/>
                                    <IconTooth fill="#ff4261"/>
                                </div>
                                <div className="tooth-roll__select">
                                    <IconTooth/>
                                </div>
                                <div className="column">
                                    <IconTooth fill="#f9d905"/>
                                    <IconTooth fill="#f9d905"/>
                                </div>

                            </div>
                            <div className="row">
                                <IconTooth fill="#ff4261"/>
                                <IconTooth fill="#f9d905"/>
                            </div>
                        </div>
                    </div>*/
                    <div className="tooth-roll__wrap">
                    <ToothRoll />
                    </div>
                }
            </React.Fragment>
        )
    }
}

class ToothList extends React.Component {
    render() {
        const {data} = this.props;

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
                            <div className="tooth-item__wrap" key={`row-item-${k}`}>
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
                <Tabs/>
                <ToothRoll />
                <ToothList data={toothData}/>
                <div className="check-group">
                    <div className="check-group__title-group">
                        <IconCheck width="10" height="7"/>
                        <span className="check-group__title">Отметить здоровыми</span>
                    </div>
                    <div className="check-group__btns">
                        <Button variant="outline--secondary" text="все" size="sm"/>
                        <Button variant="outline--secondary" text="в.ч." size="sm"/>
                        <Button variant="outline--secondary" text="н.ч." size="sm"/>
                    </div>
                    <Button variant="outline--secondary" text="Сбросить" action="reset" size="sm"/>
                </div>
            </div>
        )
    }
}