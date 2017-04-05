<?php
/**
* 2014 PrestaShop
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
*  @author    Open Code Consulting <info@opencodeconsulting.com>
*  @copyright 2016 Open Code Consulting
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

class Blockseotext extends Module
{
    public function __construct()
    {
        $this->name = 'blockseotext';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Andres Nacimiento';
        $this->bootstrap = true;
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);

        parent::__construct();

        $this->displayName = $this->l('SEO Text');
        $this->description = $this->l('Add SEO Text in home page.');
    }
    public function install()
    {
        return parent::install()
        && Configuration::updateValue('BST_text_1', '')
        && Configuration::updateValue('BST_text_2', '')
        && Configuration::updateValue('BST_text_3', '')
        && Configuration::updateValue('BST_text_4', '')
        && Configuration::updateValue('BST_text_5', '')
        && Configuration::updateValue('BST_text_6', '')
        && Configuration::updateValue('BST_text', '')
        && Configuration::updateValue('BST_title', 'Texto para mejorar el SEO')
        && $this->registerHook('displayHome');
    }
    public function uninstall()
    {
        /* Delete configuration */
        return Configuration::deleteByName('BST_text_1')
        && Configuration::deleteByName('BST_text_2')
        && Configuration::deleteByName('BST_text_3')
        && Configuration::deleteByName('BST_text_4')
        && Configuration::deleteByName('BST_text_5')
        && Configuration::deleteByName('BST_text_6')
        && Configuration::deleteByName('BST_text')
        && Configuration::deleteByName('BST_title')
        && $this->unregisterHook('displayHome')
        && parent::uninstall();
    }
    public function getContent()
    {
        $html = '';
        /* If we try to update the settings */
        if (Tools::isSubmit('submitModule')) {
            Configuration::updateValue('BST_text_'.$this->context->language->id, Tools::getValue('input_text'), true);
            Configuration::updateValue('BST_title', Tools::getValue('input_title'), true);
            $html .= $this->displayConfirmation($this->l('Configuration updated'));
        }
        $html .= $this->renderForm();
        return $html;
    }
    public function renderForm()
    {
		global $cookie;
		$iso = Language::getIsoById( (int)$cookie->id_lang );
		$content = '
			<script type="text/javascript">	
				var iso = \''.(file_exists(_PS_ROOT_DIR_.'/js/tiny_mce/langs/'.$iso.'.js') ? $iso : 'es').'\' ;
				var pathCSS = \''._THEME_CSS_DIR_.'\' ;
				var ad = \''.dirname($_SERVER['PHP_SELF']).'\' ;
			</script>
			<script type="text/javascript" src="'.__PS_BASE_URI__.'js/tiny_mce/tiny_mce.js"></script>
			<script type="text/javascript" src="'.__PS_BASE_URI__.'js/tinymce.inc.js"></script>
			<script language="javascript" type="text/javascript">
				id_language = Number('.$this->context->language->id.');
				tinySetup();
			</script>
		';
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array( /* TITLE: INSERT YOUR TITLE */
                        'type' => 'text',
                        'label' => $this->l('Title'),
                        'name' => 'input_title',
                        'id' => 'input_title',
                        'required' => true,
                        'desc' => $this->l('Insert your title here').'.',
                        'hint' => $this->l('Invalid characters:').' <>;=#{}\/',
                        'is_bool' => false,
                        'lang' => false,
                    ),
					array( /* TEXTAREA: INSERT YOUR TEXT */
                        'type' => 'textarea',
                        'label' => $this->l('Insert your text').$content,
                        'name' => 'input_text',
                        'id' => 'input_text',
                        'class' => 'rte',
                        'required' => true,
                        'desc' => $this->l('Insert your SEO text here').'.',
                        'hint' => $this->l('Invalid characters:').' <>;=#{}\/',
                        'is_bool' => false,
                        'lang' => false,
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right',
                ),
            ),
        );
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG')
            ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form = array();
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).
            '&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );
        return $helper->generateForm(array($fields_form));
    }
    public function getConfigFieldsValues()
    {
        return array(
            'input_text' => Configuration::get('BST_text_'.$this->context->language->id),
            'input_title' => Configuration::get('BST_title')
        );
    }
    public function hookDisplayHeader()
    {
        $this->context->controller->addCSS(($this->_path).'/views/css/blockseohometext.css', 'all');
    }
    /*public function hookHome()
    {

        $this->smarty->assign(array(
            'input_text' => Configuration::get('BST_text'),
            'lang' => $this->context->language->id
        ));
        return $this->display(__FILE__, 'views/templates/front/blockseohometext.tpl');
    }*/
	public function hookHome(){
		$outputValue .= '
			<div style="height: 100%; margin-bottom: 50px; position: relative; float: left; margin-top: 20px;">
				<div class="pos_title" style="border-bottom: 1px solid #E5E5E5; margin-bottom: 30px; min-height: 40px; position: relative; z-index: 1; height: 40px;">
					<h4 style="text-transform: capitalize; color: #000; font-size: 24px; margin: 0px; font-weight: bold; line-height: 24px; display: inline-block;">
						<i class="icon-thumbs-up-alt" style="border: 1px solid #E5E5E5; height: 40px; line-height: 40px; text-align: center; width: 40px; color: #D8373E; margin-right: 15px;"></i>
						<span style="margin: 0px; padding: 0px; border: 0px none; font-family: inherit; font-style: inherit; font-weight: inherit; line-height: inherit; font-size-adjust: inherit; font-stretch: inherit; font-feature-settings: inherit; font-language-override: inherit; font-kerning: inherit; font-synthesis: inherit; font-variant: inherit; font-size: 100%; vertical-align: baseline;">
							'.Configuration::get('BST_title').'
					</h4>
					<div style="float: left; text-align: justify; padding: 20px 0px 50px;"> 
						<!--
						<p>Maquinaria Ofertas es una tienda online de productos de ferretería, jardinería, bricolaje, carpintería, construcción, automoción y multitud de sectores más que nace con la intención de facilitar la compra a particulares y empresas que buscan en internet la compra de productos de muy alta calidad a un precio muy competitivo.</p>
						<p style="padding: 10px 0px 0px 0px;">Disponemos de multitud de maquinaria para jardinería, maquinaría de construcción o maquinaria para carpintería así como herramientas de taller de la mejor calidad al mejor precio del mercado.</p>
						<p style="padding: 10px 0px 0px 0px;">Para nosotros la atención al cliente es fundamental y ponemos todo el empeño en que tu compra sea lo más sencilla y satisfactoria posible, tanto si estás buscando un taladro barato como si estás buscando herramientas para reformar tu taller mecánico, para nosotros cada cliente es importante.</p>
						<p style="padding: 10px 0px 0px 0px;">Solo trabajamos con grandes marcas y hemos hecho una selección de productos de todos sectores ( Electricidad, Automoción, Construcción, Carpintería etc... ) de marcas tan prestigiosas como Festool, Milwaukee, Makita, Black & Decker, Dewalt o Michelin.</p>
						<p style="padding: 10px 0px 0px 0px;">Quedamos a tu disposición para cualquier pregunta o duda que puedas tener con tan solo rellenar nuestro formulario de contacto</p>
						-->
						'.Configuration::get('BST_text_'.$this->context->language->id).'
					</div>
				</div>
			</div>
		';
		return $outputValue;
	}

}
