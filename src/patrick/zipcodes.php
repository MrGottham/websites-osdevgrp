<?php
  // Include required files.
  require_once("misc.php");
  require_once("oswebdb/oswebdb.php");
  require_once("oswebdb/zipcodes.php");
  require_once("oswebdb/tables.php");

  // Set error reporting.
  error_reporting((int) GetConfigValue("ErrorReporting"));

  $TableName = "Zipcodes";
  $SystemNo = GetConfigValue("SystemNo");
  if (isset($_GET["CountryCode"]))
    $CountryCode = $_GET["CountryCode"];
  else if (isset($_POST["CountryCode"]))
    $CountryCode = $_POST["CountryCode"];
  if (isset($_GET["ZipCode"]))
    $ZipCode = $_GET["ZipCode"];
  else if (isset($_POST["ZipCode"]))
    $ZipCode = $_POST["ZipCode"];
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
      if (isset($CountryCode))
        $CountryName = get_table_field_value($CountryCodeSystemNo, 2, $CountryCode, "Description");
      if (isset($_POST["Insert"]) && isset($CountryCode) && isset($ZipCode) && isset($_POST["CityName"]))
      {
        if (!insert_zipcode($SystemNo, $CountryCode, $ZipCode, $_POST["CityName"]))
        {
          $Error = oswebdb_error();
          MakeHtmlPageTop("Postnumre");
          echo "    <h2><strong>$Error</strong></h2>\r\n";
          MakeHtmlPageBottom("");
          exit;
        }
      }
      else if (isset($_POST["Update"]) && isset($CountryCode) && isset($ZipCode) && isset($_POST["CityName"]))
      {
        if (!update_zipcode($SystemNo, $CountryCode, $ZipCode, $_POST["CityName"]))
        {
          $Error = oswebdb_error();
          MakeHtmlPageTop("Postnumre");
          echo "    <h2><strong>$Error</strong></h2>\r\n";
          MakeHtmlPageBottom("");
          exit;
        }
      }
      else if (isset($_POST["Delete"]) && isset($CountryCode) && isset($ZipCode))
      {
        if (!delete_zipcode($SystemNo, $CountryCode, $ZipCode))
        {
          $Error = oswebdb_error();
          MakeHtmlPageTop("Postnumre");
          echo "    <h2><strong>$Error</strong></h2>\r\n";
          MakeHtmlPageBottom("");
          exit;
        }
        unset($ZipCode);
      }
      else if (isset($_POST["ShowAll"]) && isset($CountryCode) && isset($ZipCode))
        unset($ZipCode);
      if (isset($CountryCode) && isset($ZipCode) && $ZipCodeResult = oswebdb_query("SELECT CountryCode,ZipCode,CityName FROM $TableName WHERE SystemNo=$ZipCodeSystemNo AND CountryCode=$CountryCode AND ZipCode=\"$ZipCode\""))
      {
        if ($ZipCodeRow = oswebdb_fetch_row($ZipCodeResult))
        {
          $TabIndex = 1;
          echo "<script language=\"JavaScript\">\r\n";
          echo "  function validateUpdate()\r\n";
          echo "  {\r\n";
          echo "    cityname = this.UpdateForm.CityName.value\r\n";
          echo "    if (cityname.length == 0)\r\n";
          echo "    {\r\n";
          echo "      alert('Bynavnet skal indtastes!')\r\n";
          echo "      return false\r\n";
          echo "    }\r\n";
          echo "    else\r\n";
          echo"       return true\r\n";
          echo "  }\r\n";
          echo "</script>\r\n";
          MakeHtmlPageTop("Postnumre");
          echo "    <form name=\"UpdateForm\" action=\"zipcodes.php\" method=\"post\">\r\n";
          echo "      <input type=\"hidden\" name=\"CountryCode\" value=\"$ZipCodeRow[0]\">\r\n";
          echo "      <input type=\"hidden\" name=\"ZipCode\" value=\"$ZipCodeRow[1]\">\r\n";
          echo "      <table border=\"0\" cellpadding=\"1\" cellspacing=\"1\" width=\"100%\">\r\n";
          echo "        <tr><td width=\"100%\" colspan=\"2\" valign=\"middle\"><h2><i>Postnumre - $CountryName</i></h2></td></tr>\r\n";
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\">";
          if ($AllowUpdate)
          {
            $InputSubmit = MakeHtmlInputSubmit("Update", "Opdatér", !$AllowUpdate, $TabIndex++, "javascript:return validateUpdate()");
            $InputReset = MakeHtmlInputReset("Reset", "Fortryd", !$AllowUpdate, $TabIndex++, "");
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
          echo "</td></tr>\r\n";
          $CountryCodeInput = MakeHtmlInputText("", $ZipCodeRow[0], get_table_field_length($SystemNo, 2, "No"), get_table_field_length($SystemNo, 2, "No"), 1, 1, $TabIndex++, "");
          $CountryNameInput = MakeHtmlInputText("", get_table_field_value($CountryCodeSystemNo, 2, $ZipCodeRow[0], "Description"), get_table_field_length($CountryCodeSystemNo, 2, "Description"), get_table_field_length($CountryCodeSystemNo, 2, "Description"), 1, 1, $TabIndex++, "");
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Land :</td><td width=\"99%\">$CountryCodeInput&nbsp;$CountryNameInput</td></tr>\r\n";
          $Input = MakeHtmlInputText("", $ZipCodeRow[1], get_zipcode_length($SystemNo, $ZipCodeRow[0]), get_zipcode_length($SystemNo, $ZipCodeRow[0]), 1, 1, $TabIndex++, "");
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Postnummer :</td><td width=\"99%\">$Input</td></tr>\r\n";
          $Input = MakeHtmlInputText("CityName", $ZipCodeRow[2], oswebdb_field_len($ZipCodeResult, 2), oswebdb_field_len($ZipCodeResult, 2), !$AllowUpdate, !$AllowUpdate, $TabIndex++, "");
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap>By :</td><td width=\"99%\">$Input</td></tr>\r\n";
          echo "      </table>\r\n";
          echo "    </form>\r\n";
          MakeHtmlPageBottom();
        }
        else if (isset($CountryCode))
          MakeHtmlPageReload("javascript:this.location = 'zipcodes.php?CountryCode=$CountryCode'; return true;");
        else
          MakeHtmlPageReload("javascript:this.location = 'zipcodes.php'; return true;");
        oswebdb_free_result($ZipCodeResult);
      }
      else if (isset($CountryCode) && $ZipCodeResult = oswebdb_query("SELECT CountryCode,ZipCode,CityName FROM $TableName WHERE SystemNo=$CountryCodeSystemNo AND CountryCode=$CountryCode ORDER BY CityName,ZipCode"))
      {
        $TabIndex = 1;
        echo "<script language=\"JavaScript\">\r\n";
        echo "  function validateInsert()\r\n";
        echo "  {\r\n";
        echo "    zipcode = this.InsertForm.ZipCode.value\r\n";
        echo "    cityname = this.InsertForm.CityName.value\r\n";
        echo "    if (zipcode.length == 0)\r\n";
        echo "    {\r\n";
        echo "      alert('Postnummeret skal indtastes!')\r\n";
        echo "      return false\r\n";
        echo "    }\r\n";
        echo "    else if (cityname.length == 0)\r\n";
        echo "    {\r\n";
        echo "      alert('Bynavnet skal indtastes!')\r\n";
        echo "      return false\r\n";
        echo "    }\r\n";
        echo "    else\r\n";
        echo "      return true\r\n";
        echo "  }\r\n";
        echo "</script>\r\n";
        MakeHtmlPageTop("Postnumre");
        echo "    <table border=\"0\" cellpadding=\"1\" cellspacing=\"1\" width=\"100%\">\r\n";
        echo "      <tr><td width=\"100%\" colspan=\"2\" valign=\"middle\"><h2><i>Postnumre - $CountryName</i></h2></td></tr>\r\n";
        if ($AllowInsert)
        {
          $ZipCodeInput = MakeHtmlInputText("ZipCode", "", get_zipcode_length($SystemNo, $CountryCode), get_zipcode_length($SystemNo, $CountryCode), !$AllowInsert, !$AllowInsert, $TabIndex++, "");
          $CityNameInput = MakeHtmlInputText("CityName", "", oswebdb_field_len($ZipCodeResult, 2), oswebdb_field_len($ZipCodeResult, 2), !$AllowInsert, !$AllowInsert, $TabIndex++, "");
          $SubmitInput = MakeHtmlInputSubmit("Insert", "Opret", !$AllowInsert, $TabIndex++, "javascript:return validateInsert();");
          echo "      <form name=\"InsertForm\" action=\"zipcodes.php\" method=\"post\">\r\n";
          echo "        <input type=\"hidden\" name=\"CountryCode\" value=\"$CountryCode\">\r\n";
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap><strong>Postnummer / By :</strong></td><td width=\"99%\">$ZipCodeInput&nbsp;/&nbsp;$CityNameInput&nbsp;$SubmitInput</td></tr>\r\n";
          echo "      </form>\r\n";
        }
        while ($ZipCodeRow = oswebdb_fetch_row($ZipCodeResult))
        {
          if ($AllowUpdate)
            $Input = MakeHtmlInputSubmit("", "Redigér", !$AllowUpdate, $TabIndex++, "");
          else
            $Input = MakeHtmlInputSubmit("", "Vis", 0, $TabIndex++, "");
          echo "      <form action=\"zipcodes.php\" method=\"post\"><input type=\"hidden\" name=\"CountryCode\" value=\"$ZipCodeRow[0]\"><input type=\"hidden\" name=\"ZipCode\" value=\"$ZipCodeRow[1]\"><tr><td width=\"1%\" valign=\"middle\" align=\"right\" nowrap>$Input</td><td width=\"99%\" valign=\"middle\">$ZipCodeRow[1]&nbsp;&nbsp;$ZipCodeRow[2]</td></tr></form>\r\n";
        }
        echo "    </table>\r\n";
        MakeHtmlPageBottom();
        oswebdb_free_result($ZipCodeResult);
      }
      else if ($CountryCodeResult = oswebdb_query("SELECT No,Description FROM Systemtables WHERE SystemNo=$CountryCodeSystemNo AND TableNo=2 ORDER BY Description,No"))
      {
        $TabIndex = 1;
        MakeHtmlPageTop("Postnumre");
        echo "    <table border=\"0\" cellpadding=\"1\" cellspacing=\"1\" width=\"100%\">\r\n";
        echo "      <tr><td width=\"100%\" colspan=\"2\" valign=\"middle\"><h2><i>Postnumre</i></h2></td></tr>\r\n";
        while ($CountryCodeRow = oswebdb_fetch_row($CountryCodeResult))
        {
          if ($AllowUpdate)
            $Input = MakeHtmlInputSubmit("", "Redigér", !$AllowUpdate, $TabIndex++, "");
          else
            $Input = MakeHtmlInputSubmit("", "Vis", 0, $TabIndex++, "");
          echo "      <form action=\"zipcodes.php\" method=\"post\"><input type=\"hidden\" name=\"CountryCode\" value=\"$CountryCodeRow[0]\"><tr><td width=\"1%\" valign=\"middle\" align=\"right\" nowrap>$Input</td><td width=\"99%\" valign=\"middle\">$CountryCodeRow[1]</td></tr></form>\r\n";
        }
        echo "    </table>\r\n";
        MakeHtmlPageBottom();
        oswebdb_free_result($CountryCodeResult);
      }
    }
    oswebdb_close();
  }
?>
