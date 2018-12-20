import React from 'react'
import ReactDOM from 'react-dom'
import PropTypes from 'prop-types'
import ColoredSelect from '../colored-select'
import TextInput from './text-input'
import RadioInput from './radio-input'
import PhoneInput from "./phone-input";
import Select from "./select";
import Scrollbars from '../scrollbars'

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
        additionalPhonesCount: 0,
        addFormScrollHeight: 0
    };

    //formWrapperRef = React.createRef();

    constructor(props)
    {
        super(props);
        this.loadData();
    }

    render()
    {
        return (
            <div className="new-visit">
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
        return (//this.setState({addFormScrollHeight: ref.clientHeight})   /*ref={ref => this.setState({addFormScrollHeight: ref.clientHeight})}*/
            <form className="new-visit__form form">
                <div className="form__fields-wrapper">
                    {this.renderAddFormFields()}
                </div>

                <div className="form__btns-wrapper">
                    <button type="submit" className="form__btn form__btn--submit">Создать</button>
                    <input type="reset" className="form__btn form__btn--clear" value="Отмена"/>
                </div>
            </form>
        );
    }

    renderAddFormFields()
    {
        return (
            <Scrollbars>
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
                                        additionalInputsName="ADDITIONAL_PHONES"
                                        additionalInputsCount={this.state.additionalPhonesCount}
                                        required
                            />
                        </div>

                        <button className="form__add-field-btn" onClick={this.addPhoneInput.bind(this)}>
                                    <span className="form__btn-label">
                                        <svg className="form__btn-icon" id="Layer_1" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg"
                                             viewBox="0 0 19.16 19.16">
                                            <title>plus</title>
                                            <line x1="2" y1="2" x2="17.16" y2="17.16" fill="none" strokeLinecap="round" strokeMiterlimit="10"
                                                  strokeWidth="2"/>
                                            <line x1="17.16" y1="2" x2="2" y2="17.16" fill="none" strokeLinecap="round" strokeMiterlimit="10" strokeWidth="2"/>
                                        </svg>
                                        Добавить телефон
                                    </span>
                        </button>
                    </div>

                    <div className="form__block">
                        <TextInput {...this.state.fields.PERSONAL_CITY}/>
                        <TextInput {...this.state.fields.PERSONAL_STREET}/>
                    </div>

                    <div className="form__block">
                        <TextInput {...this.state.fields.PERSONAL_HOME}/>
                        <TextInput {...this.state.fields.PERSONAL_HOUSING}/>
                        <TextInput {...this.state.fields.PERSONAL_APARTMENT}/>
                    </div>

                    <div className="form__block form__block--select">
                        <Select {...this.state.fields.SOURCE} isMulti hideSelectedOptions={false} placeholder=""/>
                    </div>
                </div>
            </Scrollbars>
        );
    }

    componentDidMount()
    {
        /*this.setState({
            addFormScrollHeight: this.formWrapperRef.current.clientHeight
        })*///
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

    addPhoneInput(e)
    {
        this.setState({
            additionalPhonesCount: this.state.additionalPhonesCount + 1
        });

        e.preventDefault();
    }
}

export default NewVisitForm