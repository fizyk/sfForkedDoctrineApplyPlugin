<?php
/**
 * @author Grzegorz Śliwiński
 */
class upgradeProfilesTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addOptions( array(
            new sfCommandOption( 'env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev' ),
                // add your own options here
        ) );

        $this->namespace = 'sfForkedDoctrineApply';
        $this->name = 'upgrade-proiles';
        $this->briefDescription = 'Upgrades profiles created before sfForked 1.4';
        $this->detailedDescription = 'Plugin upgrades profiles created before
            sfForked 1.4 to the new format, to adjust them to the new functionalities provided by sfDoctrineGuard 5.x';
    }

    protected function execute( $arguments = array( ), $options = array( ) )
    {
        $databaseManager = new sfDatabaseManager($this->configuration);

        $this->logSection( 'sfForkedDoctrineApply:upgrade-proiles', 'Gathering informations' );
        $count = sfGuardUserProfileTable::getInstance()->getProfilesWithUserQuery()->count();
        $this->logSection( 'upgrade-proiles:', $count.' profiles to upgrade');
        $this->logSection( 'upgrade-proiles:', 'retriving profiles to upgrade');
        $profiles = sfGuardUserProfileTable::getInstance()->getProfilesWithUserQuery()->execute( array(), Doctrine_Core::HYDRATE_ON_DEMAND );
        foreach( $profiles as $profile )
        {
            $this->log( 'upgrade '.$profile );
            $profile->getUser()->setEmailAddress( $profile->getEmail() );
            $profile->save();
            unset( $profile );
            $this->log( 'done' );
        }

        $this->logSection( 'upgrade-proiles:' , 'finished');
    }

}
