<?php
/**
 * Description of sfValidatorAppluEditMailclass
 *
 * @author fizyk
 */
class sfValidatorApplyEditMail extends sfValidatorSchema
{

  public function __construct($options = array(), $messages = array())
  {
    parent::__construct(null, $options, $messages);
  }

  public function configure( $options = array(), $messages = array()  )
  {
    parent::configure($options, $messages);

    $this->addRequiredOption( 'id', null );
    
    $this->setMessage('invalid', 'This email is already in use by some one else');
  }

  /**
   * @see sfValidatorBase
   */
  protected function doClean($value)
  {
    $originalValues = $value;

    $object = Doctrine_Core::getTable('sfGuardUserProfile')->createQuery('a')
        ->where('a.email = ?', $value)
        ->andWhereNotIn( 'a.id', $this->getOption( 'id' ) )->fetchOne();

    // if no object or if we're updating the object, it's ok
    if (!$object || $this->isUpdate($object, $value))
    {
      return $originalValues;
    }

    $error = new sfValidatorError($this, 'invalid', array('column' => implode(', ', $this->getOption('column'))));

    if ($this->getOption('throw_global_error'))
    {
      throw $error;
    }

    $columns = $this->getOption('column');

    throw new sfValidatorErrorSchema($this, array($columns[0] => $error));
  }

  /**
   * Returns whether the object is being updated.
   *
   * @param BaseObject  A Doctrine object
   * @param array       An array of values
   *
   * @param Boolean     true if the object is being updated, false otherwise
   */
  protected function isUpdate(Doctrine_Record $object, $values)
  {
    // check each primary key column
    foreach ($this->getPrimaryKeys() as $column)
    {
      if (!isset($values[$column]) || $object->$column != $values[$column])
      {
        return false;
      }
    }

    return true;
  }

  /**
   * Returns the primary keys for the model.
   *
   * @return array An array of primary keys
   */
  protected function getPrimaryKeys()
  {
    if (null === $this->getOption('primary_key'))
    {
      $primaryKeys = Doctrine_Core::getTable($this->getOption('model'))->getIdentifier();
      $this->setOption('primary_key', $primaryKeys);
    }

    if (!is_array($this->getOption('primary_key')))
    {
      $this->setOption('primary_key', array($this->getOption('primary_key')));
    }

    return $this->getOption('primary_key');
  }
}
?>
