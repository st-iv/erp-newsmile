import React from 'react'
import Notifications from './Notifications/Notifications'
import Time from './Time/Time'
import Contacts from './Contacts/Contacts'
import Search from './Search/Search'
import User from './User/User'

const contactsList = [{
    id: 1,
    city: "Иркутск",
    address: "Карла Либнехта 115",
    patients: "14",
    freeHours: "15:30",
    totalHours: "26:00",
    cityPhone: "45-67-45",
    mobilePhone: "8 (950) 10 555 22"
  },
  {
    id: 2,
    city: "Иркутск",
    address: "Советская 202/4",
    patients: "19",
    freeHours: "5:00",
    totalHours: "24:30",
    cityPhone: "210-456",
    mobilePhone: "8 (950) 10 555 22"
  },
  {
    id: 3,
    city: "Иркутск",
    address: "Байкальская 78/3",
    patients: "19",
    freeHours: "15:30",
    totalHours: "26:00",
    cityPhone: "55-67-78",
    mobilePhone: "8 (950) 10 555 22"
  },
];

export default class Header extends React.Component {
  render () {
    return (
      <div className="row main_header top-header">
        <Time timer="evening" age='30'/>
        <Notifications />
        <Search />
        <Contacts data={contactsList} />
        <User />
      </div>
    )
  }
}