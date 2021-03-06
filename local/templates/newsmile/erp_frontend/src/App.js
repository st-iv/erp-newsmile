import React, {Component} from 'react'
import './App.scss'
import Sidebar from './components/Sidebar/Sidebar'
import Header from './components/Header/Header'
import Schedule from './components/Schedule/Schedule'
import Appt from './components/Appt/Appt'
import TextArea from './components/common/TextArea/TextArea'
import Notifications from './components/common/Notifications/Notifications'
import ServerCommand from './common/server/server-command'
import Test from './components/Test/Test'
import ToothCard from "./components/Appt/Steps/Checkup/ToothCard";


class App extends Component {

    state = {
        calendar: null,
        doctors: null,
        initialDate: null,
        notices: null,
        schedule: null,
        search: null
    };

    componentWillMount() {
        var command = new ServerCommand('general/get-index-data');
        command.exec().then(response => this.setState(response));
    }

    render() {
        return (
            <div className="App">
                <Notifications />
                <Sidebar />
                <div className="main-content">
                    <Header />
                    {/* <Test /> */}
                    {/* <Appt /> */}
                    {this.state.schedule && (
                        <Schedule calendar={this.state.calendar} doctors={this.state.doctors} initialDate={this.state.initialDate} schedule={this.state.schedule} />
                    )}
                    <ToothCard />
                </div>
            </div>
        );
    }
}

export default App;
