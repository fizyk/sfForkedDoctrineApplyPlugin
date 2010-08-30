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
        
    }

}
