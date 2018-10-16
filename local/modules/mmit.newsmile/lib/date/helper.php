<?

namespace Mmit\NewSmile\Date;


use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;

class Helper
{
    /**
     * @var \DateTime
     */
    protected static $currentDate;
    protected static $ruMonthNamesGenitive = array('января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря');
    protected static $ruWeekdayNames = array('Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота', 'Воскресенье');

    /**
     * Возвращает дату в указанном формате с использованием дополнительных параметров:
     * F_ru_gen - название месяца на русском в родительном падеже;
     * l_ru - название дня недели на русском языке
     *
     * @param string $format
     * @param int|null $time
     *
     * @return false|string
     */
    public static function date($format, $time = null)
    {
        if($time == null)
        {
            $time = time();
        }

        $monthIndex = date('n', $time);
        $weekdayIndex = date('N', $time);

        $format = str_replace(
            array('F_ru_gen', 'l_ru'),
            array(
                static::$ruMonthNamesGenitive[$monthIndex-1],
                static::$ruWeekdayNames[$weekdayIndex-1]
            ),
            $format
        );

        return date($format, $time);
    }

    public static function getAge(Date $birthDayParam)
    {
        $birthDay = new \DateTime();
        $birthDay->setTimestamp($birthDayParam->getTimestamp());

        if(!isset(static::$currentDate))
        {
            static::$currentDate = new \DateTime();
        }

        $dateDiff = static::$currentDate->diff($birthDay);

        $yearsCount = $dateDiff->y;
        $yearsCountMod = $yearsCount % 10;

        if($yearsCountMod == 1)
        {
            $yearsCount .= ' год';
        }
        elseif (($yearsCountMod >= 2) && ($yearsCountMod <= 4))
        {
            $yearsCount .= ' года';
        }
        else
        {
            $yearsCount .=  ' лет';
        }

        return $yearsCount;
    }

    /**
     * Возвращает объект DateTime php по DateTime или Date объекту битрикса
     * @param DateTime|Date $bitrixDate
     *
     * @return \DateTime
     */
    public static function getPhpDateTime($bitrixDate)
    {
        $phpDate = new \DateTime();
        $phpDate->setTimestamp($bitrixDate->getTimestamp());
        return $phpDate;
    }

    /**
     * Возвращает дату со временем в формате NewSmile
     * @param $date
     *
     * @return string
     */
    public static function formatDateTime($date)
    {
        if(is_string($date))
        {
            $date = new \DateTime($date);
        }
        elseif ($date instanceof Date || $date instanceof DateTime)
        {
            $date = static::getPhpDateTime($date);
        }

        /**
         * @var \DateTime $date
         */

        if(!isset(static::$currentDate))
        {
            static::$currentDate = new \DateTime();
        }


        $diff = static::$currentDate->diff($date);
        if($diff->d == 0)
        {
            $result = 'Сегодня';
        }
        elseif ($diff->d == 1)
        {
            $result = ($diff->invert ? 'Вчера' : 'Завтра');
        }
        else
        {
            $result = static::date('d F_ru_gen Y', $date->getTimestamp());
        }

        $result .= ', в ' . date('H:i', $date->getTimestamp());

        return $result;
    }

    public static function formatTimeInterval($seconds)
    {
        $result = '';

        $hours = floor($seconds / 3600);
        $seconds -= $hours * 3600;

        $minutes = floor($seconds / 60);
        $seconds = $seconds % 60;

        if($hours)
        {
            $result = $hours . ' ' . static::getWordByNumber($hours, ['час', 'часа', 'часов']);
        }

        if($minutes)
        {
            $result .= ($result ? ' ' : '');
            $result .= $minutes . ' ' . static::getWordByNumber($minutes, ['минута', 'минуты', 'минут']);
        }

        if($seconds)
        {
            $result .= ($result ? ' ' : '');
            $result .= ' ' . $seconds . ' ' . static::getWordByNumber($seconds, ['секунда', 'секунды', 'секунд']);
        }

        return $result;
    }

    private static function getWordByNumber($number, array $variations)
    {
        $result = $variations[0];

        if(($number > 1) && ($number < 5))
        {
            $result = $variations[1];
        }
        elseif(!$number || ($number >= 5))
        {
            $result = $variations[2];
        }

        return $result;
    }
}