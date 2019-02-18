import React from 'react'
import PropTypes from 'prop-types'
import GeneralHelper from './../../../common/helpers/general-helper'
import DateHelper from './../../../common/helpers/date-helper'
import PhoneHelper from './../../../common/helpers/phone-helper'

class CellDetailInfo extends React.PureComponent
{
    static propTypes = {
        timeStart: PropTypes.string.isRequired,
        timeEnd: PropTypes.string.isRequired,
        patient: PropTypes.shape({
            cardNumber: PropTypes.string,
            age: PropTypes.string,
            name: PropTypes.string,
            lastName: PropTypes.string,
            secondName: PropTypes.string
        })
    };

    render()
    {
        const patient = this.props.patient;

        return (
            <div className="dClndr_popup_card">
                {/*<div className="dpopup_card_statuses">
                                                <div className="ptnt_perv">Первичный</div>
                                                <div className="ptnt_decl">
                                                    Пациент не пришёл
                                                </div>
                                            </div>*/}
                <div className="dClndr_popup_info">
                    <div className="dClndr_pinfo_name">
                        <div>
                            <span>{GeneralHelper.getFullName(patient)}</span> - {patient.age}
                        </div>
                    </div>
                    <div className="dClndr_pinfo_number">
                        <div>Карта {patient.cardNumber}</div>
                        <span/>
                    </div>

                    {!!patient.phone && (
                        <div className="dClndr_pinfo_phone">
                            <div>{PhoneHelper.format(patient.phone)}</div>
                        </div>
                    )}

                    <div className="dClndr_pinfo_time">
                        <span>{this.props.timeStart} - {this.props.timeEnd}</span>
                        <span>{DateHelper.getDurationString(this.props.timeStart, this.props.timeEnd)}</span>
                    </div>
                </div>
            </div>
        )
    }
}

export default CellDetailInfo