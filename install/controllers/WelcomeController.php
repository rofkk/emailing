<?php defined('MW_INSTALLER_PATH') or exit('No direct script access allowed');

/**
 * WelcomeController
 * 
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
class WelcomeController extends Controller
{
    public function actionIndex()
    {
        // start clean
        $_SESSION = array();
        
        $this->validateRequest();
        
        if (getSession('welcome')) {
            redirect('index.php?route=requirements');
        }
        
        $this->data['marketPlaces'] = $this->getMarketPlaces();
        
        $this->data['pageHeading'] = 'Welcome';
        $this->data['breadcrumbs'] = array(
            'Welcome' => 'index.php?route=welcome',
        );
        
        $this->render('welcome');
    }

   protected function validateRequest()
    {
        if (!getPost('next')) {
            return;
        }
        
			$licenseData = array(
			'first_name' => 'CODELIST',
			'last_name' => 'SCRIPS',
			'email' => 'mailwizz@nulled.com',
			'market_place' => 'envato',
			'purchase_code' => 'NULLED',
			);
        
        setSession('license_data', $licenseData);
        setSession('welcome', 1);
    }
    
    public function getMarketPlaces()
    {
        return array(
            'envato'    => 'Envato Market Places',
            'mailwizz'  => 'Mailwizz Website',
        );
    }

}