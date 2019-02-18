import React from 'react'
import './Accordion.scss'

export default class AccordionItem extends React.Component {
    static defaultProps = {
        variant: 'success',
        steps: 0,
        title: "[recipe]"
    }

    render() {
        const {variant} = this.props
        return (
            <div className={`accordion-item accordion-item--variant-${variant}`}>
                <span className="accordion-item__text">
                    Перенесенные и сопутствующие заболевания
                </span>
            </div>
        )
    }
}