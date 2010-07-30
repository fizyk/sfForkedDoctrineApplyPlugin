<?php

/**
 * PluginsfGuardUserProfile form.
 *
 * @package    ##PROJECT_NAME##
 * @subpackage form
 * @author     ##AUTHOR_NAME##
 * @version    SVN: $Id: sfDoctrineFormPluginTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
abstract class PluginsfGuardUserProfileForm extends BasesfGuardUserProfileForm
{
    public function setup()
    {
        parent::setup();
    }
    
    public function getStylesheets()
    {
        return array( '/sfForkedDoctrineApplyPlugin/css/forked' => 'all' );
    }
}
