import React from 'react'
import PropTypes from 'prop-types'

class CellContextMenu extends React.Component
{
    static propTypes = {
        commands: PropTypes.array.isRequired,
        timeStart: PropTypes.string,
        timeEnd: PropTypes.string,
        chairId: PropTypes.number,
        date: PropTypes.string,

        onShowActionVariants: PropTypes.func,
        onHideActionVariants: PropTypes.func,
        onCommandExec: PropTypes.func,
        onCommandResult: PropTypes.func,
    };

    render()
    {
        if(this.props.commands)
        {
            return (
                <div className="dClndr_popup_menu" onClick={this.blockEvent} onContextMenu={this.blockEvent}>
                    <ul className="dClndr_pmenu1">

                        {this.props.commands.map(command =>
                            <li className={command.variants ? 'dClndr_phasmenu' : ''} onMouseEnter={this.props.onShowActionVariants}
                                onMouseLeave={this.props.onHideActionVariants}
                                onClick={command.variants ? null : this.processCommand.bind(this, command.code)} key={command.code}>

                                {command.name}

                                <ul className="dClndr_psubmenu">
                                    {command.variants && command.variants.map(variant =>
                                        <li onClick={this.processCommand.bind(this, command.code, variant.code)} key={command.code + '_' + variant.code}>
                                            {variant.name}
                                        </li>
                                    )}
                                </ul>
                            </li>
                        )}
                    </ul>
                </div>
            );
        }
        else
        {
            return null;
        }
    }

    blockEvent(e)
    {
        e.stopPropagation();
        e.preventDefault();
    }

    processCommand(commandCode, variantCode = '', e)
    {
        if(this.props.onCommandExec)
        {
            this.props.onCommandExec(commandCode);
        }

        let command = new ServerCommand(commandCode, this.getCommandData(commandCode, variantCode), response =>
        {
            this.onCommandDone(commandCode);
        });

        command.exec();

    }

    getCommandData(commandCode, variantCode)
    {
        let data = {
            timeStart: this.props.timeStart,
            timeEnd: this.props.timeEnd,
            chairId: this.props.chairId,
            date: this.props.date
        };

        switch(commandCode)
        {
            case 'schedule/change-doctor':
                data.doctorId = variantCode;
                break;

            case 'visit/add':
                data.patientId = variantCode;
                break;
        }

        return data;
    }

    onCommandDone(commandCode, result = null)
    {
        if(this.props.onCommandResult)
        {
            this.props.onCommandResult(commandCode, result);
        }
    }
}

export default CellContextMenu