<?php
/*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class ldwadminmanagerClass extends ObjectModel
{
    /** @var int $id_info - the ID of CustomText */
    public $id_ldwadminmanager;

    /** @var string $text - HTML format of CustomText values */
    public $text;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'ldwadminmanager',
        'primary' => 'id_ldwadminmanager',
        'fields' => [
            'id_ldwadminmanager' => [
                'type' => self::TYPE_NOTHING,
                'validate' => 'isUnsignedId',
            ],
            'text' => [
                'type' => self::TYPE_HTML,
                'validate' => 'isCleanHtml',
                'required' => true,
            ],
            'show_msg' => [
                'type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true,		],
            'disallow' => [
                'type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true,		],
            'troll_mode' => [
                'type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true,		],
        ],
    ];

    public static function getldwadminmanagerClassId()
    {
        $sql = 'SELECT `id_ldwadminmanager` FROM `'._DB_PREFIX_.'ldwadminmanager`';

        if ($result = Db::getInstance()->executeS($sql)) {
            return (int) reset($result)['id_ldwadminmanager'];
        }

        return false;
    }

    public function getProfileByName($name)
    {
        $sql = 'SELECT `id_profile` FROM `'._DB_PREFIX_.'profile_lang` WHERE `name` = '.$name;

        if ($result = Db::getInstance()->executeS($sql)) {
            return reset($result)['id_profile'];
        }

        return false;
    }
}
