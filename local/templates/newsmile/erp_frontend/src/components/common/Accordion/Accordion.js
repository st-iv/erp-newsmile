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
    state = {
        isOpen: false,
    }

    static defaultProps = {
        variant: 'success',
    }

    static propTypes = {
        data: PropTypes.shape({
            text: PropTypes.string.isRequired,
            content: PropTypes.string.isRequired
        })
    }

    handleReadMoreClick = (e) => { // добавили метод
        e.preventDefault()
        this.setState({
            isOpen: !this.state.isOpen
        })
    }

    render() {
        const {isOpen} = this.state
        const {text, content} = this.props.data
        const {variant} = this.props
        return (
            <React.Fragment>
                <div onClick={this.handleReadMoreClick} className={`accordion-item accordion-item--variant-${variant}`}>
                    <span className="accordion-item__text">
                        {text}
                    </span>
                </div>
                {
                    isOpen && <p className="accordion-item__content">{content}</p>
                }
            </React.Fragment>
        )
    }
}

