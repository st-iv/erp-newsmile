import React from 'react'
import {Popper, Reference, Manager} from 'react-popper'
import PropTypes from 'prop-types'
import CellContextMenu from './cell-context-menu'
import CellDetailInfo from './cell-detail-info'
import Popup from '../../popup'
import NewVisitForm from '../../new-visit-form/main'
import PopupManager from '../../popup-manager'

class CellMenu extends React.Component
{
    static propTypes = {
        renderCell: PropTypes.func.isRequired,
        getCellNode: PropTypes.func.isRequired,
        commands: PropTypes.objectOf(PropTypes.arrayOf(PropTypes.string)).isRequired,
        detailInfo: PropTypes.shape(CellDetailInfo.propTypes),
        cellType: PropTypes.string.isRequired,
        onCommandResult: PropTypes.func,
        timeStart: PropTypes.string,
        timeEnd: PropTypes.string,
        chairId: PropTypes.number,
        doctorId: PropTypes.number,
        date: PropTypes.string,
    };

    state = {
        isHovered: false,
        showContextMenu: false,
        showDetailInfo: true,
        commands: null
    };

    popperSettings = {
        placement: 'bottom-start',
        modifiers: {
            preventOverflow: {
                enabled: true,
                boundariesElement: document.body
            },
            offset: {
                enabled: true
            }
        }
    };

    constructor(props)
    {
        super(props);
        this.handleOutsideClick = this.handleOutsideClick.bind(this);
        this.commands = this.getCommands();
    }

    render()
    {
        let cellPropsMixin = {
            onMouseEnter: this.handleHover.bind(this, true),
            onMouseLeave: this.handleHover.bind(this, false),
            onContextMenu: this.handleContextMenu.bind(this),
            onClick: this.handleCellClick.bind(this)
        };

        this.popperUpdate = null;

        if(!this.isShown()) return this.props.renderCell(cellPropsMixin);

        let popupClassName = 'dayCalendar_popup';
        if(this.needShowContextMenu())
        {
            popupClassName += ' context-menu-shown';
        }

        const popper = (
            <Popper {...this.popperSettings}>
                {({ ref, style, placement, scheduleUpdate }) =>
                {
                    this.popperUpdate = scheduleUpdate;
                    return (
                        <div ref={ref} style={style} x-placement={placement} className={popupClassName}>

                            {this.needShowContextMenu() && (
                                <CellContextMenu commands={this.state.commands}
                                                 onShowActionVariants={() => {this.setState({showDetailInfo: false}); this.popperUpdate();}}
                                                 onHideActionVariants={() => this.setState({showDetailInfo: true})}
                                                 onCommandExec={this.handleMenuAction.bind(this)}
                                                 timeStart={this.props.timeStart}
                                                 timeEnd={this.props.timeEnd}
                                                 chairId={this.props.chairId}
                                                 date={this.props.date}
                                />
                            )}

                            {this.needShowDetailInfo() && (
                                <CellDetailInfo {...this.props.detailInfo}/>
                            )}

                            <div className="dClndr_parrow"></div>
                        </div>
                    );
                }}
            </Popper>
        );

        return (
            <Manager>
                <Reference>
                    {({ ref }) => {
                        cellPropsMixin.ref = ref;
                        return this.props.renderCell(cellPropsMixin, popper);
                    }}
                </Reference>
            </Manager>
        );
    }

    componentDidUpdate(prevProps, prevState, snapshot)
    {
        /* вешаем / снимаем обработчик, который будет фиксировать клики вне меню */
        let isShown = this.isShown();
        let wasShown = this.isShown(prevState);

        if(isShown !== wasShown)
        {
            if(isShown)
            {
                document.addEventListener('mousedown', this.handleOutsideClick);
            }
            else
            {
                document.removeEventListener('mousedown', this.handleOutsideClick);
            }
        }

        /* обновляем позицию Popper */
        let isShownContextMenu = this.needShowContextMenu();
        let wasShownContextMenu = this.needShowContextMenu(prevState);
        if(isShownContextMenu && !wasShownContextMenu && this.popperUpdate)
        {
            this.popperUpdate();
        }
        /**/
    }

    /**
     * Получает список команд путем фильтрации полного списка команд из свойств компонента в зависимости от типа ячейки cellType
     * @returns {Array}
     */
    getCommands()
    {
        let result = [];
        let visitCommands = [];
        let isVisit = (this.props.cellType === 'visit');

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

    /**
     * Отображает контекстное меню.
     * Предварительно уточняет на сервере какие команды из списка доступны для выпонения в данный момент для данной ячейки.
     */
    showContextMenu()
    {
        if(!this.commands) return;

        let commands = this.commands.map(commandCode =>
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
                        commands: response,
                        showContextMenu: true
                    });
                }
            }
        });

        command.exec();
    }

    hide()
    {
        this.setState({
            showContextMenu: false,
            showDetailInfo: false
        });
    }

    isShown(state = null)
    {
        return (this.needShowContextMenu(state) || this.needShowDetailInfo(state));
    }

    needShowDetailInfo(state = null)
    {
        state = state || this.state;
        return (state.showDetailInfo && !!this.props.detailInfo && (state.isHovered || state.showContextMenu));
    }

    needShowContextMenu(state = null)
    {
        state = state || this.state;
        return (state.showContextMenu && !!state.commands);
    }

    handleContextMenu(e)
    {
        e.preventDefault();

        if(this.state.showContextMenu)
        {
            this.setState({showContextMenu: false})
        }
        else
        {
            this.showContextMenu();
        }
    }

    handleCellClick()
    {
        if(this.state.showActions)
        {
            this.setState({
                showContextMenu: false
            });
        }
    }

    handleOutsideClick(e)
    {
        const cellNode = this.props.getCellNode();
        if(cellNode && !cellNode.contains(e.target))
        {
            this.hide();
        }
    }

    handleHover(isHovered, e)
    {
        this.setState({isHovered});
    }

    showPopup(popupContent)
    {
        this.setState({popup: popupContent})
    }


    /* ---------------------------- обработка пунктов контекстного меню --------------------------------- */

    handleMenuAction(commandCode, variantCode)
    {
        let specificMethodName = 'process' + General.getCamelCase(commandCode);

        if(typeof this[specificMethodName] === 'function')
        {
            this[specificMethodName](variantCode);
        }
        else
        {
            let command = new ServerCommand(commandCode, this.getCommandData(commandCode, variantCode), response =>
            {
                this.onCommandDone(commandCode);
            });

            command.exec();
        }

        this.setState({
            showContextMenu: false,
            showDetailInfo: true
        });
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

    processVisitAdd()
    {
        PopupManager.showPopup(
            <NewVisitForm chairId={this.props.chairId}
                          timeStart={this.props.timeStart}
                          timeEnd={this.props.timeEnd}
                          date={this.props.date}
                          doctorId={this.props.doctorId}
            />
        );
    }
}

export default CellMenu