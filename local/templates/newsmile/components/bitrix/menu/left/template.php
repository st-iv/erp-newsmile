<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>

<?if($arResult):?>
    <div class="left_menu">
        <div class="menu_btn_shld"></div>
        <div class="menu_btn_kartoteka"></div>
        <div class="menu_btn_options"></div>
    </div>

    <div class="left_menu_vline"></div>

    <div class="left_menu_content">
        <div class="menu_shld_itmslst">

            <?foreach ($arResult as $item):?>
                <div class="menu_shld_item"><?=$item['TEXT']?></div>
            <?endforeach;?>

        </div>
    </div>
<?endif;?>