import React from 'react'
import './Icons.scss'
class IconCheck extends React.Component {
    render() {
        return (
            <svg className="icon-check" xmlns="http://www.w3.org/2000/svg" width={this.props.width} height={this.props.height} viewBox="0 0 5.37 3.81"><path fill="none" stroke-linecap="round" stroke-linejoin="round" d="M4.87.5L2.59 3.31.5 1.7"/></svg>
        )
    }
}

class IconPrint extends React.Component {
    render() {
        return (
            <svg className="icon-print" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 29.31 29.94" width={this.props.width} height={this.props.height}><polyline points="7.38 10.38 7.38 1.5 21.94 1.5 21.94 10.38" fill="none" stroke-linecap="round" stroke-linejoin="round"/><path d="M22.78,17.5H11.94a2.17,2.17,0,0,0-2.32,2.31V31.94H15.5" transform="translate(-8.13 -7.13)" fill="none" stroke-linecap="round" stroke-linejoin="round"/><path d="M22.78,17.5H33.62a2.17,2.17,0,0,1,2.32,2.31V31.94H30.06" transform="translate(-8.13 -7.13)" fill="none" stroke-linecap="round" stroke-linejoin="round"/><rect x="7.38" y="20.81" width="14.56" height="7.63" fill="none"  stroke-linecap="round" stroke-linejoin="round"/><line x1="7.38" y1="15.56" x2="9.94" y2="15.56" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>
        )
    }
}

class IconArrow extends React.Component {
    render() {
        return (
            <svg className="icon-arrow" width={this.props.width} height={this.props.height} id="Layer_1" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 9.96 15.68"><polyline points="8.91 1.08 2.12 7.65 8.89 14.63" fill="none" stroke-miterlimit="10"/></svg>
        )
    }
}

class IconTooth extends React.Component {
    render() {
        return (
        <svg className="icon-tooth" width={this.props.width} height={this.props.height} fill={this.props.fill} xmlns="http://www.w3.org/2000/svg" viewBox="0 0 22.9 21.5"><defs><path id="a" d="M0 0h22.9v21.5H0z"/></defs><path class="st0" d="M11.5 14.1c2.5 0 2.3 7.4 4.5 7.4 2.4 0 4.6-5.1 4.6-5.1s3.4-6.7 2-12.6S13.2.7 11.6.7h-.1C9.9.7 1.9-2.1.5 3.8-1 9.7 2.4 16.5 2.4 16.5s2.3 5.1 4.6 5.1c2.2-.1 1.9-7.5 4.5-7.5z"/></svg>
        )
    }
}

export {IconCheck, IconPrint, IconArrow, IconTooth}