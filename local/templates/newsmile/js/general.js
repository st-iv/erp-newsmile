var General = (function()
{
    var sessid = $('body').data('sessid');
    var postFormAction = $('body').data('post-form-action');

    function getParamsObject(serializedParams)
    {
        var result = {};
        serializedParams.split('&').forEach(function(keyValuePair)
        {
            var pairArray = keyValuePair.split('=');
            result[pairArray[0]] = pairArray[1];
        });

        return result;
    }

    function getFio(person)
    {
        var fio = person.lastName + ' ' + person.name[0].toUpperCase() + '.';
        if(person.secondName)
        {
            fio += ' ' + person.secondName[0].toUpperCase() + '.';
        }

        return fio;
    }

    function getFullName(person)
    {
        var fullName = person.lastName + ' ' + person.name;
        if(person.secondName)
        {
            fullName += ' ' + person.secondName;
        }

        return fullName;
    }

    function getCountString(number, variants)
    {
        if(number === 1)
        {
            return variants[0];
        }
        else if((number >= 2) && (number <= 4))
        {
            return variants[1];
        }
        else
        {
            return variants[2];
        }
    }

    /**
     * Клонирует массивы и объекты
     * @param arrayOrObject
     * @returns {any}
     */
    function clone(arrayOrObject)
    {
        return JSON.parse(JSON.stringify(arrayOrObject));
    }

    var Date = (function()
    {
        function formatTime(ts)
        {
            var time = new window.Date(ts);
            var hours = time.getHours();
            if(hours < 10)
            {
                hours = '0' + hours.toString();
            }

            var minutes = time.getMinutes();
            if(minutes < 10)
            {
                minutes = '0' + minutes.toString();
            }

            return hours + ':' + minutes;
        }

        function formatMinutes(minutes)
        {
            var hours = Math.floor(minutes / 60);
            var time = ((hours < 10) ? '0' + hours : hours);
            minutes -= hours * 60;
            time += ':' + ((minutes < 10) ? '0' + minutes : minutes);
            return time;
        }

        function getMinutesByTime(time)
        {
            var timeParts = time.split(':');
            var hours = Number(timeParts[0]);
            var minutes = Number(timeParts[1]);
            return hours * 60 + minutes;
        }

        function getDurationString(intervalStart, intervalEnd)
        {
            if((typeof intervalStart === 'string') || (typeof intervalEnd === 'string'))
            {
                var curMoment = moment();
                var strDate = curMoment.format('YYYY-MM-DD');

                if(typeof intervalStart === 'string')
                {
                    intervalStart = moment(strDate + ' ' + intervalStart);
                }

                if(typeof intervalEnd === 'string')
                {
                    intervalEnd = moment(strDate + ' ' + intervalEnd);
                }
            }

            var diff = intervalEnd.diff(intervalStart);

            var minutes = Math.floor(diff / 60000);
            var hours = Math.floor(minutes / 60);
            minutes -= hours * 60;

            var result = '';

            if(hours)
            {
                result += hours + ' ' + getCountString(hours, ['час', 'часа', 'часов']);
            }

            if(minutes)
            {
                result += ' ' + minutes + ' ' + getCountString(minutes, ['минута', 'минуты', 'минут']);
            }

            return result;
        }

        return {
            formatTime: formatTime,
            formatMinutes: formatMinutes,
            getDurationString: getDurationString,
            getMinutesByTime: getMinutesByTime,
        }
    })();

    var Color = (function()
    {
        function lighten(initColor, value){
            var color = tinycolor(initColor),
                coef = 1 - Math.pow((color.getBrightness() / 255), 3);

            return color.lighten(value * coef).toString();
        }

        function darken(initColor, value){
            var color = tinycolor(initColor),
                coef = color.getBrightness() / 255;

            return color.darken(value * coef).toString();
        }

        return {
            lighten: lighten,
            darken: darken,
        };
    })();

    return {
        Date: Date,
        Color: Color,

        getParamsObject: getParamsObject,
        getFio: getFio,
        getFullName: getFullName,
        getCountString: getCountString,
        clone: clone,

        sessid: sessid,
        postFormAction: postFormAction
    }
})();