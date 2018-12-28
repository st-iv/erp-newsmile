import React from 'react'
import PropTypes from 'prop-types'

export default class PatientsTable extends React.PureComponent
{
    static propTypes = {
        initialPatients: PropTypes.array.isRequired,
        fields: PropTypes.object.isRequired,
        onChange: PropTypes.func,
        filter: PropTypes.object,
        filterBy: PropTypes.arrayOf(PropTypes.string),
        allDataLoaded: PropTypes.bool
    };

    state = {
        patients: this.preparePatientsData(this.props.initialPatients),
        selectedPatientId: null
    };

    static defaultProps = {
        filter: {},
        filterBy: [],
        allDataLoaded: false
    };

    allDataLoaded = this.props.allDataLoaded;

    static patientsFields = ['id', 'number', 'name', 'lastName', 'secondName', 'personalBirthday', 'personalPhone'];

    render()
    {
        const patients = this.state.patients.filter(this.filterPatients.bind(this));//

        return (
            <div className="new-visit__table-wrapper">
                <div className="table__scroll-area">
                    <div className="table__clearfix"/>
                    <table className="new-visit__table table">
                        <tbody>
                        <tr className="table__row table__row--first">
                            <td className="table__cell">{this.props.fields.number.title}</td>
                            <td className="table__cell">Пациент</td>
                            <td className="table__cell">{this.props.fields.personalBirthday.title}</td>
                            <td className="table__cell">{this.props.fields.personalPhone.title}</td>
                        </tr>
                        <tr className="table__row table__row--empty"/>

                            {patients.map(patient => (
                                <tr className={'table__row' + ((this.state.selectedPatientId === patient.id) ? ' table__row--active' : '')}
                                    key={patient.id}
                                    onClick={this.handleRowClick.bind(this, patient)}>
                                    <td className="table__cell">
                                        <div className="table__container"/>
                                        {patient.number}
                                    </td>
                                    <td className="table__cell">{patient.lastName} {patient.name} {patient.secondName}</td>
                                    <td className="table__cell">
                                        {patient.personalBirthday}
                                        <span className="table__age">{patient.age}</span>
                                    </td>
                                    <td className="table__cell">{patient.personalPhoneFormatted}</td>
                                </tr>
                            ))}

                        <tr className="table__row table__row--empty"/>
                        <tr className="table__row"/>
                        </tbody>
                    </table>
                </div>
            </div>
        );
    }

    componentDidUpdate(prevProps, prevState, snapshot)
    {
        let isEmptyPrevFilter = $.isEmptyObject(this.prepareFilter(prevProps.filter));
        let isEmptyCurFilter = $.isEmptyObject(this.prepareFilter());

        if(isEmptyPrevFilter && !isEmptyCurFilter && (!this.allDataLoaded))
        {
            this.loadData();
        }
        else if(!isEmptyPrevFilter && isEmptyCurFilter)
        {
            this.allDataLoaded = this.props.allDataLoaded;
            this.setState({
                patients: this.props.initialPatients
            });
        }
    }

    filterPatients(patient)
    {
        let verdict = true;

        General.forEachObj(this.prepareFilter(), (fieldValue, fieldCode) =>
        {
            fieldValue = String(fieldValue).toLowerCase();

            if(fieldValue.length)
            {
                if(!patient[fieldCode] || (String(patient[fieldCode]).toLowerCase().indexOf(fieldValue) !== 0))
                {
                    verdict = false;
                }
            }
        });

        return verdict;
    }

    prepareFilter(rawFilter = null)
    {
        if(rawFilter === null)
        {
            rawFilter = this.props.filter;
        }

        return General.filterObj(rawFilter, (fieldValue, fieldCode) =>
        {
            return (!!String(fieldValue).length && (this.props.filterBy.indexOf(fieldCode) !== -1));
        });
    }

    preparePatientsData(patients)
    {
        let result = General.clone(patients);

        result.forEach(patient =>
        {
            if(patient.personalBirthday)
            {
                General.Date.formatDate(patient.personalBirthday, 'DD.MM.YYYY');
                patient.age = General.Date.getAge(patient.personalBirthday);
            }

            if(patient.personalPhone)
            {
                patient.personalPhoneFormatted = General.formatPhone(String(patient.personalPhone));
            }
        });

        return result;
    }

    loadData()
    {
        let data = {
            filter: this.filter,
            select: this.constructor.patientsFields,
            countTotal: true
        };

        let command = new ServerCommand('patient-card/get-list', data, result =>
        {
            if(Array.isArray(result.list))
            {
                if(result.list.length === result.count)
                {
                    this.allDataLoaded = true;
                }

                this.setState({
                    patients: result.list
                });
            }
        });

        console.log('patients table loads data!');

        command.exec();
    }

    handleRowClick(patient)
    {
        let selectedPatient;

        if(patient.id === this.state.selectedPatientId)
        {
            this.setState({
                selectedPatientId: null
            });

            selectedPatient = null;
        }
        else
        {
            this.setState({
                selectedPatientId: patient.id
            });

            selectedPatient = patient;
        }

        if(this.props.onChange)
        {
            this.props.onChange(selectedPatient);
        }
    }
}