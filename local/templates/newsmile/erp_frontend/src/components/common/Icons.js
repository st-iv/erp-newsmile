import React from 'react'
import './Icons.scss'
class IconCheck extends React.Component {
    render() {
        return (
            <svg className="icon-check" xmlns="http://www.w3.org/2000/svg" width={this.props.width} height={this.props.height} viewBox="0 0 5.37 3.81"><path fill="none" strokeLinecap="round" strokeLinejoin="round" d="M4.87.5L2.59 3.31.5 1.7"/></svg>
        )
    }
}

class IconPrint extends React.Component {
    render() {
        return (
            <svg className="icon-print" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 29.31 29.94" width={this.props.width} height={this.props.height}><polyline points="7.38 10.38 7.38 1.5 21.94 1.5 21.94 10.38" fill="none" strokeLinecap="round" strokeLinejoin="round"/><path d="M22.78,17.5H11.94a2.17,2.17,0,0,0-2.32,2.31V31.94H15.5" transform="translate(-8.13 -7.13)" fill="none" strokeLinecap="round" strokeLinejoin="round"/><path d="M22.78,17.5H33.62a2.17,2.17,0,0,1,2.32,2.31V31.94H30.06" transform="translate(-8.13 -7.13)" fill="none" strokeLinecap="round" strokeLinejoin="round"/><rect x="7.38" y="20.81" width="14.56" height="7.63" fill="none"  strokeLinecap="round" strokeLinejoin="round"/><line x1="7.38" y1="15.56" x2="9.94" y2="15.56" fill="none" strokeLinecap="round" strokeLinejoin="round"/></svg>
        )
    }
}

class IconArrow extends React.Component {
    render() {
        return (
            <svg className="icon-arrow" width={this.props.width} height={this.props.height} id="Layer_1" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 9.96 15.68"><polyline points="8.91 1.08 2.12 7.65 8.89 14.63" fill="none" strokeMiterlimit="10"/></svg>
        )
    }
}

class IconFolder extends React.Component {
    render() {
        return (
            <svg className="icon-folder" width={this.props.width} height={this.props.height} data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 18.57 15.43"><path strokeMiterlimit="10" strokeWidth="1.1" d="M18.02 14.88H.55V.55h8.04l2.21 3.26h7.22v11.07z"/></svg>
        )
    }
}

class IconTooth extends React.Component {
    static defaultProps  = {
        width: '35',
        height: '35',
        fill: '#e9e9e9'
    }

    render() {
        return (
        <svg className="icon-tooth" width={this.props.width} height={this.props.height} fill={this.props.fill} xmlns="http://www.w3.org/2000/svg" viewBox="0 0 22.9 21.5"><defs><path id="a" d="M0 0h22.9v21.5H0z"/></defs><path class="st0" d="M11.5 14.1c2.5 0 2.3 7.4 4.5 7.4 2.4 0 4.6-5.1 4.6-5.1s3.4-6.7 2-12.6S13.2.7 11.6.7h-.1C9.9.7 1.9-2.1.5 3.8-1 9.7 2.4 16.5 2.4 16.5s2.3 5.1 4.6 5.1c2.2-.1 1.9-7.5 4.5-7.5z"/></svg>
        )
    }
}

class IconClose extends React.Component {
    static defaultProps  = {
        width: '8',
        height: '8',
        stroke: '#000000'
    }

    render() {
        return (
            <svg className="icon-close" width={this.props.width} height={this.props.height} fill={this.props.fill} xmlns="http://www.w3.org/2000/svg" viewBox="0 0 27.42 27.42"><path strokeLinecap="round" strokeMiterlimit="10" strokeWidth="4" d="M2 2l23.42 23.42M25.42 2L2 25.42"/></svg>
        )
    }
}

class PrinterImg extends React.Component {
    static defaultProps  = {
        width: '140',
        height: '140',
    }

    render() {
        return (
            <svg className="print-img" width={this.props.width} height={this.props.height} xmlns="http://www.w3.org/2000/svg" viewBox="0 0 114.27 114.9"><path fill="none" stroke="#c2c2c2" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M24.66 67.57l56.84 32.81V70.17L24.66 37.36v30.21zM105.09 48.9l-.01.01-14.65 8.49-43.85-25.32 14.66-8.5 43.85 25.32zM61.24 23.58l-6.4-3.7M105.09 48.9l6.59 3.8v30.19M81.5 100.38l30.17-17.48"/><path fill="none" stroke="#c2c2c2" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M31.18 71.35L1 88.83l43.55 25.07 30.17-17.48M48.09 81.09L17.91 98.56M55.66 85.45L25.5 102.94M81.5 70.17l30.17-17.48M24.66 37.36l30.18-17.48M61.35 23.51l-.11.07M74.93 66.38l-.03.02M31.18 71.35V52.87l43.54 25.08v18.47M31.18 62.11l43.54 25.07M75.37 9.05l-5.93-3.42-8.2 17.95M105.08 48.91l8.19-17.95-6.07-3.51M98.45 44.71l12.11-25.81L79.58 1l-12.1 25.8"/><path fill="none" stroke="#60ae00" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M74.72 58.84l3.17 1.83"/></svg>
        )
    }
}

export {IconCheck, IconPrint, IconArrow, IconFolder, IconTooth, IconClose, PrinterImg}
