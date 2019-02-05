<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>
<?
if($APPLICATION->GetCurPage() != '/')
{
    $application = \Mmit\NewSmile\Application::getInstance();
    ?>

    </div>

    <?
    //\Bitrix\Main\Page\Asset::getInstance()->addJs('https://unpkg.com/babel-standalone@6/babel.min.js');
    //$APPLICATION->ShowProperty('REACT_COMPONENTS');
}   ?>

<?$application->includeReact();?>
</body>
</html>