<?php
  // Include required files.
  require_once("misc.php");
  require_once("oswebdb/oswebdb.php");
  require_once("oswebdb/tables.php");
  require_once("oswebdb/systems.php");
  require_once("oswebdb/zipcodes.php");

  // Set error reporting.
  error_reporting((int) GetConfigValue("ErrorReporting"));

  $TableName = "Systemtables";
  $SystemNo = GetConfigValue("SystemNo");
  if (isset($_GET["TableNo"]))
    $TableNo = $_GET["TableNo"];
  else if (isset($_POST["TableNo"]))
    $TableNo = $_POST["TableNo"];
  if (isset($_GET["No"]))
    $No = $_GET["No"];
  else if (isset($_POST["No"]))
    $No = $_POST["No"];
  if (oswebdb_authorize(oswebdb_getusername(), oswebdb_getpassword()))
  {
    if (oswebdb_selectdb())
    {
      $AllowSelect = oswebdb_allow_select($TableName);
      $AllowInsert = oswebdb_allow_insert($TableName);
      $AllowUpdate = oswebdb_allow_update($TableName);
      $AllowDelete = oswebdb_allow_delete($TableName);
      if (isset($TableNo))
        $TableSystemNo = get_system_for_table($SystemNo, $TableNo);
      else
        $TableSystemNo = get_system_for_table($SystemNo, 0);
      if (isset($_POST["Insert"]) && isset($TableNo) && isset($No) && isset($_POST["Description"]))
      {
        if (!insert_systemtable($SystemNo, $TableNo, $No, $_POST["Description"]))
        {
          $Error = oswebdb_error();
          MakeHtmlPageTop(get_table_name($SystemNo, $TableNo));
          echo "    <h2><strong>$Error</strong></h2>\r\n";
          MakeHtmlPageBottom("");
          exit;
        }
      }
      else if (isset($_POST["Update"]) && isset($TableNo) && isset($No) && isset($_POST["Description"]))
      {
        if (!update_systemtable($SystemNo, $TableNo, $No, $_POST["Description"], $_POST["Length"], $_POST["Field0"], $_POST["Field1"], $_POST["Field2"], $_POST["Field3"], $_POST["Field4"], $_POST["Field5"], $_POST["Field6"], $_POST["Field7"], $_POST["Field8"], $_POST["Field9"], $_POST["Field10"], $_POST["Field11"], $_POST["Public"], $_POST["Common"], $_POST["Property0"], $_POST["Property1"], $_POST["Property2"], $_POST["Property3"], $_POST["Property4"], $_POST["Property5"], $_POST["Property6"], $_POST["Property7"], $_POST["Property8"], $_POST["Property9"], $_POST["Property10"], $_POST["Property11"], $_POST["Text1"], $_POST["Text2"], $_POST["Text3"], $_POST["GroupNo"], $_POST["FromDate"], $_POST["ToDate"]))
        {
          $Error = oswebdb_error();
          MakeHtmlPageTop(get_table_name($SystemNo, $TableNo));
          echo "    <h2><strong>$Error</strong></h2>\r\n";
          MakeHtmlPageBottom("");
          exit;
        }
      }
      else if (isset($_POST["Delete"]) && isset($TableNo) && isset($No))
      {
        if (!delete_systemtable($SystemNo, $TableNo, $No))
        {
          $Error = oswebdb_error();
          MakeHtmlPageTop(get_table_name($SystemNo, $TableNo));
          echo "    <h2><strong>$Error</strong></h2>\r\n";
          MakeHtmlPageBottom("");
          exit;
        }
        unset($No);
      }
      else if (isset($_POST["ShowAll"]) && isset($TableNo) && isset($No))
        unset($No);
      if (isset($TableNo) && isset($No) && $Result = oswebdb_query("SELECT No,Description,Length,ShowFields,Public,Common,Properties,Text1,Text2,Text3,GroupNo,FromDate,ToDate FROM $TableName WHERE SystemNo=$TableSystemNo AND TableNo=$TableNo AND No=$No"))
      {
        if ($Row = oswebdb_fetch_row($Result))
        {
          $TableDescription = get_table_name($SystemNo, $TableNo);
          $ShowFields = get_table_show_fields($SystemNo, $TableNo);
          $TabIndex = 1;
          echo "<script language=\"JavaScript\">\r\n";
          echo "  function validateUpdate()\r\n";
          echo "  {\r\n";
          echo "    description = this.UpdateForm.Description.value\r\n";
          if ($ShowFields & 512)
          {
            echo "    fromDate = this.UpdateForm.FromDate.value\r\n";
            echo "    toDate = this.UpdateForm.ToDate.value\r\n";
          }
          echo "    if (description.length == 0)\r\n";
          echo "    {\r\n";
          echo "      alert('Teksten skal indtastes!')\r\n";
          echo "      return false\r\n";
          echo "    }\r\n";
          if (($ShowFields & 1) || $TableNo == 0)
          {
            switch ($TableNo)
            {
              case 2:
                $MaxLength = get_max_zipcode_length($SystemNo);
                $Error = "Længden på postnumre må ikke være større end $MaxLength!";
                break;

              case 8:
                $MaxLength = 3;
                $Error = "Antallet af decimaler må ikke være større end $MaxLength!";
                break;

              default:
                $MaxLength = oswebdb_field_len($Result, 1);
                $Error = "Længden må ikke være større end $MaxLength!";
                break;
            }
            echo "    else if (this.UpdateForm.Length.value > $MaxLength)\r\n";
            echo "    {\r\n";
            echo "      alert('$Error')\r\n";
            echo "      return false\r\n";
            echo "    }\r\n";
          }
          if ($ShowFields & 512)
          {
            echo "    else if (!parent.validateDate(parseInt(fromDate.substr(0, 4), 10), parseInt(fromDate.substr(5, 2), 10), parseInt(fromDate.substr(8, 2), 10)))\r\n";
            echo "      return false\r\n";
            echo "    else if (!parent.validateDate(parseInt(toDate.substr(0, 4), 10), parseInt(toDate.substr(5, 2), 10), parseInt(toDate.substr(8, 2), 10)))\r\n";
            echo "      return false\r\n";
          }
          echo "    else\r\n";
          echo "      return true\r\n";
          echo "  }\r\n";
          if ($ShowFields & 512)
          {
            echo "\r\n  function changeDateEditControl(ctrl)\r\n";
            echo "  {\r\n";
            echo "    s = ctrl.value\r\n";
            echo "    if (s.length == 8)\r\n";
            echo "    {\r\n";
            echo "      s = s.substr(0, 4) + '-' + s.substr(4, 2) + '-' + s.substr(6,2)\r\n";
            echo "      ctrl.value = s\r\n";
            echo "    }\r\n";
            echo "    year = parseInt(s.substr(0, 4), 10)\r\n";
            echo "    month = parseInt(s.substr(5, 2), 10)\r\n";
            echo "    date = parseInt(s.substr(8, 2), 10)\r\n";
            echo "    return parent.validateDate(year, month, date)\r\n";
            echo "  }\r\n";
          }
          echo "</script>\r\n";
          MakeHtmlPageTop($TableDescription);
          if (is_table_accessible($SystemNo, $TableNo))
          {
            echo "    <form name=\"UpdateForm\" action=\"tables.php\" method=\"post\">\r\n";
            echo "      <input type=\"hidden\" name=\"TableNo\" value=\"$TableNo\">\r\n";
            echo "      <input type=\"hidden\" name=\"No\" value=\"$No\">\r\n";
            echo "      <table border=\"0\" cellpadding=\"1\" cellspacing=\"1\" width=\"100%\">\r\n";
            echo "        <tr><td width=\"100%\" colspan=\"2\" valign=\"middle\"><h2><i>$TableDescription</i></h2></td></tr>\r\n";
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
            $Input = MakeHtmlInputText("", $Row[0], get_table_field_length($SystemNo, $TableNo, "No"), get_table_field_length($SystemNo, $TableNo, "No"), 1, 1, $TabIndex++, "");
            echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Nummer :</td><td width=\"99%\">$Input</td></tr>\r\n";
            $Input = MakeHtmlInputText("Description", $Row[1], get_table_field_length($SystemNo, $TableNo, "Description"), get_table_field_length($SystemNo, $TableNo, "Description"), !$AllowUpdate, !$AllowUpdate, $TabIndex++, "");
            echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Tekst :</td><td width=\"99%\">$Input</td></tr>\r\n";
            if (($ShowFields & 1) || $TableNo == 0)
            {
              switch ($TableNo)
              {
                case 2:
                  $Text = "Længde på postnumre :";
                  break;

                case 8:
                  $Text = "Decimaler :";
                  break;

                default:
                  $Text = "Længde :";
                  break;
              }
              $Input = MakeHtmlInputText("Length", $Row[2], get_table_field_length($SystemNo, $TableNo, "Length"), get_table_field_length($SystemNo, $TableNo, "Length"), !$AllowUpdate, !$AllowUpdate, $TabIndex++, "");
              echo "        <tr><td width=\"1%\" align=\"right\" nowrap>$Text</td><td width=\"99%\">$Input</td></tr>\r\n";
            }
            if (($ShowFields & 2) || $TableNo == 0)
            {
              $Fields = get_table_text1($SystemNo, $TableNo);
              if (strlen($Fields) > 0)
              {
                $Fields = explode("/", $Fields); $i = 0;
                while ($i < count($Fields) && i < 12)
                {
                  if (strlen($Fields[$i]) > 0)
                  {
                    $Field = explode(",", $Fields[$i]);
                    $Text = "";
                    if ($i == 0)
                      $Text = "Felter :";
                    $Input = MakeHtmlInputCheckBox("Field$i", "$Field[1]", ($Row[3] & (integer) $Field[1]), !$AllowUpdate, $TabIndex++, "", $Field[0]);
                    echo "        <tr><td width=\"1%\" align=\"right\" nowrap>$Text</td><td width=\"99%\">$Input</td></tr>\r\n";
                  }
                  $i++;
                }
              }
            }
            if ($ShowFields & 4)
            {
              $Input = MakeHtmlInputCheckBox("Public", "1", $Row[4], !$AllowUpdate, $TabIndex++, "", "Offentlig");
              echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\">$Input</td></tr>\r\n";
            }
            if (($ShowFields & 8) || $TableNo == 0)
            {
              $Input = MakeHtmlInputCheckBox("Common", "1", $Row[5], !$AllowUpdate, $TabIndex++, "", "Fælles");
              echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Systemdeling :</td><td width=\"99%\">$Input</td></tr>\r\n";
            }
            if ($ShowFields & 16)
            {
              $Properties = get_table_text2($SystemNo, $TableNo);
              if (strlen($Properties) > 0)
              {
                $Properties = explode("/", $Properties); $i = 0;
                while ($i < count($Properties) && i < 12)
                {
                  if (strlen($Properties[$i]) > 0)
                  {
                    $Property = explode(",", $Properties[$i]);
                    $Text = "";
                    if ($i == 0)
                      $Text = "Egenskaber :";
                    $Input = MakeHtmlInputCheckBox("Property$i", "$Property[1]", ($Row[6] & (integer) $Property[1]), !$AllowUpdate, $TabIndex++, "", $Property[0]);
                    echo "        <tr><td width=\"1%\" align=\"right\" nowrap>$Text</td><td width=\"99%\">$Input</td></tr>\r\n";
                  }
                  $i++;
                }
              }
            }
            if (($ShowFields & 32) || $TableNo == 0)
            {
              $Text = "";
              $Texts = get_table_text3($SystemNo, $TableNo);
              if (strlen($Texts) > 0)
              {
                $Texts = explode("/", $Texts);
                if (count($Texts) > 0)
                  $Text = "$Texts[0] :";
              }
              $Input = MakeHtmlInputText("Text1", $Row[7], oswebdb_field_len($Result, 7), oswebdb_field_len($Result, 7), !$AllowUpdate, !$AllowUpdate, $TabIndex++, "");
              echo "        <tr><td width=\"1%\" align=\"right\" nowrap>$Text</td><td width=\"99%\">$Input</td></tr>\r\n";
            }
            if (($ShowFields & 64) || $TableNo == 0)
            {
              $Text = "";
              $Texts = get_table_text3($SystemNo, $TableNo);
              if (strlen($Texts) > 0)
              {
                $Texts = explode("/", $Texts);
                if (count($Texts) > 1)
                  $Text = "$Texts[1] :";
              }
              $Input = MakeHtmlInputText("Text2", $Row[8], oswebdb_field_len($Result, 8), oswebdb_field_len($Result, 8), !$AllowUpdate, !$AllowUpdate, $TabIndex++, "");
              echo "        <tr><td width=\"1%\" align=\"right\" nowrap>$Text</td><td width=\"99%\">$Input</td></tr>\r\n";
            }
            if (($ShowFields & 128) || $TableNo == 0)
            {
              $Text = "";
              $Texts = get_table_text3($SystemNo, $TableNo);
              if (strlen($Texts) > 0)
              {
                $Texts = explode("/", $Texts);
                if (count($Texts) > 2)
                  $Text = "$Texts[2] :";
              }
              $Input = MakeHtmlInputText("Text3", $Row[9], oswebdb_field_len($Result, 9), oswebdb_field_len($Result, 9), !$AllowUpdate, !$AllowUpdate, $TabIndex++, "");
              echo "        <tr><td width=\"1%\" align=\"right\" nowrap>$Text</td><td width=\"99%\">$Input</td></tr>\r\n";
            }
            if (($ShowFields & 256) || $TableNo == 0)
            {
              $Text = "";
              $Texts = get_table_text3($SystemNo, $TableNo);
              if (strlen($Texts) > 0)
              {
                $Texts = explode("/", $Texts);
                if (count($Texts) > 3)
                  $Text = "$Texts[3] :";
              }
              $Group = get_table_group($SystemNo, $TableNo);
              $GroupSystem = get_system_for_table($SystemNo, $Group);
              $Options = "";
              if ($Group > 0)
              {
                $Options = "$Options<option value=\"0\"";
                if ($Row[10] == 0)
                  $Options = "$Options selected";
                $Options = "$Options>(Ingen)";
              }
              if ($GroupResult = oswebdb_query("SELECT No,Description FROM $TableName WHERE SystemNo=$GroupSystem AND TableNo=$Group ORDER BY Description"))
              {
                while ($GroupRow = oswebdb_fetch_row($GroupResult))
                {
                  $Options = "$Options<option value=\"$GroupRow[0]\"";
                  if ($Row[10] == $GroupRow[0])
                    $Options = "$Options selected";
                  $Options = "$Options>$GroupRow[1]";
                }
                oswebdb_free_result($GroupResult);
              }
              $Input = MakeHtmlSelect("GroupNo", 0, 0, !$AllowUpdate, !$AllowUpdate, $TabIndex++, "", $Options);
              echo "        <tr><td width=\"1%\" align=\"right\" nowrap>$Text</td><td width=\"99%\">$Input</td></tr>\r\n";
            }
            if ($ShowFields & 512)
            {
              $FirstInput = MakeHtmlInputText("FromDate", $Row[11], oswebdb_field_len($Result, 11), oswebdb_field_len($Result, 11), !$AllowUpdate, !$AllowUpdate, $TabIndex++, "javascript:return changeDateEditControl(this)");
              $SecondInput = MakeHtmlInputText("ToDate", $Row[12], oswebdb_field_len($Result, 12), oswebdb_field_len($Result, 12), !$AllowUpdate, !$AllowUpdate, $TabIndex++, "javascript:return changeDateEditControl(this)");
              echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Periode :</td><td width=\"99%\">$FirstInput&nbsp;til&nbsp;$SecondInput</td></tr>\r\n";
            }
            echo "      </table>\r\n";
            echo "    </form>\r\n";
          }
          MakeHtmlPageBottom();
        }
        else if (isset($TableNo))
          MakeHtmlPageReload("javascript:this.location = 'tables.php?TableNo=$TableNo'; return true;");
        else
          MakeHtmlPageReload("javascript:this.location = 'tables.php'; return true;");
        oswebdb_free_result($Result);
      }
      else if (isset($TableNo) && $Result = oswebdb_query("SELECT No,Description FROM $TableName WHERE SystemNo=$TableSystemNo AND TableNo=$TableNo ORDER BY SystemNo,TableNo,No"))
      {
        $TableDescription = get_table_name($SystemNo, $TableNo);
        $TabIndex = 1;
        echo "<script language=\"JavaScript\">\r\n";
        echo "  function validateInsert()\r\n";
        echo "  {\r\n";
        echo "    no = this.InsertForm.No.value\r\n";
        echo "    description = this.InsertForm.Description.value\r\n";
        echo "    if (no.length == 0)\r\n";
        echo "    {\r\n";
        echo "      alert('Nummeret skal indtastes!')\r\n";
        echo "      return false\r\n";
        echo "    }\r\n";
        if ($TableNo > 0)
        {
          echo "    else if (no <= 0)\r\n";
          echo "    {\r\n";
          echo "      alert('Nummeret skal være større end 0!')\r\n";
          echo "      return false\r\n";
          echo "    }\r\n";
        }
        echo "    else if (description.length == 0)\r\n";
        echo "    {\r\n";
        echo "      alert('Teksten skal indtastes!')\r\n";
        echo "      return false\r\n";
        echo "    }\r\n";
        echo "    return true\r\n";
        echo "  }\r\n";
        echo "</script>\r\n";
        MakeHtmlPageTop($TableDescription);
        if (is_table_accessible($SystemNo, $TableNo))
        {
          echo "    <table border=\"0\" cellpadding=\"1\" cellspacing=\"1\" width=\"100%\">\r\n";
          echo "      <tr><td width=\"100%\" colspan=\"2\" valign=\"middle\"><h2><i>$TableDescription</i></h2></td></tr>\r\n";
          if ($AllowInsert)
          {
            $NoInput = MakeHtmlInputText("No", "", get_table_field_length($SystemNo, $TableNo, "No"), get_table_field_length($SystemNo, $TableNo, "No"), !$AllowInsert, !$AllowInsert, $TabIndex++, "");
            $DescriptionInput = MakeHtmlInputText("Description", "", get_table_field_length($SystemNo, $TableNo, "Description"), get_table_field_length($SystemNo, $TableNo, "Description"), !$AllowInsert, !$AllowInsert, $TabIndex++, "");
            $SubmitInput = MakeHtmlInputSubmit("Insert", "Opret", !$AllowInsert, $TabIndex++, "javascript:return validateInsert();");
            echo "      <form name=\"InsertForm\" action=\"tables.php\" method=\"post\">\r\n";
            echo "        <input type=\"hidden\" name=\"TableNo\" value=\"$TableNo\">\r\n";
            echo "        <tr><td width=\"1%\" align=\"right\" nowrap><strong>Nummer / Tekst :</strong></td><td width=\"99%\">$NoInput&nbsp;/&nbsp;$DescriptionInput&nbsp;$SubmitInput</td></tr>\r\n";
            echo "      </form>\r\n";
          }
          while ($Row = oswebdb_fetch_row($Result))
          {
            if ($AllowUpdate)
              $Input = MakeHtmlInputSubmit("", "Redigér", !$AllowUpdate, $TabIndex++, "");
            else
              $Input = MakeHtmlInputSubmit("", "Vis", 0, $TabIndex++, "");
            echo "      <form action=\"tables.php\" method=\"post\"><input type=\"hidden\" name=\"TableNo\" value=\"$TableNo\"><input type=\"hidden\" name=\"No\" value=\"$Row[0]\"><tr><td width=\"1%\" valign=\"middle\" align=\"right\" nowrap>$Input</td><td width=\"99%\" valign=\"middle\">$Row[0]. $Row[1]</td></tr></form>\r\n";
          }
          echo "    </table>\r\n";
        }
        MakeHtmlPageBottom();
        oswebdb_free_result($Result);
      }
      else if ($Result = oswebdb_query("SELECT No,Description FROM $TableName WHERE SystemNo=$TableSystemNo AND TableNo=0 ORDER BY SystemNo,TableNo,No"))
      {
        $SystemProperties = get_system_properties($SystemNo);
        $TableDescription = get_table_name($SystemNo, 0);
        $TabIndex = 1;
        MakeHtmlPageTop($TableDescription);
        echo "    <table border=\"0\" cellpadding=\"1\" cellspacing=\"1\" width=\"100%\">\r\n";
        echo "      <tr><td width=\"100%\" colspan=\"2\" valign=\"middle\"><h2><i>$TableDescription</i></h2></td></tr>\r\n";
        while ($Row = oswebdb_fetch_row($Result))
        {
          $IncludeTable = 1;
          switch ($Row[0])
          {
            case 2:
              // Country codes.
              $IncludeTable = ($SystemProperties & 4);
              break;

            case 3:
              // Address groups.
              $IncludeTable = ($SystemProperties & 4);
              break;

            case 8:
              // Types of averages.
              $IncludeTable = ($SystemProperties & 4);
              break;
          }
          $IncludeTable = $IncludeTable && is_table_accessible($SystemNo, $Row[0]);
          if ($IncludeTable)
          {
            if ($AllowUpdate)
              $Input = MakeHtmlInputSubmit("", "Redigér", !$AllowUpdate, $TabIndex++, "");
            else
              $Input = MakeHtmlInputSubmit("", "Vis", 0, $TabIndex++, "");
            echo "      <form action=\"tables.php\" method=\"post\"><input type=\"hidden\" name=\"TableNo\" value=\"$Row[0]\"><tr><td width=\"1%\" valign=\"middle\" align=\"right\" nowrap>$Input</td><td width=\"99%\" valign=\"middle\">$Row[0]. $Row[1]</td></tr></form>\r\n";
          }
        }
        echo "    </table>\r\n";
        MakeHtmlPageBottom();
        oswebdb_free_result($Result);
      }
    }
    oswebdb_close();
  }
?>
