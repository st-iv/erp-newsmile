import React from 'react'

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

class IconFolder extends React.Component {
    render() {
        return (
            <svg className="icon-folder" width={this.props.width} height={this.props.height} data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 18.57 15.43"><path stroke-miterlimit="10" stroke-width="1.1" d="M18.02 14.88H.55V.55h8.04l2.21 3.26h7.22v11.07z"/></svg>
        )
    }
}

export {IconCheck, IconPrint, IconArrow, IconFolder}