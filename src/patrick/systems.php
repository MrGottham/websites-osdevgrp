<?php
  // Include required files.
  require_once("misc.php");
  require_once("oswebdb/oswebdb.php");
  require_once("oswebdb/systems.php");
  require_once("oswebdb/tables.php");

  // Set error reporting.
  error_reporting((int) GetConfigValue("ErrorReporting"));

  define("MAX_SIZE", 50);
  $TableName = "Systems";
  $SystemNo = GetConfigValue("SystemNo");
  if (oswebdb_authorize(oswebdb_getusername(), oswebdb_getpassword()))
  {
    if (oswebdb_selectdb())
    {
    	$IsAdministrator = oswebdb_is_administrator($SystemNo);
      $AllowUpdate = oswebdb_allow_update($TableName);
      $AllowDelete = oswebdb_allow_delete($TableName);
      if (isset($_POST["Update"]))
      {
        if (!update_system($SystemNo, $_POST["Title"], $_POST["Name"], $_POST["Address1"], $_POST["Address2"], $_POST["Address3"], $_POST["Phone"], $_POST["Mobile"], $_POST["Email"], $_POST["CountryCode"], $_POST["SeasonNo"], $_POST["Databaseadministration"], $_POST["Debate"], $_POST["Addresses"], $_POST["Calender"], $_POST["Allowance"], $_POST["PathPictures"], $_POST["DaysPictures"], $_POST["PathDocuments"], $_POST["DaysDocuments"], $_POST["AdminRole"], $_POST["ConfigRole"], $_POST["UserRole"], $_POST["MetaDescription"], $_POST["MetaKeywords"], $_POST["MenuTitle"]))
        {
          $Error = oswebdb_error();
          MakeHtmlPageTop("Systemparametre");
          echo "    <h2><strong>$Error</strong></h2>\r\n";
          MakeHtmlPageBottom("");
          exit;
        }
      }
      else if (isset($_POST["Delete"]))
      {
        if (!delete_system($SystemNo))
        {
          $Error = oswebdb_error();
          MakeHtmlPageTop("Systemparametre");
          echo "    <h2><strong>$Error</strong></h2>\r\n";
          MakeHtmlPageBottom("");
          exit;
        }
      }
      if ($Result = oswebdb_query("SELECT Title,Name,Address1,Address2,Address3,Phone,Mobile,Email,CountryCode,SeasonNo,Properties,PathPictures,DaysPictures,PathDocuments,DaysDocuments,AdminRole,ConfigRole,UserRole,MetaDescription,MetaKeywords,MenuTitle FROM $TableName WHERE SystemNo=$SystemNo"))
      {
        if ($Row = oswebdb_fetch_row($Result))
        {
          $TabIndex = 1;
          echo "<script language=\"JavaScript\">\r\n";
          echo "  function validateSystem()\r\n";
          echo "  {\r\n";
          echo "    title = this.SystemForm.Title.value\r\n";
          echo "    menuTitle = this.SystemForm.MenuTitle.value\r\n";
          echo "    daysPictures = parseInt(this.SystemForm.DaysPictures.value, 10)\r\n";
          echo "    daysDocuments = parseInt(this.SystemForm.DaysDocuments.value, 10)\r\n";
          echo "    if (title.length == 0)\r\n";
          echo "    {\r\n";
          echo "      alert('Titlen skal indtastes!')\r\n";
          echo "      return false\r\n";
          echo "    }\r\n";
          echo "    else if (menuTitle.length == 0)\r\n";
          echo "    {\r\n";
          echo "      alert('Menutitlen skal indtastes!')\r\n";
          echo "      return false\r\n";
          echo "    }\r\n";
          echo "    else if (daysPictures <= 0)\r\n";
          echo "    {\r\n";
          echo "      alert('Antal dage på billeder, der skal vises, skal være større end 0!')\r\n";
          echo "      return false\r\n";
          echo "    }\r\n";
          echo "    else if (daysDocuments <= 0)\r\n";
          echo "    {\r\n";
          echo "      alert('Antal dage på dokumenter, der skal vises, skal være større end 0!')\r\n";
          echo "      return false\r\n";
          echo "    }\r\n";
          echo "    else\r\n";
          echo "    {\r\n";
          echo "      this.SystemForm.Email.value = parent.getEmail(this.SystemForm.Email.value)\r\n";
          echo "      return true\r\n";
          echo "    }\r\n";
          echo "  }\r\n";
          echo "</script>\r\n";
          MakeHtmlPageTop("Systemparametre");
          echo "    <form name=\"SystemForm\" method=\"post\">\r\n";
          echo "      <table border=\"0\" cellpadding=\"1\" cellspacing=\"1\" width=\"100%\">\r\n";
          echo "        <tr><td width=\"100%\" colspan=\"2\" valign=\"middle\"><h2><i>Systemparametre</i></h2></td></tr>\r\n";
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\">";
          if ($AllowUpdate)
          {
            $InputSubmit = MakeHtmlInputSubmit("Update", "Opdatér", 0, $TabIndex++, "javascript:return validateSystem()");
            $InputReset = MakeHtmlInputReset("Reset", "Fortryd", 0, $TabIndex++, "");
            echo "$InputSubmit&nbsp;$InputReset&nbsp;";
          }
          if ($AllowDelete)
          {
            $InputDelete = MakeHtmlInputSubmit("Delete", "Slet", 0, $TabIndex++, "javascript:return confirm('Bekræft sletning!')");
            echo "$InputDelete&nbsp;";
          }
          echo "</td>\r\n";
          $Input = MakeHtmlInputText("Title", $Row[0], oswebdb_field_len($Result, 0), oswebdb_field_len($Result, 0), !$AllowUpdate, !$AllowUpdate, $TabIndex++, "");
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Titel :</td><td width=\"99%\">$Input</td><tr>\r\n";
          $Input = MakeHtmlInputText("Name", $Row[1], oswebdb_field_len($Result, 1), oswebdb_field_len($Result, 1), !$AllowUpdate, !$AllowUpdate, $TabIndex++, "");
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Navn :</td><td width=\"99%\">$Input</td><tr>\r\n";
          $Input = MakeHtmlInputText("Address1", $Row[2], oswebdb_field_len($Result, 2), oswebdb_field_len($Result, 2), !$AllowUpdate, !$AllowUpdate, $TabIndex++, "");
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Adresse :</td><td width=\"99%\">$Input</td><tr>\r\n";
          $Input = MakeHtmlInputText("Address2", $Row[3], oswebdb_field_len($Result, 3), oswebdb_field_len($Result, 3), !$AllowUpdate, !$AllowUpdate, $TabIndex++, "");
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\">$Input</td><tr>\r\n";
          $Input = MakeHtmlInputText("Address3", $Row[4], oswebdb_field_len($Result, 4), oswebdb_field_len($Result, 4), !$AllowUpdate, !$AllowUpdate, $TabIndex++, "");
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\">$Input</td><tr>\r\n";
          $Input = MakeHtmlInputText("Phone", $Row[5], oswebdb_field_len($Result, 5), oswebdb_field_len($Result, 5), !$AllowUpdate, !$AllowUpdate, $TabIndex++, "");
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Telefon :</td><td width=\"99%\">$Input</td><tr>\r\n";
          $Input = MakeHtmlInputText("Mobile", $Row[6], oswebdb_field_len($Result, 6), oswebdb_field_len($Result, 6), !$AllowUpdate, !$AllowUpdate, $TabIndex++, "");
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Mobil :</td><td width=\"99%\">$Input</td><tr>\r\n";
          $Input = MakeHtmlInputText("Email", $Row[7], oswebdb_field_len($Result, 7), oswebdb_field_len($Result, 7), !$AllowUpdate, !$AllowUpdate, $TabIndex++, "javascript:this.value = parent.getEmail(this.value)");
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Email :</td><td width=\"99%\">$Input</td><tr>\r\n";
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\"><strong>Standardværdier:</strong></td><tr>\r\n";
          $TableSystemNo = get_system_for_table($SystemNo, 2);
          $Options = "<option value=\"0\"";
          if ($Row[8] == 0)
            $Options = "$Options selected";
          $Options = "$Options>(Ingen)";
          if ($CountryCodeResult = oswebdb_query("SELECT No,Description FROM Systemtables WHERE SystemNo=$TableSystemNo AND TableNo=2 ORDER BY Description"))
          {
            while ($CountryCodeRow = oswebdb_fetch_row($CountryCodeResult))
            {
              $Options = "$Options<option value=\"$CountryCodeRow[0]\"";
              if ($Row[8] == $CountryCodeRow[0])
                $Options = "$Options selected";
              $Options = "$Options>$CountryCodeRow[1]";
            }
            oswebdb_free_result($CountryCodeResult);
          }
          $Input = MakeHtmlSelect("CountryCode", 0, 0, !$AllowUpdate, !$AllowUpdate, $TabIndex++, "", $Options);
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Land :</td><td width=\"99%\">$Input</td><tr>\r\n";
          $TableSystemNo = get_system_for_table($SystemNo, 7);
          $Options = "<option value=\"0\"";
          if ($Row[9] == 0)
            $Options = "$Options selected";
          $Options = "$Options>(Ingen)";
          if ($SeasonResult = oswebdb_query("SELECT No,Description FROM Systemtables WHERE SystemNo=$TableSystemNo AND TableNo=7 ORDER BY Description"))
          {
            while ($SeasonRow = oswebdb_fetch_row($SeasonResult))
            {
              $Options = "$Options<option value=\"$SeasonRow[0]\"";
              if ($Row[9] == $SeasonRow[0])
                $Options = "$Options selected";
              $Options = "$Options>$SeasonRow[1]";
            }
            oswebdb_free_result($SeasonResult);
          }
          $Input = MakeHtmlSelect("SeasonNo", 0, 0, !$AllowUpdate, !$AllowUpdate, $TabIndex++, "", $Options);
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Sæson :</td><td width=\"99%\">$Input</td><tr>\r\n";
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\"><strong>Egenskaber:</strong></td><tr>\r\n";
          $Input = MakeHtmlInputCheckBox("Addresses", "4", (int) ($Row[10] & 4), !$AllowUpdate, $TabIndex++, "", "Adressekartotek");
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\">$Input</td><tr>\r\n";
          $Input = MakeHtmlInputCheckBox("Debate", "2", (int) ($Row[10] & 2), !$AllowUpdate, $TabIndex++, "", "Debatter");
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\">$Input</td><tr>\r\n";
          $Input = MakeHtmlInputCheckBox("Calender", "8", (int) ($Row[10] & 8), !$AllowUpdate, $TabIndex++, "", "Kalender");
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\">$Input</td><tr>\r\n";
          $Input = MakeHtmlInputCheckBox("Allowance", "16", (int) ($Row[10] & 16), !$AllowUpdate, $TabIndex++, "", "Kørselsfradrag");
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\">$Input</td><tr>\r\n";
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\"><strong>Værktøjer:</strong></td><tr>\r\n";
          $Input = MakeHtmlInputCheckBox("Databaseadministration", "1", (int) ($Row[10] & 1), !$AllowUpdate, $TabIndex++, "", "Databaseadministration");
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\">$Input</td><tr>\r\n";
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\"><strong>Placeringer:</strong></td><tr>\r\n";
          $Options = "<option value=\"/\"";
          if ($Row[11] == "/")
            $Options = "$Options selected";
          $Options = "$Options>/";
          if ($Path = opendir("./"))
          {
            while (($Name = readdir($Path)) != false)
            {
              if (is_dir($Name) && $Name != "." && $Name != "..")
              {
                $Options = "$Options<option value=\"$Name/\"";
                if ($Row[11] == "$Name/")
                  $Options = "$Options selected";
                $Options = "$Options>$Name/";
              }
            }
            closedir($Path);
          }
          $Input = MakeHtmlSelect("PathPictures", 0, 0, !$AllowUpdate, !$AllowUpdate, $TabIndex++, "", $Options);
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Billeder :</td><td width=\"99%\">$Input</td><tr>\r\n";
          $Input = MakeHtmlInputText("DaysPictures", $Row[12], oswebdb_field_len($Result, 12), oswebdb_field_len($Result, 12), !$AllowUpdate, !$AllowUpdate, $TabIndex++, "");
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\">Vis billeder, der er yngre end $Input dage</td><tr>\r\n";
          $Options = "<option value=\"/\"";
          if ($Row[13] == "/")
            $Options = "$Options selected";
          $Options = "$Options>/";
          if ($Path = opendir("./"))
          {
            while (($Name = readdir($Path)) != false)
            {
              if (is_dir($Name) && $Name != "." && $Name != "..")
              {
                $Options = "$Options<option value=\"$Name/\"";
                if ($Row[13] == "$Name/")
                  $Options = "$Options selected";
                $Options = "$Options>$Name/";
              }
            }
            closedir($Path);
          }
          $Input = MakeHtmlSelect("PathDocuments", 0, 0, !$AllowUpdate, !$AllowUpdate, $TabIndex++, "", $Options);
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Dokumenter :</td><td width=\"99%\">$Input</td><tr>\r\n";
          $Input = MakeHtmlInputText("DaysDocuments", $Row[14], oswebdb_field_len($Result, 14), oswebdb_field_len($Result, 14), !$AllowUpdate, !$AllowUpdate, $TabIndex++, "");
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\">Vis dokumenter, der er yngre end $Input dage</td><tr>\r\n";
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\"><strong>Roller:</strong></td><tr>\r\n";
          $TableSystemNo = get_system_for_table($SystemNo, 10);
          $Options = "<option value=\"0\"";
          if ($Row[15] == 0)
            $Options = "$Options selected";
          $Options = "$Options>(Ingen)";
          if ($RoleResult = oswebdb_query("SELECT No,Description FROM Systemtables WHERE SystemNo=$TableSystemNo AND TableNo=10 ORDER BY Description"))
          {
            while ($RoleRow = oswebdb_fetch_row($RoleResult))
            {
              $Options = "$Options<option value=\"$RoleRow[0]\"";
              if ($Row[15] == $RoleRow[0])
                $Options = "$Options selected";
              $Options = "$Options>$RoleRow[1]";
            }
            oswebdb_free_result($RoleResult);
          }
          $Input = MakeHtmlSelect("AdminRole", 0, 0, !($AllowUpdate && $IsAdministrator), !($AllowUpdate && $IsAdministrator), $TabIndex++, "", $Options);
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Administratorer :</td><td width=\"99%\">$Input</td><tr>\r\n";
          $TableSystemNo = get_system_for_table($SystemNo, 10);
          $Options = "<option value=\"0\"";
          if ($Row[16] == 0)
            $Options = "$Options selected";
          $Options = "$Options>(Ingen)";
          if ($RoleResult = oswebdb_query("SELECT No,Description FROM Systemtables WHERE SystemNo=$TableSystemNo AND TableNo=10 ORDER BY Description"))
          {
            while ($RoleRow = oswebdb_fetch_row($RoleResult))
            {
              $Options = "$Options<option value=\"$RoleRow[0]\"";
              if ($Row[16] == $RoleRow[0])
                $Options = "$Options selected";
              $Options = "$Options>$RoleRow[1]";
            }
            oswebdb_free_result($RoleResult);
          }
          $Input = MakeHtmlSelect("ConfigRole", 0, 0, !($AllowUpdate && $IsAdministrator), !($AllowUpdate && $IsAdministrator), $TabIndex++, "", $Options);
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Konfiguratorer :</td><td width=\"99%\">$Input</td><tr>\r\n";
          $TableSystemNo = get_system_for_table($SystemNo, 10);
          $Options = "<option value=\"0\"";
          if ($Row[17] == 0)
            $Options = "$Options selected";
          $Options = "$Options>(Ingen)";
          if ($RoleResult = oswebdb_query("SELECT No,Description FROM Systemtables WHERE SystemNo=$TableSystemNo AND TableNo=10 ORDER BY Description"))
          {
            while ($RoleRow = oswebdb_fetch_row($RoleResult))
            {
              $Options = "$Options<option value=\"$RoleRow[0]\"";
              if ($Row[17] == $RoleRow[0])
                $Options = "$Options selected";
              $Options = "$Options>$RoleRow[1]";
            }
            oswebdb_free_result($RoleResult);
          }
          $Input = MakeHtmlSelect("UserRole", 0, 0, !($AllowUpdate && $IsAdministrator), !($AllowUpdate && $IsAdministrator), $TabIndex++, "", $Options);
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Brugere :</td><td width=\"99%\">$Input</td><tr>\r\n";
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\"><strong>Metadata:</strong></td><tr>\r\n";
          $Input = MakeHtmlInputText("MetaDescription", $Row[18], oswebdb_field_len($Result, 18), oswebdb_field_len($Result, 18), !$AllowUpdate, !$AllowUpdate, $TabIndex++, "");
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Beskrivelse :</td><td width=\"99%\">$Input</td><tr>\r\n";
          $Input = MakeHtmlInputText("MetaKeywords", $Row[19], oswebdb_field_len($Result, 19), oswebdb_field_len($Result, 19), !$AllowUpdate, !$AllowUpdate, $TabIndex++, "");
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Keywords :</td><td width=\"99%\">$Input</td><tr>\r\n";
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\"><strong>Menuopsætning:</strong></td><tr>\r\n";
          $Input = MakeHtmlInputText("MenuTitle", $Row[20], oswebdb_field_len($Result, 20), oswebdb_field_len($Result, 20), !$AllowUpdate, !$AllowUpdate, $TabIndex++, "");
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Titel :</td><td width=\"99%\">$Input</td><tr>\r\n";
          echo "      </table>\r\n";
          echo "    </form>\r\n";
          MakeHtmlPageBottom();
        }
        oswebdb_free_result($Result);
      }
    }
    oswebdb_close();
  }
?>
