<?php

namespace Drupal\student\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\student\EntityCsvServices;

/**
 * Configure regional settings for this site.
 *
 * @internal
 */
class ImportercsvForm extends ConfigFormBase {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity field manager service.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * The entity bundle info service.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityBundleInfo;

  /**
   * The importer plugin manager service.
   *
   * @var \Drupal\student\EntityCsvServices
   */
  protected $csvreader;

  /**
   * ImporterForm class constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager service.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_bundle_info
   *   The entity bundle info service.
   * @param \Drupal\student\EntityCsvServices $csvreader
   *   The csv service.
   *
   */


  //public function __construct(EntityTypeManagerInterface $entity_type_manager, EntityFieldManagerInterface $entity_field_manager, EntityTypeBundleInfoInterface $entity_bundle_info, ParserInterface $parser, RendererInterface $renderer, ImporterManager $importer) {
  public function __construct(EntityTypeManagerInterface $entity_type_manager, EntityFieldManagerInterface $entity_field_manager, EntityTypeBundleInfoInterface $entity_bundle_info,EntityCsvServices $csv_reader) {
    $this->entityTypeManager = $entity_type_manager;
    $this->entityFieldManager = $entity_field_manager;
    $this->entityBundleInfo = $entity_bundle_info;
    $this->csvreader = $csv_reader;

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('entity_field.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('csv.save'),

    );
  }

  /**
 * Config settings.
 *
 * @var string
 */
const SETTINGS = 'student.settings';

/**
 * {@inheritdoc}
 */
public function getFormId() {
  return 'student_admin_csv_import';
}

/**
 * {@inheritdoc}
 */
protected function getEditableConfigNames() {
  return [
    static::SETTINGS,
  ];
}
  public function buildForm(array $form, FormStateInterface $form_state) {
    $triggering_element = $form_state->getTriggeringElement();

    $form['importer'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'csv-importer',
      ],
    ];

    $form['importer']['entity_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Select entity type'),
      '#required' => TRUE,
      '#options' => $this->getEntityTypeOptions(),


    ];


      $form['importer']['delimiter'] = [
        '#type' => 'select',
        '#title' => $this->t('Select delimiter'),
        '#options' => [
          ',' => ',',
          '~' => '~',
          ';' => ';',
          ':' => ':',
        ],
        '#default_value' => ',',
        '#required' => TRUE,

      ];


    $form['importer']['csv'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Select CSV file'),
      '#required' => TRUE,
      '#autoupload' => TRUE,
      '#upload_validators' => ['file_validate_extensions' => ['csv']],

    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Import'),
      '#button_type' => 'primary',
    ];


    return $form;
  }

  /*public function getEntitytypeBundle(array $form, FormStateInterface $form_state) {
    $entity_type = $form_state->getValue('entity_type');
      $options = [];
      $entity = $this->entityTypeManager->getDefinition($entity_type);

      if ($entity && $type = $entity->getBundleEntityType()) {
        $types = $this->entityTypeManager->getStorage($type)->loadMultiple();

        if ($types && is_array($types)) {
          foreach ($types as $type) {
            $options[$type->id()] = $type->label();
          }
        }
      }

    $form['importer']['bundle']['#options'] = $options;
    return $form['importer']['bundle'];
  }*/

  /**
   * Get entity type options.
   *
   * @return array
   *   Entity type options.
   */
  protected function getEntityTypeOptions() {
    $options = [];

    $entity_definitions = $this->entityTypeManager->getDefinitions();
      // Create a list of entity types.
      $entity_types_list = [];
      foreach($entity_definitions as $entity_name => $entity_definition) {
        $options[$entity_name] = (string) $entity_definition->getLabel();
      }


    return $options;
  }


  /**
   * {@inheritdoc}
   */

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $entity_type = $form_state->getValue('entity_type');
    $entity = $this->entityTypeManager->getStorage($entity_type);
    $csv = $form_state->getValues();
    $this->csvreader->csvProcess($csv,$entity_type);
  }

}
