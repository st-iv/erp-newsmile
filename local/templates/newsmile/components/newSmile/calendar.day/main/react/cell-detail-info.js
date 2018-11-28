function CalendarDayCellDetailInfo(props)
{
    const patient = props.patient;

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
                        <span>{General.getFullName(patient)}</span> - {patient.age}
                    </div>
                </div>
                <div className="dClndr_pinfo_number">
                    <div>Карта {patient.cardNumber}</div>
                    <span></span>
                </div>
                <div className="dClndr_pinfo_phone">
                    <div>{patient.phone}</div>
                </div>
                <div className="dClndr_pinfo_time">
                    <span>{props.timeStart} - {props.timeEnd}</span>
                    <span>{General.Date.getDurationString(props.timeStart, props.timeEnd)}</span>
                </div>
            </div>
        </div>
    )
}