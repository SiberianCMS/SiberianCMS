<?php
/**
 * Base class for localization
 *
 * @category  Zend
 * @package   Zend_Locale
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 */
class Siberian_Locale extends Zend_Locale
{

    /**
     * Class wide Euro Zone Locale Constants
     *
     * @var array $_euroZoneTerritoryDatas
     */
    private static $_euroZoneTerritoryDatas = array(
        'AT' => 'de_AT',
        'BE' => 'nl_BE',
        'CY' => 'el_CY',
        'DE' => 'de_DE',
        'EE' => 'et_EE',
        'ES' => 'es_ES',
        'FI' => 'fi_FI',
        'FR' => 'fr_FR',
        'GR' => 'el_GR',
        'IE' => 'en_IE',
        'IT' => 'it_IT',
        'LU' => 'fr_LU',
        'MT' => 'mt_MT',
        'NL' => 'nl_NL',
        'PT' => 'pt_PT',
        'SI' => 'sl_SI',
        'SK' => 'sk_SK'
    );

    public static function getEuroZoneTerritoryDatas() {
        return self::$_euroZoneTerritoryDatas;
    }

}
