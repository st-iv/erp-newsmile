let PhoneHelper = {

    format: function(rawPhone)
    {
        if(typeof rawPhone !== 'string')
        {
            rawPhone = window.String(rawPhone);
        }

        if(rawPhone.length !== 11)
        {
            console.error('Номер телефона должен содержать 11 цифр');
            console.log(rawPhone);
            return null;
        }

        return '+' + rawPhone[0] + ' (' + rawPhone.substr(1, 3) + ') ' + rawPhone.substr(4, 3) + ' ' + rawPhone.substr(7, 2) + ' ' + rawPhone.substr(9, 2);
    },

    /**
     * Возвращает позицию цифры телефона с определённым номером в отформатированном варианте телефона.
     * @param numNumber
     * @returns {*}
     */
    getNumFormattedPos: function(numNumber)
    {
        let char = (numNumber === 10) ? '_' : window.String(numNumber);
        return this.format('0123456789_').indexOf(char);
    },
}

export default PhoneHelper