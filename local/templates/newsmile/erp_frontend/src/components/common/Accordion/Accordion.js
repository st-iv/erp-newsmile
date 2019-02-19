import React from 'react'
import './Accordion.scss'

const AccordionData = [
    {
        id: 1,
        text: 'description1'
    },
    {
        id: 2,
        text: 'description2'
    },
    {
        id: 3,
        text: 'description3'
    },
    {
        id: 4,
        text: 'descripdwtion'
    }
]

class AccordionList extends React.Component {
    static defaultProps = {
        variant: 'success',
    }

    render() {
        const {variant, text} = this.props
        const accordionTemplate = this.props.data.map(function(item, index){
            return (
                <div className={`accordion-item accordion-item--variant-${variant}`}>
                    <span className="accordion-item__text">
                    {item.text}
                    </span>
                </div>
            )
        })

        return (
            <div className="accordion-list">
                {accordionTemplate}
            </div>
        )
    }
}

export default class AccordionWrap extends React.Component {
    render() {
        return (
            <AccordionList data={AccordionData}/>
        )
    }
}

