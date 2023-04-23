<?php

namespace ITGaziev\PriceCalc;
use Bitrix\Main\Loader;
use Bitrix\Main\Entity;

class Main
{
    public static function getListPrices()
    {
        Loader::includeModule('catalog');

        $rsGroup = \Bitrix\Catalog\GroupTable::getList();
        $arrIBlockTypes = array();
        $arrIBlockTypes['REFERENCE'][] = 'Выбрать тип цены';
        $arrIBlockTypes['REFERENCE_ID'][] = 0;
        while ($arGroup = $rsGroup->fetch()) {
            $arrIBlockTypes['REFERENCE'][] = '['.$arGroup['ID'] .'] ' . $arGroup['NAME'];
            $arrIBlockTypes['REFERENCE_ID'][] = $arGroup['ID'];
        }
        return $arrIBlockTypes;
    }

    public static function getTypeRule() {
        $arrIBlockTypes['REFERENCE'][] = '%';
        $arrIBlockTypes['REFERENCE_ID'][] = 'P';

        $arrIBlockTypes['REFERENCE'][] = 'Число';
        $arrIBlockTypes['REFERENCE_ID'][] = 'N';

        $arrIBlockTypes['REFERENCE'][] = 'Умножение';
        $arrIBlockTypes['REFERENCE_ID'][] = 'L';

        $arrIBlockTypes['REFERENCE'][] = 'Равно базовой цены';
        $arrIBlockTypes['REFERENCE_ID'][] = 'E';

        return $arrIBlockTypes;
    }

    public static function getPriceName($PRICE_ID) {
        Loader::includeModule('catalog');

        $rsGroup = \Bitrix\Catalog\GroupTable::getList();
        while ($arGroup = $rsGroup->fetch()) {
            if($arGroup['ID'] == $PRICE_ID) {
                return $arGroup['NAME'] . ' [' . $PRICE_ID . ']';
            }
        }

        return 'Не определено [' . $PRICE_ID . ']';
    }

    public static function process($options) {
        Loader::includeModule('catalog');
        Loader::includeModule('sale');
        $action = $options['action'];
        $debug = [];
        if($action == 'update') {
            $result = PriceCalcTable::getById($options['id']);
            $step_count = $options['step_count'];
            if($condition = $result->fetch()) {
                if ($condition['PARAMETERS']) $condition['PARAMETERS'] = unserialize($condition['PARAMETERS']);
                $numberPage = intval($options['page']);
 
                $PRICE_ID = $condition['PRICE_TYPE'];
                $PRICE_BASE = $condition['BASE_PRICE_TYPE'];
                $TYPE_RULE = $condition['PARAMETERS']['TYPE_RULE'];
                $NUMBER = $condition['PARAMETERS']['NUMBER'];

                $prices = \CPrice::GetList(array(), array("CATALOG_GROUP_ID" => $PRICE_BASE));

                $arResult = [];

                while ($arr = $prices->Fetch()) {
                    $arResult[] = $arr;
                }
                if(count($arResult) > $step_count) {
                    $pages = array_chunk($arResult, $step_count);
                } else {
                    $pages[] = $arResult;
                }
                
                $options['total_item'] = count($arResult);
                if(isset($pages[$numberPage])) {
                    $curPage = $pages[$numberPage];

                    foreach ($curPage as $item) {
                        $price = \CPrice::GetList(array(), array("PRODUCT_ID" => $item['PRODUCT_ID'], "CATALOG_GROUP_ID" => $PRICE_ID));

                        $calcPrice = self::calculatePrice($TYPE_RULE, $NUMBER, $item['PRICE']);

                        $arFields = [
                            'PRODUCT_ID' => $item['PRODUCT_ID'],
                            'PRICE' => $calcPrice,
                            'CATALOG_GROUP_ID' => $PRICE_ID,
                            'CURRENCY' => 'RUB'
                        ];

                        if($arr = $price->Fetch()) {
                            \CPrice::Update($arr["ID"], $arFields);
                        } else {
                            \CPrice::Add($arFields);
                        }

                        $debug[] = $arFields;
                    }

                    $options['page'] = $numberPage + 1;

                } else {

                    $options['action'] = 'success';

                }
                if(count($arResult) > $step_count) {
                    $last_item = ($numberPage + 1) * intval($step_count);
                } else {
                    $last_item = count($arResult);
                }
                $options['procent'] = ceil($last_item / count($arResult) * 100);
                $options['last_item'] = $last_item;
            }
        }
        //self::debug([$debug, $options]);
        self::sendMessage($options);
    }

    public static function PriceEventHandler ($event) {

    }

    public static function sendMessage($options) {
        global $APPLICATION;
        $APPLICATION->RestartBuffer();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($options);
        die;
    }

    public static function calculatePrice($PRICE_RULE, $NUMBER, $PRICE) {
        switch($PRICE_RULE) {
            case 'P': //%
                return ceil(((floatval($NUMBER) / 100) + 1) * floatval($PRICE));
            case 'N': // Число
                return ceil(floatval($NUMBER) + floatval($PRICE));
            case 'L': // Умножение
                return ceil(floatval($NUMBER) * floatval($PRICE));
            default: return $PRICE;
        }
    }

    public static function debug($ar) {
        echo '<pre>'; print_r($ar); echo '</pre>';
    }
}
