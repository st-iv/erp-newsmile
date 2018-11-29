class CalendarDayCellMenu extends React.Component
{
    render()
    {
        return (
            <div className="dClndr_popup_menu" onClick={this.blockEvent} onContextMenu={this.blockEvent}>
                <ul className="dClndr_pmenu1">

                    {this.props.actions.map(action =>
                        <li className={action.variants ? 'dClndr_phasmenu' : ''} onMouseEnter={this.props.onShowActionVariants}
                            onMouseLeave={this.props.onHideActionVariants}
                            onClick={action.variants ? null : this.processAction.bind(this, action.code)} key={action.code}>

                            {action.title}

                            <ul className="dClndr_psubmenu">
                                {action.variants && action.variants.map(variant =>
                                    <li onClick={this.processAction.bind(this, action.code, variant.code)} key={action.code + '_' + variant.code}>
                                        {variant.title}
                                    </li>
                                )}
                            </ul>
                        </li>
                    )}
                </ul>
            </div>
        );
    }

    blockEvent(e)
    {
        e.stopPropagation();
        e.preventDefault();
    }

    processAction(actionCode, variantCode = '', e)
    {


        console.log('processAction');
        console.log(actionCode);
        console.log(variantCode);

        this.props.onAction(actionCode, variantCode);
    }
}