<?
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
	die();

?><?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog.php");?>
<!DOCTYPE html>
<html>
	<head>
		<?$APPLICATION->ShowHead();?>
		<title><?$APPLICATION->ShowTitle();?></title>
        <?CJSCore::Init(array("jquery"));?>
        <link href="<?=SITE_TEMPLATE_PATH?>/js/jquery.contextMenu.min.css" rel="stylesheet">
        <link href="<?=SITE_TEMPLATE_PATH?>/js/jquery-ui.min.css" rel="stylesheet">
        <script src="<?=SITE_TEMPLATE_PATH?>/js/jquery.contextMenu.min.js"></script>
        <script src="<?=SITE_TEMPLATE_PATH?>/js/jquery.ui.position.min.js"></script>
        <script src="<?=SITE_TEMPLATE_PATH?>/js/jquery-ui.min.js"></script>
	</head>
	<body>
		<div id="panel" style="width: 100%;">
			<?$APPLICATION->ShowPanel();?>
		</div>
