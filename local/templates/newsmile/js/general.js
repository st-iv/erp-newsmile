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

        return {
            formatTime: formatTime,
            formatMinutes: formatMinutes,
        }
    })();

    return {
        Date: Date,
        getParamsObject: getParamsObject,
        sessid: sessid,
        postFormAction: postFormAction
    }
})();