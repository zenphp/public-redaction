<?php

namespace Drupal\public_redaction\Plugin\CKEditorPlugin;

use Drupal\ckeditor\CKEditorPluginBase;
use Drupal\editor\Entity\Editor;

/**
 * Defines the "Redact Text" plugin.
 *
 * @CKEditorPlugin(
 *   id = "public_redaction_redact_text",
 *   label = @Translation("Redact Text"),
 *   module = "public_redaction"
 * )
 */
class RedactText extends CKEditorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getFile() {
    return drupal_get_path('module', 'public_redaction') . '/js/plugins/redact-text/plugin.js';
  }

  /**
   * {@inheritdoc}
   */
  public function getConfig(Editor $editor) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getButtons() {
    $module_path = drupal_get_path('module', 'public_redaction');
    return [
      'redact-text' => [
        'label' => $this->t('Redact Text'),
        'image' => $module_path . '/js/plugins/redact-text/icons/redact-text.png',
      ],
    ];
  }

}
