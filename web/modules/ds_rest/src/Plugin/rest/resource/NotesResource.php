<?php

namespace Drupal\ds_rest\Plugin\rest\resource;

use Drupal;
use Drupal\Component\Plugin\DependentPluginInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Routing\BcRoute;
use Drupal\ds_note\Entity\DSNode;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Represents Notes records as resources.
 *
 * @RestResource (
 *   id = "ds_rest_notes",
 *   label = @Translation("Notes"),
 *   uri_paths = {
 *     "canonical" = "/api/ds-rest-notes",
 *     "https://www.drupal.org/link-relations/create" = "/api/ds-rest-notes"
 *   }
 * )
 *
 * @DCG
 * This plugin exposes database records as REST resources. In order to enable it
 * import the resource configuration into active configuration storage. You may
 * find an example of such configuration in the following file:
 * core/modules/rest/config/optional/rest.resource.entity.node.yml.
 * Alternatively you can make use of REST UI module.
 * @see https://www.drupal.org/project/restui
 * For accessing Drupal entities through REST interface use
 * \Drupal\rest\Plugin\rest\resource\EntityResource plugin.
 */
class NotesResource extends ResourceBase implements DependentPluginInterface {

  /**
   * Default limit entities per request.
   */
  protected $limit = 10;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $dbConnection;

  /**
   * Constructs a Drupal\rest\Plugin\rest\resource\EntityResource object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Database\Connection $db_connection
   *   The database connection.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, array $serializer_formats, LoggerInterface $logger, Connection $db_connection) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->dbConnection = $db_connection;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('rest'),
      $container->get('database')
    );
  }

  /**
   * Responds to GET requests.
   *
   * @param int $id
   *   The ID of the record.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing the record.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   */
  public function get() {
    //    return new ResourceResponse($this->loadRecord($id));
    $response = [
      'items' => [],
      'count' => 0,
    ];

    $request = Drupal::request();
    $request_query = $request->query;
    $request_query_array = $request_query->all();
    $limit = $request_query->get('limit') ?: $this->limit;
    $page = $request_query->get('page') ?: 0;

    // Find out how many notes do we have.
    $query = Drupal::entityQuery('node')->condition('type', 'note');
    $note_count = $query->count()->execute();

    $response['count'] = $note_count;


    // Find articles.
    $query = Drupal::entityQuery('node')
      ->condition('type', 'note')
      ->sort('created', 'DESC')
      ->pager($limit);
    $result = $query->execute();

    $notes = DSNode::loadMultiple($result);


    $formatter = Drupal::service('date.formatter');

    /** @var \Drupal\node\Entity\Node $note */
    foreach ($notes as $note) {
      $response['items'][] = [
        'label' => $note->label(),
        'id' => $note->id(),
        'created' => $formatter->format($note->getCreatedTime(), 'points_separated'),
        'price' => $note->get('field_price')->getValue()[0]['value'],
        'image' =>['uri'=>$note->getFirstUrlWithStyle()],
        'text'=>$note->get('body')->getValue()[0]['value'],
        'images'=>$note->getImagesListEntity()
        //        'description'=>$note->get('body')->getValue()[0]['value']
      ];
    }

    return new ResourceResponse($response, 200);
  }

  /**
   * Responds to POST requests and saves the new record.
   *
   * @param mixed $data
   *   Data to write into the database.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   The HTTP response object.
   */
  public function post($data) {

    $this->validate($data);

    $id = $this->dbConnection->insert('ds_rest_notes')
      ->fields($data)
      ->execute();

    $this->logger->notice('New notes record has been created.');

    $created_record = $this->loadRecord($id);

    // Return the newly created record in the response body.
    return new ModifiedResourceResponse($created_record, 201);
  }

  /**
   * Responds to entity PATCH requests.
   *
   * @param int $id
   *   The ID of the record.
   * @param mixed $data
   *   Data to write into the database.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   The HTTP response object.
   */
  public function patch($id, $data) {
    $this->validate($data);
    return $this->updateRecord($id, $data);
  }

  /**
   * Responds to entity DELETE requests.
   *
   * @param int $id
   *   The ID of the record.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   The HTTP response object.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   */
  public function delete($id) {

    // Make sure the record still exists.
    $this->loadRecord($id);

    $this->dbConnection->delete('ds_rest_notes')
      ->condition('id', $id)
      ->execute();

    $this->logger->notice('Notes record @id has been deleted.', ['@id' => $id]);

    // Deleted responses have an empty body.
    return new ModifiedResourceResponse(NULL, 204);
  }

  /**
   * {@inheritdoc}
   */
  protected function getBaseRoute($canonical_path, $method) {
    $route = parent::getBaseRoute($canonical_path, $method);

    // Change ID validation pattern.
    if ($method != 'POST') {
      $route->setRequirement('id', '\d+');
    }

    return $route;
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function routes() {
    $collection = parent::routes();

    // Take out BC routes added in base class.
    // @see https://www.drupal.org/node/2865645
    // @todo Remove this in Drupal 9.
    foreach ($collection as $route_name => $route) {
      if ($route instanceof BcRoute) {
        $collection->remove($route_name);
      }
    }

    return $collection;
  }

  /**
   * Validates incoming record.
   *
   * @param mixed $record
   *   Data to validate.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
   */
  protected function validate($record) {
    if (!is_array($record) || count($record) == 0) {
      throw new BadRequestHttpException('No record content received.');
    }

    $allowed_fields = [
      'title',
      'description',
      'price',
    ];

    if (count(array_diff(array_keys($record), $allowed_fields)) > 0) {
      throw new BadRequestHttpException('Record structure is not correct.');
    }

    if (empty($record['title'])) {
      throw new BadRequestHttpException('Title is required.');
    }
    elseif (isset($record['title']) && strlen($record['title']) > 255) {
      throw new BadRequestHttpException('Title is too big.');
    }
    // @DCG Add more validation rules here.
  }

  /**
   * Loads record from database.
   *
   * @param int $id
   *   The ID of the record.
   *
   * @return array
   *   The database record.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   */
  protected function loadRecord($id) {
    $record = $this->dbConnection->query('SELECT * FROM {ds_rest_notes} WHERE id = :id', [':id' => $id])
      ->fetchAssoc();
    if (!$record) {
      throw new NotFoundHttpException('The record was not found.');
    }
    return $record;
  }

  /**
   * Updates record.
   *
   * @param int $id
   *   The ID of the record.
   * @param array $record
   *   The record to validate.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   The HTTP response object.
   */
  protected function updateRecord($id, array $record) {

    // Make sure the record already exists.
    $this->loadRecord($id);

    $this->validate($record);

    $this->dbConnection->update('ds_rest_notes')
      ->fields($record)
      ->condition('id', $id)
      ->execute();

    $this->logger->notice('Notes record @id has been updated.', ['@id' => $id]);

    // Return the updated record in the response body.
    $updated_record = $this->loadRecord($id);
    return new ModifiedResourceResponse($updated_record, 200);
  }

}
