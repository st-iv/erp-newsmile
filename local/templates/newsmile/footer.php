<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>
    </div>

    <script src="https://unpkg.com/react@16/umd/react.development.js" crossorigin data-skip-moving="true"></script>
    <script src="https://unpkg.com/react-dom@16/umd/react-dom.development.js" crossorigin data-skip-moving="true"></script>

    <?
    \Bitrix\Main\Page\Asset::getInstance()->addJs('https://unpkg.com/babel-standalone@6/babel.min.js');
    $APPLICATION->ShowProperty('REACT_COMPONENTS');
    \Mmit\NewSmile\Application::getInstance()->renderReactComponents();
    ?>
</body>
</html>