<?php

require_once "defines/defines.php";
require_once "classes/ipam_manager.php";

//Create instance of ipam manager.
$ipamManager = new clsIPAMManager();

//Connect to the houston edge router.
$routerConnection = $ipamManager->ConnectToRouter(ROUTER_IP,22,ROUTER_USERNAME,ROUTER_PASSWORD);

//Verify we got a connection and were able to authenticate
if (!$routerConnection)
{
    echo "Could not connect/authenticate with router" . PHP_EOL;
    die();
}

echo "Connection to router successful" . PHP_EOL;

//Retrieve a list of ips that are routed in the router.
$routes = $ipamManager->GetRoutedIps();

//Disconnect from router
$ipamManager->DisconnectFromRouter();

//Disconnect from any switches we have connection to
$ipamManager->DisconnectFromSwitches();



