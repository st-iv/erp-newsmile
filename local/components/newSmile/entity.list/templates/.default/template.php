<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
use Mmit\NewSmile\Helpers;
?>
<table id="entities-list" border="1" data->
    <tr>
        <td class="section-list">
            <?
            $rootListTemplate = '<ul>#LIST_CONTENT#</ul>';
            $listTemplate = '<li class="section-service" data-section-url="#URL#" data-edit-url="#EDIT_URL#">#NAME#<ul>#LIST_CONTENT#</ul></li>';
            $elementTemplate = '<li class="section-service" data-section-url="#URL#" data-edit-url="#EDIT_URL#">#NAME#</li>';
            Helpers::printTree($arResult['SECTIONS'], $rootListTemplate, $listTemplate, $elementTemplate);?>
        </td>

        <td class="element-list">

        </td>
    </tr>
    <tr>
        <td colspan="2" id="load-content">

        </td>
    </tr>
</table>
