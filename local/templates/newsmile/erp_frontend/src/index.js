import React from 'react';
import ReactDOM from 'react-dom';
import './index.css';
import './style.css';
import './main.css';
import './bootstrap.min.css';
import App from './App';

import * as serviceWorker from './serviceWorker';

ReactDOM.render(<App />, document.getElementById('root'));

serviceWorker.unregister();
