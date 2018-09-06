<?php
  // Include required files.
  require_once("misc.php");
  require_once("oswebdb/oswebdb.php");
  require_once("oswebdb/websites.php");
  require_once("oswebdb/tables.php");
  require_once("oswebdb/systems.php");

  // Set error reporting.
  error_reporting((int) GetConfigValue("ErrorReporting"));

  define("MAX_SIZE", 80);
  $WebsiteTableName = "Websites";
  $WebcontentTableName = "Webcontent";
  $SystemNo = GetConfigValue("SystemNo");
  if (isset($_GET["TypeNo"]))
    $TypeNo = $_GET["TypeNo"];
  else if (isset($_POST["TypeNo"]))
    $TypeNo = $_POST["TypeNo"];
  if (isset($_GET["MenuNo"]))
    $MenuNo = $_GET["MenuNo"];
  else if (isset($_POST["MenuNo"]))
    $MenuNo = $_POST["MenuNo"];
  if (isset($_GET["Description"]))
    $Description = $_GET["Description"];
  else if (isset($_POST["Description"]))
    $Description = $_POST["Description"];
  $Username = oswebdb_getusername();
  $Password = oswebdb_getpassword();
  if (oswebdb_authorize($Username, $Password))
  {
    if (oswebdb_selectdb())
    {
      $AllowSelect = oswebdb_allow_select($WebsiteTableName) && oswebdb_allow_select($WebcontentTableName);
      $AllowInsert = oswebdb_allow_insert($WebsiteTableName) && oswebdb_allow_insert($WebcontentTableName);
      $AllowUpdate = oswebdb_allow_update($WebsiteTableName) && oswebdb_allow_update($WebcontentTableName);
      $AllowDelete = oswebdb_allow_delete($WebsiteTableName) && oswebdb_allow_delete($WebcontentTableName);
      $TypeSystemNo = get_system_for_table($SystemNo, 4);
      $MenuSystemNo = get_system_for_table($SystemNo, 1);
      $MenuStatement = "SELECT No,Description FROM Systemtables WHERE SystemNo=$MenuSystemNo AND TableNo=1 AND Public IN (1) ORDER BY Description,No";
      if (isset($Username) && isset($Password))
        $MenuStatement = "SELECT No,Description FROM Systemtables WHERE SystemNo=$MenuSystemNo AND TableNo=1 AND Public IN (0,1) ORDER BY Description,No";
      if (isset($_POST["Insert"]) && isset($TypeNo) && isset($MenuNo) && isset($Description))
      {
        if (!insert_website($SystemNo, $TypeNo, $MenuNo, $Description))
        {
          $Error = oswebdb_error();
          MakeHtmlPageTop(get_table_field_value($TypeSystemNo, 4, $TypeNo, "Description"));
          echo "    <h2><strong>$Error</strong></h2>\r\n";
          MakeHtmlPageBottom("");
          exit;
        }
      }
      else if (isset($_POST["Update"]) && isset($TypeNo) && isset($MenuNo) && isset($Description))
      {
        if (!update_website($SystemNo, $TypeNo, $MenuNo, $Description, $_POST["Created"], $_POST["Public"], $_POST["Active"], $_POST["ActiveFrom"], $_POST["ActiveTo"], $_POST["Owner"], $_POST["Picture"], $_POST["PictureThumbnial"], $_POST["PictureXJustify"], $_POST["PictureYJustify"], $_POST["Address"], $_POST["Content"], $_POST["ShowOnHomepage"], $_POST["Document"], $_POST["SecureHTTP"]))
        {
          $Error = oswebdb_error();
          MakeHtmlPageTop(get_table_field_value($TypeSystemNo, 4, $TypeNo, "Description"));
          echo "    <h2><strong>$Error</strong></h2>\r\n";
          MakeHtmlPageBottom("");
          exit;
        }
      }
      else if (isset($_POST["Delete"]) && isset($TypeNo) && isset($MenuNo) && isset($Description))
      {
        if (!delete_website($SystemNo, $TypeNo, $MenuNo, $Description))
        {
          $Error = oswebdb_error();
          MakeHtmlPageTop(get_table_field_value($TypeSystemNo, 4, $TypeNo, "Description"));
          echo "    <h2><strong>$Error</strong></h2>\r\n";
          MakeHtmlPageBottom("");
          exit;
        }
        unset($MenuNo);
        unset($Description);
      }
      else if (isset($_POST["ShowAll"]))
      {
        if (isset($MenuNo))
          unset($MenuNo);
        if (isset($Description))
          unset($Description);
      }
      else if (isset($_POST["ContentInsert"]) && isset($TypeNo) && isset($MenuNo) && isset($Description) && isset($_POST["Text"]))
      {
        if (!insert_content($SystemNo, $TypeNo, $MenuNo, $Description, $_POST["Text"], $_POST["Created"], $_POST["Active"], $_POST["ActiveFrom"], $_POST["ActiveTo"], $_POST["Picture"], $_POST["PictureThumbnial"], $_POST["PictureXJustify"], $_POST["PictureYJustify"], $_POST["GroupNo"]))
        {
          $Error = oswebdb_error();
          MakeHtmlPageTop(get_table_field_value($TypeSystemNo, 4, $TypeNo, "Description"));
          echo "    <h2><strong>$Error</strong></h2>\r\n";
          MakeHtmlPageBottom("");
          exit;
        }
      }
      else if (isset($_POST["ContentUpdate"]) && isset($TypeNo) && isset($MenuNo) && isset($Description) && isset($_POST["OldText"]) && isset($_POST["Text"]))
      {
        if (!update_content($SystemNo, $TypeNo, $MenuNo, $Description, $_POST["OldText"], $_POST["Text"], $_POST["Created"], $_POST["Active"], $_POST["ActiveFrom"], $_POST["ActiveTo"], $_POST["Picture"], $_POST["PictureThumbnial"], $_POST["PictureXJustify"], $_POST["PictureYJustify"], $_POST["GroupNo"]))
        {
          $Error = oswebdb_error();
          MakeHtmlPageTop(get_table_field_value($TypeSystemNo, 4, $TypeNo, "Description"));
          echo "    <h2><strong>$Error</strong></h2>\r\n";
          MakeHtmlPageBottom("");
          exit;
        }
      }
      else if (isset($_POST["ContentDelete"]) && isset($TypeNo) && isset($MenuNo) && isset($Description) && isset($_POST["Text"]))
      {
        if (!delete_content($SystemNo, $TypeNo, $MenuNo, $Description, $_POST["Text"]))
        {
          $Error = oswebdb_error();
          MakeHtmlPageTop(get_table_field_value($TypeSystemNo, 4, $TypeNo, "Description"));
          echo "    <h2><strong>$Error</strong></h2>\r\n";
          MakeHtmlPageBottom("");
          exit;
        }
      }
      if (isset($TypeNo) && isset($MenuNo) && isset($Description) && $Result = oswebdb_query("SELECT TypeNo,MenuNo,Description,Created,Public,ShowOnHomepage,Active,ActiveFrom,ActiveTo,Owner,Picture,PictureThumbnial,PictureXJustify,PictureYJustify,Document,Address,Content,SecureHTTP FROM $WebsiteTableName WHERE SystemNo=$SystemNo AND TypeNo=$TypeNo AND MenuNo=$MenuNo AND Description=\"$Description\""))
      {
        if ($Row = oswebdb_fetch_row($Result))
        {
          $TypeName = get_table_field_value($TypeSystemNo, 4, $TypeNo, "Description");
          $TypeFields = get_table_field_value($TypeSystemNo, 4, $TypeNo, "ShowFields");
          $TypeProperties = get_table_field_value($TypeSystemNo, 4, $TypeNo, "Properties");
          $ContentTypeNo = get_table_field_value($TypeSystemNo, 4, $TypeNo, "GroupNo");
          if ($ContentTypeNo > 0)
          {
            $ContentTypeSystemNo = get_system_for_table($SystemNo, 5);
            $ContentTypeDescription = get_table_field_value($ContentTypeSystemNo, 5, $ContentTypeNo, "Description");
            $ContentTypeFields = get_table_field_value($ContentTypeSystemNo, 5, $ContentTypeNo, "ShowFields");
            $ContentTypeProperties = get_table_field_value($ContentTypeSystemNo, 5, $ContentTypeNo, "Properties");
            $ContentTypeGroupNo = get_table_field_value($ContentTypeSystemNo, 5, $ContentTypeNo, "GroupNo");
            $ContentTypeGroupSystemNo = get_system_for_table($SystemNo, $ContentTypeGroupNo);
          }
          $TabIndex = 1;
          echo "<script language=\"JavaScript\">\r\n";
          echo "  function validateWebsite(oForm)\r\n";
          echo "  {\r\n";
          echo "    return validateForm(oForm, $TypeFields, $TypeProperties)\r\n";
          echo "  }\r\n\r\n";
          echo "  function resetWebsite(oForm, active, picture)\r\n";
          echo "  {\r\n";
          echo "    return resetForm(oForm, $TypeFields, $TypeProperties, active, picture)\r\n";
          echo "  }\r\n\r\n";
          if ($ContentTypeNo > 0)
          {
            echo "  function validateContent(oForm)\r\n";
            echo "  {\r\n";
            echo "    text = oForm.Text.value\r\n";
            echo "    if (text.length == 0)\r\n";
            echo "    {\r\n";
            echo "      alert('Teksten skal indtastes!')\r\n";
            echo "      return false\r\n";
            echo "    }\r\n";
            echo "    else\r\n";
            echo "      return validateForm(oForm, $ContentTypeFields, $ContentTypeProperties)\r\n";
            echo "  }\r\n\r\n";
            echo "  function resetContent(oForm, active, picture)\r\n";
            echo "  {\r\n";
            echo "    return resetForm(oForm, $ContentTypeFields, $ContentTypeProperties, active, picture)\r\n";
            echo "  }\r\n\r\n";
          }
          echo "  function validateForm(oForm, fields, properties)\r\n";
          echo "  {\r\n";
          echo "    result = true\r\n";
          if (($TypeFields & 2) || ($ContentTypeNo > 0 && ($ContentTypeFields & 2)))
          {
            echo "    if (result && (fields & 2) > 0)\r\n";
            echo "    {\r\n";
            echo "      created = oForm.Created.value\r\n";
            echo "      result = parent.validateDate(parseInt(created.substr(0, 4), 10), parseInt(created.substr(5, 2), 10), parseInt(created.substr(8, 2), 10))\r\n";
            echo "    }\r\n";
          }
          if (($TypeFields & 8) || ($ContentTypeNo > 0 && ($ContentTypeFields & 8)))
          {
            echo "    if (result && (fields & 8) > 0)\r\n";
            echo "    {\r\n";
            echo "      active = getActiveValue(oForm)\r\n";
            echo "      activeFrom = oForm.ActiveFrom.value\r\n";
            echo "      activeTo = oForm.ActiveTo.value\r\n";
            echo "      if (result && active == 2)\r\n";
            echo "        result = parent.validateDate(parseInt(activeFrom.substr(0, 4), 10), parseInt(activeFrom.substr(5, 2), 10), parseInt(activeFrom.substr(8, 2), 10))\r\n";
            echo "      if (result && active == 2)\r\n";
            echo "        result = parent.validateDate(parseInt(activeTo.substr(0, 4), 10), parseInt(activeTo.substr(5, 2), 10), parseInt(activeTo.substr(8, 2), 10))\r\n";
            echo "    }\r\n";
          }
          if ((($TypeFields & 32) && ($TypeProperties & 32)) || ($ContentTypeNo > 0 && ($ContentTypeFields & 32) && ($ContentTypeProperties & 32)))
          {
            echo "    if (result && (fields & 32) > 0 && (properties & 32) > 0)\r\n";
            echo "    {\r\n";
            echo "      picture = getPictureValue(oForm)\r\n";
            echo "      if (picture.length == 0)\r\n";
            echo "      {\r\n";
            echo "        alert('Billedet skal vælges!')\r\n";
            echo "        result = false\r\n";
            echo "      }\r\n";
            echo "    }\r\n";
          }
          if ((($TypeFields & 512) && ($TypeProperties & 64)) || ($ContentTypeNo > 0 && ($ContentTypeFields & 512) && ($ContentTypeProperties & 64)))
          {
            echo "    if (result && (fields & 512) > 0 && (properties & 64) > 0)\r\n";
            echo "    {\r\n";
            echo "      doc = getDocumentValue(oForm)\r\n";
            echo "      if (doc.length == 0)\r\n";
            echo "      {\r\n";
            echo "        alert('Dokumentet skal vælges!')\r\n";
            echo "        result = false\r\n";
            echo "      }\r\n";
            echo "    }\r\n";
          }
          if (($TypeFields & 64) || ($ContentTypeNo > 0 && ($ContentTypeFields & 64)))
          {
            echo "    if (result && (fields & 8) > 0)\r\n";
            echo "    {\r\n";
            echo "      address = parent.getWeb(oForm.Address.value)\r\n";
            echo "      oForm.Address.value = address\r\n";
            echo "      if ((properties & 1) > 0 && address.length == 0)\r\n";
            echo "      {\r\n";
            echo "        alert('Adressen skal indtastes!')\r\n";
            echo "        result = false\r\n";
            echo "      }\r\n";
            echo "    }\r\n";
          }
          if ((($TypeFields & 128) && ($TypeProperties & 2)) || ($ContentTypeNo > 0 && ($ContentTypeFields & 128) && ($ContentTypeProperties & 2)))
          {
            echo "    if (result && (fields & 128) > 0 && (properties & 2) > 0)\r\n";
            echo "    {\r\n";
            echo "      content = oForm.Content.value\r\n";
            echo "      if (content.length == 0)\r\n";
            echo "      {\r\n";
            echo "        alert('Indholdet skal indtastes!')\r\n";
            echo "        result = false\r\n";
            echo "      }\r\n";
            echo "    }\r\n";
          }
          echo "    return result\r\n";
          echo "  }\r\n\r\n";
          echo "  function resetForm(oForm, fields, properties, active, picture)\r\n";
          echo "  {\r\n";
          if (($TypeFields & 8) || ($ContentTypeNo > 0 && ($ContentTypeFields & 8)))
          {
            echo "    if ((fields & 8) > 0)\r\n";
            echo "      changeActiveRadioControl(oForm, active)\r\n";
          }
          if (($TypeFields & 32) || ($ContentTypeNo > 0 && ($ContentTypeFields & 32)))
          {
            echo "    if ((fields & 32) > 0)\r\n";
            echo "      changePictureListBoxControl(oForm, picture)\r\n";
          }
          echo "    return true\r\n";
          echo "  }\r\n";
          if (($TypeFields & 2) || ($ContentTypeNo > 0 && ($ContentTypeFields & 2)))
          {
            echo "\r\n  function changeCreatedEditControl(oForm)\r\n";
            echo "  {\r\n";
            echo "    s = oForm.Created.value\r\n";
            echo "    if (s.length == 8)\r\n";
            echo "    {\r\n";
            echo "      s = s.substr(0, 4) + '-' + s.substr(4, 2) + '-' + s.substr(6,2)\r\n";
            echo "      oForm.Created.value = s\r\n";
            echo "    }\r\n";
            echo "    year = parseInt(s.substr(0, 4), 10)\r\n";
            echo "    month = parseInt(s.substr(5, 2), 10)\r\n";
            echo "    date = parseInt(s.substr(8, 2), 10)\r\n";
            echo "    return parent.validateDate(year, month, date)\r\n";
            echo "  }\r\n";
          }
          if (($TypeFields & 4) || ($ContentTypeNo > 0 && ($ContentTypeFields & 4)))
          {
            $DefaultOwner = get_default_owner();
            echo "\r\n  function changePublicCheckBoxControl(oForm, fields)\r\n";
            echo "  {\r\n";
            echo "    if ((fields & 16) > 0)\r\n";
            echo "    {\r\n";
            echo "      if (oForm.Public.checked)\r\n";
            echo "      {\r\n";
            echo "        for (i = 0; i < oForm.Owner.length; i++)\r\n";
            echo "          oForm.Owner[i].selected = (oForm.Owner[i].value == '$DefaultOwner')\r\n";
            echo "      }\r\n";
            echo "    }\r\n";
            echo "  }\r\n";
          }
          if (($TypeFields & 8) || ($ContentTypeNo > 0 && ($ContentTypeFields & 8)))
          {
            echo "\r\n  function getActiveValue(oForm)\r\n";
            echo "  {\r\n";
            echo "    value = 0\r\n";
            echo "    for (i = 0; i < oForm.Active.length; i++)\r\n";
            echo "    {\r\n";
            echo "      if (oForm.Active[i].checked)\r\n";
            echo "        value = oForm.Active[i].value\r\n";
            echo "    }\r\n";
            echo "    return value\r\n";
            echo "  }\r\n\r\n";
            echo "  function changeActiveRadioControl(oForm, active)\r\n";
            echo "  {\r\n";
            echo "    if (active != 2)\r\n";
            echo "    {\r\n";
            echo "      oForm.ActiveFrom.value = '0000-00-00'\r\n";
            echo "      oForm.ActiveTo.value = '0000-00-00'\r\n";
            echo "    }\r\n";
            echo "    oForm.ActiveFrom.readOnly = (active != 2)\r\n";
            echo "    oForm.ActiveFrom.disabled = (active != 2)\r\n";
            echo "    oForm.ActiveTo.readOnly = (active != 2)\r\n";
            echo "    oForm.ActiveTo.disabled = (active != 2)\r\n";
            echo "  }\r\n\r\n";
            echo "  function changeActiveFromEditControl(oForm)\r\n";
            echo "  {\r\n";
            echo "    s = oForm.ActiveFrom.value\r\n";
            echo "    if (s.length == 8)\r\n";
            echo "    {\r\n";
            echo "      s = s.substr(0, 4) + '-' + s.substr(4, 2) + '-' + s.substr(6,2)\r\n";
            echo "      oForm.ActiveFrom.value = s\r\n";
            echo "    }\r\n";
            echo "    year = parseInt(s.substr(0, 4), 10)\r\n";
            echo "    month = parseInt(s.substr(5, 2), 10)\r\n";
            echo "    date = parseInt(s.substr(8, 2), 10)\r\n";
            echo "    return parent.validateDate(year, month, date)\r\n";
            echo "  }\r\n\r\n";
            echo "  function changeActiveToEditControl(oForm)\r\n";
            echo "  {\r\n";
            echo "    s = oForm.ActiveTo.value\r\n";
            echo "    if (s.length == 8)\r\n";
            echo "    {\r\n";
            echo "      s = s.substr(0, 4) + '-' + s.substr(4, 2) + '-' + s.substr(6,2)\r\n";
            echo "      oForm.ActiveTo.value = s\r\n";
            echo "    }\r\n";
            echo "    year = parseInt(s.substr(0, 4), 10)\r\n";
            echo "    month = parseInt(s.substr(5, 2), 10)\r\n";
            echo "    date = parseInt(s.substr(8, 2), 10)\r\n";
            echo "    return parent.validateDate(year, month, date)\r\n";
            echo "  }\r\n";
          }
          if (($TypeFields & 16) || ($ContentTypeNo > 0 && ($ContentTypeFields & 16)))
          {
            $DefaultOwner = get_default_owner();
            echo "\r\n  function changeOwnerListBoxControl(oForm, fields, owner)\r\n";
            echo "  {\r\n";
            echo "    if ((fields & 4) > 0)\r\n";
            echo "    {\r\n";
            echo "      if (owner != '$DefaultOwner')\r\n";
            echo "        oForm.Public.checked = false\r\n";
            echo "    }\r\n";
            echo "  }\r\n";
          }
          if (($TypeFields & 32) || ($ContentTypeNo > 0 && ($ContentTypeFields & 32)))
          {
            echo "\r\n  function getPictureValue(oForm)\r\n";
            echo "  {\r\n";
            echo "    value = ''\r\n";
            echo "    for (i = 0; i < oForm.Picture.length && value.length == 0; i++)\r\n";
            echo "    {\r\n";
            echo "      if (oForm.Picture.options[i].selected)\r\n";
            echo "        value = oForm.Picture.options[i].value\r\n";
            echo "    }\r\n";
            echo "    return value\r\n";
            echo "  }\r\n\r\n";
            echo "  function changePictureListBoxControl(oForm, picture)\r\n";
            echo "  {\r\n";
            echo "    is_checked = false\r\n";
            echo "    for (i = 0; i < oForm.PictureThumbnial.length; i++)\r\n";
            echo "      oForm.PictureThumbnial.options[i].selected = (oForm.PictureThumbnial.options[i].value == '')\r\n";
            echo "    oForm.PictureThumbnial.readonly = (picture == '')\r\n";
            echo "    oForm.PictureThumbnial.disabled = (picture == '')\r\n";
            echo "    for (i = 0; i < oForm.PictureXJustify.length; i++)\r\n";
            echo "    {\r\n";
            echo "      if (picture == '')\r\n";
            echo "        oForm.PictureXJustify[i].checked = false\r\n";
            echo "      is_checked = is_checked || oForm.PictureXJustify[i].checked\r\n";
            echo "      oForm.PictureXJustify[i].disabled = (picture == '')\r\n";
            echo "    }\r\n";
            echo "    if (picture != '' && !is_checked)\r\n";
            echo "      oForm.PictureXJustify[0].checked = true\r\n";
            echo "    is_checked = false\r\n";
            echo "    for (i = 0; i < oForm.PictureYJustify.length; i++)\r\n";
            echo "    {\r\n";
            echo "      if (picture == '')\r\n";
            echo "        oForm.PictureYJustify[i].checked = false\r\n";
            echo "      is_checked = is_checked || oForm.PictureYJustify[i].checked\r\n";
            echo "      oForm.PictureYJustify[i].disabled = (picture == '')\r\n";
            echo "    }\r\n";
            echo "    if (picture != '' && !is_checked)\r\n";
            echo "      oForm.PictureYJustify[0].checked = true\r\n";
            echo "  }\r\n";
          }
          if (($TypeFields & 512) || ($ContentTypeNo > 0 && ($ContentTypeFields & 512)))
          {
            echo "\r\n  function getDocumentValue(oForm)\r\n";
            echo "  {\r\n";
            echo "    value = ''\r\n";
            echo "    for (i = 0; i < oForm.Document.length && value.length == 0; i++)\r\n";
            echo "    {\r\n";
            echo "      if (oForm.Document.options[i].selected)\r\n";
            echo "        value = oForm.Document.options[i].value\r\n";
            echo "    }\r\n";
            echo "    if (value == '/')\r\n";
            echo "      value = ''\r\n";
            echo "    return value\r\n";
            echo "  }\r\n";
          }
          echo "</script>\r\n";
          MakeHtmlPageTop($TypeName);
          echo "    <table border=\"0\" cellpadding=\"1\" cellspacing=\"1\" width=\"100%\">\r\n";
          echo "      <tr><td width=\"100%\" colspan=\"2\" valign=\"middle\"><h2><i>$TypeName</i></h2></td></tr>\r\n";
          echo "      <form name=\"UpdateForm\" action=\"websites.php\" method=\"post\">\r\n";
          echo "        <input type=\"hidden\" name=\"TypeNo\" value=\"$Row[0]\">\r\n";
          echo "        <input type=\"hidden\" name=\"MenuNo\" value=\"$Row[1]\">\r\n";
          echo "        <input type=\"hidden\" name=\"Description\" value=\"$Row[2]\">\r\n";
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\">";
          if ($AllowUpdate)
          {
            $Active = 0;
            if ($TypeFields & 8)
              $Active = (integer) $Row[6];
            $Picture = "";
            if ($TypeFields & 32)
              $Picture = $Row[10];
            $InputUpdate = MakeHtmlInputSubmit("Update", "Opdatér", !$AllowUpdate, $TabIndex++, "javascript:return validateWebsite(this.form)");
            $InputReset = MakeHtmlInputReset("", "Fortryd", !$AllowUpdate, $TabIndex++, "javascript:return resetWebsite(this.form, $Active, '$Picture')");
            echo "$InputUpdate&nbsp;$InputReset&nbsp;";
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
          if ($TypeFields & 1)
          {
            $Options = "<option value=\"0\"";
            if ($Row[1] == 0)
              $Options = "$Options selected";
            $Options = "$Options>(Ingen)";
            if ($TableResult = oswebdb_query($MenuStatement))
            {
              while ($TableRow = oswebdb_fetch_row($TableResult))
              {
                $Options = "$Options<option value=\"$TableRow[0]\"";
                if ($Row[1] == $TableRow[0])
                  $Options = "$Options selected";
                $Options = "$Options>$TableRow[1]";
              }
              oswebdb_free_result($TableResult);
            }
            $Input = MakeHtmlSelect("", 0, 0, 1, 1, $TabIndex++, "", $Options);
            echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Menu :</td><td width=\"99%\">$Input</td></tr>\r\n";
          }
          $Input = MakeHtmlInputText("", $Row[2], oswebdb_field_len($Result, 2), oswebdb_field_len($Result, 2), 1, 1, $TabIndex++, "");
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Beskrivelse :</td><td width=\"99%\">$Input</td></tr>\r\n";
          if ($TypeFields & 2)
          {
            $Input = MakeHtmlInputText("Created", $Row[3], oswebdb_field_len($Result, 3), oswebdb_field_len($Result, 3), !$AllowUpdate, !$AllowUpdate, $TabIndex++, "javascript:return changeCreatedEditControl(this.form)");
            echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Oprettet :</td><td width=\"99%\">$Input</td></tr>\r\n";
          }
          $Text = "Egenskaber :";
          if ($TypeFields & 4)
          {
            $Input = MakeHtmlInputCheckBox("Public", "1", $Row[4], !$AllowUpdate, $TabIndex++, "javascript:changePublicCheckBoxControl(this.form, $TypeFields)", "Offentlig");
            echo "        <tr><td width=\"1%\" align=\"right\" nowrap>$Text</td><td width=\"99%\">$Input</td></tr>\r\n";
            $Text = "";
          }
          if ($TypeFields & 256)
          {
            $Input = MakeHtmlInputCheckBox("ShowOnHomepage", "1", $Row[5], !$AllowUpdate, $TabIndex++, "", "Vises på forsiden som link");
            echo "        <tr><td width=\"1%\" align=\"right\" nowrap>$Text</td><td width=\"99%\">$Input</td></tr>\r\n";
            $Text = "";
          }
          if ($TypeFields & 64)
          {
            $Input = MakeHtmlInputCheckBox("SecureHTTP", "1", $Row[17], !$AllowUpdate, $TabIndex++, "", "Kræv servergodkendelse (https:)");
            echo "        <tr><td width=\"1%\" align=\"right\" nowrap>$Text</td><td width=\"99%\">$Input</td></tr>\r\n";
            $Text = "";
          }
          if ($TypeFields & 8)
          {
            $Input = MakeHtmlInputRadio("Active", "0", ($Row[6] == 0), !$AllowUpdate, $TabIndex++, "javascript:changeActiveRadioControl(this.form, parseInt(this.value, 10))", "Passiv");
            echo "        <tr><td width=\"1%\" align=\"right\" nowrap>$Text</td><td width=\"99%\">$Input</td></tr>\r\n";
            $Input = MakeHtmlInputRadio("Active", "1", ($Row[6] == 1), !$AllowUpdate, $TabIndex++, "javascript:changeActiveRadioControl(this.form, parseInt(this.value, 10))", "Aktiv");
            echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\">$Input</td></tr>\r\n";
            $Input = MakeHtmlInputRadio("Active", "2", ($Row[6] == 2), !$AllowUpdate, $TabIndex++, "javascript:changeActiveRadioControl(this.form, parseInt(this.value, 10))", "Aktiv i periode");
            echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\">$Input</td></tr>\r\n";
            $InputFrom = MakeHtmlInputText("ActiveFrom", $Row[7], oswebdb_field_len($Result, 7), oswebdb_field_len($Result, 7), !$AllowUpdate || ($Row[6] != 2), !$AllowUpdate || ($Row[6] != 2), $TabIndex++, "javascript:return changeActiveFromEditControl(this.form)");
            $InputTo = MakeHtmlInputText("ActiveTo", $Row[8], oswebdb_field_len($Result, 8), oswebdb_field_len($Result, 8), !$AllowUpdate || ($Row[6] != 2), !$AllowUpdate || ($Row[6] != 2), $TabIndex++, "javascript:return changeActiveToEditControl(this.form)");
            echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Aktiv i perioden :</td><td width=\"99%\">$InputFrom&nbsp;til&nbsp;$InputTo</td></tr>\r\n";
            $Text = "";
          }
          if ($TypeFields & 16)
          {
            $DefaultOwner = get_default_owner();
            $CurrentHostname = oswebdb_privilege_hostname(0);
            $CurrentUsername = oswebdb_privilege_username();
            $Options = "<option value=\"$DefaultOwner\"";
            if ($Row[9] == $DefaultOwner)
              $Options = "$Options selected";
            $Options = "$Options>(Alle)<option value=\"$CurrentUsername\"";
            if ($Row[9] == $CurrentUsername)
              $Options = "$Options selected";
            $Options = "$Options>$CurrentUsername@$CurrentHostname";
            $Input = MakeHtmlSelect("Owner", 0, 0, !$AllowUpdate, !$AllowUpdate, $TabIndex++, "javascript:changeOwnerListBoxControl(this.form, $TypeFields, this.value)", $Options);
            echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Ejer :</td><td width=\"99%\">$Input</td></tr>\r\n";
          }
          if ($TypeFields & 32)
          {
            $Options = "<option value=\"\"";
            if ($Row[10] == "")
              $Options = "$Options selected";
            $Options = "$Options>(Ingen)";
            $Pictures = get_files_in_path(get_system_path_pictures($SystemNo), $Row[10], get_table_field_value($TypeSystemNo, 4, $TypeNo, "Text2"), get_system_days_pictures($SystemNo));
            $Options = "$Options$Pictures";
            $Input = MakeHtmlSelect("Picture", 0, 0, !$AllowUpdate, !$AllowUpdate, $TabIndex++, "javascript:changePictureListBoxControl(this.form, this.value)", $Options);
            echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Billede :</td><td width=\"99%\">$Input</td></tr>\r\n";
            $Options = "<option value=\"\"";
            if ($Row[11] == "")
              $Options = "$Options selected";
            $Options = "$Options>(Ingen)";
            $Pictures = get_files_in_path(get_system_path_pictures($SystemNo), $Row[11], get_table_field_value($TypeSystemNo, 4, $TypeNo, "Text2"), get_system_days_pictures($SystemNo));
            $Options = "$Options$Pictures";
            $Input = MakeHtmlSelect("PictureThumbnial", 0, 0, !$AllowUpdate || ($Row[10] == ""), !$AllowUpdate || ($Row[10] == ""), $TabIndex++, "", $Options);
            echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Thumbnial :</td><td width=\"99%\">$Input</td></tr>\r\n";
            $InputLeft = MakeHtmlInputRadio("PictureXJustify", "1", ($Row[12] == 1), !$AllowUpdate || ($Row[10] == "") || ($Row[12] == 0), $TabIndex++, "", "Venstre");
            $InputCenter = MakeHtmlInputRadio("PictureXJustify", "2", ($Row[12] == 2), !$AllowUpdate || ($Row[10] == "") || ($Row[12] == 0), $TabIndex++, "", "Center");
            $InputRight = MakeHtmlInputRadio("PictureXJustify", "4", ($Row[12] == 4), !$AllowUpdate || ($Row[10] == "") || ($Row[12] == 0), $TabIndex++, "", "Højre");
            echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\">$InputLeft&nbsp;$InputCenter&nbsp;$InputRight</td></tr>\r\n";
            $InputTop = MakeHtmlInputRadio("PictureYJustify", "1", ($Row[13] == 1), !$AllowUpdate || ($Row[10] == "") || ($Row[13] == 0), $TabIndex++, "", "Top");
            $InputCenter = MakeHtmlInputRadio("PictureYJustify", "2", ($Row[13] == 2), !$AllowUpdate || ($Row[10] == "") || ($Row[13] == 0), $TabIndex++, "", "Center");
            $InputBottom = MakeHtmlInputRadio("PictureYJustify", "4", ($Row[13] == 4), !$AllowUpdate || ($Row[10] == "") || ($Row[13] == 0), $TabIndex++, "", "Bund");
            echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\">$InputTop&nbsp;$InputCenter&nbsp;$InputBottom</td></tr>\r\n";
          }
          if ($TypeFields & 512)
          {
            $Options = "<option value=\"\"";
            if ($Row[14] == "")
              $Options = "$Options selected";
            $Options = "$Options>(Ingen)";
            $Documents = get_files_in_path(get_system_path_documents($SystemNo), $Row[14], get_table_field_value($TypeSystemNo, 4, $TypeNo, "Text1"), get_system_days_documents($SystemNo));
            $Options = "$Options$Documents";
            $Input = MakeHtmlSelect("Document", 0, 0, !$AllowUpdate, !$AllowUpdate, $TabIndex++, "", $Options);
            echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Dokument :</td><td width=\"99%\">$Input</td></tr>\r\n";
          }
          if ($TypeFields & 64)
          {
            $Input = MakeHtmlInputText("Address", $Row[15], oswebdb_field_len($Result, 15), oswebdb_field_len($Result, 15), !$AllowUpdate, !$AllowUpdate, $TabIndex++, "javascript:this.value = parent.getWeb(this.value)");
            echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Adresse :</td><td width=\"99%\">$Input</td></tr>\r\n";
          }
          if ($TypeFields & 128)
          {
            $Text = "Indhold :";
            if ($TypeProperties & 4)
              $Text = "$Text<br>(HTML-kode) :";
            $Input = MakeHtmlTextArea("Content", 15, 60, "", !$AllowUpdate, !$AllowUpdate, $TabIndex, "", $Row[16]);
            echo "        <tr><td width=\"1%\" valign=\"top\" align=\"right\" nowrap>$Text</td><td width=\"99%\">$Input</td></tr>\r\n";
          }
          echo "      </form>\r\n";
          if ($ContentTypeNo > 0 && $ContentResult = oswebdb_query(get_content_statement($SystemNo, $Row[0], $Row[1], $Row[2], $ContentTypeNo)))
          {
            echo "      <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\"><table border=\"1\" cellpadding=\"1\" cellspacing=\"1\" width=\"100%\">\r\n";
            echo "        <tr><td width=\"100%\" valign=\"middle\"><strong>$ContentTypeDescription</strong></td></tr>\r\n";
            $RowNo = 0;
            if ($AllowInsert)
            {
              echo "        <tr><td width=\"100%\" valign=\"middle\"><table border=\"0\" cellpadding=\"1\" cellspacing=\"1\" width=\"100%\">\r\n";
              echo "          <form name=\"ContentForm$RowNo\" action=\"websites.php\" method=\"post\">\r\n";
              echo "            <input type=\"hidden\" name=\"TypeNo\" value=\"$Row[0]\">\r\n";
              echo "            <input type=\"hidden\" name=\"MenuNo\" value=\"$Row[1]\">\r\n";
              echo "            <input type=\"hidden\" name=\"Description\" value=\"$Row[2]\">\r\n";
              $InputInsert = MakeHtmlInputSubmit("ContentInsert", "Opret", !$AllowInsert, $TabIndex++, "javascript:return validateContent(this.form)");
              echo "            <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\">$InputInsert</td></tr>\r\n";
              $Input = MakeHtmlInputText("Text", "", oswebdb_field_len($ContentResult, 3), oswebdb_field_len($ContentResult, 3), !$AllowInsert, !$AllowInsert, $TabIndex++, "");
              echo "            <tr><td width=\"1%\" align=\"right\" nowrap>Tekst :</td><td width=\"99%\">$Input</td></tr>\r\n";
              if ($ContentTypeFields & 2)
              {
                $Input = MakeHtmlInputText("Created", date("Y-m-d", time()), oswebdb_field_len($ContentResult, 4), oswebdb_field_len($ContentResult, 4), !$AllowInsert, !$AllowInsert, $TabIndex++, "javascript:return changeCreatedEditControl(this.form)");
                echo "            <tr><td width=\"1%\" align=\"right\" nowrap>Oprettet :</td><td width=\"99%\">$Input</td></tr>\r\n";
              }
              $Text = "Egenskaber :";
              if ($ContentTypeFields & 8)
              {
                $Input = MakeHtmlInputRadio("Active", "0", 0, !$AllowInsert, $TabIndex++, "javascript:changeActiveRadioControl(this.form, parseInt(this.value, 10))", "Passiv");
                echo "            <tr><td width=\"1%\" align=\"right\" nowrap>$Text</td><td width=\"99%\">$Input</td></tr>\r\n";
                $Input = MakeHtmlInputRadio("Active", "1", 1, !$AllowInsert, $TabIndex++, "javascript:changeActiveRadioControl(this.form, parseInt(this.value, 10))", "Aktiv");
                echo "            <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\">$Input</td></tr>\r\n";
                $Input = MakeHtmlInputRadio("Active", "2", 0, !$AllowInsert, $TabIndex++, "javascript:changeActiveRadioControl(this.form, parseInt(this.value, 10))", "Aktiv i periode");
                echo "            <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\">$Input</td></tr>\r\n";
                $InputFrom = MakeHtmlInputText("ActiveFrom", "0000-00-00", oswebdb_field_len($ContentResult, 6), oswebdb_field_len($ContentResult, 6), 1, 1, $TabIndex++, "javascript:return changeActiveFromEditControl(this.form)");
                $InputTo = MakeHtmlInputText("ActiveTo", "0000-00-00", oswebdb_field_len($Result, 7), oswebdb_field_len($Result, 7), 1, 1, $TabIndex++, "javascript:return changeActiveToEditControl(this.form)");
                echo "            <tr><td width=\"1%\" align=\"right\" nowrap>Aktiv i perioden :</td><td width=\"99%\">$InputFrom&nbsp;til&nbsp;$InputTo</td></tr>\r\n";
                $Text = "";
              }
              if ($ContentTypeFields & 32)
              {
                $Options = "<option value=\"\" selected>(Ingen)";
                $Pictures = get_files_in_path(get_system_path_pictures($SystemNo), "", get_table_field_value($ContentTypeSystemNo, 5, $ContentTypeNo, "Text2"), get_system_days_pictures($SystemNo));
                $Options = "$Options$Pictures";
                $Input = MakeHtmlSelect("Picture", 0, 0, !$AllowInsert, !$AllowInsert, $TabIndex++, "javascript:changePictureListBoxControl(this.form, this.value)", $Options);
                echo "            <tr><td width=\"1%\" align=\"right\" nowrap>Billede :</td><td width=\"99%\">$Input</td></tr>\r\n";
                $Options = "<option value=\"\" selected>(Ingen)";
                $Pictures = get_files_in_path(get_system_path_pictures($SystemNo), "", get_table_field_value($ContentTypeSystemNo, 5, $ContentTypeNo, "Text2"), get_system_days_pictures($SystemNo));
                $Options = "$Options$Pictures";
                $Input = MakeHtmlSelect("PictureThumbnial", 0, 0, 1, 1, $TabIndex++, "", $Options);
                echo "            <tr><td width=\"1%\" align=\"right\" nowrap>Thumbnial :</td><td width=\"99%\">$Input</td></tr>\r\n";
                $InputLeft = MakeHtmlInputRadio("PictureXJustify", "1", 0, 1, $TabIndex++, "", "Venstre");
                $InputCenter = MakeHtmlInputRadio("PictureXJustify", "2", 0, 1, $TabIndex++, "", "Center");
                $InputRight = MakeHtmlInputRadio("PictureXJustify", "4", 0, 1, $TabIndex++, "", "Højre");
                echo "            <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\">$InputLeft&nbsp;$InputCenter&nbsp;$InputRight</td></tr>\r\n";
                $InputTop = MakeHtmlInputRadio("PictureYJustify", "1", 0, 1, $TabIndex++, "", "Top");
                $InputCenter = MakeHtmlInputRadio("PictureYJustify", "2", 0, 1, $TabIndex++, "", "Center");
                $InputBottom = MakeHtmlInputRadio("PictureYJustify", "4", 0, 1, $TabIndex++, "", "Bund");
                echo "            <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\">$InputTop&nbsp;$InputCenter&nbsp;$InputBottom</td></tr>\r\n";
              }
              if ($ContentTypeGroupNo > 0)
              {
                $Options = "<option value=\"0\" selected>(Ingen)";
                if ($GroupResult = oswebdb_query("SELECT No,Description FROM Systemtables WHERE SystemNo=$ContentTypeGroupSystemNo AND TableNo=$ContentTypeGroupNo ORDER BY Description"))
                {
                  while ($GroupRow = oswebdb_fetch_row($GroupResult))
                    $Options = "$Options<option value=\"$GroupRow[0]\">$GroupRow[1]";
                  oswebdb_free_result($GroupResult);
                }
                $Input = MakeHtmlSelect("GroupNo", 0, 0, !$AllowInsert, !$AllowInsert, $TabIndex++, "", $Options);
                echo "            <tr><td width=\"1%\" align=\"right\" nowrap>Type :</td><td width=\"99%\">$Input</td></tr>\r\n";
              }
              echo "          </form>\r\n";
              echo "        </table></td></tr>\r\n";
              $RowNo++;
            }
            while ($ContentRow = oswebdb_fetch_row($ContentResult))
            {
              echo "        <tr><td width=\"100%\" valign=\"middle\"><table border=\"0\" cellpadding=\"1\" cellspacing=\"1\" width=\"100%\">\r\n";
              echo "          <form name=\"ContentForm$RowNo\" action=\"websites.php\" method=\"post\">\r\n";
              echo "            <input type=\"hidden\" name=\"TypeNo\" value=\"$ContentRow[0]\">\r\n";
              echo "            <input type=\"hidden\" name=\"MenuNo\" value=\"$ContentRow[1]\">\r\n";
              echo "            <input type=\"hidden\" name=\"Description\" value=\"$ContentRow[2]\">\r\n";
              echo "            <input type=\"hidden\" name=\"OldText\" value=\"$ContentRow[3]\">\r\n";
              echo "            <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\">";
              if ($AllowUpdate && $ContentTypeFields > 0)
              {
                $Active = 0;
                if ($ContentTypeFields & 8)
                  $Active = (integer) $ContentRow[5];
                $Picture = "";
                if ($ContentTypeFields & 32)
                  $Picture = $ContentRow[8];
                $InputUpdate = MakeHtmlInputSubmit("ContentUpdate", "Opdatér", !$AllowUpdate, $TabIndex++, "javascript:return validateContent(this.form)");
                $InputReset = MakeHtmlInputReset("", "Fortryd", !$AllowUpdate, $TabIndex++, "javascript:return resetContent(this.form, $Active, '$Picture')");
                echo "$InputUpdate&nbsp;$InputReset&nbsp;";
              }
              if ($AllowDelete)
              {
                $InputDelete = MakeHtmlInputSubmit("ContentDelete", "Slet", !$AllowDelete, $TabIndex++, "javascript:return confirm('Bekræft sletning!')");
                echo "$InputDelete&nbsp;";
              }
              echo "</td></tr>\r\n";
              $Input = MakeHtmlInputText("Text", $ContentRow[3], oswebdb_field_len($ContentResult, 3), oswebdb_field_len($ContentResult, 3), !$AllowUpdate, !$AllowUpdate, $TabIndex++, "");
              echo "            <tr><td width=\"1%\" align=\"right\" nowrap>Tekst :</td><td width=\"99%\">$Input</td></tr>\r\n";
              if ($ContentTypeFields & 2)
              {
                $Input = MakeHtmlInputText("Created", $ContentRow[4], oswebdb_field_len($ContentResult, 4), oswebdb_field_len($ContentResult, 4), !$AllowUpdate, !$AllowUpdate, $TabIndex++, "javascript:return changeCreatedEditControl(this.form)");
                echo "            <tr><td width=\"1%\" align=\"right\" nowrap>Oprettet :</td><td width=\"99%\">$Input</td></tr>\r\n";
              }
              $Text = "Egenskaber :";
              if ($ContentTypeFields & 8)
              {
                $Input = MakeHtmlInputRadio("Active", "0", ($ContentRow[5] == 0), !$AllowUpdate, $TabIndex++, "javascript:changeActiveRadioControl(this.form, parseInt(this.value, 10))", "Passiv");
                echo "            <tr><td width=\"1%\" align=\"right\" nowrap>$Text</td><td width=\"99%\">$Input</td></tr>\r\n";
                $Input = MakeHtmlInputRadio("Active", "1", ($ContentRow[5] == 1), !$AllowUpdate, $TabIndex++, "javascript:changeActiveRadioControl(this.form, parseInt(this.value, 10))", "Aktiv");
                echo "            <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\">$Input</td></tr>\r\n";
                $Input = MakeHtmlInputRadio("Active", "2", ($ContentRow[5] == 2), !$AllowUpdate, $TabIndex++, "javascript:changeActiveRadioControl(this.form, parseInt(this.value, 10))", "Aktiv i periode");
                echo "            <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\">$Input</td></tr>\r\n";
                $InputFrom = MakeHtmlInputText("ActiveFrom", $ContentRow[6], oswebdb_field_len($ContentResult, 6), oswebdb_field_len($ContentResult, 6), !$AllowUpdate || ($ContentRow[5] != 2), !$AllowUpdate || ($ContentRow[5] != 2), $TabIndex++, "javascript:return changeActiveFromEditControl(this.form)");
                $InputTo = MakeHtmlInputText("ActiveTo", $ContentRow[7], oswebdb_field_len($Result, 7), oswebdb_field_len($Result, 7), !$AllowUpdate || ($ContentRow[5] != 2), !$AllowUpdate || ($ContentRow[5] != 2), $TabIndex++, "javascript:return changeActiveToEditControl(this.form)");
                echo "            <tr><td width=\"1%\" align=\"right\" nowrap>Aktiv i perioden :</td><td width=\"99%\">$InputFrom&nbsp;til&nbsp;$InputTo</td></tr>\r\n";
                $Text = "";
              }
              if ($ContentTypeFields & 32)
              {
                $Options = "<option value=\"\"";
                if ($ContentRow[8] == "")
                  $Options = "$Options selected";
                $Options = "$Options>(Ingen)";
                $Pictures = get_files_in_path(get_system_path_pictures($SystemNo), $ContentRow[8], get_table_field_value($ContentTypeSystemNo, 5, $ContentTypeNo, "Text2"), get_system_days_pictures($SystemNo));
                $Options = "$Options$Pictures";
                $Input = MakeHtmlSelect("Picture", 0, 0, !$AllowUpdate, !$AllowUpdate, $TabIndex++, "javascript:changePictureListBoxControl(this.form, this.value)", $Options);
                echo "            <tr><td width=\"1%\" align=\"right\" nowrap>Billede :</td><td width=\"99%\">$Input</td></tr>\r\n";
                $Options = "<option value=\"\"";
                if ($ContentRow[9] == "")
                  $Options = "$Options selected";
                $Options = "$Options>(Ingen)";
                $Pictures = get_files_in_path(get_system_path_pictures($SystemNo), $ContentRow[9], get_table_field_value($ContentTypeSystemNo, 5, $ContentTypeNo, "Text2"), get_system_days_pictures($SystemNo));
                $Options = "$Options$Pictures";
                $Input = MakeHtmlSelect("PictureThumbnial", 0, 0, !$AllowUpdate || ($ContentRow[8] == ""), !$AllowUpdate || ($ContentRow[8] == ""), $TabIndex++, "", $Options);
                echo "            <tr><td width=\"1%\" align=\"right\" nowrap>Thumbnial :</td><td width=\"99%\">$Input</td></tr>\r\n";
                $InputLeft = MakeHtmlInputRadio("PictureXJustify", "1", ($ContentRow[10] == 1), !$AllowUpdate || ($ContentRow[8] == "") || ($ContentRow[10] == 0), $TabIndex++, "", "Venstre");
                $InputCenter = MakeHtmlInputRadio("PictureXJustify", "2", ($ContentRow[10] == 2), !$AllowUpdate || ($ContentRow[8] == "") || ($ContentRow[10] == 0), $TabIndex++, "", "Center");
                $InputRight = MakeHtmlInputRadio("PictureXJustify", "4", ($ContentRow[10] == 4), !$AllowUpdate || ($ContentRow[8] == "") || ($ContentRow[10] == 0), $TabIndex++, "", "Højre");
                echo "            <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\">$InputLeft&nbsp;$InputCenter&nbsp;$InputRight</td></tr>\r\n";
                $InputTop = MakeHtmlInputRadio("PictureYJustify", "1", ($ContentRow[11] == 1), !$AllowUpdate || ($ContentRow[8] == "") || ($ContentRow[11] == 0), $TabIndex++, "", "Top");
                $InputCenter = MakeHtmlInputRadio("PictureYJustify", "2", ($ContentRow[11] == 2), !$AllowUpdate || ($ContentRow[8] == "") || ($ContentRow[11] == 0), $TabIndex++, "", "Center");
                $InputBottom = MakeHtmlInputRadio("PictureYJustify", "4", ($ContentRow[11] == 4), !$AllowUpdate || ($ContentRow[8] == "") || ($ContentRow[11] == 0), $TabIndex++, "", "Bund");
                echo "            <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\">$InputTop&nbsp;$InputCenter&nbsp;$InputBottom</td></tr>\r\n";
              }
              if ($ContentTypeGroupNo > 0)
              {
                $Options = "<option value=\"0\"";
                if ($ContentRow[12] == 0)
                  $Options = "$Options selected";
                $Options = "$Options>(Ingen)";
                if ($GroupResult = oswebdb_query("SELECT No,Description FROM Systemtables WHERE SystemNo=$ContentTypeGroupSystemNo AND TableNo=$ContentTypeGroupNo ORDER BY Description"))
                {
                  while ($GroupRow = oswebdb_fetch_row($GroupResult))
                  {
                    $Options = "$Options<option value=\"$GroupRow[0]\"";
                    if ($ContentRow[12] == $GroupRow[0])
                      $Options = "$Options selected";
                    $Options = "$Options>$GroupRow[1]";
                  }
                  oswebdb_free_result($GroupResult);
                }
                $Input = MakeHtmlSelect("GroupNo", 0, 0, !$AllowInsert, !$AllowInsert, $TabIndex++, "", $Options);
                echo "            <tr><td width=\"1%\" align=\"right\" nowrap>Type :</td><td width=\"99%\">$Input</td></tr>\r\n";
              }
              echo "          </form>\r\n";
              echo "        </table></td></tr>\r\n";
              $RowNo++;
            }
            echo "      </table></td></tr>\r\n";
            oswebdb_free_result($ContentResult);
          }
          echo "    </table>\r\n";
          MakeHtmlPageBottom();
        }
        else if (isset($TypeNo) && isset($MenuNo))
          MakeHtmlPageReload("javascript:this.location = 'websites.php?TypeNo=$TypeNo&MenuNo=$MenuNo'; return true;");
        else if (isset($TypeNo))
          MakeHtmlPageReload("javascript:this.location = 'websites.php?TypeNo=$TypeNo'; return true;");
        else
          MakeHtmlPageReload("javascript:this.location = 'websites.php'; return true;");
        oswebdb_free_result($Result);
      }
      else if (isset($TypeNo) && $Result = oswebdb_query("SELECT TypeNo,MenuNo,Description FROM $WebsiteTableName WHERE SystemNo=$SystemNo AND TypeNo=$TypeNo ORDER BY MenuNo,Description"))
      {
        $TypeName = get_table_field_value($TypeSystemNo, 4, $TypeNo, "Description");
        $TypeFields = get_table_field_value($TypeSystemNo, 4, $TypeNo, "ShowFields");
        $TypeProperties = get_table_field_value($TypeSystemNo, 4, $TypeNo, "Properties");
        $TypeText = get_table_field_value($TypeSystemNo, 4, $TypeNo, "Text3");
        $WebsiteFields = get_website_fields();
        $WebsiteOrder = get_website_order($TypeFields);
        $TabIndex = 1;
        echo "<script language=\"JavaScript\">\r\n";
        echo "  function validateWebsite()\r\n";
        echo "  {\r\n";
        echo "    description = this.InsertForm.Description.value\r\n";
        echo "    if (description.length == 0)\r\n";
        echo "    {\r\n";
        echo "      alert('Beskrivelsen skal indtastes!')\r\n";
        echo "      return false\r\n";
        echo "    }\r\n";
        echo "    else\r\n";
        echo "      return true\r\n";
        echo "  }\r\n";
        echo "</script>\r\n";
        MakeHtmlPageTop($TypeName);
        echo "    <table border=\"0\" cellpadding=\"1\" cellspacing=\"1\" width=\"100%\">\r\n";
        echo "      <tr><td width=\"100%\" colspan=\"3\" valign=\"middle\"><h2><i>$TypeName</i></h2></td></tr>\r\n";
        if ($AllowInsert)
        {
          echo "      <form name=\"InsertForm\" action=\"websites.php\" method=\"post\" onSubmit=\"javascript:return validateWebsite()\">\r\n";
          echo "        <input type=\"hidden\" name=\"Insert\" value=\"Opret\">\r\n";
          echo "        <input type=\"hidden\" name=\"TypeNo\" value=\"$TypeNo\">\r\n";
          if ($TypeFields & 1)
          {
            $Options = "<option value=\"0\" selected>(Ingen)";
            if ($TableResult = oswebdb_query($MenuStatement))
            {
              while ($TableRow = oswebdb_fetch_row($TableResult))
                $Options = "$Options<option value=\"$TableRow[0]\">$TableRow[1]";
              oswebdb_free_result($TableResult);
            }
            $InputMenuNo = MakeHtmlSelect("MenuNo", 0, 0, !$AllowInsert, !$AllowInsert, $TabIndex++, "", $Options);
            $InputDescription = MakeHtmlInputText("Description", "", oswebdb_field_len($Result, 2), oswebdb_field_len($Result, 2), !$AllowInsert, !$AllowInsert, $TabIndex++, "");
            $InputSubmit = MakeHtmlInputSubmit("", "Opret", !$AllowInsert, $TabIndex++, "javascript:return validateWebsite()");
            echo "        <tr><td width=\"1%\" align=\"right\" nowrap><strong>Menu / Beskrivelse :</strong></td><td width=\"99%\" colspan=\"2\">$InputMenuNo&nbsp;/&nbsp;$InputDescription&nbsp;$InputSubmit</td></tr>\r\n";
          }
          else
          {
            echo "        <input type=\"hidden\" name=\"MenuNo\" value=\"0\">\r\n";
            $InputDescription = MakeHtmlInputText("Description", "", oswebdb_field_len($Result, 2), oswebdb_field_len($Result, 2), !$AllowInsert, !$AllowInsert, $TabIndex++, "");
            $InputSubmit = MakeHtmlInputSubmit("", "Opret", !$AllowInsert, $TabIndex++, "javascript:return validateWebsite()");
            echo "        <tr><td width=\"1%\" align=\"right\" nowrap><strong>Beskrivelse :</strong></td><td width=\"99%\" colspan=\"2\">$InputDescription&nbsp;$InputSubmit</td></tr>\r\n";
          }
          echo "      </form>\r\n";
        }
        if ($TypeFields & 1)
        {
          if ($MenuResult = oswebdb_query($MenuStatement))
          {
            while ($MenuRow = oswebdb_fetch_row($MenuResult))
            {
              $Header = 0;
              if ($WebsiteResult = oswebdb_query("SELECT $WebsiteFields FROM $WebsiteTableName WHERE SystemNo=$SystemNo AND TypeNo=$TypeNo AND MenuNo=$MenuRow[0] ORDER BY $WebsiteOrder"))
              {
                while ($WebsiteRow = oswebdb_fetch_row($WebsiteResult))
                {
                  if (use_website($TypeFields, $WebsiteRow[4], $Username, $Password, 1, NULL, NULL, $WebsiteRow[8], 1))
                  {
                    if (!$Header)
                    {
                      echo "      <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\" colspan=\"2\"><strong>$MenuRow[1]:</strong></td></tr>\r\n";
                      $Header = 1;
                    }
                    if ($AllowUpdate)
                      $Input = MakeHtmlInputSubmit("", "Rediger", !$AllowUpdate, $TabIndex++, "");
                    else
                      $Input = MakeHtmlInputSubmit("", "Vis", !$AllowSelect, $TabIndex++, "");
                    $WebsiteDescription = get_website_description($TypeFields, $TypeProperties, $WebsiteRow[2], $WebsiteRow[3], $TypeText);
                    $WebsiteProperties = get_website_properties($TypeFields, $WebsiteRow[4], $WebsiteRow[5], $WebsiteRow[6], $WebsiteRow[7], $WebsiteRow[8], $WebsiteRow[15]);
                    echo "      <form action=\"websites.php\" method=\"post\"><input type=\"hidden\" name=\"TypeNo\" value=\"$WebsiteRow[0]\"><input type=\"hidden\" name=\"MenuNo\" value=\"$WebsiteRow[1]\"><input type=\"hidden\" name=\"Description\" value=\"$WebsiteRow[2]\"><tr><td width=\"1%\" align=\"right\" nowrap>$Input</td><td nowrap>$WebsiteDescription</td><td nowrap>$WebsiteProperties</td></tr></form>\r\n";
                  }
                }
                oswebdb_free_result($WebsiteResult);
              }
            }
            oswebdb_free_result($MenuResult);
          }
          $Header = 0;
          if ($WebsiteResult = oswebdb_query("SELECT $WebsiteFields FROM $WebsiteTableName WHERE SystemNo=$SystemNo AND TypeNo=$TypeNo AND MenuNo=0 ORDER BY $WebsiteOrder"))
          {
            while ($WebsiteRow = oswebdb_fetch_row($WebsiteResult))
            {
              if (use_website($TypeFields, $WebsiteRow[4], $Username, $Password, 1, NULL, NULL, $WebsiteRow[8], 1))
              {
                if (!$Header)
                {
                  echo "      <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\" colspan=\"2\"><strong>(Ingen):</strong></td></tr>\r\n";
                  $Header = 1;
                }
                if ($AllowUpdate)
                  $Input = MakeHtmlInputSubmit("", "Rediger", !$AllowUpdate, $TabIndex++, "");
                else
                  $Input = MakeHtmlInputSubmit("", "Vis", !$AllowSelect, $TabIndex++, "");
                $WebsiteDescription = get_website_description($TypeFields, $TypeProperties, $WebsiteRow[2], $WebsiteRow[3], $TypeText);
                $WebsiteProperties = get_website_properties($TypeFields, $WebsiteRow[4], $WebsiteRow[5], $WebsiteRow[6], $WebsiteRow[7], $WebsiteRow[8], $WebsiteRow[15]);
                echo "      <form action=\"websites.php\" method=\"post\"><input type=\"hidden\" name=\"TypeNo\" value=\"$WebsiteRow[0]\"><input type=\"hidden\" name=\"MenuNo\" value=\"$WebsiteRow[1]\"><input type=\"hidden\" name=\"Description\" value=\"$WebsiteRow[2]\"><tr><td width=\"1%\" align=\"right\" nowrap>$Input</td><td nowrap>$WebsiteDescription</td><td nowrap>$WebsiteProperties</td></tr></form>\r\n";
              }
            }
            oswebdb_free_result($WebsiteResult);
          }
        }
        else if ($WebsiteResult = oswebdb_query("SELECT $WebsiteFields FROM $WebsiteTableName WHERE SystemNo=$SystemNo AND TypeNo=$TypeNo AND MenuNo=0 ORDER BY $WebsiteOrder"))
        {
          while ($WebsiteRow = oswebdb_fetch_row($WebsiteResult))
          {
            if (use_website($TypeFields, $WebsiteRow[4], $Username, $Password, 1, NULL, NULL, $WebsiteRow[8], 1))
            {
              if ($AllowUpdate)
                $Input = MakeHtmlInputSubmit("", "Rediger", !$AllowUpdate, $TabIndex++, "");
              else
                $Input = MakeHtmlInputSubmit("", "Vis", !$AllowSelect, $TabIndex++, "");
              $WebsiteDescription = get_website_description($TypeFields, $TypeProperties, $WebsiteRow[2], $WebsiteRow[3], $TypeText);
              $WebsiteProperties = get_website_properties($TypeFields, $WebsiteRow[4], $WebsiteRow[5], $WebsiteRow[6], $WebsiteRow[7], $WebsiteRow[8], $WebsiteRow[15]);
              echo "      <form action=\"websites.php\" method=\"post\"><input type=\"hidden\" name=\"TypeNo\" value=\"$WebsiteRow[0]\"><input type=\"hidden\" name=\"MenuNo\" value=\"$WebsiteRow[1]\"><input type=\"hidden\" name=\"Description\" value=\"$WebsiteRow[2]\"><tr><td width=\"1%\" align=\"right\" nowrap>$Input</td><td nowrap>$WebsiteDescription</td><td nowrap>$WebsiteProperties</td></tr></form>\r\n";
            }
          }
          oswebdb_free_result($WebsiteResult);
        }
        echo "    </table>\r\n";
        MakeHtmlPageBottom();
        oswebdb_free_result($Result);
      }
      else if ($TypeResult = oswebdb_query("SELECT No,Description FROM Systemtables WHERE SystemNo=$TypeSystemNo AND TableNo=4 ORDER BY Description,No"))
      {
        $TabIndex = 1;
        MakeHtmlPageTop("Sider");
        echo "    <table border=\"0\" cellpadding=\"1\" cellspacing=\"1\" width=\"100%\">\r\n";
        echo "      <tr><td width=\"100%\" colspan=\"2\" valign=\"middle\"><h2><i>Sider</i></h2></td></tr>\r\n";
        while ($TypeRow = oswebdb_fetch_row($TypeResult))
        {
          if ($AllowUpdate)
            $Input = MakeHtmlInputSubmit("", "Redigér", !$AllowUpdate, $TabIndex++, "");
          else
            $Input = MakeHtmlInputSubmit("", "Vis", 0, $TabIndex++, "");
          echo "      <form action=\"websites.php\" method=\"post\"><input type=\"hidden\" name=\"TypeNo\" value=\"$TypeRow[0]\"><tr><td width=\"1%\" valign=\"middle\" align=\"right\" nowrap>$Input</td><td width=\"99%\" valign=\"middle\">$TypeRow[1]</td></tr></form>\r\n";
        }
        echo "    </table>\r\n";
        MakeHtmlPageBottom();
        oswebdb_free_result($TypeResult);
      }
    }
    oswebdb_close();
  }

  function get_files_in_path($PathName, $Selected, $Extensions, $YoungerThan)
  {
    $Result = "";
    if (strlen($Extensions) > 0)
    {
      if ($Path = opendir($PathName))
      {
        while (($Name = readdir($Path)) != false)
        {
          if (is_dir("$PathName$Name") && $Name != "." && $Name != "..")
          {
            $Value = get_files_in_path("$PathName$Name/", $Selected, $Extensions, $YoungerThan);
            $Result = "$Result$Value";
          }
          else if (is_file("$PathName$Name"))
          {
            $Extensions = explode("/", $Extensions);
            if (count($Extensions) > 0)
            {
              $Value = "$PathName$Name";
              if (substr($Value, 0, 2) == "./")
                $Value = substr($Value, 2, strlen($Value) - 2);
              for ($i = 0; $i < count($Extensions); $i++)
              {
                $FileExtension = ""; $p = strrpos($Value, '.');
                if ($p != false)
                {
                	$FileExtension = strtolower(substr($Value, $p, strlen($Value) - (p + 1)));
                }
                if (strlen($Extensions[$i]) > 0 && strcmp($FileExtension, strtolower($Extensions[$i])) == 0)
                {
                	$FileDate = date("Ymd", filectime($Value));
                	if ($Value == $Selected || strcmp($FileDate, date("Ymd", time() - ($YoungerThan * 24 * 60 * 60))) >= 0)
                	{
                    $Result = "$Result<option value=\"$Value\"";
                    if ($Value == $Selected)
                      $Result = "$Result selected";
                    $Result = "$Result>$Value";
                  }
                }
              }
            }
            $Extensions = implode("/", $Extensions);
          }
        }
        closedir($Path);
      }
    }
    return $Result;
  }
?>
