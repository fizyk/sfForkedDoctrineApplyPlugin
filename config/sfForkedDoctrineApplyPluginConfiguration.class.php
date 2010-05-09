<?php
/**
 * fzTagPlugin configuration class. Adds listener for the form.post_configure event
 * @author: Grzegorz Śliwinski
 */
class sfForkedDoctrineApplyPluginConfiguration extends sfPluginConfiguration
{
   
  public function initialize()
  {
    if( in_array('sfApply', sfConfig::get('sf_enabled_modules', array())))
    {
      $this->dispatcher->connect('routing.load_configuration', array($this, 'listenToRoutingLoadConfigurationEvent'));
    }
  }


  /**
   * Listens to the routing.load_configuration event.
   *
   * @param sfEvent An sfEvent instance
   * @static
   */
  public function listenToRoutingLoadConfigurationEvent(sfEvent $event)
  {
    $r = $event->getSubject();
    // preprend our route
    $r->prependRoute('apply', 
        new sfRoute('/user/new', array('module' => 'sfApply', 'action' => 'apply')));
    $r->prependRoute('reset',
        new sfRoute('/user/password-reset', array('module' => 'sfApply', 'action' => 'reset')));
    $r->prependRoute('resetRequest',
        new sfRoute('/user/reset-request', array('module' => 'sfApply', 'action' => 'resetRequest')));
    $r->prependRoute('resetCancel',
        new sfRoute('/user/reset-cancel', array('module' => 'sfApply', 'action' => 'resetCancel')));
    $r->prependRoute('validate',
        new sfRoute('/user/confirm/:validate', array('module' => 'sfApply', 'action' => 'confirm')));
    $r->prependRoute('settings',
        new sfRoute('/user/settings', array('module' => 'sfApply', 'action' => 'settings')));
    if( sfConfig::get( 'app_sfForkedApply_mail_editable' ))
    {
      $r->prependRoute('editEmail',
          new sfRoute('/user/settings/email', array('module' => 'sfApply', 'action' => 'editEmail')));
    }

  }
}