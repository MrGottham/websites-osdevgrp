<?php
  // Include required files.
  require_once("misc.php");
  require_once("oswebdb/oswebdb.php");
  require_once("oswebdb/address.php");
  require_once("oswebdb/zipcodes.php");
  require_once("oswebdb/systems.php");
  require_once("oswebdb/tables.php");

  // Set error reporting.
  error_reporting((int) GetConfigValue("ErrorReporting"));

  $AddressTableName = "Addresses";
  $AddressLinkTableName = "Addresslinks";
  $AddressMatchTableName = "Addressmatches";
  $SystemNo = GetConfigValue("SystemNo");
  $AverageTableNo = 8;
  $MatchTableNo = 9;
  if (isset($_GET["No"]))
    $No = $_GET["No"];
  else if (isset($_POST["No"]))
    $No = $_POST["No"];
  if (isset($_GET["GroupNo"]))
    $GroupNo = $_GET["GroupNo"];
  else if (isset($_POST["GroupNo"]))
    $GroupNo = $_POST["GroupNo"];
  $Username = oswebdb_getusername();
  $Password = oswebdb_getpassword();
  if (oswebdb_connect($Username, $Password))
  {
    if (oswebdb_selectdb())
    {
      $AllowSelect = oswebdb_allow_select($AddressTableName) && oswebdb_allow_select($AddressLinkTableName) && oswebdb_allow_select($AddressMatchTableName);
      $AllowInsert = oswebdb_allow_insert($AddressTableName) && oswebdb_allow_insert($AddressLinkTableName) && oswebdb_allow_insert($AddressMatchTableName);
      $AllowUpdate = oswebdb_allow_update($AddressTableName) && oswebdb_allow_update($AddressLinkTableName) && oswebdb_allow_update($AddressMatchTableName);
      $AllowDelete = oswebdb_allow_delete($AddressTableName) && oswebdb_allow_delete($AddressLinkTableName) && oswebdb_allow_delete($AddressMatchTableName);
      $Authenticated = isset($Username) && isset($Password);
      $NewAddress = isset($_POST["NewAddress"]) || isset($_GET["NewAddress"]);
      if (isset($_POST["Search"]) && isset($_POST["SearchValue"]))
      {
        $Search = $_POST["Search"];
        $SearchValue = $_POST["SearchValue"];
      }
      if ($NewAddress)
        $AddressStatement = "SELECT No,Name,Address1,Address2,CountryCode,ZipCode,Phone1,Phone2,Fax,BirthDate,GroupNo,Email,Web,Public,ParentNo FROM $AddressTableName WHERE SystemNo=$SystemNo";
      else if (isset($No) && !isset($_POST["ShowAll"]))
        $AddressStatement = "SELECT No,Name,Address1,Address2,CountryCode,ZipCode,Phone1,Phone2,Fax,BirthDate,GroupNo,Email,Web,Public,ParentNo FROM $AddressTableName WHERE SystemNo=$SystemNo AND No=$No";
      else if (isset($GroupNo) && !isset($_POST["ShowAll"]))
      {
        if (isset($Search) && isset($SearchValue))
        {
          $AddressStatement = "SELECT No,Name,Phone1 FROM $AddressTableName WHERE SystemNo=$SystemNo AND (Name LIKE \"$SearchValue\" OR Name LIKE \"%$SearchValue\" OR Name LIKE \"%$SearchValue%\" OR Name LIKE \"$SearchValue%\") AND GroupNo=$GroupNo AND Public IN (1) ORDER BY Name,Phone1";
          if ($Authenticated)
            $AddressStatement = "SELECT No,Name,Phone1 FROM $AddressTableName WHERE SystemNo=$SystemNo AND (Name LIKE \"$SearchValue\" OR Name LIKE \"%$SearchValue\" OR Name LIKE \"%$SearchValue%\" OR Name LIKE \"$SearchValue%\") AND GroupNo=$GroupNo AND Public IN (0,1) ORDER BY Name,Phone1";
        }
        else
        {
          $AddressStatement = "SELECT No,Name,Phone1 FROM $AddressTableName WHERE SystemNo=$SystemNo AND GroupNo=$GroupNo AND Public IN (1) ORDER BY Name,Phone1";
          if ($Authenticated)
            $AddressStatement = "SELECT No,Name,Phone1 FROM $AddressTableName WHERE SystemNo=$SystemNo AND GroupNo=$GroupNo AND Public IN (0,1) ORDER BY Name,Phone1";
        }
      }
      else if (isset($Search) && isset($SearchValue))
      {
        $AddressStatement = "SELECT No,Name,Phone1 FROM $AddressTableName WHERE SystemNo=$SystemNo AND (Name LIKE \"$SearchValue\" OR Name LIKE \"%$SearchValue\" OR Name LIKE \"%$SearchValue%\" OR Name LIKE \"$SearchValue%\") AND Public IN (1) ORDER BY Name,Phone1";
        if ($Authenticated)
          $AddressStatement = "SELECT No,Name,Phone1 FROM $AddressTableName WHERE SystemNo=$SystemNo AND (Name LIKE \"$SearchValue\" OR Name LIKE \"%$SearchValue\" OR Name LIKE \"%$SearchValue%\" OR Name LIKE \"$SearchValue%\") AND Public IN (0,1) ORDER BY Name,Phone1";
      }
      $GroupSystemNo = get_system_for_table($SystemNo, 3);
      $GroupStatement = "SELECT No,Description FROM Systemtables WHERE SystemNo=$GroupSystemNo AND TableNo=3 AND Public IN (1) ORDER BY Description,No";
      if ($Authenticated)
        $GroupStatement = "SELECT No,Description FROM Systemtables WHERE SystemNo=$GroupSystemNo AND TableNo=3 AND Public IN (0,1) ORDER BY Description,No";
      if (isset($_POST["Insert"]) && isset($No) && isset($_POST["Name"]))
      {
        if (!insert_address($SystemNo, $No, $_POST["Name"], $_POST["Address1"], $_POST["Address2"], $_POST["ZipCode"], $_POST["CountryCode"], $_POST["Phone1"], $_POST["Phone2"], $_POST["Fax"], $_POST["BirthDate"], $_POST["GroupNo"], $_POST["Public"], $_POST["Email"], $_POST["Web"], $_POST["ParentNo"]))
        {
          $Error = oswebdb_error();
          MakeHtmlPageTop("Adresser");
          echo "    <h2><strong>$Error</strong></h2>\r\n";
          MakeHtmlPageBottom("");
          exit;
        }
      }
      else if (isset($_POST["Update"]) && isset($No) && isset($_POST["Name"]))
      {
        if (!update_address($SystemNo, $No, $_POST["Name"], $_POST["Address1"], $_POST["Address2"], $_POST["ZipCode"], $_POST["CountryCode"], $_POST["Phone1"], $_POST["Phone2"], $_POST["Fax"], $_POST["BirthDate"], $_POST["GroupNo"], $_POST["Public"], $_POST["Email"], $_POST["Web"], $_POST["ParentNo"]))
        {
          $Error = oswebdb_error();
          MakeHtmlPageTop("Adresser");
          echo "    <h2><strong>$Error</strong></h2>\r\n";
          MakeHtmlPageBottom("");
          exit;
        }
      }
      else if (isset($_POST["Delete"]) && isset($No))
      {
        if (!delete_address($SystemNo, $No))
        {
          $Error = oswebdb_error();
          MakeHtmlPageTop("Adresser");
          echo "    <h2><strong>$Error</strong></h2>\r\n";
          MakeHtmlPageBottom("");
          exit;
        }
        unset($No);
      }
      else if (isset($_POST["ShowAll"]) && (isset($No) || isset($GroupNo)))
      {
        if (isset($No))
          unset($No);
        if (isset($GroupNo))
          unset($GroupNo);
      }
      else if (isset($_POST["InsertLink"]) && isset($No) && isset($_POST["TableNo"]) && isset($_POST["SeasonNo"]) && isset($_POST["TypeNo"]))
      {
        if (!insert_address_link($SystemNo, $No, $_POST["TableNo"], $_POST["SeasonNo"], $_POST["TypeNo"], $_POST["Property0"], $_POST["Property1"], $_POST["Property2"], $_POST["Property3"], $_POST["Property4"], $_POST["Property5"], $_POST["Property6"], $_POST["Property7"], $_POST["Average"], $_POST["Distance"]))
        {
          $Error = oswebdb_error();
          MakeHtmlPageTop("Adresser");
          echo "    <h2><strong>$Error</strong></h2>\r\n";
          MakeHtmlPageBottom("");
          exit;
        }
      }
      else if (isset($_POST["UpdateLink"]) && isset($No) && isset($_POST["TableNo"]) && isset($_POST["SeasonNo"]) && isset($_POST["TypeNo"]))
      {
        if (!update_address_link($SystemNo, $No, $_POST["TableNo"], $_POST["SeasonNo"], $_POST["TypeNo"], $_POST["Property0"], $_POST["Property1"], $_POST["Property2"], $_POST["Property3"], $_POST["Property4"], $_POST["Property5"], $_POST["Property6"], $_POST["Property7"], $_POST["Average"], $_POST["Distance"]))
        {
          $Error = oswebdb_error();
          MakeHtmlPageTop("Adresser");
          echo "    <h2><strong>$Error</strong></h2>\r\n";
          MakeHtmlPageBottom("");
          exit;
        }
      }
      else if (isset($_POST["DeleteLink"]) && isset($No) && isset($_POST["TableNo"]) && isset($_POST["SeasonNo"]) && isset($_POST["TypeNo"]))
      {
        if (!delete_address_link($SystemNo, $No, $_POST["TableNo"], $_POST["SeasonNo"], $_POST["TypeNo"]))
        {
          $Error = oswebdb_error();
          MakeHtmlPageTop("Adresser");
          echo "    <h2><strong>$Error</strong></h2>\r\n";
          MakeHtmlPageBottom("");
          exit;
        }
      }
      if (($NewAddress || isset($No)) && $AddressResult = oswebdb_query($AddressStatement))
      {
        if ($NewAddress || $AddressRow = oswebdb_fetch_row($AddressResult))
        {
          $TabIndex = 1;
          $TabAdjust = 5;
          echo "<script language=\"JavaScript\">\r\n";
          echo "  function validateAddress()\r\n";
          echo "  {\r\n";
          if ($NewAddress)
          {
            echo "    no = this.AddressForm.No.value\r\n";
            echo "    name = this.AddressForm.Name.value\r\n";
            echo "    if (no.length == 0)\r\n";
            echo "    {\r\n";
            echo "      alert('Nummeret skal indtastes!')\r\n";
            echo "      return false\r\n";
            echo "    }\r\n";
            echo "    else if (no <= 0)\r\n";
            echo "    {\r\n";
            echo "      alert('Nummeret være større end 0!')\r\n";
            echo "      return false\r\n";
            echo "    }\r\n";
            echo "    else if (name.length == 0)\r\n";
          }
          else
          {
            echo "    name = this.AddressForm.Name.value\r\n";
            echo "    if (name.length == 0)\r\n";
          }
          echo "    {\r\n";
          echo "      alert('Navnet skal indtastes!')\r\n";
          echo "      return false\r\n";
          echo "    }\r\n";
          echo "    else\r\n";
          echo "    {\r\n";
          echo "      this.AddressForm.BirthDate.value = validateBirthDate(this.AddressForm.BirthDate.value)\r\n";
          echo "      this.AddressForm.Email.value = parent.getEmail(this.AddressForm.Email.value)\r\n";
          echo "      this.AddressForm.Web.value = parent.getWeb(this.AddressForm.Web.value)\r\n";
          echo "      return true\r\n";
          echo "    }\r\n";
          echo "  }\r\n\r\n";
          echo "  function validateBirthDate(birthdate)\r\n";
          echo "  {\r\n";
          echo "    if (birthdate.length == 0)\r\n";
          echo "      birthdate = '0000-00-00'\r\n";
          echo "    return birthdate\r\n";
          echo "  }\r\n\r\n";
          echo "  function resetAddress()\r\n";
          echo "  {\r\n";
          if ($NewAddress)
          {
            $CountryCode = (integer) get_system_country_code($SystemNo);
            $Email = "";
            $Web = "";
          }
          else
          {
            $CountryCode = (integer) $AddressRow[4];
            $Email = $AddressRow[11];
            $Web = $AddressRow[12];
          }
          echo "    web = '$Web'\r\n";
          echo "    email = '$Email'\r\n";
          echo "    parent.getZipCodeLength($CountryCode, AddressForm.ZipCode, AddressForm.CityName)\r\n";
          echo "    AddressForm.MailTo.disabled = web.length == 0\r\n";
          echo "    AddressForm.Visit.disabled = web.length == 0\r\n";
          echo "    return true\r\n";
          echo "  }\r\n\r\n";
          if (!$NewAddress)
          {
            $GroupProperties = (integer) get_table_field_value($GroupSystemNo, 3, $AddressRow[10], "Properties");
            if ($GroupProperties & 2)
            {
              echo "  function validateAverageForm(func, oForm)\r\n";
              echo "  {\r\n";
              echo "    switch (func)\r\n";
              echo "    {\r\n";
              echo "      case 1:\r\n";
              echo "        seasonNo = 0\r\n";
              echo "        for (i = 0; i < oForm.SeasonNo.length; i++)\r\n";
              echo "        {\r\n";
              echo "          if (oForm.SeasonNo.options[i].selected)\r\n";
              echo "            seasonNo = parseInt(oForm.SeasonNo.options[i].value, 10)\r\n";
              echo "        }\r\n";
              echo "        typeNo = 0\r\n";
              echo "        for (i = 0; i < oForm.TypeNo.length; i++)\r\n";
              echo "        {\r\n";
              echo "          if (oForm.TypeNo.options[i].selected)\r\n";
              echo "            typeNo = parseInt(oForm.TypeNo.options[i].value, 10)\r\n";
              echo "        }\r\n";
              echo "        break\r\n\r\n";
              echo "      case 2:\r\n";
              echo "        seasonNo = parseInt(oForm.SeasonNo.value, 10)\r\n";
              echo "        typeNo = parseInt(oForm.TypeNo.value, 10)\r\n";
              echo "        break\r\n\r\n";
              echo "      default:\r\n";
              echo "        seasonNo = 0\r\n";
              echo "        typeNo = 0\r\n";
              echo "    }\r\n";
              echo "    average = parent.getFloatValue(oForm.Average)\r\n";
              echo "    if (average == 0)\r\n";
              echo "      oForm.Average.value = ''\r\n";
              echo "    distance = parent.getIntValue(oForm.Distance)\r\n";
              echo "    if (distance == 0)\r\n";
              echo "        oForm.Distance.value = ''\r\n";
              echo "    if (!(parseInt(oForm.No.value, 10) > 0))\r\n";
              echo "    {\r\n";
              echo "      alert('Nummeret på adressen skal være større end 0!')\r\n";
              echo "      return false\r\n";
              echo "    }\r\n";
              echo "    else if (!(parseInt(oForm.TableNo.value, 10) > 0))\r\n";
              echo "    {\r\n";
              echo "      alert('Nummeret på tabellen skal være større end 0!')\r\n";
              echo "      return false\r\n";
              echo "    }\r\n";
              echo "    else if (!(seasonNo > 0))\r\n";
              echo "    {\r\n";
              echo "      alert(func == 1 ? 'Der skal vælges en sæson!' : 'Nummeret på sæsonen skal være større end 0!')\r\n";
              echo "      return false\r\n";
              echo "    }\r\n";
              echo "    else if (!(typeNo > 0))\r\n";
              echo "    {\r\n";
              echo "      alert(func == 1 ? 'Der skal vælges en type!' : 'Nummeret på typen skal være større end 0!')\r\n";
              echo "      return false\r\n";
              echo "    }\r\n";
              echo "    else if (!(average > 0))\r\n";
              echo "    {\r\n";
              echo "      alert('Gennemsnittet være større end 0!')\r\n";
              echo "      return false\r\n";
              echo "    }\r\n";
              echo "    else if (!(distance >= 0))\r\n";
              echo "    {\r\n";
              echo "      alert('Den interne distance være større end eller lig med 0!')\r\n";
              echo "      return false\r\n";
              echo "    }\r\n";
              echo "    else\r\n";
              echo "      return true\r\n";
              echo "  }\r\n\r\n";
            }
          }
          echo "  function changeEmail()\r\n";
          echo "  {\r\n";
          echo "    this.AddressForm.Email.value = parent.getEmail(this.AddressForm.Email.value)\r\n";
          echo "    email = this.AddressForm.Email.value\r\n";
          echo "    this.AddressForm.MailTo.disabled = email.length == 0\r\n";
          echo "  }\r\n\r\n";
          echo "  function changeWeb()\r\n";
          echo "  {\r\n";
          echo "    this.AddressForm.Web.value = parent.getWeb(this.AddressForm.Web.value)\r\n";
          echo "    web = this.AddressForm.Web.value\r\n";
          echo "    this.AddressForm.Visit.disabled = web.length == 0\r\n";
          echo "  }\r\n";
          echo "</script>\r\n";
          MakeHtmlPageTop("Adresser");
          echo "    <table border=\"0\" cellpadding=\"1\" cellspacing=\"1\" width=\"100%\">\r\n";
          echo "      <form name=\"AddressForm\" action=\"address.php\" method=\"post\">\r\n";
          if (!$NewAddress)
            echo "        <input type=\"hidden\" name=\"No\" value=\"$AddressRow[0]\">\r\n";
          echo "        <tr><td width=\"100%\" colspan=\"4\" valign=\"middle\"><h2><i>Adresser</i></h2></td></tr>\r\n";
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\" colspan=\"3\">";
          if ($NewAddress)
          {
            if ($AllowInsert)
            {
              $InputSubmit = MakeHtmlInputSubmit("Insert", "Opret", !$AllowInsert, $TabIndex++, "javascript:return validateAddress()");
              $InputReset = MakeHtmlInputReset("Reset", "Fortryd", !$AllowInsert, $TabIndex++, "javescript:return resetAddress()");
              echo "$InputSubmit&nbsp;$InputReset&nbsp;";
            }
            if ($AllowSelect)
            {
              $InputShowAll = MakeHtmlInputSubmit("ShowAll", "Vis alle", !$AllowSelect, $TabIndex++, "");
              echo "$InputShowAll&nbsp;";
            }
            $NewNo = 1;
            if ($NewNoResult = oswebdb_query("SELECT No FROM $AddressTableName WHERE SystemNo=$SystemNo ORDER BY No DESC"))
            {
              if ($NewNoRow = oswebdb_fetch_row($NewNoResult))
                $NewNo = $NewNoRow[0] + 1;
              oswebdb_free_result($NewNoResult);
            }
            $NoInput = MakeHtmlInputText("No", "$NewNo", oswebdb_field_len($AddressResult, 0), oswebdb_field_len($AddressResult, 0), !$AllowInsert, !$AllowInsert, $TabIndex++, "");
          }
          else
          {
            if ($AllowUpdate)
            {
              $InputSubmit = MakeHtmlInputSubmit("Update", "Opdatér", !$AllowUpdate, $TabIndex++, "javascript:return validateAddress()");
              $InputReset = MakeHtmlInputReset("Reset", "Fortryd", !$AllowUpdate, $TabIndex++, "javescript:return resetAddress()");
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
            $NoInput = MakeHtmlInputText("", $AddressRow[0], oswebdb_field_len($AddressResult, 0), oswebdb_field_len($AddressResult, 0), 1, 1, $TabIndex++, "");
          }
          echo "</td></tr>\r\n";
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Nummer :</td><td width=\"99%\" colspan=\"3\">$NoInput</td></tr>\r\n";
          $FirstInput = MakeHtmlInputText("Name", ($NewAddress ? "" : $AddressRow[1]), oswebdb_field_len($AddressResult, 1), oswebdb_field_len($AddressResult, 1), ($NewAddress ? !$AllowInsert : !$AllowUpdate), ($NewAddress ? !$AllowInsert : !$AllowUpdate), $TabIndex++, "");
          $SecondInput = MakeHtmlInputText("Phone1", ($NewAddress ? "" : $AddressRow[6]), oswebdb_field_len($AddressResult, 6), oswebdb_field_len($AddressResult, 6), ($NewAddress ? !$AllowInsert : !$AllowUpdate), ($NewAddress ? !$AllowInsert : !$AllowUpdate), $TabIndex + $TabAdjust, "");
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Navn :</td><td width=\"59%\" nowrap>$FirstInput</td><td width=\"1%\" align=\"right\" nowrap>Telefon :</td><td width=\"39%\">$SecondInput</td></tr>\r\n";
          $FirstInput = MakeHtmlInputText("Address1", ($NewAddress ? "" : $AddressRow[2]), oswebdb_field_len($AddressResult, 2), oswebdb_field_len($AddressResult, 2), ($NewAddress ? !$AllowInsert : !$AllowUpdate), ($NewAddress ? !$AllowInsert : !$AllowUpdate), $TabIndex++, "");
          $SecondInput = MakeHtmlInputText("Phone2", ($NewAddress ? "" : $AddressRow[7]), oswebdb_field_len($AddressResult, 7), oswebdb_field_len($AddressResult, 7), ($NewAddress ? !$AllowInsert : !$AllowUpdate), ($NewAddress ? !$AllowInsert : !$AllowUpdate), $TabIndex + $TabAdjust, "");
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Adresse :</td><td width=\"59%\" nowrap>$FirstInput</td><td width=\"1%\" align=\"right\" nowrap></td><td width=\"39%\">$SecondInput</td></tr>\r\n";
          $FirstInput = MakeHtmlInputText("Address2", ($NewAddress ? "" : $AddressRow[3]), oswebdb_field_len($AddressResult, 3), oswebdb_field_len($AddressResult, 3), ($NewAddress ? !$AllowInsert : !$AllowUpdate), ($NewAddress ? !$AllowInsert : !$AllowUpdate), $TabIndex++, "");
          $SecondInput = MakeHtmlInputText("Fax", ($NewAddress ? "" : $AddressRow[8]), oswebdb_field_len($AddressResult, 8), oswebdb_field_len($AddressResult, 8), ($NewAddress ? !$AllowInsert : !$AllowUpdate), ($NewAddress ? !$AllowInsert : !$AllowUpdate), $TabIndex + $TabAdjust, "");
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"59%\" nowrap>$FirstInput</td><td width=\"1%\" align=\"right\" nowrap>Telefax :</td><td width=\"39%\">$SecondInput</td></tr>\r\n";
          $FirstInput = MakeHtmlInputText("ZipCode", ($NewAddress ? "" : $AddressRow[5]), get_zipcode_length($SystemNo, ($NewAddress ? get_system_country_code($SystemNo) : $AddressRow[4])), get_zipcode_length($SystemNo, ($NewAddress ? get_system_country_code($SystemNo) : $AddressRow[4])), ($NewAddress ? !$AllowInsert : !$AllowUpdate), ($NewAddress ? !$AllowInsert : !$AllowUpdate), $TabIndex++, "javascript:parent.getCityName(this.form.CountryCode.value, this.value, this.form.CityName)");
          $SecondInput = MakeHtmlInputText("CityName", ($NewAddress ? "" : get_city_name($SystemNo, $AddressRow[4], $AddressRow[5])), get_city_name_length($SystemNo), get_city_name_length($SystemNo), 1, ($NewAddress ? !$AllowInsert : !$AllowUpdate), $TabIndex++, "");
          $ThirdInput = MakeHtmlInputText("BirthDate", ($NewAddress ? "0000-00-00" : $AddressRow[9]), oswebdb_field_len($AddressResult, 9), oswebdb_field_len($AddressResult, 9), ($NewAddress ? !$AllowInsert : !$AllowUpdate), ($NewAddress ? !$AllowInsert : !$AllowUpdate), ($TabIndex - 1) + $TabAdjust, "javascript:this.value = validateBirthDate(this.value)");
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Postnummer og by :</td><td width=\"59%\" nowrap>$FirstInput&nbsp;&nbsp;$SecondInput</td><td width=\"1%\" align=\"right\" nowrap>Fødselsdato :</td><td width=\"39%\">$ThirdInput</td></tr>\r\n";
          $TableSystemNo = get_system_for_table($SystemNo, 2);
          $Options = "<option value=\"0\"";
          if ($NewAddress)
          {
            if (get_system_country_code($SystemNo) == 0)
              $Options = "$Options selected";
          }
          $Options = "$Options>(Intet)";
          if ($CountryCodeResult = oswebdb_query("SELECT No,Description FROM Systemtables WHERE SystemNo=$TableSystemNo AND TableNo=2 ORDER BY Description"))
          {
            while ($CountryCodeRow = oswebdb_fetch_row($CountryCodeResult))
            {
              $Options = "$Options<option value=\"$CountryCodeRow[0]\"";
              if ($NewAddress)
              {
                if (get_system_country_code($SystemNo) == $CountryCodeRow[0])
                  $Options = "$Options selected";
              }
              else if ($AddressRow[4] == $CountryCodeRow[0])
                $Options = "$Options selected";
              $Options = "$Options>$CountryCodeRow[1]";
            }
            oswebdb_free_result($CountryCodeResult);
          }
          $FirstInput = MakeHtmlSelect("CountryCode", 0, 0, ($NewAddress ? !$AllowInsert : !$AllowUpdate), ($NewAddress ? !$AllowInsert : !$AllowUpdate), $TabIndex++, "javascript:parent.getZipCodeLength(this.value, this.form.ZipCode, this.form.CityName)", $Options);
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Land :</td><td width=\"99%\" colspan=\"3\">$FirstInput</td></tr>\r\n";
          echo "        <tr><td width=\"100%\" colspan=\"4\"><br></td></tr>\r\n";
          $TabIndex += ($TabAdjust - 1);
          $TabAdjust = 4;
          $Options = "<option value=\"0\"";
          if ($NewAddress && !isset($GroupNo))
            $Options = "$Options selected";
          $Options = "$Options>(Ingen)";
          if ($GroupResult = oswebdb_query($GroupStatement))
          {
            while ($GroupRow = oswebdb_fetch_row($GroupResult))
            {
              $Options = "$Options<option value=\"$GroupRow[0]\"";
              if ($NewAddress)
              {
                if ($GroupNo == $GroupRow[0])
                  $Options = "$Options selected";
              }
              else if ($AddressRow[10] == $GroupRow[0])
                $Options = "$Options selected";
              $Options = "$Options>$GroupRow[1]";
            }
            oswebdb_free_result($GroupResult);
          }
          $FirstInput = MakeHtmlSelect("GroupNo", 0, 0, ($NewAddress ? !$AllowInsert : !$AllowUpdate), ($NewAddress ? !$AllowInsert : !$AllowUpdate), $TabIndex++, "javascript:parent.getAddressGroupPublic(this.value, this.form.Public)", $Options);
          $SecondInput = MakeHtmlInputCheckBox("Public", "1", ($NewAddress ? (isset($GroupNo) ? get_table_field_value($SystemNo, 3, $GroupNo, "Public") : 0) : $AddressRow[13]), ($NewAddress ? !$AllowInsert : !$AllowUpdate), $TabIndex + $TabAdjust, "", "Offentlig");
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Gruppe :</td><td width=\"59%\">$FirstInput</td><td width=\"1%\" align=\"right\" nowrap></td><td width=\"39%\">$SecondInput</td></tr>\r\n";
          $FirstInput = MakeHtmlInputText("Email", ($NewAddress ? "" : $AddressRow[11]), oswebdb_field_len($AddressResult, 11), oswebdb_field_len($AddressResult, 11), ($NewAddress ? !$AllowInsert : !$AllowUpdate), ($NewAddress ? !$AllowInsert : !$AllowUpdate), $TabIndex++, "javascript:changeEmail()");
          $SecondInput = MakeHtmlInputButton("MailTo", "Email", ($NewAddress ? 1 : strlen($AddressRow[11]) == 0), $TabIndex++, "javascript:parent.openDoc(0, 'mailto:' + this.form.Email.value)");
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Email :</td><td width=\"99%\" colspan=\"3\">$FirstInput&nbsp;$SecondInput</td></tr>\r\n";
          $FirstInput = MakeHtmlInputText("Web", ($NewAddress ? "" : $AddressRow[12]), oswebdb_field_len($AddressResult, 12), oswebdb_field_len($AddressResult, 12), ($NewAddress ? !$AllowInsert : !$AllowUpdate), ($NewAddress ? !$AllowInsert : !$AllowUpdate), $TabIndex++, "javascript:changeWeb()");
          $SecondInput = MakeHtmlInputButton("Visit", "Besøg", ($NewAddress ? 1 : strlen($AddressRow[12]) == 0), $TabIndex++, "javascript:parent.openDoc(1, 'http://' + this.form.Web.value)");
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Web :</td><td width=\"99%\" colspan=\"3\">$FirstInput&nbsp;$SecondInput</td></tr>\r\n";
          echo "        <tr><td width=\"100%\" colspan=\"4\"><br></td></tr>\r\n";
          $TabIndex += ($TabAdjust - 3);
          $Options =  "<option value=\"0\"";
          if ($NewAddress)
            $Options = "$Options selected";
          $Options = "$Options>(Ingen)";
          $ParentStatement = "SELECT No,Name FROM $AddressTableName WHERE SystemNo=$SystemNo AND Public IN (1) ORDER BY Name,Phone1,No";
          if ($Authenticated)
            $ParentStatement = "SELECT No,Name FROM $AddressTableName WHERE SystemNo=$SystemNo AND Public IN (0,1) ORDER BY Name,Phone1,No";
          if ($ParentResult = oswebdb_query($ParentStatement))
          {
            while ($ParentRow = oswebdb_fetch_row($ParentResult))
            {
              $Options = "$Options<option value=\"$ParentRow[0]\"";
              if (!$NewAddress)
              {
                if ($AddressRow[14] == $ParentRow[0])
                  $Options = "$Options selected";
              }
              else if (isset($_GET["ParentNo"]))
              {
                if ($_GET["ParentNo"] == $ParentRow[0])
                  $Options = "$Options selected";
              }
              $Options = "$Options>$ParentRow[1]";
            }
            oswebdb_free_result($ParentResult);
          }
          $Input = MakeHtmlSelect("ParentNo", 0, 0, ($NewAddress ? !$AllowInsert : !$AllowUpdate), ($NewAddress ? !$AllowInsert : !$AllowUpdate), $TabIndex++, "", $Options);
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Tilknyttet :</td><td width=\"99%\" colspan=\"3\">$Input</td></tr>\r\n";
          echo "      </form>\r\n";
          if (!$NewAddress)
          {
            $GroupProperties = (integer) get_table_field_value($GroupSystemNo, 3, $AddressRow[10], "Properties");
            if ($GroupProperties & 1)
            {
              if ($ChildResult = oswebdb_query("SELECT No,Name,Phone1 FROM $AddressTableName WHERE SystemNo=$SystemNo AND ParentNo=$AddressRow[0]"))
              {
                if ($AllowInsert || oswebdb_num_rows($ChildResult) > 0)
                {
                  echo "      <tr><td width=\"100%\" colspan=\"4\"><br></td></tr>\r\n";
                  echo "      <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\" colspan=\"3\"><table border=\"1\" cellpadding=\"2\" cellspacing=\"0\">\r\n";
                  echo "        <tr><td colspan=\"3\"><strong>Tilknytninger</strong></td></tr>\r\n";
                  $Input = "&nbsp;";
                  if ($AllowInsert)
                    $Input = MakeHtmlInputButton("", "Opret", 0, $TabIndex++, "javascript:parent.openDoc(0, 'address.php?NewAddress=1&ParentNo=$AddressRow[0]')");
                  echo "        <tr><td align=\"right\" nowrap>$Input</td><td nowrap><strong>Navn</strong></td><td nowrap><strong>Telefon</strong></td></tr>\r\n";
                  while ($ChildRow = oswebdb_fetch_row($ChildResult))
                  {
                    $Input = MakeHtmlInputButton("", ($AllowUpdate ? "Redigér" : "Vis"), 0, $TabIndex++, "javascript:parent.openDoc(0, 'address.php?No=$ChildRow[0]')");
                    $Name = $ChildRow[1];
                    if (strlen($Name) == 0)
                      $Name = "&nbsp;";
                    $Phone = $ChildRow[2];
                    if (strlen($Phone) == 0)
                      $Phone = "&nbsp;";
                    echo "        <tr><td align=\"right\" nowrap>$Input</td><td nowrap>$Name</td><td nowrap>$Phone</td></tr>\r\n";
                  }
                  echo "      </table></td></tr>\r\n";
                }
                oswebdb_free_result($ChildResult);
              }
            }
            if ($GroupProperties & 2)
            {
              $SeasonSystemNo = get_system_for_table($SystemNo, 7);
              $CurrentSeasonNo = (integer) get_system_season_no($SystemNo);
              $PrevSeasonNo = (integer) get_table_field_value($SeasonSystemNo, 7, $CurrentSeasonNo, "GroupNo");
              $TypeSystemNo = get_system_for_table($SystemNo, $AverageTableNo);
              $Statement = "SELECT al.AddressNo,al.TableNo,al.SeasonNo,als.Description,al.TypeNo,alt.Description,alt.Length,al.Average,al.Properties,al.Distance FROM $AddressLinkTableName AS al, Systemtables AS als, Systemtables AS alt WHERE al.SystemNo=$SystemNo AND al.AddressNo=$AddressRow[0] AND al.TableNo=$AverageTableNo AND al.SeasonNo IN ($CurrentSeasonNo,$PrevSeasonNo) AND als.SystemNo=$SeasonSystemNo AND als.TableNo=7 AND als.No=al.SeasonNo AND alt.SystemNo=$TypeSystemNo AND alt.TableNo=$AverageTableNo AND alt.No=al.TypeNo ORDER BY al.SeasonNo DESC, al.TypeNo";
              if ($AllowUpdate || $AllowDelete)
                $Statement = "SELECT al.AddressNo,al.TableNo,al.SeasonNo,als.Description,al.TypeNo,alt.Description,alt.Length,al.Average,al.Properties,al.Distance FROM $AddressLinkTableName AS al, Systemtables AS als, Systemtables AS alt WHERE al.SystemNo=$SystemNo AND al.AddressNo=$AddressRow[0] AND al.TableNo=$AverageTableNo AND als.SystemNo=$SeasonSystemNo AND als.TableNo=7 AND als.No=al.SeasonNo AND alt.SystemNo=$TypeSystemNo AND alt.TableNo=$AverageTableNo AND alt.No=al.TypeNo ORDER BY al.SeasonNo DESC, al.TypeNo";
              if ($ChildResult = oswebdb_query($Statement))
              {
                if ($AllowInsert || oswebdb_num_rows($ChildResult) > 0)
                {
                  echo "      <tr><td width=\"100%\" colspan=\"4\"><br></td></tr>\r\n";
                  echo "      <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\" colspan=\"3\"><table border=\"1\" cellpadding=\"2\" cellspacing=\"0\">\r\n";
                  $Columns = 5;
                  if ($AllowInsert || $AllowUpdate || $AllowDelete)
                    $Columns++;
                  echo "        <tr><td colspan=\"$Columns\"><strong>Gennemsnit</strong></td></tr>\r\n";
                  echo "        <tr>";
                  if ($AllowInsert || $AllowUpdate || $AllowDelete)
                    echo "<td align=\"right\" nowrap>&nbsp;</td>";
                  echo "<td nowrap><strong>Sæson</strong></td><td nowrap><strong>Type</strong></td><td align=\"right\" nowrap><strong>Gennemsnit</strong></td><td nowrap><strong>&nbsp;</strong></td><td align=\"right\" nowrap><strong>Intern distance</strong></td></tr>\r\n";
                  if ($AllowInsert)
                  {
                    echo "        <form name=\"AverageForm\" action=\"address.php\" method=\"post\">\r\n";
                    echo "          <input type=\"hidden\" name=\"No\" value=\"$AddressRow[0]\">\r\n";
                    echo "          <input type=\"hidden\" name=\"TableNo\" value=\"$AverageTableNo\">\r\n";
                    echo "          <tr><td align=\"right\" nowrap>";
                    $Input = MakeHtmlInputSubmit("InsertLink", "Opret", !$AllowInsert, $TabIndex++, "javascript:return validateAverageForm(1, this.form)");
                    echo "$Input</td><td nowrap>";
                    $Options = "<option value=\"0\"";
                    if ($CurrentSeasonNo == 0)
                      $Options = "$Options selected";
                    $Options = "$Options>(Ingen)";
                    if ($SeasonResult = oswebdb_query("SELECT No,Description FROM Systemtables WHERE SystemNo=$SeasonSystemNo AND TableNo=7 AND No IN ($CurrentSeasonNo,$PrevSeasonNo) ORDER BY Description"))
                    {
                      while ($SeasonRow = oswebdb_fetch_row($SeasonResult))
                      {
                        $Options = "$Options<option value=\"$SeasonRow[0]\"";
                        if ($SeasonRow[0] == $CurrentSeasonNo)
                          $Options = "$Options selected";
                        $Options = "$Options>$SeasonRow[1]";
                      }
                      oswebdb_free_result($SeasonResult);
                    }
                    $Input = MakeHtmlSelect("SeasonNo", 0, 0, !$AllowInsert, !$AllowInsert, $TabIndex++, "", $Options);
                    echo "$Input</td><td nowrap>";
                    $Options = "<option value=\"0\" selected>(Ingen)";
                    if ($TypeResult = oswebdb_query("SELECT No,Description FROM Systemtables WHERE SystemNo=$TypeSystemNo AND TableNo=$AverageTableNo ORDER BY Description"))
                    {
                      while ($TypeRow = oswebdb_fetch_row($TypeResult))
                        $Options = "$Options<option value=\"$TypeRow[0]\">$TypeRow[1]";
                      oswebdb_free_result($TypeResult);
                    }
                    $Input = MakeHtmlSelect("TypeNo", 0, 0, !$AllowInsert, !$AllowInsert, $TabIndex++, "", $Options);
                    echo "$Input</td><td align=\"right\" nowrap>";
                    $Input = MakeHtmlInputText("Average", "", oswebdb_field_len($ChildResult, 7), oswebdb_field_len($ChildResult, 7), !$AllowInsert, !$AllowInsert, $TabIndex++, "");
                    echo "$Input</td><td nowrap>";
                    $Input = MakeHtmlInputCheckBox("Property0", "1", 0, !$AllowInsert, $TabIndex, "", "Fra startbog");
                    echo "$Input</td><td align=\"right\" nowrap>";
                    $Input = MakeHtmlInputText("Distance", "", oswebdb_field_len($ChildResult, 9), oswebdb_field_len($ChildResult, 9), !$AllowInsert, !$AllowInsert, $TabIndex++, "");
                    echo "$Input</td></tr>\r\n";
                    echo "        </form>\r\n";
                  }
                  while ($ChildRow = oswebdb_fetch_row($ChildResult))
                  {
                    echo "        <form name=\"AverageForm\" action=\"address.php\" method=\"post\">\r\n";
                    echo "          <input type=\"hidden\" name=\"No\" value=\"$ChildRow[0]\">\r\n";
                    echo "          <input type=\"hidden\" name=\"TableNo\" value=\"$ChildRow[1]\">\r\n";
                    echo "          <input type=\"hidden\" name=\"SeasonNo\" value=\"$ChildRow[2]\">\r\n";
                    echo "          <input type=\"hidden\" name=\"TypeNo\" value=\"$ChildRow[4]\">\r\n";
                    echo "          <tr>";
                    if ($AllowUpdate || $AllowDelete)
                    {
                      $InputUpdate = MakeHtmlInputSubmit("UpdateLink", "Opdatér", !$AllowUpdate, $TabIndex++, "javascript:return validateAverageForm(2, this.form)");
                      $InputDelete = MakeHtmlInputSubmit("DeleteLink", "Slet", !$AllowDelete, $TabIndex++, "javascript:return confirm('Bekræft sletning!')");
                      $InputSpace = "";
                      if ($AllowUpdate && $AllowDelete)
                        $InputSpace = "&nbsp;";
                      echo "<td align=\"right\" nowrap>$InputUpdate$InputSpace$InputDelete</td>";
                    }
                    else if ($AllowInsert)
                      echo "<td align=\"right\" nowrap>&nbsp;</td>";
                    echo "<td nowrap>$ChildRow[3]</td><td nowrap>$ChildRow[5]</td><td align=\"right\" nowrap>";
                    $Input = "&nbsp;";
                    if ($AllowUpdate)
                      $Input = MakeHtmlInputText("Average", (((float) $ChildRow[7]) > 0 ? FormatNumber($ChildRow[7], $ChildRow[6], 1) : ""), oswebdb_field_len($ChildResult, 7), oswebdb_field_len($ChildResult, 7), !$AllowUpdate, !$AllowUpdate, $TabIndex++, "");
                    else if (((float) $ChildRow[7]) > 0)
                      $Input = FormatNumber($ChildRow[7], $ChildRow[6], 1);
                    echo "$Input</td><td nowrap>";
                    $Input = MakeHtmlInputCheckBox("Property0", "1", $ChildRow[8] & 1, !$AllowUpdate, $TabIndex, "", "Fra startbog");
                    echo "$Input</td><td align=\"right\" nowrap>";
                    $Input = "&nbsp;";
                    if ($AllowUpdate)
                      $Input = MakeHtmlInputText("Distance", (((integer) $ChildRow[9]) > 0 ? FormatNumber($ChildRow[9], 0, 1) : ""), oswebdb_field_len($ChildResult, 9), oswebdb_field_len($ChildResult, 9), !$AllowUpdate, !$AllowUpdate, $TabIndex++, "");
                    else if (((integer) $ChildRow[9]) > 0)
                      $Input = FormatNumber($ChildRow[9], 0, 1);
                    echo "$Input</td></tr>\r\n";
                    echo "        </form>\r\n";
                  }
                  echo "      </table></td></tr>\r\n";
                }
                oswebdb_free_result($ChildResult);
              }
            }
            if ($GroupProperties & 8)
            {
              $SeasonSystemNo = get_system_for_table($SystemNo, 7);
              $AverageSystemNo = get_system_for_table($SystemNo, $AverageTableNo);
              $MatchSystemNo = get_system_for_table($SystemNo, $MatchTableNo);
              if ($ChildResult = oswebdb_query("SELECT s.No,s.Description,t.No,t.Description,t.Properties,SUM(am.Score),SUM(am.Entries),MAX(am.Series),SUM(am.Point),SUM(am.Point>1),SUM(am.Point=1),SUM(am.Point<1),SUM(am.Matches),al.Average,at.Length FROM Systemtables AS s, Systemtables AS t, $AddressMatchTableName as am, $AddressLinkTableName as al, Systemtables AS at WHERE s.SystemNo=$SeasonSystemNo AND s.TableNo=7 AND t.SystemNo=$MatchSystemNo AND t.TableNo=$MatchTableNo AND am.SystemNo=$SystemNo AND am.AddressNo=$AddressRow[0] AND am.TableNo=$MatchTableNo AND am.SeasonNo=s.No AND am.TypeNo=t.No AND al.SystemNo=am.SystemNo AND al.AddressNo=am.AddressNo AND al.TableNo=$AverageTableNo AND al.SeasonNo=am.SeasonNo AND al.TypeNo=t.GroupNo AND at.SystemNo=$AverageSystemNo AND at.TableNo=$AverageTableNo AND at.No=t.GroupNo GROUP BY s.No,t.No ORDER BY s.No DESC, t.No"))
              {
                if (oswebdb_num_rows($ChildResult) > 0)
                {
                  $LastSeasonNo = 0;
                  echo "      <tr><td width=\"100%\" colspan=\"4\"><br></td></tr>\r\n";
                  echo "      <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\" colspan=\"3\"><table border=\"1\" cellpadding=\"2\" cellspacing=\"0\">\r\n";
                  echo "        <tr><td colspan=\"11\"><strong>Kampresultater</strong></td></tr>\r\n";
                  echo "        <tr><td nowrap><strong>Kamptype</strong></td><td align=\"right\" nowrap><strong>Score</strong></td><td align=\"right\" nowrap><strong>Indgange</strong></td><td align=\"right\" nowrap><strong>Serie</strong></td><td align=\"right\" nowrap><strong>Gennemsnit</strong></td><td align=\"right\" nowrap><strong>%-gennemsnit</strong></td><td align=\"right\" nowrap><strong>Point</strong></td><td align=\"right\" nowrap><strong>V</strong></td><td align=\"right\" nowrap><strong>U</strong></td><td align=\"right\" nowrap><strong>T</strong></td><td align=\"right\" nowrap><strong>Kampe</strong></td></tr>\r\n";
                  while ($ChildRow = oswebdb_fetch_row($ChildResult))
                  {
                    if ($ChildRow[0] != $LastSeasonNo)
                    {
                      echo "        <tr><td colspan=\"11\"><strong>$ChildRow[1]</strong></td></tr>\r\n";
                      $LastSeasonNo = $ChildRow[0];
                    }
                    $Score = FormatNumber($ChildRow[5], 0, 1);
                    $Entries = FormatNumber($ChildRow[6], 0, 1);
                    $Series = FormatNumber($ChildRow[7], 0, 1);
                    $MatchAverage = FormatNumber($ChildRow[5] / $ChildRow[6], $ChildRow[14], 1);
                    $ProcentAverage = FormatNumber((($ChildRow[5] / $ChildRow[6]) * 100) / $ChildRow[13], $ChildRow[14], 1);
                    $Point = "&nbsp";
                    $Wound = "&nbsp";
                    $Equal = "&nbsp";
                    $Lost = "&nbsp";
                    if ($ChildRow[4] & 1)
                    {
                      $Point = $ChildRow[8];
                      $Wound = $ChildRow[9];
                      $Equal = $ChildRow[10];
                      $Lost = $ChildRow[11];
                    }
                    $Matches = $ChildRow[12];
                    echo "        <tr><td nowrap>$ChildRow[3]</td><td align=\"right\" nowrap>$Score</td><td align=\"right\" nowrap>$Entries</td><td align=\"right\" nowrap>$Series</td><td align=\"right\" nowrap>$MatchAverage</td><td align=\"right\" nowrap>$ProcentAverage</td><td align=\"right\" nowrap>$Point</td><td align=\"right\" nowrap>$Wound</td><td align=\"right\" nowrap>$Equal</td><td align=\"right\" nowrap>$Lost</td><td align=\"right\" nowrap>$Matches</td></tr>\r\n";
                  }
                  echo "      </table></td></tr>\r\n";
                }
                oswebdb_free_result($ChildResult);
              }
            }
          }
          echo "    </table>\r\n";
          MakeHtmlPageBottom();
        }
        else if (isset($GroupNo))
          MakeHtmlPageReload("javascript:this.location = 'address.php?GroupNo=$GroupNo'; return true;");
        else if (isset($Search) && isset($SearchValue))
          MakeHtmlPageReload("javascript:this.location = 'address.php?Search=$Search&SearchValue=$SearchValue'; return true;");
        else
          MakeHtmlPageReload("javascript:this.location = 'address.php'; return true;");
        oswebdb_free_result($AddressResult);
      }
      else if ((isset($GroupNo) || (isset($Search) && isset($SearchValue))) && $AddressResult = oswebdb_query($AddressStatement))
      {
        $TabIndex = 1; $Header = !isset($GroupNo);
        MakeHtmlPageTop("Adresser");
        echo "    <table border=\"0\" cellpadding=\"1\" cellspacing=\"1\" width=\"100%\">\r\n";
        echo "      <tr><td width=\"100%\" colspan=\"3\" valign=\"middle\"><h2><i>Adresser</i></h2></td></tr>\r\n";
        echo "      <form action=\"address.php\" method=\"post\">\r\n";
        if (isset($GroupNo))
          echo "        <input type=\"hidden\" name=\"GroupNo\" value=\"$GroupNo\">\r\n";
        echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\" colspan=\"2\">";
        if ($AllowInsert)
        {
          $Input = MakeHtmlInputSubmit("NewAddress", "Opret", !$AllowInsert, $TabIndex++, "");
          echo "$Input&nbsp;";
        }
        if ($AllowSelect)
        {
          $FirstInput = MakeHtmlInputSubmit("ShowAll", "Vis alle", !$AllowSelect, $TabIndex++, "");
          $SecondInput = MakeHtmlInputSubmit("Search", "Søg", !$AllowSelect, $TabIndex++, "javascript:return this.form.SearchValue.value.length > 0");
          $ThirdInput = MakeHtmlInputText("SearchValue", "", 30, 255, !$AllowSelect, !$AllowSelect, $TabIndex++, "");
          echo "$FirstInput&nbsp;$SecondInput&nbsp;$ThirdInput";
        }
        echo "</td></tr>\r\n";
        echo "      </form>\r\n";
        while ($AddressRow = oswebdb_fetch_row($AddressResult))
        {
          if (!$Header)
          {
            $Group = get_table_field_value($SystemNo, 3, $GroupNo, "Description");
            echo "      <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\" colspan=\"2\"><strong>$Group:</strong></td><tr>\r\n";
            $Header = 1;
          }
          if ($AllowUpdate)
            $Input = MakeHtmlInputSubmit("", "Redigér", !$AllowUpdate, $TabIndex++, "");
          else
            $Input = MakeHtmlInputSubmit("", "Vis", 0, $TabIndex++, "");
          echo "      <form action=\"address.php\" method=\"post\"><input type=\"hidden\" name=\"No\" value=\"$AddressRow[0]\"><tr><td width=\"1%\" align=\"right\" nowrap>$Input</td><td nowrap>$AddressRow[1]</td><td nowrap>$AddressRow[2]</td></tr></form>\r\n";
        }
        echo "    </table>\r\n";
        MakeHtmlPageBottom();
        oswebdb_free_result($AddressResult);
      }
      else if ($GroupResult = oswebdb_query($GroupStatement))
      {
        $TabIndex = 1;
        MakeHtmlPageTop("Adresser");
        echo "    <table border=\"0\" cellpadding=\"1\" cellspacing=\"1\" width=\"100%\">\r\n";
        echo "      <tr><td width=\"100%\" colspan=\"3\" valign=\"middle\"><h2><i>Adresser</i></h2></td></tr>\r\n";
        echo "      <form action=\"address.php\" method=\"post\">\r\n";
        echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\" colspan=\"2\">";
        if ($AllowInsert)
        {
          $Input = MakeHtmlInputSubmit("NewAddress", "Opret", !$AllowInsert, $TabIndex++, "");
          echo "$Input&nbsp;";
        }
        if ($AllowSelect)
        {
          $FirstInput = MakeHtmlInputSubmit("Search", "Søg", !$AllowSelect, $TabIndex++, "javascript:return this.form.SearchValue.value.length > 0");
          $SecondInput = MakeHtmlInputText("SearchValue", "", 30, 255, !$AllowSelect, !$AllowSelect, $TabIndex++, "");
          echo "$FirstInput&nbsp;$SecondInput";
        }
        echo "</td></tr>\r\n";
        echo "      </form>\r\n";
        $First = 1;
        while ($GroupRow = oswebdb_fetch_row($GroupResult))
        {
          $Statement = "SELECT No,Name,Phone1 FROM $AddressTableName WHERE SystemNo=$SystemNo AND GroupNo=$GroupRow[0] AND Public IN (1) ORDER BY Name,Phone1";
          if ($Authenticated)
            $Statement = "SELECT No,Name,Phone1 FROM $AddressTableName WHERE SystemNo=$SystemNo AND GroupNo=$GroupRow[0] AND Public IN (0,1) ORDER BY Name,Phone1";
          if ($AddressResult = oswebdb_query($Statement))
          {
            $Header = 0;
            while ($AddressRow = oswebdb_fetch_row($AddressResult))
            {
              if (!$Header)
              {
                if (!$First)
                  echo "      <tr><td width=\"100%\" colspan=\"3\"><br></td></tr>\r\n";
                else
                  $First = 0;
                echo "      <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\" colspan=\"2\"><strong>$GroupRow[1]:</strong></td><tr>\r\n";
                $Header = 1;
              }
              if ($AllowUpdate)
                $Input = MakeHtmlInputSubmit("", "Redigér", !$AllowUpdate, $TabIndex++, "");
              else
                $Input = MakeHtmlInputSubmit("", "Vis", 0, $TabIndex++, "");
              echo "      <form action=\"address.php\" method=\"post\"><input type=\"hidden\" name=\"No\" value=\"$AddressRow[0]\"><tr><td width=\"1%\" align=\"right\" nowrap>$Input</td><td nowrap>$AddressRow[1]</td><td nowrap>$AddressRow[2]</td></tr></form>\r\n";
            }
            oswebdb_free_result($AddressResult);
          }
        }
        $Statement = "SELECT No,Name,Phone1 FROM $AddressTableName WHERE SystemNo=$SystemNo AND GroupNo=0 AND Public IN (1) ORDER BY Name,Phone1";
        if ($Authenticated)
          $Statement = "SELECT No,Name,Phone1 FROM $AddressTableName WHERE SystemNo=$SystemNo AND GroupNo=0 AND Public IN (0,1) ORDER BY Name,Phone1";
        if ($AddressResult = oswebdb_query($Statement))
        {
          $Header = 0;
          while ($AddressRow = oswebdb_fetch_row($AddressResult))
          {
            if (!$Header)
            {
              if (!$First)
                echo "      <tr><td width=\"100%\" colspan=\"3\"><br></td></tr>\r\n";
              else
                $First = 0;
              echo "      <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\" colspan=\"2\"><strong>(Ingen adressegruppe):</strong></td><tr>\r\n";
              $Header = 1;
            }
            if ($AllowUpdate)
              $Input = MakeHtmlInputSubmit("", "Redigér", !$AllowUpdate, $TabIndex++, "");
            else
              $Input = MakeHtmlInputSubmit("", "Vis", 0, $TabIndex++, "");
            echo "      <form action=\"address.php\" method=\"post\"><input type=\"hidden\" name=\"No\" value=\"$AddressRow[0]\"><tr><td width=\"1%\" align=\"right\" nowrap>$Input</td><td nowrap>$AddressRow[1]</td><td nowrap>$AddressRow[2]</td></tr></form>\r\n";
          }
          oswebdb_free_result($AddressResult);
        }
        echo "    </table>\r\n";
        MakeHtmlPageBottom();
        oswebdb_free_result($GroupResult);
      }
    }
    oswebdb_close();
  }
?>
