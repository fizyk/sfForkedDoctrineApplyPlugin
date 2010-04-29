<?php
/**
 * sfApplyActionsLibrary is an sfActions based library for sfApplyActions class.
 * Inherit it if you want to override any methods, and still use some of the
 * original functionality.
 *
 * @author fizyk
 */
class sfApplyActionsLibrary extends sfActions
{
    //When user is applying for new account
    public function executeApply(sfRequest $request)
    {
        // we're getting default or customized applyForm for the task
        if( !( ($this->form = $this->newForm( 
                sfConfig::get( 'app_sfForkedApply_applyForm', 'sfApplyApplyForm' ))
                ) instanceof sfApplyApplyForm) )
        {
            // if the form isn't instance of sfApplyApplyForm, we don't accept it
            throw new InvalidArgumentException(
                    'The custom apply form should be instance of sfApplyApplyForm' );
        }

        //Code below is used when user is sending his application!
        if( $request->isMethod('post') )
        {
            //gathering form request in one array
            $formValues = $request->getParameter( $this->form->getName() );
            if(sfConfig::get('app_recaptcha_enabled') )
            {
                $captcha = array(
                  'recaptcha_challenge_field' => $request->getParameter('recaptcha_challenge_field'),
                  'recaptcha_response_field'  => $request->getParameter('recaptcha_response_field'),
                );
                //Adding captcha to form array
                $formValues = array_merge( $formValues, array('captcha' => $captcha)  );
            }
            //binding request form parameters with form
            $this->form->bind($formValues);
            if ($this->form->isValid())
            {
                $guid = "n" . self::createGuid();
                $this->form->setValidate($guid);
                $this->form->save();
                try
                {
                    //Extracting object and sending creating verification mail
                    $profile = $this->form->getObject();
                    $this->sendVerificationMail($profile);
                    return 'After';
                }
                catch (Exception $e)
                {
                    //Cleaning after possible exception thrown in ::sendVerificationMail() method
                    $profile = $this->form->getObject();
                    $user = $profile->getUser();
                    $user->delete();
                    // You could re-throw $e here if you want to
                    // make it available for debugging purposes
                    return 'MailerError';
                }
            }
        }
    }

    //Processes reset requests
    public function executeResetRequest(sfRequest $request)
    {
        $user = $this->getUser();

        if ($user->isAuthenticated())
        {
            $this->redirect( 'sfApply/reset' );
        }
        else
        {
            // we're getting default or customized resetRequestForm for the task
            if( !( ($this->form = $this->newForm(
                    sfConfig::get( 'app_sfForkedApply_resetRequestForm', 'sfApplyResetRequestForm' ) )
                    ) instanceof sfApplyResetRequestForm) )
            {
                // if the form isn't instance of sfApplySettingsForm, we don't accept it
                throw new InvalidArgumentException( 
                        'The custom resetRequest form should be instance of sfApplyResetRequestForm'
                        );
            }
            if ($request->isMethod('post'))
            {
                //gathering form request in one array
                $formValues = $request->getParameter( $this->form->getName() );
                if(sfConfig::get('app_recaptcha_enabled') )
                {
                    $captcha = array(
                      'recaptcha_challenge_field' => $request->getParameter('recaptcha_challenge_field'),
                      'recaptcha_response_field'  => $request->getParameter('recaptcha_response_field'),
                    );
                    //Adding captcha to form array
                    $formValues = array_merge( $formValues, array('captcha' => $captcha)  );
                }
                //binding request form parameters with form
                $this->form->bind($formValues);
                if ($this->form->isValid())
                {
                    // The form matches unverified users, but retrieveByUsername does not, so
                    // use an explicit query. We'll special-case the unverified users in
                    // resetRequestBody

                    $username_or_email = $this->form->getValue('username_or_email');
                    if (strpos($username_or_email, '@') !== false)
                    {
                        $user = Doctrine::getTable('sfGuardUser')->createQuery('u')->
                                innerJoin('u.Profile p')->where('p.email = ?', $username_or_email)->
                                fetchOne();

                    }
                    else
                    {
                        $user = Doctrine::getTable('sfGuardUser')->createQuery('u')->
                                where('username = ?', $username_or_email)->fetchOne();
                    }
                    return $this->resetRequestBody($user);
                }
            }
        }
    }

    public function executeConfirm(sfRequest $request)
    {
        $validate = $this->request->getParameter('validate');
        // 0.6.3: oops, this was in sfGuardUserProfilePeer in my application
        // and therefore never got shipped with the plugin until I built
        // a second site and spotted it!

        // Note that this only works if you set foreignAlias and
        // foreignType correctly
        $sfGuardUser = Doctrine_Query::create()
            ->from("sfGuardUser u")
            ->innerJoin("u.Profile p with p.validate = ?", $validate)
            ->fetchOne();
        if (!$sfGuardUser)
        {
          return 'Invalid';
        }
        $type = self::getValidationType($validate);
        if (!strlen($validate))
        {
          return 'Invalid';
        }
        $profile = $sfGuardUser->getProfile();
        $profile->setValidate(null);
        $profile->save();
        if ($type == 'New')
        {
          $sfGuardUser->setIsActive(true);
          $sfGuardUser->save();
          $this->getUser()->signIn($sfGuardUser);
        }
        if ($type == 'Reset')
        {
          $this->getUser()->setAttribute('sfApplyReset', $sfGuardUser->getId());
          return $this->redirect('sfApply/reset');
        }
        if( $type == 'Email' )
        {
          //TODO! serve the email change confirmation here
        }
    }

    public function executeReset(sfRequest $request)
    {
        //won't present this page to users that are not authenticated or haven't got confirmation code
        if( !$this->getUser()->isAuthenticated() && !$this->getUser()->getAttribute('sfApplyReset', false)  )
        {
            $this->redirect( '@sf_guard_signin' );
        }
        // we're getting default or customized resetForm for the task
        if( !( ($this->form = $this->newForm(
                sfConfig::get( 'app_sfForkedApply_resetForm', 'sfApplyResetForm' ))
                ) instanceof sfApplyResetForm) )
        {
            // if the form isn't instance of sfApplyResetForm, we don't accept it
            throw new InvalidArgumentException( 
                    'The custom reset form should be instance of sfApplyResetForm'
                    );
        }
        if ($request->isMethod('post'))
        {
            $this->form->bind($request->getParameter( $this->form->getName() ));
            if ($this->form->isValid())
            {
                //This got fixed (0.9.1), so if user is authenticated, and requests password change, we're still getting his id.
                $this->id = ( $this->getUser()->isAuthenticated() ) ? $this->getUser()->getGuardUser()->getId() : $this->getUser()->getAttribute('sfApplyReset', false);
                $this->forward404Unless($this->id);
                $this->sfGuardUser = Doctrine::getTable('sfGuardUser')->find($this->id);
                $this->forward404Unless($this->sfGuardUser);
                $sfGuardUser = $this->sfGuardUser;
                $sfGuardUser->setPassword($this->form->getValue('password'));
                $sfGuardUser->save();
                $this->getUser()->signIn($sfGuardUser);
                $this->getUser()->setAttribute('sfApplyReset', null);
                return 'After';
            }
        }
        if( $this->getUser()->isAuthenticated() )
        {
            return 'Logged';
        }
    }

    public function executeResetCancel()
    {
        $this->getUser()->setAttribute('sfApplyReset', null);
        return $this->redirect(sfConfig::get('app_sfApplyPlugin_after', '@homepage'));
    }

    public function executeSettings(sfRequest $request)
    {
        // sfApplySettingsForm inherits from sfApplyApplyForm, which
        // inherits from sfGuardUserProfile. That minimizes the amount
        // of duplication of effort. If you want, you can use a different
        // form class. I suggest inheriting from sfApplySettingsForm and
        // making further changes after calling parent::configure() from
        // your own configure() method.

        $profile = $this->getUser()->getProfile();
        // we're getting default or customized settingsForm for the task
        if( !( ($this->form = $this->newForm(
                sfConfig::get( 'app_sfForkedApply_settingsForm', 'sfApplySettingsForm' ), $profile)
                ) instanceof sfApplySettingsForm) )
        {
            // if the form isn't instance of sfApplySettingsForm, we don't accept it
            throw new InvalidArgumentException( sfContext::getInstance()->
                    getI18N()->
                    __( 'The custom %action% form should be instance of %form%',
                            array( '%action%' => 'settings',
                                '%form%' => 'sfApplySettingsForm' ), 'sfForkedApply' )
                    );
        }
        if ($request->isMethod('post'))
        {
            $this->form->bind($request->getParameter( $this->form->getName() ));
            if ($this->form->isValid())
            {
                $this->form->save();
                return $this->redirect('@homepage');
            }
        }
    }

    public function executeEditEmail(sfRequest $request)
    {
      $this->form = new sfApplyEditEmailForm();
      if ($request->isMethod('post'))
      {
        $this->form->bind($request->getParameter( $this->form->getName() ));
        if ($this->form->isValid())
        {
          $profile = $this->getUser()->getGuardUser();
          $profile->setEmailNew( $this->form->getValue( 'email' ) );
          $profile->setValidate('e' . self::createGuid());
          $profile->save();
          $this->mail(array('subject' => sfConfig::get('app_sfApplyPlugin_apply_subject',
            sfContext::getInstance()->getI18N()->__("Please verify your account on %1%",
                                                    array('%1%' => $this->getRequest()->getHost()), 'sfForkedApply')),
            'fullname' => $profile->getFullname(),
            'email' => $profile->getEmail(),
            'parameters' => array('fullname' => $profile->getFullname(),
                                  'validate' => $profile->getValidate(),
                                  'oldemail' => $profile->getEmail(),
                                  'newemail' => $profile->getEmailNew() ),
            'text' => 'sfApply/sendValidateEmailText',
            'html' => 'sfApply/sendValidateEmail'));
          //TODO! Here add code to send confirmation emails
//            return $this->redirect('@homepage');
        }
      }

    }

    /**
     * gets From information for email. may throw Exception.
     * @return array
     */
    protected function getFromAddress()
    {
        $from = sfConfig::get('app_sfApplyPlugin_from', false);
        if (!$from)
        {
            throw new Exception('app_sfApplyPlugin_from is not set');
        }
        // i18n the full name
        return array('email' => $from['email'], 'fullname' => sfContext::getInstance()->getI18N()->__($from['fullname']));
    }

    /**
     * apply uses this. Password reset also uses it in the case of a user who
     * was never verified to begin with.
     * @param object $profile
     */
    protected function sendVerificationMail( $profile )
    {
        $this->mail(array('subject' => sfConfig::get('app_sfApplyPlugin_apply_subject',
            sfContext::getInstance()->getI18N()->__("Please verify your account on %1%",
                                                    array('%1%' => $this->getRequest()->getHost()), 'sfForkedApply')),
            'fullname' => $profile->getFullname(),
            'email' => $profile->getEmail(),
            'parameters' => array('fullname' => $profile->getFullname(),
                                    'validate' => $profile->getValidate()),
            'text' => 'sfApply/sendValidateNewText',
            'html' => 'sfApply/sendValidateNew'));
    }

    /**
     * This function has been overriden. Original used Zend_Mail here. It's used
     * to actually compose and send e-mail verification message.
     * @param array $options
     */
    protected function mail( $options )
    {
        //Checking for all required options
        $required = array('subject', 'parameters', 'email', 'fullname', 'html', 'text');
        foreach ($required as $option)
        {
            if (!isset($options[$option]))
            {
                throw new sfException("Required option $option not supplied to sfApply::mail");
            }
        }
        $message = $this->getMailer()->compose();
        $message->setSubject($options['subject']);

        // Render message parts
        $message->setBody($this->getPartial($options['html'], $options['parameters']), 'text/html');
        $message->addPart($this->getPartial($options['text'], $options['parameters']), 'text/plain');

        //getting information on sender (that's us). May be source of exception.
        $address = $this->getFromAddress();
        $message->setFrom(array($address['email'] => $address['fullname']));
        $message->setTo(array($options['email'] => $options['fullname']));

        //Sending email
        $this->getMailer()->send($message);
    }

    // A convenience method to instantiate a form of the
    // specified class... unless the user has specified a
    // replacement class in app.yml. Sweet, no?
    protected function newForm($className, $object = null)
    {
        $key = "app_sfApplyPlugin_$className" . "_class";
        $class = sfConfig::get($key, $className);
        if ($object !== null)
        {
            return new $class($object);
        }
        return new $class;
    }
    
    static public function createGuid()
    {
        $guid = "";
        // This was 16 before, which produced a string twice as
        // long as desired. I could change the schema instead
        // to accommodate a validation code twice as big, but
        // that is completely unnecessary and would break
        // the code of anyone upgrading from the 1.0 version.
        // Ridiculously unpasteable validation URLs are a
        // pet peeve of mine anyway.
        for ($i = 0; ($i < 16); $i++)
        {
            $guid .= sprintf("%02x", mt_rand(0, 255));
        }
        return $guid;
    }

    //Returns validation type
    static public function getValidationType($validate)
    {
        $t = substr($validate, 0, 1);
        if( $t == 'n' )
        {
            return 'New';
        }
        elseif( $t == 'r' )
        {
            return 'Reset';
        }
        elseif( $t == 'e' )
        {
          return 'Email';
        }
        else
        {
            return sfView::NONE;
        }
    }

    public function resetRequestBody($user)
    {
        if (!$user)
        {
            return 'NoSuchUser';
        }
        $this->forward404Unless($user);
        $profile = $user->getProfile();

        if (!$user->getIsActive())
        {
            $type = $this->getValidationType($profile->getValidate());
            if ($type === 'New')
            {
                try
                {
                    $this->sendVerificationMail($profile);
                }
                catch (Exception $e)
                {
                    return 'UnverifiedMailerError';
                }
                return 'Unverified';
            }
            elseif ($type === 'Reset')
            {
                // They lost their first password reset email. That's OK. let them try again
            }
            else
            {
                return 'Locked';
            }
        }
        $profile->setValidate('r' . self::createGuid());
        $profile->save();
        try
        {
            $this->mail(array('subject' => sfConfig::get('app_sfApplyPlugin_reset_subject',
                    sfContext::getInstance()->getI18N()->__("Please verify your password reset request on %1%",
                                                            array('%1%' => $this->getRequest()->getHost()), array(), 'sfForkedApply')),
                'fullname' => $profile->getFullname(),
                'email' => $profile->getEmail(),
                'parameters' => array('fullname' => $profile->getFullname(),
                                        'validate' => $profile->getValidate(), 'username' => $user->getUsername()),
                'text' => 'sfApply/sendValidateResetText',
                'html' => 'sfApply/sendValidateReset'));
        }
        catch (Exception $e)
        {
            return 'MailerError';
        }
        return 'After';
    }
}
?>
