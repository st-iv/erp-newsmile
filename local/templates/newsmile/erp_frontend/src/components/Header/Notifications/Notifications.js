import React from 'react'

export default class Notifications extends React.Component {
    render () {
        return (
            <div className="header_notifWrap">
                <div className="header_notif">
                    <div className="notif_bell">
                        <div className="notif_amnt">238</div>
                    </div>
                </div>
                <div className="notif_content">
                    <div className="notif_header">Уведомления</div>
                    <div className="notif_content_close"></div>
                    <div className="notif_tabs">
                        <div className="notif_tab tActive" data-select="all">Все</div>
                        <div className="notif_tab" data-select="VISIT">Приёмы</div>
                        <div className="notif_tab" data-select="SYSTEM">Системные</div>
                        <div className="notif_tab" data-select="BOSSES">Начальство</div>
                        <div className="notif_tab" data-select="CALLS">Обзвон</div>
                    </div>
                </div>
            </div>
        )
    }
}
