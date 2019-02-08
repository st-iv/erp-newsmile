import React from 'react'
import Scrollbar from 'react-scrollbars-custom'
import './Notifications.scss'

const NotificationsTabs = () => {
    return (
        <div className="notif_tabs">
            <div className="notif_tab tActive" data-select="all">Все</div>
            <div className="notif_tab" data-select="VISIT">Приёмы</div>
            <div className="notif_tab" data-select="SYSTEM">Системные</div>
            <div className="notif_tab" data-select="BOSSES">Начальство</div>
            <div className="notif_tab" data-select="CALLS">Обзвон</div>
        </div>
    )
}

const NotificationsItems = () => {
    return (
        <div className="notif_items">
            <Scrollbar className="scrolltst"
                style={{width: '100%', height: '200px', maxHeight: 595}}
            >
            <div className="notif_item">
                <div className="notif_data">
                    <div className="notif_data_date">
                        01 февраля 2019, в 14:33
                    </div>
                </div>
                <div className="notif_status status">
                    Новая запись на приём
                </div>
                <div className="notif_text">
                    Вы записаны на приём 2019-02-01 с 16:45 до 17:00 (Кресло 1, врач )
                </div>
                <div className="notif_close"></div>
            </div>
                <div className="notif_item">
                    <div className="notif_data">
                        <div className="notif_data_date">
                            01 февраля 2019, в 14:33
                        </div>
                    </div>
                    <div className="notif_status status">
                        Новая запись на приём
                    </div>
                    <div className="notif_text">
                        Вы записаны на приём 2019-02-01 с 16:45 до 17:00 (Кресло 1, врач )
                    </div>
                    <div className="notif_close"></div>
                </div>
                <div className="notif_item">
                    <div className="notif_data">
                        <div className="notif_data_date">
                            01 февраля 2019, в 14:33
                        </div>
                    </div>
                    <div className="notif_status status">
                        Новая запись на приём
                    </div>
                    <div className="notif_text">
                        Вы записаны на приём 2019-02-01 с 16:45 до 17:00 (Кресло 1, врач )
                    </div>
                    <div className="notif_close"></div>
                </div>
                <div className="notif_item">
                    <div className="notif_data">
                        <div className="notif_data_date">
                            01 февраля 2019, в 14:33
                        </div>
                    </div>
                    <div className="notif_status status">
                        Новая запись на приём
                    </div>
                    <div className="notif_text">
                        Вы записаны на приём 2019-02-01 с 16:45 до 17:00 (Кресло 1, врач )
                    </div>
                    <div className="notif_close"></div>
                </div>
            </Scrollbar>
        </div>
    )
}

export default class Notifications extends React.Component {
    render() {
        return (
            <div className="notify__wrap">
                <div className="header_notif">
                    <div className="notif_bell">
                        <div className="notif_amnt">238</div>
                    </div>
                </div>
                <div className="notif_content">
                    <div className="notif_header">Уведомления</div>
                    <div className="notif_content_close"></div>
                    <NotificationsTabs />
                    <NotificationsItems />
                </div>
            </div>
        )
    }
}
