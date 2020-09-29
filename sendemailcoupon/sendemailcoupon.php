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



class SendEmailCoupon extends Module
{

    protected $config_form = false;

    public function __construct()
    {

        $this->name = 'sendemailcoupon';
        $this->author = 'Aquiroz';
        $this->version = '1.0';
        $this->controllers = array('default');
        $this->bootstrap = true;
        $this->need_instance = 1;

        $this->displayName = $this->l('A Module information status');
        $this->description = $this->l('Created for Webimpacto Iniciation, este modulo te permite emitir informacion al usuario automaticamente');
        $this->confirmUninstall = $this->l('Estas seguro de querer remover el Modulo');
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);

        parent::__construct();
    }

    public function install()
    {

        $sql = "ALTER TABLE " . _DB_PREFIX_ ."customer ADD money_spent_total  FLOAT(11,2)";
           $db = \Db::getInstance();

            $res = $db->execute($sql);
            if (!$res) {
                return false;
            }

        return parent::install() &&
            $this->registerHook('backOfficeHeader')&&
            $this->registerHook('actionPaymentConfirmation');
    }

    public function uninstall()
    {
        $sql = "ALTER TABLE " . _DB_PREFIX_ . "customer DROP money_spent_total";

        $res = Db::getInstance()->execute($sql);
        if (!$res) {
            return false;
        }
        
        Configuration::deleteByName('SENDEMAILCOUPON_COUPON_AMOUNT_NECESSARY');
        Configuration::deleteByName('SENDEMAILCOUPON_COUPON_AMOUNT');
        
        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitSendemailcouponModule')) == true) {
            $this->postProcess();

        }
        $this->context->smarty->assign(array('module_dir'=> $this->_path, 'coupons' => $this->getAllCartRules()));

        $output =  $this->context->smarty->fetch($this->local_path.'views/templates/configure.tpl');
        $output2 = $this->context->smarty->fetch($this->local_path.'views/templates/coupontable.tpl');

        return $output.$this->renderForm().$output2;




    }

    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitSendemailcouponModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'step' => '0.01',
                        'min' => '0.01',
                        'desc' => $this->l('Enter a valid amount'),
                        'name' => 'SENDEMAILCOUPON_COUPON_AMOUNT_NECESSARY',
                        'label' => $this->l('Amount to spend to generate coupon'),
                    ),
                     array(
                        'col' => 3,
                        'type' => 'text',
                        'step' => '0.01',
                        'min' => '0.01',
                        'desc' => $this->l('Enter a valid amount'),
                        'name' => 'SENDEMAILCOUPON_COUPON_AMOUNT',
                        'label' => $this->l('Coupon amount generated'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }



    
    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'SENDEMAILCOUPON_COUPON_AMOUNT_NECESSARY' => Configuration::get('SENDEMAILCOUPON_COUPON_AMOUNT_NECESSARY'),
            'SENDEMAILCOUPON_COUPON_AMOUNT' => Configuration::get('SENDEMAILCOUPON_COUPON_AMOUNT'),
        );
    }
    
    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }

      


    }
    
    /*
     * Request all discount(cart-rules)
     */
    public static function getAllCartRules()
    {
        $db = Db::getInstance();
        
        $request = "SELECT *"
                . " FROM ". _DB_PREFIX_ ."cart_rule";
        
        $request = "SELECT c.id_customer , c.firstname , c.lastname , c.email , cr.code , cr.date_from "
                . " FROM "._DB_PREFIX_."cart_rule as cr, "._DB_PREFIX_."customer as c "
                . " WHERE c.id_customer = cr.id_customer ";
        
        return $db->executeS($request);
    }
    
    public function generateDiscountCode()
    {
        $code = Tools::passwdGen();
        while (CartRule::cartRuleExists($code)) {
            $code = Tools::passwdGen();
        }
       
        return $code;
    }

   
    public function hookActionPaymentConfirmation($params)
    {
        
        //Recopilacion datos
        $expensesCoupoGet = Configuration::get('SENDEMAILCOUPON_COUPON_AMOUNT_NECESSARY');
        $customer = new Customer($params['cart']->id_customer);
        $order = new Order($params['id_order']);
        $totalExpenses = $customer->getMoneySpentTotal();
        $discount = false;
        
        $theme = 'totalExpenses';
        $subject = 'Spending information generated';
        $emailDetail = [
            '{firstname}'=> $customer->firstname,
            '{lastname}'=> $customer->lastname,
            '{totalamount}'=> (float)$totalExpenses + (float)$order->total_paid,
            '{order}'=> $order->reference,
            '{currency}' => $this->context->currency->iso_code,
        ];
        
        //Comprobando si hay que generar cupon
        if ($totalExpenses < $expensesCoupoGet) {
            if ($totalExpenses + $order->total_paid >= $expensesCoupoGet) {
                //Generando cupon
                $discount = new CartRule();
                $discount->id_customer = $customer->id;
                $discount->date_from = date('Y-m-d H:i:s');
                $discount->date_to = date('Y-12-31 H:i:s');
                $discount->reduction_amount = (float)Configuration::get('SENDEMAILCOUPON_COUPON_AMOUNT');
                $discount->code = $discount->generateDiscountCode();
                $names = $this->createLangNamesDiscount("Discount250");
                $discount->name = $names;
                $discount->quantity_per_user = 1;
                $discount->quantity = 1;
                $discount->add();
            }
        }
        
        $customer->addMoneySpentTotal($order->total_paid);
        
        
        if ($discount) {
            $themeDetail = 'totalExpensesCoupon';
            $subject = 'You have generated a discount coupon';
            $dataEmail['{coupon}'] = $discount->code;
            $dataEmail['{money}'] = $discount->reduction_amount;
        }
    
        Mail::send(
            $this->context->language->id,         
            $themeDetail,                                   
            $subject,                                     
            $dataEmail,                                  
            $customer->email,                            
            $customer->firstname.' '.$customer->lastname, 
            null,                                        
            'WebImpacto',                              
            null,
            null,
            $this->local_path.'views/templates/mails/'
        );
    }
            
    public function createLangNamesDiscount($name)
    {
        $names = array();
        foreach (Language::getLanguages(true) as $language) {
            $names[$language['id_lang']] = $name ;
        }
        return $names;
    }
}