<?

namespace Mmit\NewSmile\Notice;

class Helper
{
    public static function getNotificationTokenInfo($token)
    {
        $deviceSepPos = strpos($token, '#');
        return [
            'DEVICE' => substr($token,0, $deviceSepPos),
            'CLEAN_TOKEN' => substr($token, $deviceSepPos + 1)
        ];
    }
}