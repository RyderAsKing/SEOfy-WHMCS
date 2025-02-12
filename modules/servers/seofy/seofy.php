<?php

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

function seofy_MetaData()
{
    return [
        'DisplayName' => 'SEOfy Provisioning Module',
        'APIVersion' => '1', // Use API Version 1
        'RequiresServer' => true, // Set true if module requires a server to work
        'DefaultNonSSLPort' => '80', // Default Non-SSL Connection Port
        'DefaultSSLPort' => '443', // Default SSL Connection Port
        'ServiceSingleSignOnLabel' => 'Login to SEOfy as User',
    ];
}

function seofy_ConfigOptions()
{
    return [
        'planId' => [
            'FriendlyName' => 'Plan ID',
            'Type' => 'text',
            'Size' => '25',
            'Default' => '1',
            'Description' => 'Enter plan ID from SEOfy here',
        ],
    ];
}

function seofy_CreateAccount(array $params)
{
    try {
        // die(print '<pre>' . print_r($params, true) . '</pre>');

        // checking if SEOfy ID exists
        if ($params['model']->serviceProperties->get('seofy_id')) {
            return 'Project already exists in SEOfy';
        }
        // user data

        $server = [
            'hostname' => $params['serverhostname'],
            'api' => $params['serveraccesshash'],
        ];

        $protocol = $params['serversecure'] ? 'https' : 'http';

        $apiEndpoint =
            $protocol .
            '://' .
            $server['hostname'] .
            '/api/whmcs/create-account';

        $request = [
            'ext_id' => $params['clientsdetails']['userid'],
            'name' =>
                $params['clientsdetails']['firstname'] .
                ' ' .
                $params['clientsdetails']['lastname'],
            'email' => $params['clientsdetails']['email'],
            'password' => $params['password'],
            'plan_id' => $params['configoption1'],
            'project_name' =>
                $params['domain'] .
                ' - ' .
                $params['clientsdetails']['firstname'] .
                ' ' .
                $params['clientsdetails']['lastname'],
            'project_url' => 'https://' . $params['domain'],
            'project_description' =>
                $params['customfields']['description'] .
                ' ' .
                $params['domain'],
        ];

        // Convert data to JSON format
        $jsonData = json_encode($request);

        // Bearer token
        $api = $server['api'];
        // Set cURL options
        $ch = curl_init($apiEndpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-API-KEY: ' . $api,
        ]);

        // Execute the cURL request
        $response = curl_exec($ch);

        // Check for errors
        if (curl_errno($ch)) {
            echo 'Error: ' . curl_error($ch);
        }

        // Close cURL session
        curl_close($ch);

        // Process the response
        $response = json_decode($response, true);
        if ($response['success']) {
            $params['model']->serviceProperties->save([
                'seofy_id' => $response['project_id'],
            ]);

            return 'success';
        } else {
            return $response['message'];
        }
    } catch (Exception $e) {
        logModuleCall(
            'seofy',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}

/**
 * Suspend an instance of a product/service.
 *
 * Called when a suspension is requested. This is invoked automatically by WHMCS
 * when a product becomes overdue on payment or can be called manually by admin
 * user.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 *
 * @return string "success" or an error message
 */
function seofy_SuspendAccount(array $params)
{
    try {
        if (!$params['model']->serviceProperties->get('seofy_id')) {
            return 'Project does not exist in SEOfy';
        }

        $server = [
            'hostname' => $params['serverhostname'],
            'api' => $params['serveraccesshash'],
        ];

        $protocol = $params['serversecure'] ? 'https' : 'http';

        $apiEndpoint =
            $protocol .
            '://' .
            $server['hostname'] .
            '/api/whmcs/suspend-account';

        $request = [
            'ext_id' => $params['clientsdetails']['userid'],
            'project_id' => $params['model']->serviceProperties->get(
                'seofy_id'
            ),
        ];

        // Convert data to JSON format
        $jsonData = json_encode($request);

        // Bearer token
        $api = $server['api'];

        // Set cURL options
        $ch = curl_init($apiEndpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-API-KEY: ' . $api,
        ]);

        // Execute the cURL request
        $response = curl_exec($ch);

        // Check for errors
        if (curl_errno($ch)) {
            echo 'Error: ' . curl_error($ch);
        }

        // Close cURL session
        curl_close($ch);

        // Process the response
        $response = json_decode($response, true);
        if ($response['success']) {
            return 'success';
        } else {
            return $response['message'];
        }
    } catch (Exception $e) {
        logModuleCall(
            'seofy',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}

/**
 * Un-suspend instance of a product/service.
 *
 * Called when an un-suspension is requested. This is invoked
 * automatically upon payment of an overdue invoice for a product, or
 * can be called manually by admin user.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 *
 * @return string "success" or an error message
 */
function seofy_UnsuspendAccount(array $params)
{
    try {
        if (!$params['model']->serviceProperties->get('seofy_id')) {
            return 'Project does not exist in SEOfy';
        }

        $server = [
            'hostname' => $params['serverhostname'],
            'api' => $params['serveraccesshash'],
        ];

        $protocol = $params['serversecure'] ? 'https' : 'http';

        $apiEndpoint =
            $protocol .
            '://' .
            $server['hostname'] .
            '/api/whmcs/unsuspend-account';

        $request = [
            'ext_id' => $params['clientsdetails']['userid'],
            'project_id' => $params['model']->serviceProperties->get(
                'seofy_id'
            ),
        ];

        // Convert data to JSON format
        $jsonData = json_encode($request);

        // Bearer token
        $api = $server['api'];

        // Set cURL options
        $ch = curl_init($apiEndpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-API-KEY: ' . $api,
        ]);

        // Execute the cURL request
        $response = curl_exec($ch);

        // Check for errors
        if (curl_errno($ch)) {
            echo 'Error: ' . curl_error($ch);
        }

        // Close cURL session
        curl_close($ch);

        // Process the response
        $response = json_decode($response, true);
        if ($response['success']) {
            return 'success';
        } else {
            return $response['message'];
        }
    } catch (Exception $e) {
        logModuleCall(
            'seofy',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}

/**
 * Terminate instance of a product/service.
 *
 * Called when a termination is requested. This can be invoked automatically for
 * overdue products if enabled, or requested manually by an admin user.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 *
 * @return string "success" or an error message
 */
function seofy_TerminateAccount(array $params)
{
    try {
        if (!$params['model']->serviceProperties->get('seofy_id')) {
            return 'Project does not exist in SEOfy';
        }

        $server = [
            'hostname' => $params['serverhostname'],
            'api' => $params['serveraccesshash'],
        ];

        $protocol = $params['serversecure'] ? 'https' : 'http';

        $apiEndpoint =
            $protocol .
            '://' .
            $server['hostname'] .
            '/api/whmcs/terminate-account';

        $request = [
            'ext_id' => $params['clientsdetails']['userid'],
            'project_id' => $params['model']->serviceProperties->get(
                'seofy_id'
            ),
        ];

        // Convert data to JSON format

        $jsonData = json_encode($request);

        // Bearer token
        $api = $server['api'];

        // Set cURL options
        $ch = curl_init($apiEndpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-API-KEY: ' . $api,
        ]);

        // Execute the cURL request
        $response = curl_exec($ch);

        // Check for errors
        if (curl_errno($ch)) {
            return 'Error: ' . curl_error($ch);
        }

        // Close cURL session
        curl_close($ch);

        // Process the response
        $response = json_decode($response, true);

        // die(print '<pre>' . print_r($response) . (print '</pre>'));

        if ($response['success']) {
            $params['model']->serviceProperties->save(['seofy_id' => null]);
            return 'success';
        } else {
            return $response['message'];
        }
    } catch (Exception $e) {
        logModuleCall(
            'seofy',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}

/**
 * Change the password for an instance of a product/service.
 *
 * Called when a password change is requested. This can occur either due to a
 * client requesting it via the client area or an admin requesting it from the
 * admin side.
 *
 * This option is only available to client end users when the product is in an
 * active status.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 *
 * @return string "success" or an error message
 */
// function seofy_ChangePassword(array $params)
// {
//     try {
//         // Call the service's change password function, using the values
//         // provided by WHMCS in `$params`.
//         //
//         // A sample `$params` array may be defined as:
//         //
//         // ```
//         // array(
//         //     'username' => 'The service username',
//         //     'password' => 'The new service password',
//         // )
//         // ```
//     } catch (Exception $e) {
//         // Record the error in WHMCS's module log.
//         logModuleCall(
//             'seofy',
//             __FUNCTION__,
//             $params,
//             $e->getMessage(),
//             $e->getTraceAsString()
//         );

//         return $e->getMessage();
//     }

//     return 'success';
// }

/**
 * Renew an instance of a product/service.
 *
 * Attempt to renew an existing instance of a given product/service. This is
 * called any time a product/service invoice has been paid.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 *
 * @return string "success" or an error message
 */
function seofy_Renew(array $params)
{
    try {
        if (!$params['model']->serviceProperties->get('seofy_id')) {
            return 'Project does not exist in SEOfy';
        }

        $server = [
            'hostname' => $params['serverhostname'],
            'api' => $params['serveraccesshash'],
        ];

        $protocol = $params['serversecure'] ? 'https' : 'http';

        $apiEndpoint =
            $protocol . '://' . $server['hostname'] . '/api/whmcs/renew';

        $request = [
            'ext_id' => $params['clientsdetails']['userid'],
            'project_id' => $params['model']->serviceProperties->get(
                'seofy_id'
            ),
        ];

        // Convert data to JSON format

        $jsonData = json_encode($request);

        // Bearer token
        $api = $server['api'];

        // Set cURL options
        $ch = curl_init($apiEndpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-API-KEY: ' . $api,
        ]);

        // Execute the cURL request
        $response = curl_exec($ch);

        // Check for errors
        if (curl_errno($ch)) {
            return 'Error: ' . curl_error($ch);
        }

        // Close cURL session
        curl_close($ch);

        // Process the response
        $response = json_decode($response, true);

        // die(print '<pre>' . print_r($response) . (print '</pre>'));

        if ($response['success']) {
            return 'success';
        } else {
            return $response['message'];
        }
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'seofy',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}

/**
 * Test connection with the given server parameters.
 *
 * Allows an admin user to verify that an API connection can be
 * successfully made with the given configuration parameters for a
 * server.
 *
 * When defined in a module, a Test Connection button will appear
 * alongside the Server Type dropdown when adding or editing an
 * existing server.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 *
 * @return array
 */
function seofy_TestConnection(array $params)
{
    try {
        // Call the service's connection test function.

        $success = true;
        $errorMsg = '';
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'seofy',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        $success = false;
        $errorMsg = $e->getMessage();
    }

    return [
        'success' => $success,
        'error' => $errorMsg,
    ];
}

/**
 * Additional actions a client user can invoke.
 *
 * Define additional actions a client user can perform for an instance of a
 * product/service.
 *
 * Any actions you define here will be automatically displayed in the available
 * list of actions within the client area.
 *
 * @return array
 */
function seofy_ClientAreaCustomButtonArray()
{
    return [
        'View in SEOfy' => 'ServiceSingleSignOn',
    ];
}

/**
 * Custom function for performing an additional action.
 *
 * You can define an unlimited number of custom functions in this way.
 *
 * Similar to all other module call functions, they should either return
 * 'success' or an error message to be displayed.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 * @see seofy_ClientAreaCustomButtonArray()
 *
 * @return string "success" or an error message
 */
function seofy_actionOneFunction(array $params)
{
    try {
        // Call the service's function, using the values provided by WHMCS in
        // `$params`.
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'seofy',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}

/**
 * Admin services tab additional fields.
 *
 * Define additional rows and fields to be displayed in the admin area service
 * information and management page within the clients profile.
 *
 * Supports an unlimited number of additional field labels and content of any
 * type to output.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 * @see seofy_AdminServicesTabFieldsSave()
 *
 * @return array
 */
function seofy_AdminServicesTabFields(array $params)
{
    try {
        // Call the service's function, using the values provided by WHMCS in
        // `$params`.
        $response = [];

        // Return an array based on the function's response.
        return [
            'SEOfy Project ID' => (int) $params[
                'model'
            ]->serviceProperties->get('seofy_id'),
        ];
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'seofy',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        // In an error condition, simply return no additional fields to display.
    }

    return [];
}

/**
 * Execute actions upon save of an instance of a product/service.
 *
 * Use to perform any required actions upon the submission of the admin area
 * product management form.
 *
 * It can also be used in conjunction with the AdminServicesTabFields function
 * to handle values submitted in any custom fields which is demonstrated here.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 * @see seofy_AdminServicesTabFields()
 */
function seofy_AdminServicesTabFieldsSave(array $params)
{
    // Fetch form submission variables.
    $originalFieldValue = isset($_REQUEST['seofy_original_uniquefieldname'])
        ? $_REQUEST['seofy_original_uniquefieldname']
        : '';

    $newFieldValue = isset($_REQUEST['seofy_uniquefieldname'])
        ? $_REQUEST['seofy_uniquefieldname']
        : '';

    // Look for a change in value to avoid making unnecessary service calls.
    if ($originalFieldValue != $newFieldValue) {
        try {
            // Call the service's function, using the values provided by WHMCS
            // in `$params`.
        } catch (Exception $e) {
            // Record the error in WHMCS's module log.
            logModuleCall(
                'seofy',
                __FUNCTION__,
                $params,
                $e->getMessage(),
                $e->getTraceAsString()
            );

            // Otherwise, error conditions are not supported in this operation.
        }
    }
}

/**
 * Perform single sign-on for a given instance of a product/service.
 *
 * Called when single sign-on is requested for an instance of a product/service.
 *
 * When successful, returns a URL to which the user should be redirected.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 *
 * @return array
 */
function seofy_ServiceSingleSignOn(array $params)
{
    try {
        // Call the service's single sign-on token retrieval function, using the
        // values provided by WHMCS in `$params`.

        $server = [
            'hostname' => $params['serverhostname'],
            'api' => $params['serveraccesshash'],
        ];

        $protocol = $params['serversecure'] ? 'https' : 'http';

        $apiEndpoint =
            $protocol . '://' . $server['hostname'] . '/api/whmcs/sso';

        $request = [
            'ext_id' => $params['clientsdetails']['userid'],
            'project_id' => $params['model']->serviceProperties->get(
                'seofy_id'
            ),
        ];

        // Convert data to JSON format
        $jsonData = json_encode($request);

        // Bearer token
        $api = $server['api'];
        // Set cURL options
        $ch = curl_init($apiEndpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-API-KEY: ' . $api,
        ]);

        // Execute the cURL request
        $response = curl_exec($ch);

        // Check for errors
        if (curl_errno($ch)) {
            echo 'Error: ' . curl_error($ch);
        }

        // Close cURL session
        curl_close($ch);

        // Process the response
        $response = json_decode($response, true);

        // dump the response (die)
        // die(print '<pre>' . print_r($response) . (print '</pre>'));
        if ($response['error']) {
            return $response['error'];
        }
        return [
            'success' => true,
            'redirectTo' => $response['redirectTo'],
        ];
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'seofy',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return [
            'success' => false,
            'errorMsg' => $e->getMessage(),
        ];
    }
}

/**
 * Client area output logic handling.
 *
 * This function is used to define module specific client area output. It should
 * return an array consisting of a template file and optional additional
 * template variables to make available to that template.
 *
 * The template file you return can be one of two types:
 *
 * * tabOverviewModuleOutputTemplate - The output of the template provided here
 *   will be displayed as part of the default product/service client area
 *   product overview page.
 *
 * * tabOverviewReplacementTemplate - Alternatively using this option allows you
 *   to entirely take control of the product/service overview page within the
 *   client area.
 *
 * Whichever option you choose, extra template variables are defined in the same
 * way. This demonstrates the use of the full replacement.
 *
 * Please Note: Using tabOverviewReplacementTemplate means you should display
 * the standard information such as pricing and billing details in your custom
 * template or they will not be visible to the end user.
 *
 * @param array $params common module parameters
 *
 * @see https://developers.whmcs.com/provisioning-modules/module-parameters/
 *
 * @return array
 */
function seofy_ClientArea(array $params)
{
    // Determine the requested action and set service call parameters based on
    // the action.
    $requestedAction = isset($_REQUEST['customAction'])
        ? $_REQUEST['customAction']
        : '';

    if ($requestedAction == 'manage') {
        $serviceAction = 'get_usage';
        $templateFile = 'templates/manage.tpl';
    } else {
        $serviceAction = 'get_stats';
        $templateFile = 'templates/overview.tpl';
    }

    try {
        // Call the service's function based on the request action, using the
        // values provided by WHMCS in `$params`.
        $response = [];

        $view =
            'clientarea.php?action=productdetails&id=' .
            $params['serviceid'] .
            '&dosinglesignon=1';

        return [
            'tabOverviewReplacementTemplate' => $templateFile,
            'templateVariables' => [
                'view' => $view,
            ],
        ];
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'seofy',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        // In an error condition, display an error page.
        return [
            'tabOverviewReplacementTemplate' => 'error.tpl',
            'templateVariables' => [
                'usefulErrorHelper' => $e->getMessage(),
            ],
        ];
    }
}
