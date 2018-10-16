<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>
<?
/**
 * @var array $interval
 */
?>
<?if($interval['OPERATIONS']):?>

    <div class="dClndr_popup_menu">
        <ul class="dClndr_pmenu dClndr_pmenu1">
            <?foreach ($interval['OPERATIONS'] as $operationCode => $operation):?>
                <li class="<?=($operation['VARIANTS'] ? 'dClndr_phasmenu' : '')?>" data-operation-code="<?=$operationCode?>">
                    <?=$operation['TITLE']?>

                    <?if($operation['VARIANTS']):?>
                        <ul class="dClndr_psubmenu">
                            <?foreach ($operation['VARIANTS'] as $variantCode => $variantTitle):?>
                                <li data-variant-code="<?=$variantCode?>"><?=$variantTitle?></li>
                            <?endforeach;?>
                        </ul>

                        <svg version="1.1" id="svg2" xmlns:cc="http://creativecommons.org/ns#" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:svg="http://www.w3.org/2000/svg" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 13.2 22.1" style="enable-background:new 0 0 13.2 22.1;" xml:space="preserve"><style type="text/css">.st0{clip-path:url(#SVGID_2_);}.st1{fill:none;stroke:#00D7D7;stroke-width:3;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;}</style><g id="g10" transform="matrix(1.3333333,0,0,-1.3333333,0,13.16664)"><g id="g12"><g><defs><rect id="SVGID_1_" x="-1.6" y="-9.4" width="13.2" height="22.1"/></defs><clipPath id="SVGID_2_"><use xlink:href="#SVGID_1_" style="overflow:visible;"/></clipPath><g id="g14" class="st0"><g id="g20" transform="translate(1.5,8.18)"><path id="path22" class="st1" d="M0.2-13.4l6.7,6.7L0,0.2"/></g></g></g></g></g></svg>
                    <?endif;?>
                </li>
            <?endforeach;?>
        </ul>
    </div>

<?endif;?>
