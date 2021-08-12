<?php

namespace Drupal\public_redaction\Plugin\Filter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\filter\FilterPluginManager;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Redact' filter.
 *
 * @Filter(
 *   id = "filter_public_redaction_redact",
 *   title = @Translation("Redact"),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_MARKUP_LANGUAGE,
 *   settings = {
 *     "example" = "foo",
 *   },
 *   weight = -10
 * )
 */
class FilterRedact extends FilterBase implements ContainerFactoryPluginInterface {

  /**
   * Filter manager.
   *
   * @var \Drupal\filter\FilterPluginManager
   */
  protected $filterManager;

  /**
   * Renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected RendererInterface $renderer;

  /**
   * Current User.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected AccountProxyInterface $current_user;

  /**
   * Constructs a new FilterRedact.
   *
   * @param array $configuration
   *   Configuration.
   * @param string $plugin_id
   *   Plugin ID.
   * @param mixed $plugin_definition
   *   Definition.
   * @param \Drupal\filter\FilterPluginManager $filter_manager
   *   Filter plugin manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RendererInterface $renderer, AccountProxyInterface $current_user, FilterPluginManager $filter_manager = NULL) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->filterManager = $filter_manager ?: \Drupal::service('plugin.manager.filter');
    $this->renderer = $renderer;
    $this->current_user = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): FilterRedact {
    /** @var FilterPluginManager $filter_manager */
    $filter_manager = $container->get('plugin.manager.filter');

    /** @var \Drupal\Core\Render\RendererInterface $renderer */
    $renderer = $container->get('renderer');

    /** @var \Drupal\Core\Session\AccountProxyInterface $current_user */
    $current_user = $container->get('current_user');

    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $renderer,
      $current_user,
      $filter_manager
    );
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state): array {
    $form['replacement'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Replacement Text'),
      '#default_value' => $this->settings['replacement'] ?? '[[ REDACTED ]]',
      '#description' => $this->t('The string to use to replace the redacted text  This is fixed length to avoid potential information leaks due to string length matching.'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    $result = new FilterProcessResult($text);

    if (!$this->current_user->hasPermission('view redacted information')) {
      if (stripos($text, 'drupal-redact') !== FALSE) {
        $dom = Html::load($text);
        $xpath = new \DOMXPath($dom);

        foreach ($xpath->query('//drupal-redact') as $node) {
          $redacted = [
            '#type' => 'html_tag',
            '#tag' => 'span',
            '#attributes' => [
              'class' => [
                'redacted-text',
              ],
            ],
            '#value' => $this->settings['replacement'] ?? '[[ REDACTED ]]',
          ];

          $altered_html = $this->renderer->render($redacted);

          $updated_nodes = Html::load($altered_html)
            ->getElementsByTagName('body')
            ->item(0)
            ->childNodes;

          foreach ($updated_nodes as $updated_node) {
            $updated_node = $dom->importNode($updated_node, TRUE);
            $node->parentNode->insertBefore($updated_node, $node);
          }
          $node->parentNode->removeChild($node);

        }
        $result->setProcessedText(Html::serialize($dom))
          ->setCacheContexts([
            'user.permissions'
          ])
          ->addAttachments([
            'library' => [
              'public_redaction/public_redaction',
            ],
          ]);
      }
    }

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function tips($long = FALSE) {
    return $this->t('Some filter tips here.');
  }

}
