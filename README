# sfForkedDoctrineApply plugin #
Forked from [sfDoctrineApply](http://www.symfony-project.org/plugins/sfDoctrineApplyPlugin) plugin (version 1.1.1).

It's stripped of Zend Mail dependencies as proposed by stephenrs on symfony forums,
[here](http://forum.symfony-project.org/index.php/t/25217/)

Thanks to punkave guys for the original plugin, and Big thanks to stephen for
the modifications.

##Requirements##
* symfony 1.4
* sfDoctrineGuardPlugin - installed and configured

Requirements should be similar as the original plugin, although I can only be
sure of symfony 1.4.
When sfDoctrineGuardPlugin will introduce email in official package, our current
plugin should be modified to use sfGuardUser's email field, not it's own.

##Changes to sfDoctrineApplyPlugin##

* removed all Zend Mail dependency
* created a general library with all sfApplyActions functions
* introduced inheritance to Profile model.

##Installation##

Installation should be simple as:

    symfony plugin:install sfForkedDoctrineApplyPlugin

However it is also possible to install it through archive:

    symfony plugin:install sfForkedDoctrineApplyPlugin-1.2.0.tgz

just place downloaded package in your project's root first.

You can also install it manually, unpacking archive, placing it's content in your
project's plugin/ directory, and enabling it in your ProjectConfiguration.class.php file:

config/ProjectConfiguration.class.php

    class ProjectConfiguration extends sfProjectConfiguration
    {
        //....//
        public function setup()
        {
            //....//
            $this->enablePlugins('sfDoctrineGuardPlugin');
            $this->enablePlugins('sfForkedDoctrineApplyPlugin');
            //....//
        }
    }

After installing this plugin, add this declaration into your schema.yml file:
**config/doctrine/schema.yml**

    sfGuardUserProfile:
      inheritance:
        type: simple
        extends: sfGuardUserProfileBasis
      # Don't forget this!
      relations:
        User:
          class: sfGuardUser
          foreign: id
          local: user_id
          type: one
          onDelete: cascade
          foreignType: one
          foreignAlias: Profile

You can add your own columns, if needed, it just won't be possible to set these
as not null in database. Build your model after those steps, or create migrations:

    ./symfony doctrine:generate-migrations-diff

Review your migration files after this step, and run:

    ./symfony doctrine:migrate
    ./symfony doctrine:build --all-classes

All you need to do is to enable sfApply module in your settings.yml file:

**apps/APPLICATION/config/settings.yml**

    all:
      .settings:
        #...#
        enabled_modules: [default, ... , sfGuardAuth, sfApply]

and set up routes for your app:

**apps/APPLICATION/config/routing.yml**

    apply:
      url:  /user/new
      param: { module: sfApply, action: apply }

    reset:
      url: /user/password-reset
      param: { module: sfApply, action: reset }

    resetRequest:
      url: /user/reset-request
      param: { module: sfApply, action: resetRequest }

    resetCancel:
      url: /user/reset-cancel
      param: { module: sfApply, action: resetCancel }

    validate:
      url: /user/confirm/:validate
      param: { module: sfApply, action: confirm }

    settings:
      url: /user/settings
      param: { module: sfApply, action: settings }

And you can enjoy user registration on your website.

##Configuration##

To configure this plugin to actually send registration emails,
You need to set up email settings according to
[day 16](http://www.symfony-project.org/jobeet/1_4/Doctrine/en/16) of Jobeet tutorial.

###Basic###

In order to send emails with confirmation codes you've got to add these settings in your app.yml:

**apps/APPLICATION/config/app.yml**

    sfApplyPlugin:
            from:
              email: "your@emailaddress.com"
              fullname: "the staff at yoursite.com"


You should also turn on i18n engine, as this plugin, like the project it rooted
from is fully internationalised (You might have to prepare i18n files for your language though):

**apps/APPLICATION/config/settings.yml**

    all:
      .settings:
        i18n: on


###CAPTCHA###

Starting from 1.1.0 version, sfForkedDoctrineApplyPlugin integrates reCaptcha. To use it, you have to install [sfFormExtraPlugin](http://www.symfony-project.org/plugins/sfFormExtraPlugin) to get access to [reCaptcha](http://recaptcha.net/) widget and validator. Second step is to be conducted in your app.yml file, and add these:

**apps/APPLICATION/config/app.yml**

    all:
      #...
      recaptcha:
        enabled:        true
        public_key:     YOUR_PUBLIC_reCAPTCHA_KEY
        private_key:    YOUR_PRIVATE_reCAPTCHA_KEY

enabled property is for enabling and disabling captcha. After setting this, reCaptcha will appear on apply and reset request pages.

###Custom forms###

Since version 1.1.1, it is possible to define own, custom forms for apply action, however all custom forms must extend the Apply ones. To use custom forms, you need to define them in your app.yml file:

**apps/APPLICATION/config/app.yml**

    all:
      #...
      sfForkedApply:
        applyForm: sfApplyApplyForm
        resetForm: sfApplyResetForm
        resetRequestForm: sfApplyResetRequestForm
        settingsForm: sfApplySettingsForm

The above example uses standard sfApplyForms.

###Email editing###

To allow users to edit their emails, you've got to add app_sfForkedApply_mail_editable setting:

    all:
      #...
      sfForkedApply:
        #...
        mail_editable: false

And then add route to your apps routning.yml file:

    editEmail:
      url: /user/settings/email
      param: { module: sfApply, action: editEmail }

Now, when user will try to edit their email, he'll receive confirmation email on his old address.

###Confirmation disabling###

It is possible, although not recommended to disable email confirmations for the following actions:

* Apply - new users will be registered and logged as soon as they submit valid apply form.
* Password reset - this will disable the reset request, password change will be possible only for logged in users.
* Email edit - new email will immediately replace old one.

To disable confirmation emails for any of this actions, simply add and modify following options to application's app.yml file:

    all:
      #...
      sfForkedApply:
        #...
        confirmation:
          reset: true
          apply: true
          email: true

###Login redirect###

There are two settings regarding directing user after actions he takes within sfApply module:

    all:
      #...
      sfApplyPlugin:
        afterLogin: after_login_route
        after: after_route

You can use these settings to direct user to your own pages after user loggs in, or in other cases with second setting.

## Displaying Login and Logout Prompts##
(fragment of sfDoctrineApplyPlugin's README)

You probably have pages on which logging in is optional. It's nice to
display a login prompt directly on these pages. If you want to do that,
try including my login/logout prompt component from your
`apps/frontend/templates/layout.php` file:

    <?php include_component('sfApply', 'login') ?>

Note that you can suppress the login prompt on pages that do include
this partial by setting the `sf_apply_login` slot:

    <?php slot('sf_apply_login') ?>
    <?php end_slot() ?>


## Credits ##

sfDoctrineApplyPlugin was written by Tom Boutell. He can be contacted
at [tom@punkave.com](mailto:tom@punkave.com). See also [www.punkave.com](http://www.punkave.com/) and
[www.boutell.com](http://www.boutell.com/) for further information about his work.

Changes resulting in forking the original plugin were written by [stephenrs](http://forum.symfony-project.org/index.php/u/11253/).
sfForkedDoctrineApplyPlugin was created by Grzegorz Śliwiński as a result of those changes with some additions.
You can contact him at [fizyk@fizyk.net.pl](mailto:fizyk@fizyk.net.pl) or through
jabber/xmpp at fizyk@jabbim.pl and follow his adventures on his [homepage](http://www.fizyk.net.pl/).

###Translations###
* Italian - Alessandro Rossi
* Polish - Grzegorz Śliwiński