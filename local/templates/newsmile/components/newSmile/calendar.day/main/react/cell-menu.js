class CalendarDayCellMenu extends React.Component
{
    constructor(props)
    {
        super(props);
        this.state = {
            commands: null
        };

        this.specifyCommandsList();
    }

    specifyCommandsList()
    {
        let commands = this.props.commands.map(commandCode =>
        {
            let commandInfo = {};

            commandInfo.code = commandCode;
            commandInfo.params = {
                timeStart: this.props.timeStart,
                timeEnd: this.props.timeEnd,
                chairId: this.props.chairId,
                date: this.props.date,
            };

            switch(commandCode)
            {
                case 'schedule/change-doctor':
                    commandInfo.varyParam = 'doctorId';
                    break;

                case 'visit/add':
                    commandInfo.varyParam = 'patientId';
                    break;
            }

            return commandInfo;
        });

        let commandData = {
            'commands': commands
        };

        let command = new ServerCommand('command/get-list', commandData, response =>
        {
            if(response)
            {
                console.log(response);
                let hasAvailableCommands = false;

                response.forEach(command =>
                {
                    if(command.available)
                    {
                        hasAvailableCommands = true;
                    }
                });

                if(hasAvailableCommands)
                {
                    this.setState({
                        commands: response
                    });
                }
            }
        });

        command.exec();
    }

    render()
    {
        console.log('render!');

        if(this.state.commands)
        {
            return (
                <div className="dClndr_popup_menu" onClick={this.blockEvent} onContextMenu={this.blockEvent}>
                    <ul className="dClndr_pmenu1">

                        {this.state.commands.map(command =>
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