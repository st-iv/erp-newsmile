import React from 'react'
import {default as ReactSelect} from 'react-select'
import GeneralHelper from 'js/helpers/general-helper.js';

class Select extends React.Component
{
    state = {
        isActive: false,
        isOpened: false
    };

    selectRef = React.createRef();

    render()
    {
        const options = this.props.variants.map(variant =>
        {
            return {
                label: variant.title,
                value: variant.code
            };
        });

        const labelClassName = 'form__label' + ((this.state.isActive || this.props.value) ? ' form__label--focus' : '');
        const uniqueId = GeneralHelper.uniqueId(this.props.name);
        const selectClassName = 'select' + (this.state.isOpened ? ' select--opened' : '');
        const wrapperClass = 'form__wrapper' + (this.props.disabled ? ' form__wrapper--disabled' : '');

        return (
            <label htmlFor={uniqueId} className={wrapperClass}>
                <span className={labelClassName} onClick={this.handleLabelClick.bind(this)}>
                    {this.props.title}
                 </span>
                <ReactSelect {...this.props}
                             options={options}
                             className={selectClassName}
                             classNamePrefix="select"

                             onFocus={() => this.setState({isActive: true})}
                             onBlur={this.handleBlur.bind(this)}
                             id={uniqueId}
                             ref={this.selectRef}

                             onMenuOpen={() => this.setState({isOpened: true})}
                             onMenuClose={() => this.setState({isOpened: false})}
                             menuIsOpen={this.state.isOpened}

                             value={this.props.value}
                             onChange={this.handleChange.bind(this)}
                />
            </label>
        );
    }

    handleLabelClick(e)
    {
        this.selectRef.current.focus();
    }

    handleBlur()
    {
        if(!this.props.value.length)
        {
            this.setState({isActive: false});
        }
    }

    handleChange(value)
    {
        if(this.props.onChange)
        {
            this.props.onChange(value);
        }
    }
}


export default Select