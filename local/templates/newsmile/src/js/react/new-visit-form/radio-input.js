import React from 'react'
import PropTypes from 'prop-types'

class RadioInput extends React.PureComponent
{
    static propTypes = {
        name: PropTypes.string.isRequired,
        title: PropTypes.string.isRequired,
        defaultValue: PropTypes.string,
        value: PropTypes.string,
        required: PropTypes.bool,
        variants: PropTypes.arrayOf(PropTypes.shape({
            code: PropTypes.string,
            title: PropTypes.string
        })).isRequired,
        onChange: PropTypes.func.isRequired
    };

    render()
    {
        return (
            <label className="form__wrapper form__wrapper--radio">
                <span className="form__label form__label--focus">{this.props.title}</span>

                {this.props.variants.map(variant =>
                {
                    let id = General.uniqueId(this.props.name);
                    return  [
                        <input className="form__radio visually-hidden"
                               type="radio"
                               id={id}
                               name={this.props.name}
                               value={variant.code}
                               defaultChecked={variant.code === this.props.defaultValue}
                               key={'input_' + variant.code}
                               onChange={e => this.props.onChange(e.target.value)}
                               disabled={this.props.disabled}
                        />,
                        <label className="form__radio-label" htmlFor={id} key={'label' + variant.code}>
                            {General.ucfirst(variant.title)}
                        </label>
                    ];
                })}
            </label>
        );
    }
}

export default RadioInput