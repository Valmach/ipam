<?php
/**
 * Created by PhpStorm.
 * User: Mike Jones
 * Date: 12/14/2017
 * Time: 2:56 PM
 */

require_once "../defines/defines.php";

function AddOneToRange($range)
{
    $rangeParts = explode("/",$range);
    $subnet = $rangeParts[1];
    $range = $rangeParts[0];
    $rangeParts = explode(".",$range);
    $rangeParts[3] = $rangeParts[3] + 1;
    $range = implode(".",$rangeParts) . "/" . $subnet;

    return $range;
}

class clsIPRange
{
    private $m_hostAddress = null;
    public function GetHostAddress() { return $this->m_hostAddress; }

    private $m_hosts = array();
    public function GetHosts() { return $this->m_hosts; }

    private $m_maskBits = null;
    public function GetMaskBits() { return $this->m_maskBits; }

    private $m_cidr = null;
    public function GetRangeCIDR() { return $this->m_cidr; }


    private function PopulateHosts($start,$end,$itteration=null)
    {
        $addressParts = explode(".", $this->m_hostAddress);

        for ($i = $start; $i <= $end; $i++) {
            $this->m_hosts[] = $addressParts[0] . "." . $addressParts[1] . "." . $addressParts[2] . "." . $i;
        }

        if ($itteration)
        {
            for ($j=1; $j<=$itteration; $j++ ) {
                $b = $addressParts[2] + $j;

                for ($i = $start; $i <= $end; $i++) {
                    $this->m_hosts[] = $addressParts[0] . "." . $addressParts[1] . "." . $b . "." . $i;
                }
            }
        }




    }

    public function __construct($ipRange)
    {
        $this->m_cidr = $ipRange;

        //Get the mask bits from the ip range
        $parts = explode("/",$ipRange);
        $this->m_maskBits = $parts[1];

        //Get the host address
        $this->m_hostAddress = $parts[0];

        //Now we populate the hosts.
        switch ($this->m_maskBits)
        {
            case 16:
            {
                $start = 0;
                $end = 255;

                $this->PopulateHosts($start,$end,255);

                break;
            }
            case 17:
            {
                $start = 0;
                $end = 255;

                $this->PopulateHosts($start,$end,127);

                break;
            }

            case 18:
            {
                $start = 0;
                $end = 255;

                $this->PopulateHosts($start,$end,63);

                break;

            }
            case 19:
            {
                $start = 0;
                $end = 255;

                $this->PopulateHosts($start,$end,31);

                break;
            }

            case 20:
            {
                $start = 0;
                $end = 255;

                $this->PopulateHosts($start,$end,15);

                break;
            }
            case 21:
            {
                $start = 0;
                $end = 255;

                $this->PopulateHosts($start,$end,7);

                break;
            }
            case 22:
            {
                $start = 0;
                $end = 255;

                $this->PopulateHosts($start,$end,3);

                break;
            }

            case 23:
            {
                $start = 0;
                $end = 255;

                $this->PopulateHosts($start,$end,1);

                break;
            }

            case 24:
            {
               $start = 0;
               $end = 255;
               $this->PopulateHosts($start,$end);
               break;
            }

            case 25:
            {
                $addressParts = explode(".",$this->m_hostAddress);

                $start = $addressParts[3];
                $end = $start + 127;
                $this->PopulateHosts($start,$end);
                break;
            }

            case 26:
            {
                $addressParts = explode(".",$this->m_hostAddress);
                $start = $addressParts[3];
                $end = $start + 63;
                $this->PopulateHosts($start,$end);
                break;
            }

            case 27:
            {
                $addressParts = explode(".",$this->m_hostAddress);
                $start = $addressParts[3];
                $end = $start + 31;
                $this->PopulateHosts($start,$end);
                break;
            }

            case 28:
            {
                $addressParts = explode(".",$this->m_hostAddress);
                $start = $addressParts[3];
                $end = $start + 15;
                $this->PopulateHosts($start,$end);
                break;
            }

            case 29:
            {
                $addressParts = explode(".",$this->m_hostAddress);
                $start = $addressParts[3];
                $end = $start + 7;
                $this->PopulateHosts($start,$end);
                break;

            }

            case 30:
            {
                $addressParts = explode(".",$this->m_hostAddress);
                $start = $addressParts[3];
                $end = $start + 3;
                $this->PopulateHosts($start,$end);
                break;
            }

            case 31:
            {
                $addressParts = explode(".",$this->m_hostAddress);
                $start = $addressParts[3];
                $end = $start + 1;
                $this->PopulateHosts($start,$end);
                break;
            }

            case 32:
            {
                $addressParts = explode(".",$this->m_hostAddress);
                $start = $addressParts[3];
                $end = $start;
                $this->PopulateHosts($start,$end);
                break;
            }

        }

    }
}

class clsRoute
{
    private $m_ipRangeObj;           //The range that is routed.
    private $m_routeDestination;  //The routes destination

    public function __construct(clsIPRange $ipRange,$routeDestination)
    {
        $this->m_ipRangeObj = $ipRange;
        $this->m_routeDestination = $routeDestination;
    }

    public function getIPRaange()
    {
        return $this->m_ipRangeObj;
    }

    public function getRouteDestination()
    {
        return $this->m_routeDestination;
    }
}

class clsSwitchIrbUnit
{
    //The ips that belong to the irb unit.
    private $m_ipRanges = null;
    public function getIPRanges() { return $this->m_ipRanges; }

    //The id of this unit
    private $m_unitID = null;

    public function __construct($unitID, array $arrRanges)
    {
        $this->m_unitID = $unitID;
        $this->m_ipRanges = $arrRanges;
    }

    public function IsRangeInIrbUnit($rangeToCheck)
    {
        foreach ($this->m_ipRanges as $range)
        {
            $rangeCIDR = $range->GetRangeCIDR();
            $checkAgainst = AddOneToRange($rangeToCheck->GetRangeCIDR());
          //  echo "Checking " . $rangeToCheck->GetRangeCIDR() . " against $checkAgainst" . PHP_EOL;
            if ($rangeCIDR == $checkAgainst)
            {
                return true;
            }
        }

        return false;
    }

    public function IsPartialRangeInIrbUnit($rangeToCheck)
    {
        //Get an array of ips in the range we want to check for
        $ipsToCheckArray = $rangeToCheck->GetHosts();

        $partialRangesArray = array();

        //Loop through the ranges in the irb unit
        foreach ($this->m_ipRanges as $range) {

            //Get an array of ips we are checking against
            $ipsToCheckAgainstArray = $range->GetHosts();

            //intersect the two arrays, the result will hold any ips from the range we want to check that belong to this irb unit
            $ipsInIrbUnit = array_intersect($ipsToCheckArray,$ipsToCheckAgainstArray);

            $numIpsInIRBUnit = count($ipsInIrbUnit);
            if ($numIpsInIRBUnit > 0)
            {
                echo $range->GetRangeCIDR() . " is within " . $rangeToCheck->GetRangeCIDR() . "\n";
                $partialRangesArray[] = $range;
            }

        }


        return $partialRangesArray;
    }
}

class clsSwitchVLAN
{
    private $m_id = null;
    public function GetVlanID() { return $this->m_id; }

    private $m_irbUnit = null;
    public function GetVlanIrbUnit() { return $this->m_irbUnit; }

    private $m_name = null;
    public function GetVlanName() { return $this->m_name; }

    private $m_description = null;
    public function GetVlanDescription() { return $this->m_description; }

    private $m_switchIBelongTo = null;

    public function __construct($id,$name,$description,$irbUnitName,$switch)
    {
        $this->m_id                 = $id;
        $this->m_name               = $name;
        $this->m_description        = $description;
        $this->m_switchIBelongTo    = $switch;

        $this->ParseIrbUnit($irbUnitName);
    }

    private function ParseIrbUnit($irbUnitName)
    {
        //Command to get the interface irb unit for this vlan
        $command = "show configuration interfaces irb unit $irbUnitName";

        //Run the command on the switch
        $stream = $this->m_switchIBelongTo->ExecuteCommand($command);
        if (!$stream)
        {
            echo "Failed to execute command on the switch" . PHP_EOL;
            return false;
        }

        //Split into an array of config lines.
        $arrLines = explode("\n",$stream);

        //An array to store the ranges that belong to the irb unit
        $arrRanges = array();

        //Loop through each line
        foreach ($arrLines as $line) {

            //Break into words
            $lineParts = explode(" ",trim($line));

            //Check if this is an address line.
            if (trim($lineParts[0]) == "address")
            {
                //Add this range to the array of ranges
                $arrRanges[] = new clsIPRange(str_replace(";","",trim($lineParts[1])));
            }
        }

        //Create the irb unit.
        $this->m_irbUnit = new clsSwitchIrbUnit($irbUnitName,$arrRanges);

        return true;
    }

    public function GetIPSInVLAN()
    {
        if ($this->m_irbUnit) {
            return $this->m_irbUnit->getIPRanges();
        }

        return null;
    }

    public function IsRangeInVlan($rangeToCheck)
    {
        //Check to make sure the vlan has an irb unit.
        if ($this->m_irbUnit) {
            //Check the irb unit for the range.
            return $this->m_irbUnit->IsRangeInIrbUnit($rangeToCheck);
        }

        return false;
    }

    public function IsPartialRangeInVlan($rangeToCheck)
    {
        //check to make sure the vlan has irb unit,
        if ($this->m_irbUnit)
        {
            //Check the irb unit for a partial range.
            return $this->m_irbUnit->IsPartialRangeInIrbUnit($rangeToCheck);
        }

        return false;
    }


}

class clsSwitchPort
{
    //The port description
    private $m_portDescription = null;
    public function GetPortDescription() { return $this->m_portDescription; }

    //The port number
    private $m_portNumber =  null;
    public function GetPortNumber() { return $this->m_portNumber; }

    //The vlan assigned to port
    private $m_portVLAN = null;
    public function GetPortVLAN() { return $this->m_portVLAN; }

    //The ranges assigned directly to the port.
    private $m_portRanges = null;
    public function GetPortRanges() { return $this->m_portRanges; }

    //The type of port
    private $m_portType = null;
    public function GetPortType() { return $this->m_portType; }

    public function __construct($portNumber, $portDescription, $portType, $portRanges, $portVLAN)
    {
        $this->m_portNumber = $portNumber;
        $this->m_portDescription = $portDescription;
        $this->m_portType = $portType;
        $this->m_portRanges = $portRanges;
        $this->m_portVLAN = $portVLAN;
    }


}

class clsSwitch
{
    //The ssh connection resource to the switch
    private $m_switchConnection;
    public function GetSwitchConnection() { return $this->m_switchConnection; }

    private $m_switchIP;
    public function GetSwitchIP() { return $this->m_switchIP; }

    private $m_switchUsername = null;
    private $m_switchPassword = null;
    private $m_switchSSHPort  = null;

    private $m_switchVLANs = array();
    public function GetSwitchVLANS() { return $this->m_switchVLANs; }

    private $m_ports = array();

    public function __construct($switchIP,$user,$pass,$port)
    {
        $this->m_switchIP = $switchIP;
        $this->m_switchUsername = $user;
        $this->m_switchPassword = $pass;
        $this->m_switchSSHPort = $port;
    }

    public function ConnectToSwitch()
    {
        echo "Connecting to switch " . $this->m_switchIP . PHP_EOL;

        //Create ssh connection to the router.
        $connection = ssh2_connect($this->m_switchIP, $this->m_switchSSHPort);
        if (!$connection)
        {
            echo "Connection to switch failed." . PHP_EOL;
            return false;
        }

        //Authenticate
        $authResult = ssh2_auth_password($connection, $this->m_switchUsername,$this->m_switchPassword);
        if (!$authResult)
        {
            echo "Connection failed to authenticate" . PHP_EOL;
            return false;
        }

        $this->m_switchConnection = $connection;

        echo "Connection to switch successfull" . PHP_EOL;
        return true;
    }

    public function DisconnectFromSwitch()
    {
        if ($this->m_switchConnection)
        {
            echo "Disconnecting from switch " . $this->m_switchIP . PHP_EOL;
            ssh2_exec($this->m_switchConnection, 'exit');
            unset($this->m_switchConnection);
            echo "Disconnected from switch" . PHP_EOL;

        }
    }

    public function ExecuteCommand($command)
    {
        //Make sure we have a connection
        if (!$this->m_switchConnection)
        {
            echo "Cant run command on switch, no connection present." . PHP_EOL;
            return false;
        }
        $stream = ssh2_exec($this->m_switchConnection, $command);
        if (!$stream)
        {
            echo "Failed to execute command $command" . PHP_EOL;
            return false;
        }

        //Set the stream to blocking mode.
        $result = stream_set_blocking($stream, true);
        if (!$result)
        {
            echo "Failed to apply blocking mode to the stream" . PHP_EOL;
            return false;
        }

        //Get the stream contents that will hold the routing options
        $streamContents = stream_get_contents($stream);

        return $streamContents;
    }

    public function ParseSwitchVlans()
    {
        //The command to run.
        $command = "show configuration vlans";

        //Run the command.
        $vlansConfig = $this->ExecuteCommand($command);

        //Make sure we got the config
        if (!$vlansConfig)
        {
            echo "Failed to retrieve vlans" . PHP_EOL;
            return false;
        }

        //Break into lines.
        $arr = explode("\n",$vlansConfig);

        $nextLineIsID = false;
        $nextLineIsInterface = false;

        $interface = null;
        $vlanID = null;
        $vlanName = null;
        $vlanDescription = null;

        //Loop through each line
        foreach ($arr as $line)
        {
            //See if this line is the interface
            if ($nextLineIsInterface)
            {
                //Get the interface
                $lineParts = explode(" ",trim($line));
                $irbUnitName = $lineParts[1];
                $irbUnitNameParts = explode(".",$irbUnitName);
                $irbUnitName = $irbUnitNameParts[1];

                $nextLineIsID = false;
                $nextLineIsInterface = false;
                continue;
            }
            //See if this line is the vlan id.
            if ($nextLineIsID)
            {
                //echo $line . PHP_EOL;

                $lineParts = explode(" ",trim($line));

                //Check to see if this is a description or the id
                if (trim($lineParts[0]) == "description")
                {

                    //Get the description
                    $vlanDescription = str_replace(";","",str_replace('"',"",trim($lineParts[1])));
                    continue;

                }

                //Get the vlan id.
                $vlanID = $lineParts[1];

                $nextLineIsID = false;
                $nextLineIsInterface = true;
                continue;
            }

            if (strpos($line, '{') !== false) {

                //Get the vlan name
                $lineParts = explode(" ",trim($line));
                $vlanName = trim($lineParts[0]);
                $nextLineIsID = true;
                continue;
            }

            if (strpos($line, '}') !== false) {

               //Create the vlan object.
                $vlanObject = new clsSwitchVLAN($vlanID,$vlanName,$vlanDescription,$irbUnitName,$this);
                $this->m_switchVLANs[$vlanID] = $vlanObject;
                $vlanID = null;
                $vlanName = null;
                $interface = null;

            }

        }

    }


    public function IsRangeOnSwitchVLAN($rangeToCheck)
    {
        //Check the vlans for the range
        foreach ($this->m_switchVLANs as $vlan)
        {
            //Check this vlan
            if ($vlan->IsRangeInVlan($rangeToCheck))
            {
                //Found it
                return $vlan;
            }
        }

        //Never found it on any of the vlans.
        return null;
    }

    public function IsPartialRangeOnSwitchVLAN($rangeRoCheck)
    {
        $returnValue = null;

        //Loop through the vlans on this switch
        foreach ($this->m_switchVLANs as $vlan)
        {
            $partialRangesArray = $vlan->IsPartialRangeInVlan($rangeRoCheck);

            //Check to see if partial range is in this vlan.
            if (count($partialRangesArray) > 0)
            {
                $returnValue = new stdClass();
                foreach ($partialRangesArray as $partialRange)
                {
                   // echo $partialRange->GetRangeCIDR() . " is a partial range within " . $rangeRoCheck->GetRangeCIDR() . " on VLAN " . $vlan->GetVlanName();
                    $returnValue->partialRanges[] = $partialRange;
                    $returnValue->vLAN = $vlan;

                }

                return $returnValue;
            }


        }

        return null;
    }

}



class clsIPAMManager
{
    private $m_sshConnection = null;

    private $m_switches = array();
    public function GetSwitch($switchIP)
    {
        if (isset($this->m_switches[$switchIP]))
        {
            return $this->m_switches[$switchIP];
        }

        return null;
    }

    public function ConnectToRouter($ip,$port,$user,$pass)
    {
        //Connect to the router via ssh.
        $this->m_sshConnection = $this->ConnectToDevice($ip,$port,$user,$pass);
        if (!$this->m_sshConnection)
        {
            echo "Failed to connect to the router." . PHP_EOL;
            return false;
        }

        return true;

    }

    private function ConnectToDevice($ip,$port,$user,$pass)
    {
        //Create ssh connection to the router.
        $connection = ssh2_connect($ip, $port);
        if (!$connection)
        {
            echo "Connection to router failed." . PHP_EOL;
            return false;
        }

        //Authenticate
        $authResult = ssh2_auth_password($connection, $user,$pass);
        if (!$authResult)
        {
            echo "Connection failed to authenticate" . PHP_EOL;
            return false;
        }

        return $connection;
    }

    public function DisconnectFromRouter()
    {
        if ($this->m_sshConnection)
        {
            ssh2_exec($this->m_sshConnection, 'exit');
            unset($this->m_sshConnection);
            echo "Disconnected from router" . PHP_EOL;
        }
    }

    public function DisconnectFromSwitches()
    {
        //Loop through any switches we have created
        foreach ($this->m_switches as $switch)
        {
            //Disconnect from this switch
            $switch->DisconnectFromSwitch();
        }

        unset($this->m_switches);
    }

    private function parseRoutingOptions($streamContents)
    {

        //An array to hold the route objects.
        $routesArray = array();

        //Parse the stream into lines
        $arr = explode("\n",$streamContents);

        //Loop through each line of the routing options
        foreach ($arr as $line)
        {
            //Trim the line to remove whitespaces before and after
            $trimmedLine = trim($line);

            //Split the line on spaces.
            $lineArr = explode(" ",$trimmedLine);

            //See if this line is a static route
            if ($lineArr[0] == "route")
            {

                //Check to see if there is a next hop.
                if ($lineArr[2] == "next-hop")
                {
                    //Get the range that is routed
                    $routedRange = $lineArr[1];

                    //Create the range object.
                    $rangeObj = new clsIPRange($routedRange);

                    //Get where it is routed to
                    $nextHop = str_replace(";","",$lineArr[3]);


                      //  echo $routedRange . " routed to " . $nextHop . PHP_EOL;


                    //Create the routing object
                    $route = new clsRoute($rangeObj,$nextHop);

                    //Add it to the array holding the routes.
                    if (!isset($routesArray[$nextHop])) $routesArray[$nextHop] = array();
                    $routesArray[$nextHop][] = $route;


                }

            }
        }

        return $routesArray;
    }

    private function followRoute($destination)
    {
        echo "Following route to switch $destination" . PHP_EOL;

        //Check to see if we have a connection to the switch already
        if (!isset($this->m_switches[$destination]))
        {
            //Create the switch object.
            $this->m_switches[$destination] = new clsSwitch($destination,ROUTER_USERNAME,ROUTER_PASSWORD,22);

            //Connect to the switch
            $result = $this->m_switches[$destination]->ConnectToSwitch();

            if (!$result)
            {
                echo "Failed to connect to switch" . PHP_EOL;
                return false;
            }

        }

        return true;

    }

    private function RunCommand($command, $connection)
    {
        $stream = ssh2_exec($connection, $command);
        if (!$stream)
        {
            echo "Failed to execute command $command" . PHP_EOL;
            return false;
        }

        //Set the stream to blocking mode.
        $result = stream_set_blocking($stream, true);
        if (!$result)
        {
            echo "Failed to apply blocking mode to the stream" . PHP_EOL;
            return false;
        }

        //Get the stream contents that will hold the routing options
        $streamContents = stream_get_contents($stream);

        return $streamContents;
    }


    public function GetRoutedIps()
    {

        $fp_unused = fopen("/var/www/html/cpa/temp/unusedRanges.csv","a+");
        if (!$fp_unused)
        {
            echo "Couldnt open file to read";
            die();
        }

        $fp_used = fopen("/var/www/html/cpa/temp/usedRanges.csv","a+");
        if (!$fp_unused)
        {
            echo "Couldnt open file to write";
            die();
        }

        echo "Retrieving a list of routed ips" . PHP_EOL;

        //Make sure we have a connection to the router.
        if (!$this->m_sshConnection)
        {
            echo "Not connected to a router." . PHP_EOL;
            return false;
        }

        $editCommand = 'show configuration routing-options';

        //Run the command.
        $streamContents = $this->RunCommand($editCommand,$this->m_sshConnection);
        if (!$streamContents)
        {
            echo "Failed to get routing options" . PHP_EOL;
            return false;
        }

        //Parse out the routes
        $routes = $this->parseRoutingOptions($streamContents);

        //Loop through the routes.
        foreach ($routes as $destination => $routesToDestination)
        {


            //Check to see if the route goes to a switch
            if ($destination == "23.239.133.45" || $destination == "23.239.133.53" || $destination == "23.239.133.49" || $destination == "23.239.133.69" || $destination == "23.239.133.25" || $destination == "23.239.133.33" || $destination == "23.239.133.37" || $destination == "23.239.133.29")
            {
                //Follow the route to the switch
                $result = $this->followRoute($destination);
                if (!$result)
                {
                    echo "Could not follow route to $destination" . PHP_EOL;
                    return false;
                }

                //Parse out the switches interfaces
                $this->GetSwitch($destination)->ParseSwitchVlans();

                //Loop through each route to this destination
                foreach ($routesToDestination as $route) {

                    //The range that is routed.
                    $routedRange = $route->getIPRaange();

                    //Check the destination switch for the range the router says is routed to it.
                    $rangeVLAN = $this->GetSwitch($route->GetRouteDestination())->IsRangeOnSwitchVLAN($routedRange);

                    if (!$rangeVLAN) {
                        echo $routedRange->GetRangeCIDR() . " is not on any vlans on switch " . $route->GetRouteDestination() . PHP_EOL;

                        //Check is a prt of the range is on the switches vlans.
                        $rangeVLAN = $this->GetSwitch($route->GetRouteDestination())->IsPartialRangeOnSwitchVLAN($routedRange);
                        if ($rangeVLAN)
                        {
                            foreach ($rangeVLAN->partialRanges as $partialRange) {
                                echo "A partial Range " . $partialRange->GetRangeCIDR() . " within range " . $routedRange->GetRangeCIDR() . " is on switch " . $route->GetRouteDestination() . ", it belongs to VLAN " . $rangeVLAN->vLAN->GetVlanName() . PHP_EOL;
                                fputcsv($fp_used,array($routedRange->GetRangeCIDR(),$partialRange->GetRangeCIDR(),$route->GetRouteDestination(),"VLAN " . $rangeVLAN->vLAN->GetVlanName()));
                            }

                        }

                        else {

                            //Check the rest of the switch
                            $command = "show configuration interfaces";


                            $stream = $this->RunCommand($command, $this->GetSwitch($route->GetRouteDestination())->GetSwitchConnection());
                            if (!$stream) {
                                echo "Falied to check rest of interfaces" . PHP_EOL;
                                return false;
                            }

                            $foundOnOtherInterface = false;

                            $lines = explode("\n", $stream);
                            foreach ($lines as $thisLine) {
                                $words = explode(" ", trim($thisLine));

                                if (trim($words[0]) == "address") {
                                    $rangeRouted = AddOneToRange($routedRange->GetRangeCIDR());
                                    $address = str_replace(";", "", trim($words[1]));
                                    if ($rangeRouted == $address) {
                                        echo "Found range on another interface" . PHP_EOL;
                                        fputcsv($fp_used, array($routedRange->GetRangeCIDR(), $route->GetRouteDestination(), "Other Interface"));
                                        $foundOnOtherInterface = true;
                                        break;

                                    }

                                }
                            }


                            if (!$foundOnOtherInterface) {
                                echo "Range could not be found on another interface" . PHP_EOL;
                                fputcsv($fp_unused, array($route->getIPRaange()->GetRangeCIDR(), $route->GetRouteDestination()));
                            }
                        }

                    }

                    else{
                        echo $routedRange->GetRangeCIDR() . " is on switch " . $route->GetRouteDestination() . ", it belongs to VLAN " . $rangeVLAN->GetVlanName() . PHP_EOL;
                        fputcsv($fp_used,array($routedRange->GetRangeCIDR(),$route->getIPRaange()->GetRangeCIDR(),$route->GetRouteDestination(),"VLAN " . $rangeVLAN->GetVlanName()));
                    }
                }


            }
        }

        fclose($fp_used);
        fclose($fp_unused);

        return $routes;

    }
}
