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
        var units = number % 10;
        var variantIndex = 2;

        if(units === 1)
        {
            variantIndex = 0;
        }
        else if((units >= 2) && (units <= 4))
        {
            variantIndex = 1;
        }

        return number + ' ' + variants[variantIndex];
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

    function forEachObj(object, handler, from = null, end = null)
    {
        var bActiveKey = (from === null);

        for(let key in object)
        {
            if((end !== null) && (key === end)) break;

            if(key === from)
            {
                bActiveKey = true;
            }

            if(bActiveKey)
            {
                if(object.hasOwnProperty(key))
                {
                    handler(object[key], key, object);
                }
            }
        }
    }

    function filterObj(object, handler)
    {
        const result = {};
        forEachObj(object, function(value, key, object)
        {
            if(handler(value, key, object))
            {
                result[key] = value;
            }
        });

        return result;
    }

    function mapObj(object, handler)
    {
        var result = [];

        forEachObj(object, (value, key) =>
        {
            result.push(handler(value, key, object));
        });

        return result;
    }

    function formatPhone(rawPhone)
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
    }

    /**
     * Возвращает позицию цифры телефона с определённым номером в отформатированном варианте телефона.
     * @param numNumber
     * @returns {*}
     */
    function getPhoneNumFormattedPos(numNumber)
    {
        let char = (numNumber === 10) ? '_' : window.String(numNumber);
        return formatPhone('0123456789_').indexOf(char);
    }

    function isEqual(var1, var2)
    {
        var result = true;

        if((typeof var1 === 'object') && (typeof var2 === 'object'))
        {
            for (var key in var1)
            {
                if(!var1.hasOwnProperty(key)) continue;

                if(!isEqual(var1[key], var2[key]))
                {
                    result = false;
                    break;
                }
            }
        }
        else
        {
            result = var1 === var2;
        }

        return result;
    }

    function isEqualArrays(ar1, ar2)
    {
        var result = true;
        ar1.forEach(function(element1, index)
        {
            var element2 = ar2[index];
        });
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

        function formatDate(date, formatTo, formatFrom = null)
        {
            var dateMoment = moment(date, formatFrom);
            formatTo = formatTo.replace('ru_month_gen', ruMonthsGen[dateMoment.get('month')]).replace('ru_weekday', ruWeekdays[dateMoment.get('weekday')]);
            return dateMoment.format(formatTo);
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
                result += getCountString(hours, ['час', 'часа', 'часов']);
            }

            if(minutes)
            {
                result += ' ' + getCountString(minutes, ['минута', 'минуты', 'минут']);
            }

            return result;
        }

        function getAge(birthday)
        {
            var yearsCount = moment().diff(moment(birthday), 'y');
            return getCountString(yearsCount, ['год', 'года', 'лет']);
        }

        return {
            formatTime: formatTime,
            formatMinutes: formatMinutes,
            getDurationString: getDurationString,
            getMinutesByTime: getMinutesByTime,
            formatDate: formatDate,
            getAge: getAge
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

    var String = (function()
    {
        function insert(substr, target, pos)
        {
            return target.substr(0, pos) + substr + target.substr(pos);
        }

        return {
            insert: insert
        }
    })();

    return {
        Date: Date,
        Color: Color,
        String: String,

        getParamsObject: getParamsObject,
        getFio: getFio,
        getFullName: getFullName,
        getCountString: getCountString,
        getCamelCase: getCamelCase,
        clone: clone,
        uniqueId: uniqueId,
        ucfirst: ucfirst,
        serializeInObject: serializeInObject,
        forEachObj: forEachObj,
        filterObj: filterObj,
        mapObj: mapObj,
        isEqual: isEqual,
        formatPhone: formatPhone,
        getPhoneNumFormattedPos: getPhoneNumFormattedPos,

        sessid: sessid,
        postFormAction: postFormAction
    }
})();