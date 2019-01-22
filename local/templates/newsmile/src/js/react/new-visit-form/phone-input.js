import React from 'react'
import TextInput from './text-input'

function PhoneInput(props)
{
    return (
        <TextInput mask="+7 (999) 999 99 99" maskChar="-" {...props}/>
    );
}

export default PhoneInput
