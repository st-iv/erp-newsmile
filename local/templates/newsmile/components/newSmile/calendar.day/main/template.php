<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>
<?
/**
 * @var \Mmit\NewSmile\Component\AdvancedComponent $component
 */

\Bitrix\Main\Page\Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . '/js/day_calendar.js');
?>

<div class="main_content_center">
    <?$component->renderReactComponent($arResult)?>
</div>
