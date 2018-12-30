import React from 'react'
import PropTypes from 'prop-types'
import ColoredSelect from '../colored-select'
import TextInput from './text-input'
import RadioInput from './radio-input'
import PhoneInput from "./phone-input";
import Select from "./select";
import Scrollbars from '../scrollbars'
import PatientsTable from './patients-table'

export default class NewVisitForm extends React.Component
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
        values: {},
        selectedPatient: null
    };

    formRef = React.createRef();

    maskedInputsMaskChars = {
        personalBirthday: '_',
        personalPhone: '-',
        additionalPhones: '-'
    };

    patientCardFields = [
        'id', 'name', 'lastName', 'secondName', 'number', 'personalBirthday', 'personalGender', 'parents',
        'personalPhone', 'additionalPhones', 'personalCity', 'personalStreet', 'personalHome', 'personalHousing',
        'personalApartment', 'source'
    ];

    patientNotEditableFields = ['number'];

    patientsInitialCount = 200;
    valuesSnapshot = this.state.values;

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
                                       filter={(this.state.selectedPatient === null) ? this.state.values : {}}
                                       filterBy={['number', 'name', 'lastName', 'secondName']}
                                       allDataLoaded={this.state.allPatientsLoaded}
                                       selectedPatientId={this.state.selectedPatient ? this.state.selectedPatient.id : null}
                                       onChange={this.handlePatientChange.bind(this)}
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
                <div className="form__top-block">
                    {(this.state.selectedPatient === null)
                        ? (
                            <div className="form__selected-patient">
                                Выберите пациента<br/>или создайте нового заполнив форму
                            </div>
                        )
                        : (
                            <div className="form__selected-patient">
                                <div className="selected-patient__text">Выбран пациент</div>
                                <div className="selected-patient__fio">
                                    {this.state.selectedPatient.lastName + ' ' + this.state.selectedPatient.name + ' ' + this.state.selectedPatient.secondName}
                                </div>
                                <div className="selected-patient__cancel" onClick={this.handlePatientDeselect.bind(this)}>
                                    Отменить выбор
                                </div>
                            </div>
                        )
                    }
                </div>

                <div className="form__fields-wrapper">
                    <Scrollbars>
                        <div className="form__scroll-area">

                            <div className="form__block">
                                <TextInput {...this.addGeneralInputMixin(this.state.fields.number)}/>
                            </div>

                            <div className="form__block">
                                <TextInput {...this.addGeneralInputMixin(this.state.fields.lastName)}/>
                                <TextInput {...this.addGeneralInputMixin(this.state.fields.name)}/>
                                <TextInput {...this.addGeneralInputMixin(this.state.fields.secondName)}/>
                            </div>

                            <div className="form__block">
                                <TextInput {...this.addGeneralInputMixin(this.state.fields.personalBirthday)} mask="99.99.9999" maskChar={this.maskedInputsMaskChars.personalBirthday}/>
                                <RadioInput {...this.addGeneralInputMixin(this.state.fields.personalGender)}/>
                            </div>

                            <div className="form__block form__block--phone">
                                <div className="form__fields form__fields--phone">
                                    <TextInput {...this.addGeneralInputMixin(this.state.fields.parents)}/>
                                    <PhoneInput {...this.addGeneralInputMixin(this.state.fields.personalPhone)}
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
                                <TextInput {...this.addGeneralInputMixin(this.state.fields.personalCity)}/>
                                <TextInput {...this.addGeneralInputMixin(this.state.fields.personalStreet)}/>
                            </div>

                            <div className="form__block">
                                <TextInput {...this.addGeneralInputMixin(this.state.fields.personalHome)}/>
                                <TextInput {...this.addGeneralInputMixin(this.state.fields.personalHousing)}/>
                                <TextInput {...this.addGeneralInputMixin(this.state.fields.personalApartment)}/>
                            </div>

                            <div className="form__block form__block--select">
                                <Select {...this.addGeneralInputMixin(this.state.fields.source)} isMulti hideSelectedOptions={false} placeholder=""/>
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
            patientsSelect: this.patientCardFields
        };

        let command = new ServerCommand('visit/get-add-form-info', data, result =>
        {
            let newState = {};

            if(result.doctors)
            {
                newState.doctors = result.doctors.map(doctor =>
                {
                    let doctorOption = {
                        label: doctor.fio,
                        color: doctor.color,
                        value: doctor.code,
                    };

                    if(doctor.isCurrent)
                    {
                        newState.doctor = doctorOption;
                    }

                    return doctorOption;
                });
            }

            newState.values = this.getDefaultValues(result.fields);

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

    getGeneralInputMixin(fieldName)
    {
        return {
            value: ((this.state.values[fieldName] === undefined) ? '' : this.state.values[fieldName]),
            onChange: this.handleInputChange.bind(this, fieldName),
            disabled: (this.state.selectedPatient !== null && (this.patientNotEditableFields.indexOf(fieldName) !== -1))
        }
    }

    addGeneralInputMixin(field)
    {
        return Object.assign({}, field, this.getGeneralInputMixin(field.name));
    }

    savePatient(id = null)
    {
        return new Promise(resolve =>
        {
            let data = General.clone(this.state.values);
            data.additionalPhones = data.personalPhone.slice(1);
            data.personalPhone = data.personalPhone[0];

            if(id)
            {
                data.id = id
            }

            if(data.personalBirthday)
            {
                data.personalBirthday = General.Date.formatDate(data.personalBirthday, 'YYYY-MM-DD', 'DD.MM.YYYY');
            }

            let command = new ServerCommand('patient-card/' + (id ? 'edit' : 'add'), data, response =>
            {
                this.props.onSuccessSubmit && this.props.onSuccessSubmit();
                resolve(response ? response.primary.id : id);
            });

            command.exec();
        });
    }

    addVisit(patientId)
    {
        let command = new ServerCommand('visit/add', {
            timeStart: this.props.date + ' ' + this.props.timeStart,
            timeEnd: this.props.date + ' ' + this.props.timeEnd,
            workChairId: this.props.chairId,
            patientId: patientId,
            doctorId: this.state.doctor.value
        });

        command.exec().then(() => this.props.onSuccessSubmit(), err => {throw err});
    }

    getDefaultValues(fields = null)
    {
        if(fields === null)
        {
            fields = this.state.fields;
        }

        let values = {};

        General.forEachObj(fields, field =>
        {
            values[field.name] = ((field.defaultValue === undefined) ? '' : field.defaultValue);
            delete field.defaultValue;
        });

        values.personalPhone = [];

        return values;
    }

    handleSubmit(e)
    {
        if(this.state.selectedPatient === null)
        {
            this.savePatient().then(patientId => this.addVisit(patientId));
        }
        else
        {
            if(General.isEqualObjects(this.state.values, this.valuesSnapshot))
            {
                this.addVisit(this.state.selectedPatient.id);
            }
            else
            {
                this.savePatient(this.state.selectedPatient.id).then(() => this.addVisit(this.state.selectedPatient.id));
            }
        }

        e.preventDefault();
    }

    handleInputChange(fieldName, value)
    {
        let newValues = General.clone(this.state.values);
        newValues[fieldName] = value;

        this.setState({
            values: newValues
        });
    }

    handlePatientChange(patient)
    {
        let newState = {
            selectedPatient: patient,
            values: {}
        };

        patient = General.clone(patient);

        General.forEachObj(patient, (fieldValue, fieldCode) =>
        {
            if(fieldCode === 'personalBirthday')
            {
                fieldValue = General.Date.formatDate(fieldValue, 'DD.MM.YYYY');
            }
            else if(fieldCode === 'personalPhone')
            {
                fieldValue = [fieldValue];
            }

            newState.values[fieldCode] = fieldValue;
        });

        this.valuesSnapshot = Object.assign({}, this.state.values, newState.values);

        this.setState(newState);
    }

    handlePatientDeselect()
    {
        this.setState({
            selectedPatient: null,
            values: this.getDefaultValues()
        });
    }
}