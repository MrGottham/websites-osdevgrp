<?php
  // Include required files.
  require_once("misc.php");
  require_once("oswebdb/oswebdb.php");
  require_once("oswebdb/distance.php");
  require_once("oswebdb/zipcodes.php");
  require_once("oswebdb/systems.php");
  require_once("oswebdb/tables.php");

  // Set error reporting.
  error_reporting((int) GetConfigValue("ErrorReporting"));

  $TableName = "Distances";
  $SystemNo = GetConfigValue("SystemNo");
  if (isset($_GET["FromCountryCode"]))
    $FromCountryCode = $_GET["FromCountryCode"];
  else if (isset($_POST["FromCountryCode"]))
    $FromCountryCode = $_POST["FromCountryCode"];
  if (isset($_GET["FromZipCode"]))
    $FromZipCode = $_GET["FromZipCode"];
  else if (isset($_POST["FromZipCode"]))
    $FromZipCode = $_POST["FromZipCode"];
  if (isset($_GET["ToCountryCode"]))
    $ToCountryCode = $_GET["ToCountryCode"];
  else if (isset($_POST["ToCountryCode"]))
    $ToCountryCode = $_POST["ToCountryCode"];
  if (isset($_GET["ToZipCode"]))
    $ToZipCode = $_GET["ToZipCode"];
  else if (isset($_POST["ToZipCode"]))
    $ToZipCode = $_POST["ToZipCode"];
  if (oswebdb_authorize(oswebdb_getusername(), oswebdb_getpassword()))
  {
    if (oswebdb_selectdb())
    {
      $AllowSelect = oswebdb_allow_select($TableName);
      $AllowInsert = oswebdb_allow_insert($TableName);
      $AllowUpdate = oswebdb_allow_update($TableName);
      $AllowDelete = oswebdb_allow_delete($TableName);
      $CountryCodeSystemNo = get_system_for_table($SystemNo, 2);
      $ZipCodeSystemNo = get_system_for_zipcodes($SystemNo);
      $DistanceSystemNo = get_system_for_distances($SystemNo);
      if (isset($FromCountryCode))
        $FromCountryName = get_table_field_value($CountryCodeSystemNo, 2, $FromCountryCode, "Description");
      $NewDistance = isset($_POST["NewDistance"]);
      if (isset($_POST["Insert"]) && isset($FromCountryCode) && isset($FromZipCode) && isset($ToCountryCode) && isset($ToZipCode) && isset($_POST["Distance"]))
      {
        if (!insert_distance($SystemNo, $FromCountryCode, $FromZipCode, $ToCountryCode, $ToZipCode, $_POST["Distance"], $_POST["Property1"], $_POST["Property2"], $_POST["Property3"], $_POST["Property4"], $_POST["Property5"], $_POST["Property6"], $_POST["Property7"], $_POST["Property8"], 1))
        {
          $Error = oswebdb_error();
          MakeHtmlPageTop("Distancer");
          echo "    <h2><strong>$Error</strong></h2>\r\n";
          MakeHtmlPageBottom("");
          exit;
        }
      }
      if (isset($_POST["Update"]) && isset($FromCountryCode) && isset($FromZipCode) && isset($ToCountryCode) && isset($ToZipCode) && isset($_POST["Distance"]))
      {
        if (!update_distance($SystemNo, $FromCountryCode, $FromZipCode, $ToCountryCode, $ToZipCode, $_POST["Distance"], $_POST["Property1"], $_POST["Property2"], $_POST["Property3"], $_POST["Property4"], $_POST["Property5"], $_POST["Property6"], $_POST["Property7"], $_POST["Property8"], 1))
        {
          $Error = oswebdb_error();
          MakeHtmlPageTop("Distancer");
          echo "    <h2><strong>$Error</strong></h2>\r\n";
          MakeHtmlPageBottom("");
          exit;
        }
      }
      if (isset($_POST["Delete"]) && isset($FromCountryCode) && isset($FromZipCode) && isset($ToCountryCode) && isset($ToZipCode) && isset($_POST["Distance"]))
      {
        if (!delete_distance($SystemNo, $FromCountryCode, $FromZipCode, $ToCountryCode, $ToZipCode, 1))
        {
          $Error = oswebdb_error();
          MakeHtmlPageTop("Distancer");
          echo "    <h2><strong>$Error</strong></h2>\r\n";
          MakeHtmlPageBottom("");
          exit;
        }
        unset($FromZipCode);
        unset($ToCountryCode);
        unset($ToZipCode);
      }
      else if (isset($_POST["ShowAll"]) && (isset($FromZipCode) || isset($ToCountryCode) || isset($ToZipCode)))
      {
        if (isset($FromZipCode))
          unset($FromZipCode);
        if (isset($ToCountryCode))
          unset($ToCountryCode);
        if (isset($ToZipCode))
          unset($ToZipCode);
      }
      if ($NewDistance)
        $DistanceStatement = "SELECT FromCountryCode,FromZipCode,ToCountryCode,ToZipCode,Distance,Properties FROM $TableName WHERE SystemNo=$DistanceSystemNo";
      else if (isset($FromCountryCode) && isset($FromZipCode) && isset($ToCountryCode) && isset($ToZipCode))
        $DistanceStatement = "SELECT FromCountryCode,FromZipCode,ToCountryCode,ToZipCode,Distance,Properties FROM $TableName WHERE SystemNo=$DistanceSystemNo AND FromCountryCode=$FromCountryCode AND FromZipCode=\"$FromZipCode\" AND ToCountryCode=$ToCountryCode AND ToZipCode=\"$ToZipCode\"";
      else if (isset($FromCountryCode) && isset($FromZipCode))
        $DistanceStatement = "SELECT d.FromCountryCode,d.FromZipCode,f.CityName,d.ToCountryCode,d.ToZipCode,t.CityName,d.Distance FROM $TableName AS d, Zipcodes AS f, Zipcodes AS t WHERE d.SystemNo=$DistanceSystemNo AND d.FromCountryCode=$FromCountryCode AND d.FromZipCode=\"$FromZipCode\" AND f.SystemNo=$ZipCodeSystemNo AND f.CountryCode=d.FromCountryCode AND f.ZipCode=d.FromZipCode AND t.SystemNo=$ZipCodeSystemNo AND t.CountryCode=d.ToCountryCode AND t.ZipCode=d.ToZipCode ORDER BY f.CityName,d.FromCountryCode,d.FromZipCode,t.CityName,d.ToCountryCode,d.ToZipCode";
      else if (isset($FromCountryCode))
        $DistanceStatement = "SELECT d.FromCountryCode,d.FromZipCode,f.CityName,d.ToCountryCode,d.ToZipCode,t.CityName,d.Distance FROM $TableName AS d, Zipcodes AS f, Zipcodes AS t WHERE d.SystemNo=$DistanceSystemNo AND d.FromCountryCode=$FromCountryCode AND f.SystemNo=$ZipCodeSystemNo AND f.CountryCode=d.FromCountryCode AND f.ZipCode=d.FromZipCode AND t.SystemNo=$ZipCodeSystemNo AND t.CountryCode=d.ToCountryCode AND t.ZipCode=d.ToZipCode ORDER BY f.CityName,d.FromCountryCode,d.FromZipCode,t.CityName,d.ToCountryCode,d.ToZipCode";
      if (($NewDistance || (isset($FromCountryCode) && isset($FromZipCode) && isset($ToCountryCode) && isset($ToZipCode))) && $DistanceResult = oswebdb_query($DistanceStatement))
      {
        $TabIndex = 1;
        if ($NewDistance || $DistanceRow = oswebdb_fetch_row($DistanceResult))
        {
          $FromCountryCodeOptions = "";
          $ToCountryCodeOptions = "";
          if ($CountryCodeResult = oswebdb_query("SELECT No,Description FROM Systemtables WHERE SystemNo=$CountryCodeSystemNo AND TableNo=2 ORDER BY Description"))
          {
            while ($CountryCodeRow = oswebdb_fetch_row($CountryCodeResult))
            {
              $FromCountryCodeOptions = "$FromCountryCodeOptions<option value=\"$CountryCodeRow[0]\"";
              if ($NewDistance)
              {
                if ($CountryCodeRow[0] == (isset($FromCountryCode) ? $FromCountryCode : get_system_country_code($SystemNo)))
                  $FromCountryCodeOptions = "$FromCountryCodeOptions selected";
              }
              else if ($CountryCodeRow[0] == $DistanceRow[0])
                $FromCountryCodeOptions = "$FromCountryCodeOptions selected";
              $FromCountryCodeOptions = "$FromCountryCodeOptions>$CountryCodeRow[1]";
              $ToCountryCodeOptions = "$ToCountryCodeOptions<option value=\"$CountryCodeRow[0]\"";
              if ($NewDistance)
              {
                if ($CountryCodeRow[0] == (isset($ToCountryCode) ? $ToCountryCode : get_system_country_code($SystemNo)))
                  $ToCountryCodeOptions = "$ToCountryCodeOptions selected";
              }
              else if ($CountryCodeRow[0] == $DistanceRow[2])
                $ToCountryCodeOptions = "$ToCountryCodeOptions selected";
              $ToCountryCodeOptions = "$ToCountryCodeOptions>$CountryCodeRow[1]";
            }
            oswebdb_free_result($CountryCodeResult);
          }
          echo "<script language=\"JavaScript\">\r\n";
          echo "  function validateDistance()\r\n";
          echo "  {\r\n";
          if ($NewDistance)
          {
            echo "    fromCountryCodeSelected = false\r\n";
            echo "    for (i = 0; i < DistanceForm.FromCountryCode.length && !fromCountryCodeSelected; i++)\r\n";
            echo "      fromCountryCodeSelected = DistanceForm.FromCountryCode.options[i].selected\r\n";
            echo "    fromZipCode = DistanceForm.FromZipCode.value\r\n";
            echo "    fromCityName = DistanceForm.FromCityName.value\r\n";
            echo "    toCountryCodeSelected = false\r\n";
            echo "    for (i = 0; i < DistanceForm.ToCountryCode.length && !toCountryCodeSelected; i++)\r\n";
            echo "      toCountryCodeSelected = DistanceForm.ToCountryCode.options[i].selected\r\n";
            echo "    toZipCode = DistanceForm.ToZipCode.value\r\n";
            echo "    toCityName = DistanceForm.ToCityName.value\r\n";
            echo "    if (!fromCountryCodeSelected)\r\n";
            echo "    {\r\n";
            echo "      alert('Landet for fra sted skal vælges!')\r\n";
            echo "      return false\r\n";
            echo "    }\r\n";
            echo "    else if (fromZipCode.length == 0)\r\n";
            echo "    {\r\n";
            echo "      alert('Postnummeret for fra sted skal indtastes!')\r\n";
            echo "      return false\r\n";
            echo "    }\r\n";
            echo "    else if (fromCityName.length == 0)\r\n";
            echo "    {\r\n";
            echo "      alert('Postnummeret for fra sted eksisterer ikke!')\r\n";
            echo "      return false\r\n";
            echo "    }\r\n";
            echo "    else if (!toCountryCodeSelected)\r\n";
            echo "    {\r\n";
            echo "      alert('Landet for til sted skal vælges!')\r\n";
            echo "      return false\r\n";
            echo "    }\r\n";
            echo "    else if (toZipCode.length == 0)\r\n";
            echo "    {\r\n";
            echo "      alert('Postnummeret for til sted skal indtastes!')\r\n";
            echo "      return false\r\n";
            echo "    }\r\n";
            echo "    else if (toCityName.length == 0)\r\n";
            echo "    {\r\n";
            echo "      alert('Postnummeret for til sted eksisterer ikke!')\r\n";
            echo "      return false\r\n";
            echo "    }\r\n";
            echo "    else if (!(parseInt(DistanceForm.Distance.value, 10) > 0))\r\n";
          }
          else
            echo "    if (!(parseInt(DistanceForm.Distance.value, 10) > 0))\r\n";
          echo "    {\r\n";
          echo "      alert('Distancen skal være større end 0!')\r\n";
          echo "      return false\r\n";
          echo "    }\r\n";
          echo "    else\r\n";
          echo "      return true\r\n";
          echo "  }\r\n\r\n";
          echo "  function resetDistance()\r\n";
          echo "  {\r\n";
          if ($NewDistance)
          {
            $FCC = (integer) (isset($FromCountryCode) ? $FromCountryCode : get_system_country_code($SystemNo));
            $TCC = (integer) (isset($ToCountryCode) ? $ToCountryCode : get_system_country_code($SystemNo));
            echo "    parent.getZipCodeLengths($FCC, DistanceForm.FromZipCode, DistanceForm.FromCityName, $TCC, DistanceForm.ToZipCode, DistanceForm.ToCityName)\r\n";
          }
          echo "    return true\r\n";
          echo "  }\r\n";
          echo "</script>\r\n";
          MakeHtmlPageTop("Distancer");
          echo "    <form name=\"DistanceForm\" action=\"distance.php\" method=\"post\">\r\n";
          if (!$NewDistance)
          {
            echo "      <input type=\"hidden\" name=\"FromCountryCode\" value=\"$DistanceRow[0]\">\r\n";
            echo "      <input type=\"hidden\" name=\"FromZipCode\" value=\"$DistanceRow[1]\">\r\n";
            echo "      <input type=\"hidden\" name=\"ToCountryCode\" value=\"$DistanceRow[2]\">\r\n";
            echo "      <input type=\"hidden\" name=\"ToZipCode\" value=\"$DistanceRow[3]\">\r\n";
          }
          echo "      <table border=\"0\" cellpadding=\"1\" cellspacing=\"1\" width=\"100%\">\r\n";
          echo "        <tr><td width=\"100%\" colspan=\"2\" valign=\"middle\"><h2><i>Distancer - $FromCountryName</i></h2></td></tr>\r\n";
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\">";
          if ($NewDistance)
          {
            if ($AllowInsert)
            {
              $InputSubmit = MakeHtmlInputSubmit("Insert", "Opret", !$AllowInsert, $TabIndex++, "javascript:return validateDistance()");
              $InputReset = MakeHtmlInputReset("Reset", "Fortryd", !$AllowInsert, $TabIndex++, "javascript:return resetDistance()");
              echo "$InputSubmit&nbsp;$InputReset&nbsp;";
            }
            if ($AllowSelect)
            {
              $InputShowAll = MakeHtmlInputSubmit("ShowAll", "Vis alle", !$AllowSelect, $TabIndex++, "");
              echo "$InputShowAll&nbsp;";
            }
          }
          else
          {
            if ($AllowUpdate)
            {
              $InputSubmit = MakeHtmlInputSubmit("Update", "Opdatér", !$AllowUpdate, $TabIndex++, "javascript:return validateDistance()");
              $InputReset = MakeHtmlInputReset("Reset", "Fortryd", !$AllowUpdate, $TabIndex++, "javascript:return resetDistance()");
              echo "$InputSubmit&nbsp;$InputReset&nbsp;";
            }
            if ($AllowDelete)
            {
              $InputDelete = MakeHtmlInputSubmit("Delete", "Slet", !$AllowDelete, $TabIndex++, "javascript:return confirm('Bekræft sletning!')");
              echo "$InputDelete&nbsp;";
            }
            if ($AllowSelect)
            {
              $InputShowAll = MakeHtmlInputSubmit("ShowAll", "Vis alle", !$AllowSelect, $TabIndex++, "");
              echo "$InputShowAll&nbsp;";
            }
          }
          echo "</td></tr>\r\n";
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\"><strong>Fra sted:</strong></td></tr>\r\n";
          $Input = MakeHtmlSelect(($NewDistance ? "FromCountryCode" : ""), 0, 0, ($NewDistance ? !$AllowInsert : 1), ($NewDistance ? !$AllowInsert : 1), $TabIndex++, ($NewDistance ? "javascript:parent.getZipCodeLength(this.value, this.form.FromZipCode, this.form.FromCityName)" : ""), $FromCountryCodeOptions);
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Land :</td><td width=\"99%\">$Input</td></tr>\r\n";
          $InputZipCode = MakeHtmlInputText(($NewDistance ? "FromZipCode" : ""), ($NewDistance ? "" : $DistanceRow[1]), get_zipcode_length($SystemNo, ($NewDistance ? (isset($FromCountryCode) ? $FromCountryCode : get_system_country_code($SystemNo)) : $DistanceRow[0])), get_zipcode_length($SystemNo, ($NewDistance ? (isset($FromCountryCode) ? $FromCountryCode : get_system_country_code($SystemNo)) : $DistanceRow[0])), ($NewDistance ? !$AllowInsert : 1), ($NewDistance ? !$AllowInsert : 1), $TabIndex++, ($NewDistance ? "javascript:parent.getCityName(this.form.FromCountryCode.value, this.value, this.form.FromCityName)" : ""));
          $InputCityName = MakeHtmlInputText(($NewDistance ? "FromCityName" : ""), ($NewDistance ? "" : get_city_name($SystemNo, $DistanceRow[0], $DistanceRow[1])), get_city_name_length($SystemNo), get_city_name_length($SystemNo), 1, ($NewDistance ? !$AllowInsert : 1), $TabIndex++, "");
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Postnummer og by :</td><td width=\"99%\">$InputZipCode&nbsp;&nbsp;$InputCityName</td></tr>\r\n";
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\"><strong>Til sted:</strong></td></tr>\r\n";
          $Input = MakeHtmlSelect(($NewDistance ? "ToCountryCode" : ""), 0, 0, ($NewDistance ? !$AllowInsert : 1), ($NewDistance ? !$AllowInsert : 1), $TabIndex++, ($NewDistance ? "javascript:parent.getZipCodeLength(this.value, this.form.ToZipCode, this.form.ToCityName)" : ""), $ToCountryCodeOptions);
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Land :</td><td width=\"99%\">$Input</td></tr>\r\n";
          $InputZipCode = MakeHtmlInputText(($NewDistance ? "ToZipCode" : ""), ($NewDistance ? "" : $DistanceRow[3]), get_zipcode_length($SystemNo, ($NewDistance ? (isset($ToCountryCode) ? $ToCountryCode : get_system_country_code($SystemNo)) : $DistanceRow[2])), get_zipcode_length($SystemNo, ($NewDistance ? (isset($ToCountryCode) ? $ToCountryCode : get_system_country_code($SystemNo)) : $DistanceRow[2])), ($NewDistance ? !$AllowInsert : 1), ($NewDistance ? !$AllowInsert : 1), $TabIndex++, ($NewDistance ? "javascript:parent.getCityName(this.form.ToCountryCode.value, this.value, this.form.ToCityName)" : ""));
          $InputCityName = MakeHtmlInputText(($NewDistance ? "ToCityName" : ""), ($NewDistance ? "" : get_city_name($SystemNo, $DistanceRow[2], $DistanceRow[3])), get_city_name_length($SystemNo), get_city_name_length($SystemNo), 1, ($NewDistance ? !$AllowInsert : 1), $TabIndex++, "");
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Postnummer og by :</td><td width=\"99%\">$InputZipCode&nbsp;&nbsp;$InputCityName</td></tr>\r\n";
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\"><strong>Distance:</strong></td></tr>\r\n";
          $Input = MakeHtmlInputText("Distance", ($NewDistance ? "" : $DistanceRow[4]), oswebdb_field_len($DistanceResult, 4), oswebdb_field_len($DistanceResult, 4), ($NewDistance ? !$AllowInsert : !$AllowUpdate), ($NewDistance ? !$AllowInsert : !$AllowUpdate), $TabIndex++, "");
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Distance :</td><td width=\"99%\">$Input&nbsp;km</td></tr>\r\n";
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\"><strong>Egenskaber:</strong></td></tr>\r\n";
          $Input = MakeHtmlInputCheckBox("Property1", "1", ($NewDistance ? 0 : ($DistanceRow[5] & 1)), ($NewDistance ? !$AllowInsert : !$AllowUpdate), $TabIndex++, "", "Krydser Storebælt");
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\">$Input</td></tr>\r\n";
          $Input = MakeHtmlInputCheckBox("Property2", "2", ($NewDistance ? 0 : ($DistanceRow[5] & 2)), ($NewDistance ? !$AllowInsert : !$AllowUpdate), $TabIndex++, "", "Krydser Øresund");
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\">$Input</td></tr>\r\n";
          echo "      </table>\r\n";
          echo "    </form>\r\n";
          MakeHtmlPageBottom();
          oswebdb_free_result($DistanceResult);
        }
        else if (isset($FromCountryCode) && isset($FromZipCode))
          MakeHtmlPageReload("javascript:this.location = 'distance.php?FromCountryCode=$FromCountryCode&FromZipCode=$FromZipCode'; return true;");
        else if (isset($FromCountryCode))
          MakeHtmlPageReload("javascript:this.location = 'distance.php?FromCountryCode=$FromCountryCode'; return true;");
        else
          MakeHtmlPageReload("javascript:this.location = 'distance.php'; return true;");
      }
      else if (isset($FromCountryCode) && $DistanceResult = oswebdb_query($DistanceStatement))
      {
        $TabIndex = 1; $Header = 0;
        MakeHtmlPageTop("Distancer");
        echo "    <table border=\"0\" cellpadding=\"1\" cellspacing=\"1\" width=\"100%\">\r\n";
        echo "      <tr><td width=\"100%\" colspan=\"4\" valign=\"middle\"><h2><i>Distancer - $FromCountryName</i></h2></td></tr>\r\n";
        if ($AllowInsert || $AllowSelect)
        {
          echo "      <form action=\"distance.php\" method=\"post\">\r\n";
          echo "        <input type=\"hidden\" name=\"FromCountryCode\" value=\"$FromCountryCode\">\r\n";
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\" colspan=\"3\">";
          if ($AllowInsert)
          {
            $Input = MakeHtmlInputSubmit("NewDistance", "Opret", !$AllowInsert, $TabIndex++, "");
            echo "$Input&nbsp;";
          }
          if ($AllowSelect && isset($FromZipCode))
          {
            $Input = MakeHtmlInputSubmit("ShowAll", "Vis alle", !$AllowSelect, $TabIndex++, "");
            echo "$Input&nbsp;";
          }
          echo "</td></tr>\r\n";
          echo "      </form>\r\n";
        }
        while ($DistanceRow = oswebdb_fetch_row($DistanceResult))
        {
          if (!$Header)
          {
            echo "      <tr><td width=\"1%\" align=\"right\" nowrap></td><td nowrap><strong>Fra sted</strong></td><td nowrap><strong>Til sted</strong></td><td align=\"right\" nowrap><strong>Distance</strong></td></tr>\r\n";
            $Header = 1;
          }
          if ($AllowUpdate)
            $Input = MakeHtmlInputSubmit("", "Redigér", !$AllowUpdate, $TabIndex++, "");
          else
            $Input = MakeHtmlInputSubmit("", "Vis", 0, $TabIndex++, "");
          $Distance = FormatNumber((integer) $DistanceRow[6], 0, 1);
          echo "      <form action=\"distance.php\" method=\"post\"><input type=\"hidden\" name=\"FromCountryCode\" value=\"$DistanceRow[0]\"><input type=\"hidden\" name=\"FromZipCode\" value=\"$DistanceRow[1]\"><input type=\"hidden\" name=\"ToCountryCode\" value=\"$DistanceRow[3]\"><input type=\"hidden\" name=\"ToZipCode\" value=\"$DistanceRow[4]\"><tr><td width=\"1%\" align=\"right\" nowrap>$Input</td><td nowrap>$DistanceRow[1]&nbsp;&nbsp;$DistanceRow[2]</td><td nowrap>$DistanceRow[4]&nbsp;&nbsp;$DistanceRow[5]</td><td align=\"right\" nowrap>$Distance&nbsp;km</td></tr></form>\r\n";
        }
        echo "    </table>\r\n";
        MakeHtmlPageBottom();
        oswebdb_free_result($DistanceResult);
      }
      else if ($CountryCodeResult = oswebdb_query("SELECT No,Description FROM Systemtables WHERE SystemNo=$CountryCodeSystemNo AND TableNo=2 ORDER BY Description,No"))
      {
        $TabIndex = 1;
        MakeHtmlPageTop("Distancer");
        echo "    <table border=\"0\" cellpadding=\"1\" cellspacing=\"1\" width=\"100%\">\r\n";
        echo "      <tr><td width=\"100%\" colspan=\"2\" valign=\"middle\"><h2><i>Distancer</i></h2></td></tr>\r\n";
        while ($CountryCodeRow = oswebdb_fetch_row($CountryCodeResult))
        {
          if ($AllowUpdate)
            $Input = MakeHtmlInputSubmit("", "Redigér", !$AllowUpdate, $TabIndex++, "");
          else
            $Input = MakeHtmlInputSubmit("", "Vis", 0, $TabIndex++, "");
          echo "      <form action=\"distance.php\" method=\"post\"><input type=\"hidden\" name=\"FromCountryCode\" value=\"$CountryCodeRow[0]\"><tr><td width=\"1%\" valign=\"middle\" align=\"right\" nowrap>$Input</td><td width=\"99%\" valign=\"middle\">$CountryCodeRow[1]</td></tr></form>\r\n";
        }
        echo "    </table>\r\n";
        MakeHtmlPageBottom();
        oswebdb_free_result($CountryCodeResult);
      }
    }
    oswebdb_close();
  }
?>
