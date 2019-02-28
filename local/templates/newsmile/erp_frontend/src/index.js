import React from 'react';
import ReactDOM from 'react-dom';
import './index.scss';
import './bootstrap.min.css';
import './styles/vendor/jquery.mCustomScrollbar.css';
// import './styles/vendor/jquery-ui.min.css';
// import './styles/vendor/jquery.contextMenu.min.css';
// import './styles/vendor/magnific-popup.css';
import './styles/vendor/popup.css';
import './styles/vendor/select2.min.css';
import './style.css';
import './main.css';
import App from './App';
import ArrayHelper from './common/helpers/array-helper'
import ServerCommand from './common/server/server-command.js'

import * as serviceWorker from './serviceWorker';

ReactDOM.render(<App />, document.getElementById('root'));
ArrayHelper.modifyPrototype();

window.ServerCommand = ServerCommand;

//server
/*window.React =  React;
window.ReactDOM =  ReactDOM;
window.App = App;
serviceWorker.unregister();*/
