import React from 'react'
import PropTypes from 'prop-types'
import Scrollbars from '../scrollbars'
import ServerCommand from 'js/server/server-command'
import GeneralHelper from 'js/helpers/general-helper'
import DateHelper from 'js/helpers/date-helper'
import PhoneHelper from 'js/helpers/phone-helper'

export default class PatientsTable extends React.PureComponent
{
    static propTypes = {
        initialPatients: PropTypes.array.isRequired,
        fields: PropTypes.object.isRequired,
        onChange: PropTypes.func,
        filter: PropTypes.object,
        filterBy: PropTypes.arrayOf(PropTypes.string),
        allDataLoaded: PropTypes.bool,
        selectedPatientId: PropTypes.number
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
                <Scrollbars>
                    <div className="table__scroll-area">
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
                                    <tr className={'table__row' + ((this.props.selectedPatientId === patient.id) ? ' table__row--active' : '')  }
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
                                        <td className="table__cell">{patient.personalPhoneFormatted || (<span className="table__not-phone"/>)}</td>
                                    </tr>
                                ))}

                            <tr className="table__row table__row--empty"/>
                            <tr className="table__row"/>
                            </tbody>
                        </table>
                    </div>
                </Scrollbars>
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
                patients: this.preparePatientsData(this.props.initialPatients)
            });
        }
    }

    filterPatients(patient)
    {
        let verdict = true;

        GeneralHelper.forEachObj(this.prepareFilter(), (fieldValue, fieldCode) =>
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

        return GeneralHelper.filterObj(rawFilter, (fieldValue, fieldCode) =>
        {
            return (!!String(fieldValue).length && (this.props.filterBy.indexOf(fieldCode) !== -1));
        });
    }

    preparePatientsData(patients)
    {
        let result = GeneralHelper.clone(patients);

        result.forEach(patient =>
        {
            if(patient.personalBirthday)
            {
                DateHelper.formatDate(patient.personalBirthday, 'DD.MM.YYYY');
                patient.age = DateHelper.getAge(patient.personalBirthday);
            }

            if(patient.personalPhone)
            {
                patient.personalPhoneFormatted = PhoneHelper.format(String(patient.personalPhone));
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
//
        let command = new ServerCommand('patient-card/get-list', data, result =>
        {
            if(Array.isArray(result.list))
            {
                if(result.list.length === result.count)
                {
                    this.allDataLoaded = true;
                }

                this.setState({
                    patients: this.preparePatientsData(result.list)
                });
            }
        });

        command.exec();
    }

    handleRowClick(patient)
    {
        let selectedPatient;

        if(patient.id === this.props.selectedPatientId)
        {
            /*this.setState({
                selectedPatientId: null
            });*/

            selectedPatient = null;
        }
        else
        {
            /*this.setState({
                selectedPatientId: patient.id
            })*/

            selectedPatient = patient;
        }

        if(this.props.onChange)
        {
            this.props.onChange(selectedPatient);
        }
    }
}