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

    /**
     * Возвращает дату в указанном формате с использованием дополнительных параметров:
     * F_ru_gen - название месяца на русском в родительном падеже
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
        $format = str_replace('F_ru_gen', static::$ruMonthNamesGenitive[$monthIndex-1], $format);

        return date($format);
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
            if($diff->invert)
            {
                $result = 'Завтра';
            }
            else
            {
                $result = 'Вчера';
            }
        }
        else
        {
            $result = static::date('d F_ru_gen Y', $date->getTimestamp());
        }

        $result .= ', в ' . date('H:i', $date->getTimestamp());

        return $result;
    }
}