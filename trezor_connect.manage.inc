<?php
/**
 * @file
 * Provides manage functionality.
 */

/**
 * Provides the manage mappings form.
 *
 * @param $form
 * @param $form_state
 * @param $account
 *
 * @return array
 * @throws \Exception
 */
function trezor_connect_manage_form($form, &$form_state, $account) {
  $form = array();

  $mapping = trezor_connect_account_mapping($account);

  if (!$mapping) {
    $form_id = 'trezor_connect_manage_form';

    trezor_connect_alter_form($form, $form_state, $form_id);

    $mode = 'manage';
    $url = '/' . str_replace('%user', $account->uid, TREZOR_CONNECT_URL_MANAGE_JS);

    $form['#attached']['js'][] = array(
      'data' => array(
        'trezor_connect' => array(
          'mode' => $mode,
          'url' => $url,
          'form_id' => $form_id,
        ),
      ),
      'type' => 'setting',
    );
  }
  else {
    $header = array(
      t('Created On'),
      t('Address'),
    );

    $rows = array();

    $created = $mapping['created'];

    $row[] = format_date($created);

    $address = $mapping['address'];
    $address = check_plain($address);

    /*
    $path = 'http://blockchain.info/address/' . $address;

    $options = array(
      'attributes' => array(
        'target' => '_new',
      ),
    );

    $link = l($address, $path, $options);
    */

    $link = $address;

    $row[] = $link;

    $rows[] = $row;

    $variables = array(
      'header' => $header,
      'rows' => $rows,
    );

    $markup = theme('table', $variables);

    $form['table'] = array(
      '#markup' => $markup,
    );

    $form['forget'] = array(
      '#type' => 'submit',
      '#value' => t('Forget Authentication Device'),
    );

    $form['account'] = array(
      '#type' => 'value',
      '#value' => $account,
    );
  }

  return $form;
}

/**
 * Provides the manage mappings form submit handler.
 *
 * @param $form
 * @param $form_state
 * @param $account
 *
 * @return array
 * @throws \Exception
 */
function trezor_connect_manage_form_submit($form, &$form_state) {
  $values = $form_state['values'];

  $account = $values['account'];

  $uid = $account->uid;

  $form_state['redirect'] = 'user/' . $uid . '/trezor-connect/forget';
}

/**
 * Provides the forget authenticated device confirmation form.
 *
 * @param $form
 * @param $form_state
 * @param $account
 *
 * @return mixed
 */
function trezor_connect_forget_form($form, &$form_state, $account) {
  $form['account'] = array(
    '#type' => 'value',
    '#value' => $account,
  );

  $question = t('Are you sure you want to forget your authentication device?');
  $path = url('user/' . $account->uid . '/trezor-connect');
  $description = t('You will have to re-associate your authentication device to your account if you continue.');
  $yes = t('Confirm');
  $no = t('Cancel');

  $form = confirm_form($form, $question, $path, $description, $yes, $no);

  return $form;
}

/**
 * Provides the forget authenticated device confirmation form submit handler.
 *
 * @param $form
 * @param $form_state
 */
function trezor_connect_forget_form_submit($form, &$form_state) {
  $values = $form_state['values'];

  $account = $values['account'];

  trezor_connect_delete_mapping($account);

  $message = t('Your authentication device has been forgotten.');

  drupal_set_message($message);

  $path = url('user/' . $account->uid . '/trezor-connect');

  $form_state['redirect'] = $path;
}

/**
 * Provides the page callback used to process a TREZOR connect manage response.
 */
function trezor_connect_page_callback_manage($account, $type = 'nojs') {
  $output = NULL;

  $response = $_POST['response'];

  $result = trezor_connect_response_valid($response);

  if (!$result) {
    if ($type == 'nojs') {
      drupal_access_denied();
    }
    else {
      $commands = array();

      $selector = '';

      if (isset($_POST['selector'])) {
        $selector = $_POST['selector'];
        $selector = check_plain($selector);
        //$selector = '#' . $selector;
      }

      $arguments = array();

      $arguments['redirect'] = FALSE;

      $arguments['error'] = TRUE;

      $message = t('An error has occurred validating your TREZOR credentials.');

      $variables = array(
        'type' => 'error',
        'message' => $message,
      );

      $message = theme('trezor_connect_message', $variables);

      $arguments['message'] = $message;

      // IMPORTANT: misc/ajax.js line 605 $element[response.method].apply($element, response.arguments);
      // requires a very specific format otherwise the $arguments will be passed as undefined
      $arguments = array(
        'callback',
        $arguments,
      );

      $commands[] = ajax_command_invoke($selector, 'trezor_connect', $arguments);

      $output = array(
        '#type' => 'ajax',
        '#commands' => $commands,
      );

      $output = ajax_deliver($output);
    }
  }
  else {
    $result = trezor_connect_mapping($response);

    if (is_array($result) && isset($result['uid'])) {
      $message = t('There is already an account associated with the TREZOR device.');

      drupal_set_message($message, 'warning');
    }
    else {
      $response['uid'] = $account->uid;

      trezor_connect_write_map($response);

      $message = t('Your TREZOR device has been associated to your account.  You should now be able to login with just your TREZOR device.');

      drupal_set_message($message);
    }

    $path = str_replace('%user', $account->uid, TREZOR_CONNECT_URL_MANAGE);

    if ($type != 'ajax') {
      drupal_goto($path);
    }
    else {
      $commands = array();

      $selector = '';

      if (isset($_POST['selector'])) {
        $selector = $_POST['selector'];
        $selector = check_plain($selector);
        //$selector = '#' . $selector;
      }

      $arguments = array();

      $arguments['redirect'] = TRUE;

      $variables = array(
        'message' => $message,
      );

      $message = theme('trezor_connect_message', $variables);

      $arguments['message'] = $message;

      $arguments['url'] = '/' . $path;

      // IMPORTANT: misc/ajax.js line 605 $element[response.method].apply($element, response.arguments);
      // requires a very specific format otherwise the $arguments will be passed as undefined
      $arguments = array(
        'callback',
        $arguments,
      );

      $commands[] = ajax_command_invoke($selector, 'trezor_connect', $arguments);

      $output = array(
        '#type' => 'ajax',
        '#commands' => $commands,
      );

      $output = ajax_deliver($output);
    }
  }

  return $output;
}
