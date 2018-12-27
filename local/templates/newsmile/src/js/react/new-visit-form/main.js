import React from 'react'
import PropTypes from 'prop-types'
import ColoredSelect from '../colored-select'
import TextInput from './text-input'
import RadioInput from './radio-input'
import PhoneInput from "./phone-input";
import Select from "./select";
import Scrollbars from '../scrollbars'
import PatientsTable from './patients-table'

class NewVisitForm extends React.Component
{
    static propTypes = {
        timeStart: PropTypes.string.isRequired,
        timeEnd: PropTypes.string.isRequired,
        date: PropTypes.string.isRequired,
        chairId: PropTypes.number.isRequired,

        onSuccessSubmit: PropTypes.func,
        onClose: PropTypes.func
    };

    state = {
        doctors: [],
        doctor: {},
        fields: null,
        additionalPhonesCount: 0,
        addFormScrollHeight: 0,
        values: {}
    };

    formRef = React.createRef();

    maskedInputsMaskChars = {
        personalBirthday: '_',
        personalPhone: '-',
        additionalPhones: '-'
    };

    patientCardFields = [
        'name', 'lastName', 'secondName', 'number', 'personalBirthday', 'personalGender', 'parents',
        'personalPhone', 'additionalPhones', 'personalCity', 'personalStreet', 'personalHome', 'personalHousing',
        'personalApartment', 'source'
    ];

    patientsInitialCount = 200;

    constructor(props)
    {
        super(props);
        this.loadData();

        this.handleInputChange = this.handleInputChange.bind(this);
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
                    {!!this.state.fields && this.renderAddPatientForm()}
                    {!!this.state.fields && !!this.state.patients && (
                        <PatientsTable initialPatients={this.state.patients}
                                       fields={this.state.fields}
                                       filter={this.state.values}
                                       filterBy={['number', 'name', 'lastName', 'secondName']}
                                       allDataLoaded={this.state.allPatientsLoaded}
                        />
                    )}
                </main>
            </div>
        );
    }

    renderAddPatientForm()
    {
        return (
            <form className="new-visit__form form" onSubmit={this.handleSubmit.bind(this)} ref={this.formRef}>
                <div className="form__fields-wrapper">
                    <Scrollbars>
                        <div className="form__scroll-area">

                            <div className="form__block">
                                <TextInput {...this.addControlledMixin(this.state.fields.number)}/>
                            </div>

                            <div className="form__block">
                                <TextInput {...this.addControlledMixin(this.state.fields.lastName)}/>
                                <TextInput {...this.addControlledMixin(this.state.fields.name)}/>
                                <TextInput {...this.addControlledMixin(this.state.fields.secondName)}/>
                            </div>

                            <div className="form__block">
                                <TextInput {...this.addControlledMixin(this.state.fields.personalBirthday)} mask="99.99.9999" maskChar={this.maskedInputsMaskChars.personalBirthday}/>
                                <RadioInput {...this.addControlledMixin(this.state.fields.personalGender)}/>
                            </div>

                            <div className="form__block form__block--phone">
                                <div className="form__fields form__fields--phone">
                                    <TextInput {...this.addControlledMixin(this.state.fields.parents)}/>
                                    <PhoneInput {...this.addControlledMixin(this.state.fields.personalPhone)}
                                                additionalInputsName="additionalPhones"
                                                additionalInputsCount={this.state.additionalPhonesCount}
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
                                <TextInput {...this.addControlledMixin(this.state.fields.personalCity)}/>
                                <TextInput {...this.addControlledMixin(this.state.fields.personalStreet)}/>
                            </div>

                            <div className="form__block">
                                <TextInput {...this.addControlledMixin(this.state.fields.personalHome)}/>
                                <TextInput {...this.addControlledMixin(this.state.fields.personalHousing)}/>
                                <TextInput {...this.addControlledMixin(this.state.fields.personalApartment)}/>
                            </div>

                            <div className="form__block form__block--select">
                                <Select {...this.addControlledMixin(this.state.fields.source)} isMulti hideSelectedOptions={false} placeholder=""/>
                            </div>
                        </div>
                    </Scrollbars>
                </div>

                <div className="form__btns-wrapper">
                    <button type="submit" className="form__btn form__btn--submit">Создать</button>
                    <input type="reset" className="form__btn form__btn--clear" value="Отмена" onClick={this.props.onClose}/>
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
            fields: this.patientCardFields,
            patientsCount: this.patientsInitialCount,
            patientsSelect: PatientsTable.patientsFields
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

            newState.values = {};

            General.forEachObj(result.fields, field =>
            {
                newState.values[field.name] = ((field.defaultValue === undefined) ? '' : field.defaultValue);
                delete field.defaultValue;
            });

            newState.values.personalPhone = [];

            newState.fields = result.fields;
            newState.patients = result.patients;
            newState.allPatientsLoaded = (result.patientsTotalCount === newState.patients.length);

            this.setState(newState);
        });//

        command.exec();
    }

    addPhoneInput(e)
    {
        this.setState({
            additionalPhonesCount: this.state.additionalPhonesCount + 1
        });

        e.preventDefault();
    }

    handleSubmit(e)
    {
        let data = General.clone(this.state.values);
        data.additionalPhones = data.personalPhone.slice(1);
        data.personalPhone = data.personalPhone[0];

        if(data.personalBirthday)
        {
            data.personalBirthday = General.Date.formatDate(data.personalBirthday, 'YYYY-MM-DD', 'DD.MM.YYYY');
        }

        let command = new ServerCommand('patient-card/add', data, response =>
        {
            this.props.onSuccessSubmit && this.props.onSuccessSubmit();
        });

        command.exec();//

        e.preventDefault();
    }

    getControlledMixin(fieldName)
    {
        return {
            value: ((this.state.values[fieldName] === undefined) ? '' : this.state.values[fieldName]),
            onChange: this.handleInputChange.bind(this, fieldName)
        }
    }

    addControlledMixin(field)
    {
        return Object.assign({}, field, this.getControlledMixin(field.name));
    }

    handleInputChange(fieldName, value)
    {
        let newValues = General.clone(this.state.values);
        newValues[fieldName] = value;

        this.setState({
            values: newValues
        });
    }
}

export default NewVisitForm