<?

namespace Mmit\NewSmile;

use Bitrix\Main\Diag\Debug;

/**
 * Реализует разбиение кода на области (area) ajax загрузки. Области делятся на блоки (block) и чанки (chunk).
 * Блок - это область первого уровня. Компоненты AdvancedComponent обязательно должны быть включены внутрь
 * блока для возможности использовать их в ajax запросах, иначе они просто не будут выполняться (при ajax запросах).
 *
 * Чанк - это область 2-го уровня и далее.
 *
 * При запросах из js в качестве кода области можно указать только id блока, либо id блока и id чанка, разделенные точкой.
 *
 * Class Ajax
 * @package Mmit\NewSmile
 */
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
            echo '<div ' . static::getAreaCodeAttr() . '>';
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


            if(static::getAreaParam('SHOW_AREA_ID'))
            {
                echo '</div>';
            }

            if(static::getAreaParam('WRITE_CONTENT'))
            {
                static::$result['content'][static::getAreaCode()] = ob_get_contents();
                static::$isContentRecording = false;
            }

            $currentArea = array_pop(static::$areasStack);

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
        $result = false;

        if(!static::isAjaxQuery())
        {
            return false;
        }

        $areaAddress = explode('.', $_REQUEST['area']);
        $countAreaAddress = count($areaAddress);

        $curAreaId = static::getAreaParam('AREA_ID');

        if($countAreaAddress == 1)
        {
            $requestedBlockId = $areaAddress[0];
            $result = ((count(static::$areasStack) == 1) && ($curAreaId == $requestedBlockId));
        }
        elseif($countAreaAddress == 2)
        {
            $requestedBlockId = $areaAddress[0];
            $requestedChunkId = $areaAddress[1];

            // если в запросе указан блок и чанк, проверяем id и того, и другого
            $result = ((count(static::$areasStack) > 1) && ($requestedChunkId == $curAreaId)
                && (static::getBlockParam('AREA_ID') == $requestedBlockId));
        }

        return $result;
    }

    public static function isCurrentBlockRequested()
    {
        if(!static::isAjaxQuery())
        {
            return false;
        }

        $areaAddress = explode('.', $_REQUEST['area']);
        $requestedBlockId = $areaAddress[0];

        return ($requestedBlockId && ($requestedBlockId == static::getBlockParam('AREA_ID')));
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

    public static function getAreaParam($paramName, $level = 1)
    {
        return static::$areasStack[count(static::$areasStack) - $level][$paramName];
    }

    public static function getBlockParam($paramName)
    {
        return static::$areasStack[0][$paramName];
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

    public static function getAreaCode()
    {
        $areaCode = static::getBlockParam('AREA_ID');

        if(count(static::$areasStack) > 1)
        {
            $areaCode .= '.' . static::getAreaParam('AREA_ID');
        }

        return $areaCode;
    }

    public static function getAreaCodeAttr()
    {
        return 'data-is-ajax-area="Y" data-ajax-area="' . static::getAreaCode() . '"';
    }

    public static function getLoaderClass()
    {
        return 'js-ajax-load';
    }
}