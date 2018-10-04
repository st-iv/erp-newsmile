<?

namespace Mmit\NewSmile;

use Bitrix\Main\Config\Option;

class Config
{
    public static function getScheduleStartTime()
    {
        return Option::get('mmit.newsmile', 'start_time_schedule', '09:00');
    }

    public static function getScheduleEndTime()
    {
        return Option::get('mmit.newsmile', 'end_time_schedule', '21:00');
    }

    public static function getClinicId()
    {
        return 1; // stub
    }

    public static function getSiteId()
    {
        return 's1';
    }
}