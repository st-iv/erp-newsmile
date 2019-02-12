import React, {Component} from 'react'
import './App.scss'
import Header from './components/Header/Header'
import Appt from './components/Appt/Appt'
import Sidebar from './components/Sidebar/Sidebar'
// import Test from './components/Test/Test'


class App extends Component {
    render() {
        return (
            <div className="App">
                <Sidebar />
                <div className="main-content">
                    <Header />
                    <Appt />
                </div>
            </div>
        );
    }
}

export default App;
