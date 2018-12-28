import React from 'react'
import Cell from './cell'

class Column extends React.PureComponent
{
    constructor(props)
    {
        super(props);
    }

    render()
    {
        const cells = this.props.cells;
        const doctors = this.props.doctors;
        const mainDoctors = this.props.mainDoctors;
        const isOneMainDoctor = (mainDoctors[0]) && (mainDoctors[0] === mainDoctors[1]);

        return (
            <div className="dayCalendar_column">
                <div className="dayCalendar_roomName">{this.props.chair.name}</div>

                {mainDoctors.map((mainDoctorId, index) =>
                    <div className={'dayCalendar_doctor ' + (isOneMainDoctor ? 'sameD' : '') + (mainDoctorId ? '' : ' emptyD')}
                         style={mainDoctorId ? {backgroundColor: doctors[mainDoctorId].color} : {}}
                         key={index}>
                        {mainDoctorId ? doctors[mainDoctorId].fio : ''}
                    </div>
                )}

                {this.renderCells(cells)}

            </div>
        );
    }

    renderCells(cells)
    {
        let result = [];

        General.forEachObj(this.props.timeLine, (timeLineItem, time) =>
        {
            if(!cells[time]) return;

            let cellProps = Object.assign({}, cells[time]);

            cellProps.doctor = (cellProps.doctorId ? this.props.doctors[cellProps.doctorId] : null);
            cellProps.patient = (cellProps.patientId ? this.props.patients[cellProps.patientId] : null);
            cellProps.isMainDoctor = (this.props.mainDoctors[cellProps.halfDayNum] === cellProps.doctorId);

            cellProps.key = cellProps.timeStart;
            cellProps.commands = this.props.commands;
            cellProps.date = this.props.date;
            cellProps.chairId = this.props.chairId;
            cellProps.onUpdate = this.props.update;
            cellProps.height = timeLineItem.height;
            cellProps.timeLine = this.props.timeLine;

            result.push(
                <Cell {...cellProps}/>
            );
        });

        return result;
    }
}

export default Column