import React from 'react'
import './Accordion.scss'

export default class AccordionItem extends React.Component {
    static defaultProps = {
        variant: 'success',
    }

    render() {
        const {variant, text} = this.props
        return (
            {this.props.accordionData.map((item, index) =>
                <div className={`accordion-item accordion-item--variant-${variant}`}>
                <span className="accordion-item__text">
                    {item.text}
                </span>
                </div>
            )}

    )
    }
}