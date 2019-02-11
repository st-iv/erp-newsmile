import React from 'react'
import './User.css'

export default class Contacts extends React.Component {
  render() {
    return (
      <div className="header_user">
        <div className="header_user_imgWrap">
          <div className="header_user_img" style={ { backgroundImage: `url(https://cdn2.iconfinder.com/data/icons/professions/512/doctor_assistant-512.png)` } }>
          </div>
          <div className="header_user_status header__user_offline"></div>
        </div>
        <div className="header_user_data">
          <div className="header_user_pstn">Администратор</div>
          <div className="header_user_name">Константинов М.В.</div>
        </div>
        <div className="header_drwnarr"></div>
      </div>
    )
  }
}