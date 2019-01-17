import React from 'react'
import PropTypes from 'prop-types'
import ColoredSelect from '../colored-select'
import TextInput from './text-input'
import RadioInput from './radio-input'
import AdditionalTextInput from "./additional-text-input";
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
        freezeTimeout: PropTypes.number,

        onSuccessSubmit: PropTypes.func,
        onClose: PropTypes.func
    };

    static defaultProps = {
        freezeTimeout: 0
    };

    state = {
        doctors: [],
        doctor: {},
        fields: null,
        addFormScrollHeight: 0,
        values: {},
        selectedPatient: null
    };

    formRef = React.createRef();

    patientCardFields = [
        'id', 'name', 'lastName', 'secondName', 'number', 'personalBirthday', 'personalGender', 'parents',
        'personalPhone', 'additionalPhone', 'personalCity', 'personalStreet', 'personalHome', 'personalHousing',
        'personalApartment', 'source'
    ];

    patientNotEditableFields = ['number'];

    patientsInitialCount = 200;
    valuesSnapshot = this.state.values;

    constructor(props)
    {
        super(props);
        this.loadData(this.props.freezeTimeout);

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
        const formClass = 'new-visit__form form' + ((this.state.selectedPatient === null) ? '' : ' selected-patient');
        const additionalPhoneProps = this.addGeneralInputMixin(this.state.fields.additionalPhone);

        return (
            <form className={formClass} onSubmit={this.handleSubmit.bind(this)} ref={this.formRef}>
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
                                    <span>{this.state.selectedPatient.lastName + ' ' + this.state.selectedPatient.name + ' ' + this.state.selectedPatient.secondName}</span>
                                </div>
                                <div className="selected-patient__cancel" onClick={this.handlePatientDeselect.bind(this)}>
                                    Отменить выбор
                                </div>
                            </div>
                        )
                    }
                </div>

                <div className="form__fields-wrapper">
                    <Scrollbars verticalScrollSide="left">
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
                                <TextInput {...this.addGeneralInputMixin(this.state.fields.personalBirthday)} mask="99.99.9999" maskChar="_"/>
                                <RadioInput {...this.addGeneralInputMixin(this.state.fields.personalGender)}/>
                            </div>

                            <div className="form__fields-group">
                                <div className="form__block">
                                    <TextInput {...this.addGeneralInputMixin(this.state.fields.parents)}/>
                                </div>

                                <div className="form__block">
                                    <TextInput mask="+7 (999) 999 99 99" maskChar="-" {...this.addGeneralInputMixin(this.state.fields.personalPhone)}/>

                                    <AdditionalTextInput buttonTitle="Добавить телефон"
                                                         updateKey={this.state.selectedPatient}
                                                         mask="+7 (999) 999 99 99"
                                                         maskChar="-"
                                                         {...additionalPhoneProps} />
                                </div>
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
                    <button type="submit" className="form__btn form__btn--submit">
                        {((this.state.selectedPatient === null) || this.isPatientChanged()) ? 'Сохранить и записать' : 'Записать'}
                    </button>

                    {(this.state.selectedPatient !== null) && this.isPatientChanged() && (
                        <div className="form__data-changed-note">
                            Внесены изменения в карту пациента
                        </div>
                    )}
                </div>
            </form>
        );
    }


    loadData(minimalLoadTime = 0)
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

        let loadStartTs = Date.now();

        let command = new ServerCommand('visit/get-add-form-info', data, result =>
        {
            let needWaitTime = minimalLoadTime - (Date.now() - loadStartTs);

            if(needWaitTime > 0)
            {
                setTimeout(this.processData.bind(this, result), needWaitTime);
            }
            else
            {
                this.processData(result);
            }
        });

        command.exec();
    }

    processData(data)
    {
        let newState = {};

        if(data.doctors)
        {
            newState.doctors = data.doctors.map(doctor =>
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

        newState.values = this.getDefaultValues(data.fields);

        newState.fields = data.fields;
        newState.patients = data.patients;
        newState.allPatientsLoaded = (data.patientsTotalCount === newState.patients.length);

        this.setState(newState);
    }

    getGeneralInputMixin(field)
    {
        return {
            value: ((this.state.values[field.name] === undefined) ? '' : this.state.values[field.name]),
            onChange: this.handleInputChange.bind(this, field),
            disabled: (this.state.selectedPatient !== null && (this.patientNotEditableFields.indexOf(field.name) !== -1))
        }
    }

    addGeneralInputMixin(field)
    {
        return Object.assign({}, field, this.getGeneralInputMixin(field));
    }

    savePatient(id = null)
    {
        return new Promise(resolve =>
        {
            let data = General.clone(this.state.values);
            data.source = data.source ? data.source.map(source => (source.value)) : [];

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
            if(this.isPatientChanged())
            {
                this.savePatient(this.state.selectedPatient.id).then(() => this.addVisit(this.state.selectedPatient.id));
            }
            else
            {
                this.addVisit(this.state.selectedPatient.id);
            }
        }

        e.preventDefault();
    }

    handleInputChange(field, value)
    {
        let newValues = General.clone(this.state.values);
        newValues[field.name] = value;

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
            let field = this.state.fields[fieldCode];

            if(!field) return;

            if(fieldCode === 'personalBirthday')
            {
                fieldValue = General.Date.formatDate(fieldValue, 'DD.MM.YYYY');
            }

            if((field.type === 'multipleenum') && Array.isArray(fieldValue))
            {
                let variantsMap = {};
                field.variants.forEach(variant =>
                {
                    variantsMap[variant.code] = variant.title;
                });

                fieldValue = fieldValue.map(value => ({
                    label: variantsMap[value],
                    value
                }));
            }

            if((field.type === 'phone') && fieldValue)
            {
                fieldValue = General.formatPhone(fieldValue);
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

    isPatientChanged()
    {
        return !General.isEqual(this.state.values, this.valuesSnapshot);
    }
}
