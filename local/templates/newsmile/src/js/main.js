import './vendor';
import Schedule from './react/schedule/main';
import Search from './react/search/main'
import React from 'react';
import ReactDOM from 'react-dom'
import ServerCommand from './server/server-command'
import ArrayHelper from 'js/helpers/array-helper';

window.Schedule = Schedule;
window.ServerCommand = ServerCommand;
window.Search = Search;
window.React = React;
window.ReactDOM = ReactDOM;

ArrayHelper.modifyPrototype();