<?php

namespace Drupal\currency_converter\Plugin\rest\resource;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Site\Settings;
use Drupal\rest\Plugin\ResourceBase;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a resource to get currency layer api.
 * @RestResource(
 *   id = "get_content_rest_resource",
 *   label = @Translation("API Currency"),
 *   uri_paths = {
 *     "canonical" = "/api/currency1"
 *   }
 * )
 */
class CurrencyLayerApi extends ResourceBase {
  /**
   * Currency layer API_URL
   * @var Drupal\Core\Site\Settings
   */
  protected $api_url;

  /**
   * Currency layer API_TOKEN.
   *
   * @var Drupal\Core\Site\Settings;
   */
  protected $apiKey;

  /**
   * Http client.
   *
   * @var \GuzzleHttp\Client
   */
  protected $client;

  /**
   * Cache bin service.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheService;

  /**
   * Constructs a Drupal\rest\Plugin\ResourceBase object.
   *
   * @param array $config
   *   A configuration array which contains the
   *   information about the plugin instance.
   * @param string $module_id
   *   The module_id for the plugin instance.
   * @param mixed $module_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \GuzzleHttp\Client $client
   *   Http client instance.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend.
   */
  public function __construct(array $config, $module_id, $module_definition, array $serializer_formats, LoggerInterface $logger, Client $client, CacheBackendInterface $cache_backend) {
    parent::__construct($config, $module_id, $module_definition, $serializer_formats, $logger);
    $this->client = $client;
    $this->cacheService = $cache_backend;
    $this->apiUrl = Settings::get('currency_api_url');
    $this->apiKey = Settings::get('currency_api_key');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $config, $module_id, $module_definition) {
    return new static(
      $config,
      $module_id,
      $module_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('rest'),
      $container->get('http_client'),
    );
  }

  /**
   * Responds to GET request.
   *
   * GET request to handle the api by fetching the data as a middleware
   * from external api
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   * @return Symfony\Component\HttpFoundation\JsonResponse
   *   The JsonResponse object.
   */
  public function get(Request $request): JsonResponse {
    $data = [];
    $query_params = $request->query->all();
    $options = [
      'headers' => ['apikey' => $this->apiKey],
      'query' => $query_params,
    ];
    try {
      $response = $this->client->get($this->apiUrl, $options);
      $data = Json::decode($response->getBody()->getContents());
    }
    catch (\Exception $e) {
      // If the api itself is not available we show our custom error message.
      $this->logger->log('error', $e->getMessage());
      return new JsonResponse(
        [
          'error' => [
            'message' => 'Currency API is not available',
            'code' => 502,
          ],
        ], 502);
    }
    return new JsonResponse($data);
  }

}
