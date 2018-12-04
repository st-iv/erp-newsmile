<?

namespace Mmit\NewSmile\Date;


use Bitrix\Main\ArgumentException;
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

    const TIME_INTERVAL_FORMAT_WORDS = 1;
    const TIME_INTERVAL_FORMAT_COLON = 2;

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

    public static function getAge(Date $birthDayParam, $bNumberOnly = false)
    {
        $birthDay = new \DateTime();
        $birthDay->setTimestamp($birthDayParam->getTimestamp());

        if(!isset(static::$currentDate))
        {
            static::$currentDate = new \DateTime();
        }

        $dateDiff = static::$currentDate->diff($birthDay);

        $yearsCount = $dateDiff->y;

        if(!$bNumberOnly)
        {
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
     * Проверяет равны ли между собой два объекта DateTime
     * @param \DateTime | DateTime $dateTimeA
     * @param \DateTime | DateTime $dateTimeB
     *
     * @return bool
     */
    public static function isDateTimeEquals($dateTimeA, $dateTimeB)
    {
        return ($dateTimeA->getTimestamp() == $dateTimeB->getTimestamp());
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

    public static function formatTimeInterval($seconds, $format = self::TIME_INTERVAL_FORMAT_COLON)
    {
        $result = '';

        $time = new \DateTime('midnight');
        $time->setTime(0, 0, $seconds);

        /*$hours = floor($seconds / 3600);
        $seconds -= $hours * 3600;

        $minutes = floor($seconds / 60);
        $seconds = $seconds % 60;*/

        if($format == self::TIME_INTERVAL_FORMAT_WORDS)
        {
            $hours = (int)$time->format('H');
            $minutes = (int)$time->format('i');
            $seconds = (int)$time->format('s');

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
        }
        elseif ($format == self::TIME_INTERVAL_FORMAT_COLON)
        {
            $result = $time->format('H:i:s');
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

    /**
     * Возвращает разницу между датами в минутах
     * @param \DateTime | DateTime $dateA
     * @param \DateTime | DateTime $dateB
     *
     * @return int
     */
    public static function getDiffMinutes($dateA, $dateB)
    {
        return abs(($dateA->getTimestamp() - $dateB->getTimestamp()) / 60);
    }

    public static function isBefore($dateA, $dateB, $bAndEqual = false)
    {
        $compareResult = static::compareDates($dateA, $dateB);

        return (($compareResult === -1) && (!$bAndEqual || ($compareResult === 0)));
    }

    public static function isAfter($dateA, $dateB, $bAndEqual = false)
    {
        $compareResult = static::compareDates($dateA, $dateB);

        return (($compareResult === 1) && (!$bAndEqual || ($compareResult === 0)));
    }

    public static function isEqual($dateA, $dateB)
    {
        return (static::compareDates($dateA, $dateB) === 0);
    }

    protected static function compareDates($dateA, $dateB)
    {
        if(is_string($dateA))
        {
            $timestampA = strtotime($dateA);
        }
        elseif($dateA instanceof \DateTime || $dateA instanceof Date)
        {
            $timestampA = $dateA->getTimestamp();
        }
        else
        {
            throw new ArgumentException('В качестве аргумента dateA функции сравнения дат ожидается DateTime, Date или строка', 'dateA');
        }

        if(is_string($dateB))
        {
            $timestampB = strtotime($dateB);
        }
        elseif($dateB instanceof \DateTime || $dateB instanceof Date)
        {
            $timestampB = $dateB->getTimestamp();
        }
        else
        {
            throw new ArgumentException('В качестве аргумента dateB функции сравнения дат ожидается DateTime, Date или строка', 'dateB');
        }

        $result = 0;

        if($timestampA > $timestampB)
        {
            $result = 1;
        }
        elseif($timestampA < $timestampB)
        {
            $result = -1;
        }

        return $result;
    }
}