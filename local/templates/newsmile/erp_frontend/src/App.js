import React, {Component} from 'react'
import './App.scss'
import Sidebar from './components/Sidebar/Sidebar'
import Header from './components/Header/Header'
import Schedule from './components/Schedule/Schedule'
import Appt from './components/Appt/Appt'
// import Test from './components/Test/Test'


class App extends Component {
    render() {
        return (
            <div className="App">
                <Sidebar />
                <div className="main-content">
                    <Header />
                    <Appt />
                    {/*<Schedule calendar={this.props.calendar} doctors={this.props.doctors} initialDate={this.props.initialDate} schedule={this.props.schedule} />*/}
                </div>
            </div>
        );
    }
}

export default App;
