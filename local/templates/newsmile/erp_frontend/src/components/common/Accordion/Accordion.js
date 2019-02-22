import React from 'react'
import PropTypes from 'prop-types'
import './Accordion.scss'

export default class AccordionList extends React.Component {
    static propTypes = {
        data: PropTypes.array.isRequired
    }

    render() {
        const accordionTemplate = this.props.data.map(function(item, index){
            return (
                <AccordionItem key={item.id} data={item}/>
            )
        })

        return (
            <div className="accordion-list">
                {accordionTemplate}
            </div>
        )
    }
}

class AccordionItem extends React.Component {
    static defaultProps = {
        variant: 'success',
    }

    static propTypes = {
        data: PropTypes.shape({
            text: PropTypes.string.isRequired,
            content: PropTypes.string.isRequired
        })
    }

    render() {
        const {text, content} = this.props.data
        const {variant} = this.props
        return (
            <React.Fragment>
                <div className={`accordion-item accordion-item--variant-${variant}`}>
                    <span className="accordion-item__text">
                        {text}
                    </span>
                </div>
                <p className="accordion-item__content">
                    {content}
                </p>
            </React.Fragment>
        )
    }
}

