<?php
  // Include required files.
  require_once("misc.php");
  require_once("oswebdb/oswebdb.php");
  require_once("oswebdb/tables.php");
  require_once("oswebdb/zipcodes.php");
  require_once("oswebdb/distance.php");
  require_once("oswebdb/allowanc.php");

  // Set error reporting.
  error_reporting((int) GetConfigValue("ErrorReporting"));

  // Make the html menu page with information from the oswebdb database.
  if (oswebdb_connect(oswebdb_getusername(), oswebdb_getpassword()))
  {
    if (oswebdb_selectdb())
    {
      $SystemNo = (int) GetConfigValue("SystemNo");
      if ($Result = oswebdb_query("SELECT Title FROM Systems WHERE SystemNo=$SystemNo"))
      {
        if ($Row = oswebdb_fetch_row($Result))
        {
          if (isset($_GET["Method"]))
          {
            $Method = $_GET["Method"];
            MakeJavaScriptBegin();
            if ($Method & 1)
            {
              // Get city name.
              if (isset($_GET["CountryCode"]) && isset($_GET["ZipCode"]) && isset($_GET["Frame"]) && isset($_GET["Form"]) && isset($_GET["Ctrl"]))
              {
                $CityName = get_city_name($SystemNo, $_GET["CountryCode"], $_GET["ZipCode"]);
                $Frame = $_GET["Frame"];
                $Form = $_GET["Form"];
                $Ctrl = $_GET["Ctrl"];
                echo "  $Frame.$Form.$Ctrl.value = '$CityName'\r\n";
              }
            }
            if ($Method & 2)
            {
              // Get zipcode length.
              if (isset($_GET["CountryCode"]) && isset($_GET["Frame"]) && isset($_GET["Form"]) && isset($_GET["Ctrl"]))
              {
                $ZipCodeLength = get_zipcode_length($SystemNo, $_GET["CountryCode"]);
                $Frame = $_GET["Frame"];
                $Form = $_GET["Form"];
                $Ctrl = $_GET["Ctrl"];
                echo "  $Frame.$Form.$Ctrl.size = $ZipCodeLength\r\n";
                echo "  $Frame.$Form.$Ctrl.maxLength = $ZipCodeLength\r\n";
              }
            }
            if ($Method & 4)
            {
              // Get zipcode lengths.
              if (isset($_GET["FromCountryCode"]) && isset($_GET["ToCountryCode"]) && isset($_GET["Frame"]) && isset($_GET["FromForm"]) && isset($_GET["FromCtrl"]) && isset($_GET["ToForm"]) && isset($_GET["ToCtrl"]))
              {
                $FromZipCodeLength = get_zipcode_length($SystemNo, $_GET["FromCountryCode"]);
                $ToZipCodeLength = get_zipcode_length($SystemNo, $_GET["ToCountryCode"]);
                $Frame = $_GET["Frame"];
                $FromForm = $_GET["FromForm"];
                $FromCtrl = $_GET["FromCtrl"];
                $ToForm = $_GET["ToForm"];
                $ToCtrl = $_GET["ToCtrl"];
                echo "  $Frame.$FromForm.$FromCtrl.size = $FromZipCodeLength\r\n";
                echo "  $Frame.$FromForm.$FromCtrl.maxLength = $FromZipCodeLength\r\n";
                echo "  $Frame.$FromForm.$ToCtrl.size = $ToZipCodeLength\r\n";
                echo "  $Frame.$FromForm.$ToCtrl.maxLength = $ToZipCodeLength\r\n";
              }
            }
            if ($Method & 8)
            {
              // Get address group public.
              if (isset($_GET["GroupNo"]) && isset($_GET["Frame"]) && isset($_GET["Form"]) && isset($_GET["Ctrl"]))
              {
                $Public = (get_table_field_value($SystemNo, 3, $_GET["GroupNo"], "Public") ? "true" : "false");
                $Frame = $_GET["Frame"];
                $Form = $_GET["Form"];
                $Ctrl = $_GET["Ctrl"];
                echo "  $Frame.$Form.$Ctrl.checked = $Public\r\n";
              }
            }
            if ($Method & 16)
            {
              // Get distance information.
              if (isset($_GET["CountryCode"]) && isset($_GET["ZipCode"]) && isset($_GET["OtherCountryCode"]) && isset($_GET["OtherZipCode"]) && isset($_GET["Frame"]) && isset($_GET["Form"]) && isset($_GET["DistanceCtrl"]) && isset($_GET["Property1Ctrl"]) && isset($_GET["Property2Ctrl"]))
              {
                $DistanceInformation = get_distance_information($SystemNo, $_GET["CountryCode"], $_GET["ZipCode"], $_GET["OtherCountryCode"], $_GET["OtherZipCode"]);
                $Frame = $_GET["Frame"];
                $Form = $_GET["Form"];
                $DistanceCtrl = $_GET["DistanceCtrl"];
                $Property1Ctrl = $_GET["Property1Ctrl"];
                $Property2Ctrl = $_GET["Property2Ctrl"];
                if ($DistanceInformation[0] > 0)
                  echo "  $Frame.$Form.$DistanceCtrl.value = '$DistanceInformation[0]'\r\n";
                echo "  $Frame.$Form.$Property1Ctrl.checked = ($DistanceInformation[1] & 1)\r\n";
                echo "  $Frame.$Form.$Property2Ctrl.checked = ($DistanceInformation[1] & 2)\r\n";
              }
            }
            if ($Method & 32)
            {
              // Get distance for a date.
              if (isset($_GET["Year"]) && isset($_GET["No"]) && isset($_GET["Date"]) && isset($_GET["Frame"]) && isset($_GET["Form"]) && isset($_GET["DistanceCtrl"]))
              {
                $Distance = (integer) get_distance_for_date($SystemNo, $_GET["Year"], $_GET["No"], $_GET["Date"]);
                $Frame = $_GET["Frame"];
                $Form = $_GET["Form"];
                $DistanceCtrl = $_GET["DistanceCtrl"];
                if ($Distance > 0)
                {
                  $Distance = FormatNumber($Distance, 0, 1);
                  echo "  $Frame.$Form.$DistanceCtrl.value = '$Distance'\r\n";
                }
              }
            }
            if ($Method & 64)
            {
              // Get allowance for a date.
              if (isset($_GET["Year"]) && isset($_GET["No"]) && isset($_GET["Date"]) && isset($_GET["Frame"]) && isset($_GET["Form"]) && isset($_GET["AllowanceCtrl"]))
              {
                $Allowance = (float) get_allowance_for_date($SystemNo, $_GET["Year"], $_GET["No"], $_GET["Date"]);
                $Frame = $_GET["Frame"];
                $Form = $_GET["Form"];
                $AllowanceCtrl = $_GET["AllowanceCtrl"];
                if ($Allowance > 0)
                {
                  $Allowance = FormatNumber($Allowance, 2, 1);
                  echo "  $Frame.$Form.$AllowanceCtrl.value = '$Allowance'\r\n";
                }
              }
            }
            if ($Method & 128)
            {
              // Get internal match type.
              if (isset($_GET["TypeNo"]) && isset($_GET["Frame"]) && isset($_GET["Form"]) && isset($_GET["Ctrl"]))
              {
                $TypeSystemNo = get_system_for_table($SystemNo, 9);
                $TypeProperties = (integer) get_table_field_value($TypeSystemNo, 9, $_GET["TypeNo"], "Properties");
                $Frame = $_GET["Frame"];
                $Form = $_GET["Form"];
                $Ctrl = $_GET["Ctrl"];
                echo "  $Frame.$Form.$Ctrl.checked = ($TypeProperties & 1)\r\n";
              }
            }
            if ($Method & 256)
            {
              // Get internal match type.
              if (isset($_GET["SeasonNo"]) && isset($_GET["Frame"]) && isset($_GET["Form"]) && isset($_GET["Ctrl"]))
              {
                $SeasonSystemNo = get_system_for_table($SystemNo, 7);
                $SeasonStartDate = get_table_field_value($SeasonSystemNo, 7, $_GET["SeasonNo"], "FromDate");
                if (strlen($SeasonStartDate) > 0)
                {
                  $Frame = $_GET["Frame"];
                  $Form = $_GET["Form"];
                  $Ctrl = $_GET["Ctrl"];
                  echo "  $Frame.$Form.$Ctrl.value = '$SeasonStartDate'\r\n";
                }
              }
            }
            MakeJavaScriptEnd();
          }
          MakeHtmlPageTop($Row[0]);
          MakeHtmlPageBottom();
        }
        oswebdb_free_result($Result);
      }
    }
    oswebdb_close();
  }

  function MakeJavaScriptBegin()
  {
    echo "<script language=\"JavaScript\">\r\n";
  }

  function MakeJavaScriptEnd()
  {
    echo "</script>\r\n";
  }
?>
