/**
 * @file
 * Redact Text CKEditor plugin.
 *
 * Basic plugin inserting abbreviation elements into the CKEditor editing area.
 *
 * @DCG The code is based on an example from CKEditor Plugin SDK tutorial.
 *
 * @see http://docs.ckeditor.com/#!/guide/plugin_sdk_sample_1
 */

(function (Drupal) {

  'use strict';

  CKEDITOR.plugins.add('public_redaction_redact_text', {

    // Register the icons.
    icons: 'redact-text',

    beforeInit: function (editor) {
      var dtd = CKEDITOR.dtd;
      dtd['drupal-redact'] = 1;
      dtd.$inline['drupal-redact'] = 1;
    },

    // The plugin initialization logic goes inside this method.
    init: function (editor) {

      // Define an editor command that opens our dialog window.
      editor.addCommand('redactText', {
        exec: function (editor) {
          editor.insertHtml('<drupal-redact>' + editor.getSelection().getSelectedText() + '</drupal-redact>', 'unfiltered_html');
        }
      });



      // Create a toolbar button that executes the above command.
      editor.ui.addButton('redact-text', {

        // The text part of the button (if available) and the tooltip.
        label: Drupal.t('Redact Text'),

        // The command to execute on click.
        command: 'redactText',

        // The button placement in the toolbar (toolbar group name).
        toolbar: 'insert'
      });

    }
  });

} (Drupal));
