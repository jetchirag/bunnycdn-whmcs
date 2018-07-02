<?php
/**
 * This module provides ability to manage/provision zones on bunnycdn through whmcs.
 * Developed by @jetchirag
 * Please report any issue to the github page
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

// Require any libraries needed for the module to function.
require_once __DIR__ . '/api.php';
use WHMCS\Database\Capsule;

function bunnycdn_MetaData()
{
    return array(
        'DisplayName' => 'BunnyCDN',
        'APIVersion' => '1.0',
        'RequiresServer' => true
    );
}

function bunnycdn_ConfigOptions()
{
    return array(
         'bandwidth' => array(
            'FriendlyName' => 'Bandwidth limit',
            'Type' => 'text',
            'Default' => '10737418240',
            'Description' => 'Enter in bytes',
        ),
    );
}

function bunnycdn_CreateAccount(array $params){
    try {
        
        $protocol = $params['model']->serviceProperties->get('protocol');
        
        if ($protocol != 'http' || $protocol != 'https') {
            return "Invalid Protocol given";
        }
        
        $bunny = new BunnyCDN($params['serverpassword']);
        
        $response = $bunny->CreateNewZone($params['username'], $protocol . "://" . $params['domain']);
        
        if ($response['status'] != 'success') {
            return json_encode($response['msg']);
        }
        
        $updateFields = [
            'MonthlyBandwidthLimit' => $params['configoption1']
        ];
        
        $params['model']->serviceProperties->save(['zoneid' => $response['zone_id']]);
        
        $response = $bunny->UpdateZone($response['zone_id'], $updateFields);
                
        if ($response['status'] != 'success') {
            return json_encode($response['msg']);   
        }
        
        
    } catch (Exception $e) {
        logModuleCall(
            'bunnycdn',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}

function bunnycdn_SuspendAccount(array $params)
{
    try {
        $bunny = new BunnyCDN($params['serverpassword']);
        
        $zoneid = $params['model']->serviceProperties->get('zoneid');
        
        $updateFields = [
            'MonthlyBandwidthLimit' => 1
        ];
        
        $response = $bunny->UpdateZone($zoneid, $updateFields);
                
        if ($response['status'] != 'success') {
            return json_encode($response['msg']);   
        }
    } catch (Exception $e) {
        logModuleCall(
            'bunnycdn',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}

function bunnycdn_UnsuspendAccount(array $params)
{
    try {
        $bunny = new BunnyCDN($params['serverpassword']);
        
        $zoneid = $params['model']->serviceProperties->get('zoneid');
        
        $updateFields = [
            'MonthlyBandwidthLimit' => $params['configoption1']
        ];
        
        $response = $bunny->UpdateZone($zoneid, $updateFields);
                
        if ($response['status'] != 'success') {
            return json_encode($response['msg']);   
        }
    } catch (Exception $e) {
        logModuleCall(
            'bunnycdn',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}

function bunnycdn_TerminateAccount(array $params)
{
    try {
        $bunny = new BunnyCDN($params['serverpassword']);
        
        $zoneid = $params['model']->serviceProperties->get('zoneid');
        
        $response = $bunny->DeleteZone($zoneid);
        
        if ($response['status'] != 'success') {
            return json_encode($response['msg']);
        }
    } catch (Exception $e) {
        logModuleCall(
            'bunnycdn',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}

function bunnycdn_TestConnection(array $params)
{
    try {
        $bunny = new BunnyCDN($params['serverpassword']);       
        $response = $bunny->GetZoneList();
        
        if ($response['status'] != 'success') {
            $success = false;
            $errorMsg = $response['msg'];
        }
        else {
            $success = true;
            $errorMsg = '';            
        }
        
    } catch (Exception $e) {
        logModuleCall(
            'bunnycdn',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        $success = false;
        $errorMsg = $e->getMessage();
    }

    return array(
        'success' => $success,
        'error' => $errorMsg,
    );
}

function bunnycdn_AdminCustomButtonArray()
{
    return array(
        "Purge Cache" => "purgeCache",
    );
}

function bunnycdn_ClientAreaCustomButtonArray()
{
    return array(
        "Purge Cache" => "purgeCache",
    );
}

function bunnycdn_purgeCache(array $params)
{
    try {
        $bunny = new BunnyCDN($params['serverpassword']);
        
        $zoneid = $params['model']->serviceProperties->get('zoneid');
        
        $response = $bunny->PurgeZoneCache($zoneid);
        
        if ($response['status'] != 'success') {
            return "An error occured. Please contact Administrator";
        }
    } catch (Exception $e) {
        logModuleCall(
            'bunnycdn',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}

function bunnycdn_AdminServicesTabFields(array $params)
{
    try {
        $bunny = new BunnyCDN($params['serverpassword']);
        
        $zoneid = $params['model']->serviceProperties->get('zoneid');
        
        $response = $bunny->GetZone($zoneid);
        
        if ($response['status'] != 'success') {
            return [];
        }
        
        $zoneDetails = [];
        foreach ((array)$response['zone_details'] as $key => $value) {
            if (!is_array($value)){
                $zoneDetails[$key] = $value;
            }
        }
        return $zoneDetails;
    } catch (Exception $e) {
        logModuleCall(
            'bunnycdn',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );
    }

    return array();
}

function bunnycdn_ClientArea(array $params){   
    try {
        $bunny = new BunnyCDN($params['serverpassword']);

        $zoneid = $params['model']->serviceProperties->get('zoneid');
        $cdnURL = $params['model']->serviceProperties->get('CDNURL');
        

        $response = $bunny->GetZone($zoneid);
        
        if ($response['status'] != 'success') {
            return array(
                'tabOverviewReplacementTemplate' => 'templates/error.tpl',
                'templateVariables' => array(
                    'errMsg' => 'Unknown error'
                ),
            );
        }

        $requestedAction = isset($_REQUEST['customAction']) ? $_REQUEST['customAction'] : '';

        if ($requestedAction == 'purge') {
        } else {
            $templateFile = 'templates/overview.tpl';
            return array(
                'tabOverviewReplacementTemplate' => 'templates/overview.tpl',
                'templateVariables' => array(
                    'MonthlyBandwidthLimit' => $bunny->format_bytes($response['zone_details']->MonthlyBandwidthLimit),
                    'MonthlyBandwidthUsed' => $bunny->format_bytes($response['zone_details']->MonthlyBandwidthUsed),
                    'OriginUrl' => $response['zone_details']->OriginUrl,
                    'Hostnames' => (array) $response['zone_details']->Hostnames,
                ),
            );
            
        }
    } catch (Exception $e) {
        logModuleCall(
            'bunnycdn',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return array(
            'tabOverviewReplacementTemplate' => 'error.tpl',
            'templateVariables' => array(
                'usefulErrorHelper' => $e->getMessage(),
            ),
        );
    }
}
