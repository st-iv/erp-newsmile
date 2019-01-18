import React from 'react'
import PropTypes from 'prop-types'
import ServerCommand from 'js/server/server-command'
import Queue from "../../server/queue";
import ResultCategory from './result-category'

export default class Search extends React.Component
{
    state = {
        popupOpened: false
    };

    static propTypes = {
        useLanguageGuess: PropTypes.bool,
        minQueryLength: PropTypes.number,
        topCount: PropTypes.number
    };

    static defaultProps = {
        useLanguageGuess: false,
        minQueryLength: 3,
        topCount: 200
    };

    commandsQueue = new Queue(result => this.setState({result}));

    constructor(props)
    {
        super(props);
        this.handleKeyUp = this.handleKeyUp.bind(this);
    }

    render()
    {
        return (
            <div className={'header_search_wrapper' + (this.state.popupOpened ? ' search-opened' : '')}>
                {this.state.popupOpened && this.renderPopup()}

                <form className="header_search_form">
                    <div className="search_sign"/>
                    <input type="text"
                           name="q"
                           className="search_str"
                           placeholder="Искать пациента, врача, документ"
                           autoComplete="off"
                           onChange={this.handleInputChange.bind(this)}
                    />
                </form>
            </div>
        );
    }

    renderPopup()
    {
        return (
            <div className="search_content">
                <div className="search_fake_header">
                    <div className="search_adv_button">Расширенный поиск</div>
                </div>

                <div className="search_result">

                    {!!this.state.result && General.mapObj(this.state.result, (subcategories, categoryCode) =>
                    {
                        return (
                            <ResultCategory code={categoryCode} subcategories={subcategories} key={categoryCode}/>
                        )
                    })}

                </div>
            </div>
        );
    }

    search(query)
    {
        const command = new ServerCommand('search/search', {
            query,
            useLanguageGuess: this.props.useLanguageGuess,
            categories: ['patientcard', 'doctor'],
            select: {
                patientcard: ['name', 'lastName', 'secondName', 'personalBirthday'],
                doctor: ['name', 'lastName', 'secondName', 'personalBirthday', 'color'],
            }
        });

        this.commandsQueue.push(command);
    }

    componentDidMount()
    {
        $(document).on('keyup', this.handleKeyUp);
    }

    componentWillUnmount()
    {
        $(document).off('keyup', this.handleKeyUp)
    }

    handleInputChange(e)
    {
        if(e.target.value.length >= this.props.minQueryLength)
        {
            if(!this.state.popupOpened)
            {
                this.setState({popupOpened: true});
            }

            this.search(e.target.value);
        }
    }

    handleKeyUp(e)
    {
        if(e.which === 27)
        {
            this.setState({popupOpened: false});
        }
    }
}