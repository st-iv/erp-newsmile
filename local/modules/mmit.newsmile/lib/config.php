<?

namespace Mmit\NewSmile;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

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

    public static function getOption($name, $default = '')
    {
        return Option::get('mmit.newsmile', $name, $default);
    }

    public static function printAllOptions()
    {
        $allOptions = [];

        foreach (static::getOptionsPageConfig() as $optionTab)
        {
            foreach ($optionTab['OPTIONS'] as $option)
            {
                $allOptions[$option[0]] = $option[1];
            }
        }
    }

    public static function getOptionsPageConfig()
    {
        return array(

            array(
                'DIV' => 'sms_auth_tab',
                'TAB' => Loc::getMessage('MMIT_NEWSMILE_TAB_SMS'),
                'OPTIONS' => array(
                    array(
                        'sms_auth_login',
                        Loc::getMessage('MMIT_NEWSMILE_OPTION_SMS_LOGIN'),
                        '',
                        array('text', 20)
                    ),
                    array(
                        'sms_auth_pass',
                        Loc::getMessage('MMIT_NEWSMILE_OPTION_SMS_PASSWORD'),
                        '',
                        array('text', 20)
                    ),
                    array(
                        'sms_auth_sender',
                        Loc::getMessage('MMIT_NEWSMILE_OPTION_SMS_SENDER'),
                        'ERP New Smile',
                        array('text', 20)
                    ),
                    array(
                        'sms_auth_message',
                        Loc::getMessage('MMIT_NEWSMILE_OPTION_SMS_MESSAGE'),
                        '',
                        array('textarea', 3, 50)
                    ),
                    array(
                        'sms_auth_code_length',
                        Loc::getMessage('MMIT_NEWSMILE_OPTION_SMS_CODE_LENGTH'),
                        4,
                        array('text', 1)
                    ),
                    array(
                        'sms_auth_code_expiration',
                        Loc::getMessage('MMIT_NEWSMILE_OPTION_SMS_CODE_EXPIRATION'),
                        60,
                        array('text', 5)
                    ),
                    array(
                        'sms_auth_attempt_limit',
                        Loc::getMessage('MMIT_NEWSMILE_OPTION_SMS_ATTEMPT_LIMIT'),
                        5,
                        array('text', 3)
                    ),
                    array(
                        'sms_auth_sms_limit',
                        Loc::getMessage('MMIT_NEWSMILE_OPTION_SMS_SMS_LIMIT'),
                        3,
                        array('text', 3)
                    ),
                    array(
                        'sms_auth_sms_limit_duration',
                        Loc::getMessage('MMIT_NEWSMILE_OPTION_SMS_SMS_LIMIT_DURATION'),
                        5,
                        array('text', 5)
                    ),
                    array(
                        'sms_auth_block_duration',
                        Loc::getMessage('MMIT_NEWSMILE_OPTION_SMS_BLOCK_DURATION'),
                        5,
                        array('text', 5)
                    ),
                )
            ),

        );
    }
}