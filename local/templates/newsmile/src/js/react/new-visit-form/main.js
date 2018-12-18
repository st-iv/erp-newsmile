import React from 'react'
import ReactDOM from 'react-dom'
import PropTypes from 'prop-types'
import ColoredSelect from '../colored-select'
import TextInput from './text-input'
import RadioInput from './radio-input'
import PhoneInput from "./phone-input";

class NewVisitForm extends React.Component
{
    static propTypes = {
        timeStart: PropTypes.string.isRequired,
        timeEnd: PropTypes.string.isRequired,
        date: PropTypes.string.isRequired,
        chairId: PropTypes.number.isRequired
    };

    state = {
        doctors: [],
        doctor: {},
        fields: null,
        additionalPhonesCount: 0
    };

    addPhoneButtonRef = React.createRef();

    constructor(props)
    {
        super(props);
        this.loadData();
    }

    render()
    {
        return (
            <div>
                <header className="new-visit__header">
                    <h2 className="new-visit__title">Новый прием</h2>

                    <ColoredSelect options={this.state.doctors}
                                   className="doctor"
                                   placeholder="Врач"
                                   value={this.state.doctor}
                                   isDisabled={!this.state.doctors}
                                   onChange={doctor => this.setState({doctor})}
                    />

                    {/*<select name="doctor" id="new-visit-doctor" className="doctor">//
                        <option data-color="fff" disabled selected>Врач</option>
                        <option data-color="fff">Любой</option>
                        <option data-color="936de0">Виноградова И.Б.</option>
                        <option data-color="e36a52">Дмитриева Е.В.</option>
                        <option data-color="ffcb87">Груничев В.А.</option>
                        <option data-color="16aeed">Рудзит Ю.Ф.</option>
                        <option data-color="936de0">Виноградова И.Б.</option>
                        <option data-color="e36a52">Дмитриева Е.В.</option>
                        <option data-color="ffcb87">Груничев В.А.</option>
                    </select>*/}
                    <div className="new-visit__day-signal" style={{backgroundColor: '#ffb637'}}></div>
                    <span className="new-visit__day">{General.Date.formatDate(this.props.date, 'ru_weekday, DD ru_month_gen')}</span>
                    <span className="new-visit__time">{this.props.timeStart}</span>
                    <span className="new-visit__duration">Длительность приема &mdash; {General.Date.getDurationString(this.props.timeStart, this.props.timeEnd)}</span>
                </header>

                <main className="new-visit__content">
                    {this.state.fields && this.renderAddForm()}
                </main>
            </div>
        );
    }

    renderAddForm()
    {
        return (
            <form className="new-visit__form form">
                <div className="form__fields-wrapper">
                    <div className="form__scroll-area">

                        <div className="form__block">
                            <TextInput {...this.state.fields.NUMBER}/>
                        </div>

                        <div className="form__block">
                            <TextInput {...this.state.fields.LAST_NAME}/>
                            <TextInput {...this.state.fields.NAME}/>
                            <TextInput {...this.state.fields.SECOND_NAME}/>
                        </div>

                        <div className="form__block">
                            <TextInput {...this.state.fields.PERSONAL_BIRTHDAY} mask="99.99.9999"/>
                            <RadioInput {...this.state.fields.PERSONAL_GENDER}/>
                        </div>

                        <div className="form__block form__block--phone">
                            <div className="form__fields form__fields--phone">
                                <TextInput {...this.state.fields.PARENTS}/>
                                <PhoneInput {...this.state.fields.PERSONAL_PHONE}
                                            mask="+7 (999) 999 99 99"
                                            maskChar="-"
                                            alwaysShowMask
                                            labelClassName="form__label--phones"
                                            addButtonContainerRef={this.addPhoneButtonRef}
                                            additionalInputsName="ADDITIONAL_PHONES"
                                            required
                                />
                            </div>
                        </div>

                        <div className="form__add-field-btn-cont" ref={this.addPhoneButtonRef}/>

                    </div>
                </div>

                <div className="form__btns-wrapper">
                    <button type="submit" className="form__btn form__btn--submit">Создать</button>
                    <input type="reset" className="form__btn form__btn--clear" value="Отмена"/>
                </div>
            </form>
        );
    }

    loadData()
    {
        let data = {
            timeStart: this.props.timeStart,
            timeEnd: this.props.timeEnd,
            date: this.props.date,
            chairId: this.props.chairId,
        };

        let command = new ServerCommand('visit/get-add-form-info', data, result =>
        {
            let newState = {};

            if(result.doctors)
            {
                newState.doctors = [];

                for(let doctorId in result.doctors)
                {
                    let doctor = result.doctors[doctorId];
                    let doctorOption = {
                        label: doctor.fio,
                        color: doctor.color,
                        value: doctorId,
                    };

                    newState.doctors.push(doctorOption);

                    if(doctor.isCurrent)
                    {
                        newState.doctor = doctorOption;
                    }
                }
            }

            newState.fields = result.fields;

            this.setState(newState);
        });

        command.exec();
    }
}

export default NewVisitForm