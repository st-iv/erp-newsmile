import React from 'react'

export default class Sidebar extends React.Component {
    render() {
        return (
            <div className=" left_menu__wrap">
                <div className="left_menu">
                    <div className="menu_btn_shld"></div>
                    <div className="menu_btn_kartoteka"></div>
                    <div className="menu_btn_options"></div>
                </div>
                {/*<div className="left_menu_vline"></div>*/}
                <div className="left_menu_content">
                    <div className="menu_shld_itmslst">
                        <div className="menu_shld_item">Запланировать прием</div>
                        <div className="menu_shld_item">Лист ожидания</div>
                        <div className="menu_shld_item">Новая запись в листе ожидания</div>
                        <div className="menu_shld_item">Рассылки</div>
                        <div className="menu_shld_item">Отправить SMS</div>
                        <div className="menu_shld_item">Журнал заказ-нарядов</div>
                    </div>
                </div>
            </div>
        )
    }
}