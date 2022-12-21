<?php
namespace  Drupal\student;


use Drupal\Core\File\FileSystemInterface;
use Drupal\file\FileRepositoryInterface;
use Drupal\file\Entity\File;
use Drupal\Core\Entity\EntityTypeManagerInterface;
  
/**
* @file providing services to parse of csv
*
*/

class EntityCsvServices {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

 /**
  * ImporterForm class constructor.
  *
  * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
  *   The entity type manager service.
  * @param Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
  *   The entity type manager service.
  */

 public function __construct(EntityTypeManagerInterface $entityTypeManager) {
      $this->entityTypeManager = $entityTypeManager;

 }

 /**
  * {@inheritdoc}
  */
 public function __call($fun, $arg)
 {
   $file = File::load(reset($arg[0]['csv']));
   $file->setPermanent();
   $handle = fopen($file->getFileUri(),"r");
   $count = 0;$i=1;
   $entity_storage = $this->entityTypeManager->getStorage('student');

   while ($row = fgetcsv($handle, 100, ',')) {
     $count++;
     if ($count == 1) { continue; }
       if($arg[1] == 'student') {
         $data = [
           'name' => $row[0],
           'class' => $row[1],
           'contact_number' => $row[2],
         ]; $i++;
       } elseif ($arg[1] == 'stresult') {
         $chk = $entity_storage->load($row[1]);
         if(isset($chk)) {
           $data = [
             'subject' => $row[0],
             'rollnumber' => $row[1],
             'score' => $row[2],
           ]; $i++;
         } elseif(!isset($chk)) {
              continue;
         }
       }
       $entity = $entity_storage->create($data);
       $entity->save();

     }
     \Drupal::messenger()->addMessage(($i-1).t('Record Added '));

 }

}
