import React from 'react'
import ReactDOM from 'react-dom'
import CellMenu from './cell-menu'

class Cell extends React.PureComponent
{
    defaultCellHeight = 22;
    defaultCellMargin = 2;

    render()
    {
        if(this.props.isBlocked)
        {
            return (
                this.renderCell()
            );
        }
        else
        {
            let detailInfo;

            if(!!this.props.patient)
            {
                detailInfo = {
                    patient: General.clone(this.props.patient),
                    timeStart: this.props.timeStart,
                    timeEnd: this.props.timeEnd
                };

                detailInfo.patient.cardNumber = String(detailInfo.patient.cardNumber);
            }
            else
            {
                detailInfo = null;
            }

            return (
                <CellMenu renderCell={this.renderCell.bind(this)}
                          commands={this.props.commands}
                          getCellNode={this.getCellNode.bind(this)}
                          detailInfo={detailInfo}
                          cellType={!!this.props.patient ? 'visit' : 'schedule'}
                          onCommandResult={() => this.props.onUpdate()}
                          update={this.props.onUpdate}
                          timeStart={this.props.timeStart}
                          timeEnd={this.props.timeEnd}
                          chairId={this.props.chairId}
                          date={this.props.date}
                          doctorId={this.props.doctorId}
                />
            );
        }
    }

    renderCell(mixin = {}, menu = null)
    {
        const doctor = this.props.doctor;
        const patient = this.props.patient;
        const isMainDoctor = this.props.isMainDoctor;

        let cellStyle = {
            height: this.getHeight() + 'px'
        };

        let cellContent = [];
        let className = 'dayCalendar_interval';

        if(doctor)
        {
            cellStyle.color = General.Color.darken(doctor.color, 50);

            if(patient)
            {
                cellStyle.backgroundColor = General.Color.lighten(doctor.color, 30);
                cellStyle.borderColor = General.Color.lighten(doctor.color, 13);

                cellContent.push(<span key="patient-fio">{General.getFio(patient)}</span>);
            }
            else
            {
                cellStyle.backgroundColor = General.Color.lighten(doctor.color, 40);
            }

            if(!isMainDoctor)
            {
                cellContent.push(<span className="freedoctor_intrvl" key="doctor-fio">{'Врач - ' + doctor.fio}</span>);
            }

            className += ' resrvdI';
        }
        else
        {
            className += ' emptyI';
        }

        if(this.props.isBlocked)
        {
            cellStyle.visibility = 'hidden';
        }

        let cellAttrs = {
            className: className,
            style: cellStyle
        };

        Object.assign(cellAttrs, mixin);

        return (
            <div {...cellAttrs}>
                {cellContent}{menu}
            </div>
        )
    }

    getHeight()
    {
        let height = -this.defaultCellMargin;

        General.forEachObj(this.props.timeLine, timeLineItem =>
        {
            let timeLineItemHeight = timeLineItem.height || this.defaultCellHeight;
            height += timeLineItemHeight + this.defaultCellMargin;

        }, this.props.timeStart, this.props.timeEnd);


        return height;
    }

    getCellNode()
    {
        return ReactDOM.findDOMNode(this);
    }

}

export default Cell