<?php
/**
 * 2007-2019 PrestaShop.
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
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2019 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
use PrestaShop\PrestaShop\Adapter\Entity\Access;
use PrestaShop\PrestaShop\Adapter\Entity\Employee;
use PrestaShop\PrestaShop\Adapter\Entity\Profile;

require_once _PS_MODULE_DIR_.'ldwadminmanager/classes/ldwadminmanagerClass.php';

class Ldwadminmanager extends Module
{
    private $templateFile;

    public function __construct()
    {
        $this->name = 'ldwadminmanager';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Lamour du web';
        $this->need_instance = 0;

        // Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6).
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('LDW Admin manager');
        $this->description = $this->l('Gestion des permissions');
        $this->templateFile = 'module:ldwadminmanager\views\templates\hook\message.tpl';
        $this->ps_versions_compliancy = [
            'min' => '1.7',
            'max' => _PS_VERSION_,
        ];
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update.
     */
    public function install()
    {
        Configuration::updateValue('text', Tools::getValue('text'));

        return parent::install() &&
        $this->install_db() &&
        $this->installFixtures() &&
        $this->registerHook('header') &&
        $this->registerHook('displayDashboardTop') &&
        $this->registerHook('displayBackOfficeFooter') &&
        $this->registerHook('backOfficeHeader') &&
        $this->createNewProfile();
    }

    /**
     * uninstall module.
     *
     * @return bool
     */
    public function uninstall()
    {
        Configuration::deleteByName('text');
        // Configuration::deleteByName( 'LDWADMINMANAGER_PERMISSIONS' );
        return parent::uninstall() &&
        $this->uninstall_db();
    }

    /**
     * Install database.
     *
     * @return string
     */
    public function install_db()
    {
        $return = true;
        $return &= Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ldwadminmanager` (`id_ldwadminmanager` INT UNSIGNED NOT NULL AUTO_INCREMENT, `text` LONGTEXT NOT NULL, `show_msg` BOOLEAN, `disallow` BOOLEAN, `troll_mode` BOOLEAN, PRIMARY KEY (`id_ldwadminmanager`)) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;');

        return $return;
    }

    /**
     * Create new custom profile.
     */
    public function createNewProfile()
    {
        $profiles = Profile::getProfiles(1);
        $ids = end($profiles);
        $new_id = $ids['id_profile'] + 1;
        $save = true;
        $new_profile = new Profile($new_id, 1, 1);
        $new_profile->name = 'Client LDW';
        // $access = Access::sluggifyModule()
        $save &= $new_profile->save();

        return $save;
    }

    /* TODO: Set automaticaly permissions
    public function setProfilePermissions() {
        $roles = Access::getRoles( $this->context->employee->id_profile );
        foreach ($roles as $key => $value) {
            Access::addAccess($this->context->employee->id_profile, $value)
        }
        var_dump( $role );
    } */

    /**
     * Default Values.
     *
     * @return bool
     */
    public function installFixtures()
    {
        $saved = true;
        $adminmanager = new ldwadminmanagerClass();
        $adminmanager->text = '<p>Bonjour, votre compte est actuellement suspendu pour défaut de paiement - Merci de compléter votre mandat de prélèvement et de régulariser votre situation via le lien suivant <a target="_blank" href="https://pay.gocardless.com/AL0000YEK7VPBY" class="btn btn-warning">Paiement</a></p>';
        $adminmanager->show_msg = false;
        $adminmanager->disallow = false;
        $adminmanager->troll_mode = false;

        return $adminmanager->save();
    }

    /**
     * Uninstall database.
     *
     * @return string
     */
    public function uninstall_db()
    {
        return Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'ldwadminmanager`');
    }

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookBackOfficeHeader()
    {
        $profile = $this->getCurrentUserProfile();

        if ('SuperAdmin' !== $profile['name']) {
            $message = $this->getMessage();
            $is_show = (bool) $message['show_msg'];
            $disallow = (bool) $message['disallow'];

            if ($is_show && $disallow) {
                $this->context->controller->addCSS($this->_path.'views/css/back.css');
            }
        }
    }

    public function hookDisplayBackOfficeFooter()
    {
        $profile = $this->getCurrentUserProfile();

        if ('SuperAdmin' !== $profile['name']) {
            $message = $this->getMessage();
            $is_show = (bool) $message['show_msg'];
            $disallow = (bool) $message['disallow'];
            $troll = (bool) $message['troll_mode'];

            $this->context->smarty->assign(
                [
                    'troll' => $troll,
                ]
            );
            if ($is_show && $disallow) {
                return $this->display($this->local_path, 'views/templates/hook/modal.tpl');
            }
        }
    }

    public function getCurrentUserProfile()
    {
        return Profile::getProfile($this->context->employee->id_profile);
    }

    /**
     * Hook message on BO dashboard.
     */
    public function hookDisplayDashboardTop()
    {
        $profile = $this->getCurrentUserProfile();

        if ('SuperAdmin' !== $profile['name']) {
            $message = $this->getMessage();
            $is_show = (bool) $message['show_msg'];
            $disallow = (bool) $message['disallow'];
            $this->context->smarty->assign(
                [
                    'ldwmessage' => $message['text'],
                ]
            );
            if ('AdminDashboardController' === get_class($this->context->controller) && $is_show && !$disallow) {
                return $this->display($this->local_path, 'views/templates/hook/message.tpl');
            }
        }
    }

    /**
     * Get message from database.
     */
    public function getMessage()
    {
        $sql = 'SELECT * FROM `'._DB_PREFIX_.'ldwadminmanager`';

        return Db::getInstance()->getRow($sql);
    }

    /**
     * Load the configuration form.
     */
    public function getContent()
    {
        $output = '';
        // If values have been submitted in the form, process.
        if (Tools::isSubmit('submitLdwadminmanagerModule')) {
            $this->postProcess();
            $this->displayConfirmation($this->getTranslator()->trans('Configuration updated', [], 'Admin.Notifications.Success'));
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output &= $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output.$this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitLdwadminmanagerModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->fields_value = $this->getConfigFormValues();

        return $helper->generateForm([$this->getConfigForm()]);
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return [
            'form' => [
                'tinymce' => true,
                'legend' => [
                    'title' => $this->l('Message d\'information'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'hidden',
                        'name' => 'id_ldwadminmanager',
                    ],
                    [
                        'type' => 'textarea',
                        'label' => $this->l('Notification de paiement'),
                        'name' => 'text',
                        'cols' => 40,
                        'rows' => 20,
                        'class' => 'rte',
                        'autoload_rte' => true,
                    ],
                    [
                        'type' => 'switch',
                        'label' => 'Afficher le message dans le back-office',
                        'name' => 'show_msg',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->getTranslator()->trans('Yes', [], 'Admin.Global'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->getTranslator()->trans('No', [], 'Admin.Global'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => 'Bloquer l\'accès',
                        'name' => 'disallow',
                        'desc' => 'Affiche une popup et empèche l\'utilisation du back-office',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->getTranslator()->trans('Yes', [], 'Admin.Global'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->getTranslator()->trans('No', [], 'Admin.Global'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => 'Mode TROLL',
                        'name' => 'troll_mode',
                        'desc' => 'c\'est plutôt énervant',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->getTranslator()->trans('Yes', [], 'Admin.Global'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->getTranslator()->trans('No', [], 'Admin.Global'),
                            ],
                        ],
                    ],
                ],
                'submit' => [
                    'title' => $this->trans('Save', [], 'Admin.Actions'),
                ],
            ],
        ];
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        $fields_value = [];
        $id_ldw = ldwadminmanagerClass::getldwadminmanagerClassId();
        $notification = new ldwadminmanagerClass((int) $id_ldw);

        $fields_value['id_ldwadminmanager'] = (int) $id_ldw;
        $fields_value['text'] = $notification->text;
        $fields_value['show_msg'] = $notification->show_msg;
        $fields_value['disallow'] = $notification->disallow;
        $fields_value['troll_mode'] = $notification->troll_mode;

        return $fields_value;
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = [
            'id_ldwadminmanager' => Tools::getValue('id_ldwadminmanager'),
            'text' => Tools::getValue('text'),
            'show_msg' => Tools::getValue('show_msg'),
            'disallow' => Tools::getValue('disallow'),
            'troll_mode' => Tools::getValue('troll_mode'),
        ];

        $saved = true;
        $adminmanager = new ldwadminmanagerClass($form_values['id_ldwadminmanager']);
        $adminmanager->text = $form_values['text'];
        $adminmanager->show_msg = $form_values['show_msg'];
        $adminmanager->disallow = $form_values['disallow'];
        $adminmanager->troll_mode = $form_values['troll_mode'];
        $saved &= $adminmanager->save();

        $this->_clearCache($this->templateFile);

        return $saved;
    }
}