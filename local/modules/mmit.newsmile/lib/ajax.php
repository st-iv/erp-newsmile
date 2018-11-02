<?

namespace Mmit\NewSmile;

use Bitrix\Main\Diag\Debug;

class Ajax
{
    protected static $areasStack = array();
    protected static $requestedArea = '';
    protected static $result = array();
    protected static $isContentRecording = false;

    public static function start($areaId, $bWriteContent = true, $bShowAreaId = true)
    {
        global $APPLICATION;

        static::addArea($areaId, $bWriteContent, $bShowAreaId);

        if($bWriteContent && !static::$isContentRecording && static::isAreaRequested())
        {
            $APPLICATION->RestartBuffer();
            ob_start();
            static::$isContentRecording = true;
        }

        if($bShowAreaId)
        {
            echo '<div ' . static::getAreaIdAttr() . '>';
        }

        if(static::isCurrentAreaRequested())
        {
            static::$requestedArea = $areaId;
        }
    }

    public static function finish()
    {


        if(static::isAreaRequested())
        {
            $isCurrentAreaRequested = static::isCurrentAreaRequested();
            $currentArea = array_pop(static::$areasStack);

            if($currentArea['SHOW_AREA_ID'])
            {
                echo '</div>';
            }

            if($currentArea['WRITE_CONTENT'])
            {
                static::$result['content'][$currentArea['AREA_ID']] = ob_get_contents();
                static::$isContentRecording = false;
            }

            foreach ($currentArea as $paramName => $paramValue)
            {
                if(($paramName != 'SHOW_AREA_ID') && ($paramName != 'AREA_ID') && ($paramName != 'WRITE_CONTENT'))
                {
                    static::$result[$paramName] = $paramValue;
                }
            }

            if($isCurrentAreaRequested)
            {
                global $APPLICATION;
                $APPLICATION->RestartBuffer();
                static::$result['success'] = true;
                die(json_encode(static::$result));
            }
        }
        else
        {
            $currentArea = array_pop(static::$areasStack);

            if($currentArea['SHOW_AREA_ID'])
            {
                echo '</div>';
            }
        }
    }

    public static function isAjaxQuery()
    {
        return (($_REQUEST['ajax'] == 'Y') && !empty($_REQUEST['area']));
    }

    protected static function isCurrentAreaRequested()
    {
        return (static::isAjaxQuery() && ($_REQUEST['area'] == static::getAreaParam('AREA_ID')));
    }

    protected static function isParentAreaRequested()
    {
        return !empty(static::$requestedArea);
    }

    public static function isAreaRequested()
    {
        return (static::isParentAreaRequested() || static::isCurrentAreaRequested());
    }

    protected static function addArea($areaId, $bWriteContent, $bShowAreaId)
    {
        static::$areasStack[] = array(
            'AREA_ID' => $areaId,
            'SHOW_AREA_ID' => $bShowAreaId,
            'WRITE_CONTENT' => !static::$isContentRecording && $bWriteContent,
        );
    }

    public static function getAreaParam($paramName)
    {
        return static::$areasStack[count(static::$areasStack) - 1][$paramName];
    }

    /**
     * Добавляет параметр в json ответ сервера
     * @param $paramName
     * @param $paramValue
     */
    public static function setAreaParam($paramName, $paramValue)
    {
        static::$areasStack[count(static::$areasStack) - 1][$paramName] = $paramValue;
    }

    /**
     * Добавляет js скрипт в динамическую загрузку
     * @param string $url
     */
    public static function addJs($url)
    {
        $jsList = static::getAreaParam('scripts');

        if(!in_array($url, $jsList))
        {
            $jsList[] = $url;
        }

        static::setAreaParam('scripts', $jsList);
    }

    public static function getAreaIdAttr()
    {
        return 'data-is-ajax-area="Y" data-ajax-area="' . static::getAreaParam('AREA_ID') . '"';
    }

    public static function getLoaderClass()
    {
        return 'js-ajax-load';
    }
}