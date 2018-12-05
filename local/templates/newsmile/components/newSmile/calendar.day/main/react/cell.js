class CalendarDayCell extends React.Component
{
    defaultCellHeight = 22;
    defaultCellMargin = 2;

    popperSettings = {
        placement: 'bottom-start',
        modifiers: {
            /*preventOverflow: {
                enabled: true,
                boundariesElement: document.body
            },*/
            offset: {
                enabled: true
            }
        }
    };

    state = {
        isHovered: false,
        showActions: false,
        showDetailInfo: true
    };

    constructor(props)
    {
        super(props);
        this.handleOutsideClick = this.handleOutsideClick.bind(this);
    }

    componentDidUpdate(prevProps, prevState)
    {
        if(prevState.showActions !== this.state.showActions)
        {
            if(this.state.showActions)
            {
                document.addEventListener('mousedown', this.handleOutsideClick);
            }
            else
            {
                document.removeEventListener('mousedown', this.handleOutsideClick);
            }
        }
    }

    render()
    {
        if(this.state.isHovered || this.state.showActions)
        {
            const isVisit = !!this.props.patient;
            const commands = this.getCommands();
            console.log(commands);

            const popper = (
                <ReactPopper.Popper {...this.popperSettings}>
                    {({ ref, style, placement, arrowProps }) => (
                        <div ref={ref} style={style} x-placement={placement} className="dayCalendar_popup">

                            {this.state.showActions && !!commands.length && (
                                <CalendarDayCellMenu commands={commands}
                                                     onShowActionVariants={this.setShowDetailInfo.bind(this, false)}
                                                     onHideActionVariants={this.setShowDetailInfo.bind(this, true)}
                                                     onCommandExec={this.handleMenuAction.bind(this)}
                                                     onCommandResult={this.props.onUpdate}
                                                     timeStart={this.props.timeStart}
                                                     timeEnd={this.props.timeEnd}
                                                     chairId={this.props.chairId}
                                                     date={this.props.date}

                                />
                            )}

                            {this.needShowDetailInfo() && (
                                <CalendarDayCellDetailInfo patient={this.props.patient}
                                                           timeStart={this.props.timeStart}
                                                           timeEnd={this.props.timeEnd}/>
                            )}

                            <div className="dClndr_parrow"></div>
                        </div>
                    )}
                </ReactPopper.Popper>
            );

            return (
                <ReactPopper.Manager>
                    <ReactPopper.Reference>
                        {({ ref }) => (
                            this.renderCell(ref, popper)
                        )}
                    </ReactPopper.Reference>
                </ReactPopper.Manager>
            );
        }
        else
        {
            return this.renderCell();
        }
    }

    renderCell(refCallback = null, popup = null)
    {
        const doctor = this.props.doctor;
        const patient = this.props.patient;
        const isMainDoctor = this.props.isMainDoctor;

        let cellStyle = {
            height: this.getHeight() + 'px'
        };

        let cellContent = '';
        let className = 'dayCalendar_interval';

        if(doctor)
        {
            cellStyle.color = General.Color.darken(doctor.color, 50);

            if(patient)
            {
                cellStyle.backgroundColor = General.Color.lighten(doctor.color, 30);
                cellStyle.borderColor = General.Color.lighten(doctor.color, 13);

                cellContent = <span>{General.getFio(patient)}</span>;
            }
            else
            {
                if(!isMainDoctor)
                {
                    cellContent = <span className="freedoctor_intrvl">{'Врач - ' + doctor.fio}</span>
                }

                cellStyle.backgroundColor = General.Color.lighten(doctor.color, 40);
            }

            className += ' resrvdI';
        }
        else
        {
            className += ' emptyI';
        }

        const showPopup = (this.state.isHovered || this.state.showActions);

        if(showPopup)
        {
            className += ' dClndr_pshowed';

            if(!this.state.showDetailInfo)
            {
                className += ' dClndr_submemu_act';
            }
        }

        if(this.state.showActions)
        {
            className += ' dClndr_pshowmenu';
        }

        let cellAttrs = {
            className: className,
            style: cellStyle,
            onMouseEnter: this.setHover.bind(this, true),
            onMouseLeave: this.setHover.bind(this, false),
            onContextMenu: this.handleContextMenu.bind(this),
            onClick: this.handleCellClick.bind(this),
            ref: refCallback
        };

        return (
            <div {...cellAttrs}>
                {cellContent}
                {popup}
            </div>
        )
    }

    getHeight()
    {
        let height = this.defaultCellHeight;

        if(this.props.doctor)
        {
            height = (this.props.size * this.defaultCellHeight + (this.props.size - 1) * this.defaultCellMargin);
        }

        return height;
    }

    getCommands()
    {
        let result = [];
        let visitCommands = [];
        let isVisit = !!this.props.patient;

        for(let entityCode in this.props.commands)
        {
            let entityCommands = this.props.commands[entityCode];

            entityCommands.forEach(commandCode =>
            {
                if(visitCommands.indexOf(commandCode) !== -1)
                {
                    if(isVisit)
                    {
                        result.push(commandCode);
                    }
                }
                else if(!isVisit)
                {
                    result.push(commandCode);
                }
            });
        }

        return result;
    }

    setHover(isHover)
    {
        if(this.props.patient)
        {
            this.setState({
                isHovered: isHover
            });
        }
    }

    setShowDetailInfo(showDetailInfo)
    {
        this.setState({
            showDetailInfo: showDetailInfo
        });
    }

    handleContextMenu(e)
    {
        e.preventDefault();

        this.setState({
            showActions: !this.state.showActions
        });
    }

    needShowDetailInfo()
    {
        return (this.props.patient && this.state.showDetailInfo && (this.state.isHovered || this.state.showActions));
    }

    handleCellClick()
    {
        if(this.state.showActions)
        {
            this.setState({
                showActions: false
            });
        }
    }

    handleOutsideClick(e)
    {
        const cellNode = ReactDOM.findDOMNode(this);
        if(cellNode && !cellNode.contains(e.target))
        {
            this.setState({
                showActions: false
            });
        }
    }

    handleMenuAction()
    {
        this.setState({
            showActions: false,
            showDetailInfo: true
        });
    }
}
