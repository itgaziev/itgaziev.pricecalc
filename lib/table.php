<?php
namespace ITGaziev\PriceCalc;

use \Bitrix\Main\Entity;
use \Bitrix\Main\Type;

class PriceCalcTable extends Entity\DataManager {
    public static function getTableName() {
        return 'b_itgaziev_pricecalc';
    }

    public static function getUfId() {
        return 'PRICE_CALC';
    }

    public static function getMap() {
        return [
            new Entity\IntegerField('ID', array('primary' => true, 'autocomplete' => true)),
            new Entity\StringField('ACTIVE', array('required' => true)),
            new Entity\DateTimeField('TIME_CREATE', array('required' => true)),
            new Entity\StringField('NAME', array('required' => true)),

            new Entity\IntegerField("PRICE_TYPE"),
            new Entity\IntegerField("BASE_PRICE_TYPE"),
            new Entity\TextField("PARAMETERS"),
        ];
    }
}