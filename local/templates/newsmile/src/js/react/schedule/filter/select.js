import React from 'react'
import {default as ReactSelect, components} from 'react-select'

class Select extends React.Component
{
    state = {
        isOpened: false
    };

    render()
    {
        const defaults = {
            isSearchable: false
        };

        let props = Object.assign({}, defaults, this.props);
        delete props.className;

        props.menuIsOpen = this.state.isOpened;
        props.onMenuOpen = () => this.setState({isOpened: true});
        props.onMenuClose = () => this.setState({isOpened: false});

        props.classNamePrefix = 'filter-select';
        props.className = this.props.className + ' filter-select' + (this.state.isOpened ? ' filter-select--opened' : '');

        return (
            <ReactSelect {...props}/>
        );
    }
}

export default Select