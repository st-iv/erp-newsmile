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
            var paramName = pairArray[0];
            var paramValue = decodeURI(pairArray[1]);
            
            if(result[paramName])
            {
                if(Array.isArray(result[paramName]))
                {
                    result[paramName].push(paramValue);
                }
                else
                {
                    result[paramName] = [result[paramName], paramValue];
                }
            }
            else
            {
                result[paramName] = paramValue;
            }
        });

        return result;
    }

    function serializeInObject(formNode)
    {
        return getParamsObject($(formNode).serialize());
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

    function getCamelCase(str, isUpper = true)
    {
        if(!str.length) return str;

        /*separators = separators || ['_', '-', '/'];
        var regExp = new RegExp('[\\' + separators.join('\\') + ']([A-Za-z])', 'g');*/

        var result = str.toLowerCase().replace(/[^A-Z^a-z]([A-Za-z])/g, function(match, p1)
        {
            return p1.toUpperCase();
        });

        if(isUpper)
        {
            result = result[0].toUpperCase() + result.substr(1);
        }

        return result;
    }

    var lastId = 0;

    function uniqueId(prefix = 'generated-id-')
    {
        return ++lastId + prefix;
    }

    function ucfirst(str)
    {
        var f = str.charAt(0).toUpperCase();
        return f + str.substr(1, str.length-1);

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

        var ruMonthsGen = ['января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'];
        var ruWeekdays = ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'];

        function formatDate(date, format)
        {
            var dateMoment = moment(date);
            format = format.replace('ru_month_gen', ruMonthsGen[dateMoment.get('month')]).replace('ru_weekday', ruWeekdays[dateMoment.get('weekday')]);
            return dateMoment.format(format);
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
            formatDate: formatDate
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
        getCamelCase: getCamelCase,
        clone: clone,
        uniqueId: uniqueId,
        ucfirst: ucfirst,
        serializeInObject: serializeInObject,

        sessid: sessid,
        postFormAction: postFormAction
    }
})();