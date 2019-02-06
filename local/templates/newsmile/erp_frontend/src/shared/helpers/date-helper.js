import GeneralHelper from './general-helper.js';
import moment from 'moment'

let ruMonthsGen = ['января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'];
let ruWeekdays = ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'];

let DateHelper = {

    formatTime: function(ts)
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
    },

    formatMinutes: function(minutes)
    {
        var hours = Math.floor(minutes / 60);
        var time = ((hours < 10) ? '0' + hours : hours);
        minutes -= hours * 60;
        time += ':' + ((minutes < 10) ? '0' + minutes : minutes);
        return time;
    },

    formatDate: function(date, formatTo, formatFrom = null)
    {
        var dateMoment = moment(date, formatFrom);
        formatTo = formatTo.replace('ru_month_gen', ruMonthsGen[dateMoment.get('month')]).replace('ru_weekday', ruWeekdays[dateMoment.get('weekday')]);
        return dateMoment.format(formatTo);
    },

    getMinutesByTime: function(time)
    {
        var timeParts = time.split(':');
        var hours = Number(timeParts[0]);
        var minutes = Number(timeParts[1]);
        return hours * 60 + minutes;
    },

    getDurationString: function(intervalStart, intervalEnd)
    {
        if((typeof intervalStart === 'string') || (typeof intervalEnd === 'string'))
        {
            let curMoment = moment();
            let strDate = curMoment.format('YYYY-MM-DD');

            if(typeof intervalStart === 'string')
            {
                intervalStart = moment(strDate + ' ' + intervalStart);
            }

            if(typeof intervalEnd === 'string')
            {
                intervalEnd = moment(strDate + ' ' + intervalEnd);
            }
        }

        let diff = intervalEnd.diff(intervalStart);

        let minutes = Math.floor(diff / 60000);
        let hours = Math.floor(minutes / 60);
        minutes -= hours * 60;

        let result = '';

        if(hours)
        {
            result += GeneralHelper.getCountString(hours, ['час', 'часа', 'часов']);
        }

        if(minutes)
        {
            result += ' ' + GeneralHelper.getCountString(minutes, ['минута', 'минуты', 'минут']);
        }

        return result;
    },

    getAge: function(birthday)
    {
        let yearsCount = moment().diff(moment(birthday), 'y');
        return GeneralHelper.getCountString(yearsCount, ['год', 'года', 'лет']);
    },

    getStandardIntervalTime: function(intervalTime)
    {
        let intervalMoment = moment(intervalTime, 'HH:mm');
        if(intervalMoment.get('m') % 30 === 15)
        {
            intervalMoment.add(-15, 'm');
        }

        return intervalMoment.format('HH:mm');
    }
};

export default DateHelper