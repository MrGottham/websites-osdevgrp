<?php
  // Include required files.
  require_once("misc.php");
  require_once("oswebdb/oswebdb.php");
  require_once("oswebdb/allowanc.php");
  require_once("oswebdb/distance.php");
  require_once("oswebdb/zipcodes.php");
  require_once("oswebdb/systems.php");
  require_once("oswebdb/tables.php");

  // Set error reporting.
  error_reporting((int) GetConfigValue("ErrorReporting"));

  $AllowanceTableName = "Allowances";
  $AllowanceLineTableName = "Allowancelines";
  $SystemNo = GetConfigValue("SystemNo");
  if (isset($_GET["Fun"]))
    $Fun = $_GET["Fun"];
  else if (isset($_POST["Fun"]))
    $Fun = $_POST["Fun"];
  if (isset($_GET["Year"]))
    $Year = $_GET["Year"];
  else if (isset($_POST["Year"]))
    $Year = $_POST["Year"];
  if (isset($_GET["No"]))
    $No = $_GET["No"];
  else if (isset($_POST["No"]))
    $No = $_POST["No"];
  if (oswebdb_authorize(oswebdb_getusername(), oswebdb_getpassword()))
  {
    if (oswebdb_selectdb())
    {
      switch ($Fun)
      {
        case 1:
          MakeListOfAllowances($AllowanceTableName, $AllowanceLineTableName, $SystemNo);
          break;

        case 2:
          MakeAllowanceFrames($AllowanceTableName, $AllowanceLineTableName, $SystemNo, $Fun, (integer) (isset($_POST["NewAllowance"])), $Year, $No);
          break;

        case 3:
          MakeAllowancePrint($AllowanceTableName, $AllowanceLineTableName, $SystemNo, $Fun, (integer) (isset($_POST["NewAllowance"])), $Year, $No);
          break;

        case 21:
          MakeAllowanceToolbar($AllowanceTableName, $AllowanceLineTableName, $SystemNo);
          break;

        case 22:
          MakeAllowanceContent($AllowanceTableName, $AllowanceLineTableName, $SystemNo, $Fun, (integer) $_GET["NewAllowance"], $Year, $No);
          break;

        case 23:
          MakeAllowanceLines($AllowanceTableName, $AllowanceLineTableName, $SystemNo, $Fun, (integer) $_GET["NewAllowance"], $Year, $No);
          break;

        default:
          MakeListOfAllowances($AllowanceTableName, $AllowanceLineTableName, $SystemNo);
          break;
      }
    }
    oswebdb_close();
  }

  function MakeListOfAllowances($AllowanceTableName, $AllowanceLineTableName, $SystemNo)
  {
    $AllowSelect = oswebdb_allow_select($AllowanceTableName) && oswebdb_allow_select($AllowanceLineTableName);
    $AllowInsert = oswebdb_allow_insert($AllowanceTableName) && oswebdb_allow_insert($AllowanceLineTableName);
    $AllowUpdate = oswebdb_allow_update($AllowanceTableName) && oswebdb_allow_update($AllowanceLineTableName);
    $AllowDelete = oswebdb_allow_delete($AllowanceTableName) && oswebdb_allow_delete($AllowanceLineTableName);
    if ($AllowanceResult = oswebdb_query("SELECT a.Year,a.No,a.Description FROM $AllowanceTableName AS a WHERE a.SystemNo=$SystemNo ORDER BY a.Year DESC,a.Description,a.No"))
    {
      $TabIndex = 1; $Header = 0;
      MakeHtmlPageTop("Kørselsfradrag");
      echo "    <table border=\"0\" cellpadding=\"1\" cellspacing=\"1\" width=\"100%\">\r\n";
      echo "      <tr><td width=\"100%\" colspan=\"5\" valign=\"middle\"><h2><i>Kørselsfradrag</i></h2></td></tr>\r\n";
      if ($AllowInsert)
      {
        echo "      <form action=\"allowanc.php\" method=\"post\">\r\n";
        echo "        <input type=\"hidden\" name=\"Fun\" value=\"2\">\r\n";
        $Input = MakeHtmlInputSubmit("NewAllowance", "Opret", !$AllowInsert, $TabIndex++, "");
        echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\" colspan=\"4\">$Input</td></tr>\r\n";
        echo "      </form>\r\n";
      }
      while ($AllowanceRow = oswebdb_fetch_row($AllowanceResult))
      {
        if (!$Header)
        {
          echo "      <tr><td width=\"1%\" align=\"right\" nowrap></td><td nowrap><strong>År</strong></td><td nowrap><strong>Beskrivelse</strong></td><td align=\"right\" nowrap><strong>Distance</strong></td><td align=\"right\" nowrap><strong>Fradrag</strong></td></tr>\r\n";
          $Header = 1;
        }
        if ($AllowUpdate)
          $Input = MakeHtmlInputSubmit("", "Redigér", !$AllowUpdate, $TabIndex++, "");
        else
          $Input = MakeHtmlInputSubmit("", "Vis", 0, $TabIndex++, "");
        $Distance = FormatNumber((integer) get_distance_for_allowance($SystemNo, $AllowanceRow[0], $AllowanceRow[1]), 0, 1);
        $Allowance = FormatNumber((float) get_allowance($SystemNo, $AllowanceRow[0], $AllowanceRow[1]), 2, 1);
        echo "      <form action=\"allowanc.php\" method=\"post\"><input type=\"hidden\" name=\"Fun\" value=\"2\"><input type=\"hidden\" name=\"Year\" value=\"$AllowanceRow[0]\"><input type=\"hidden\" name=\"No\" value=\"$AllowanceRow[1]\"><tr><td width=\"1%\" align=\"right\" nowrap>$Input</td><td nowrap>$AllowanceRow[0]</td><td nowrap>$AllowanceRow[2]</td><td align=\"right\" nowrap>$Distance&nbsp;km</td><td align=\"right\" nowrap>$Allowance&nbsp;kr</td></tr></form>\r\n";
      }
      echo "    </table>\r\n";
      MakeHtmlPageBottom();
      oswebdb_free_result($AllowanceResult);
    }
  }

  function MakeAllowanceFrames($AllowanceTableName, $AllowanceLineTableName, $SystemNo, $Fun, $NewAllowance, $Year, $No)
  {
    $Parameters = "";
    if (isset($Year))
      $Parameters = "$Parameters&Year=$Year";
    if (isset($No))
      $Parameters = "$Parameters&No=$No";
    echo "<script language=\"JavaScript\">\r\n";
    echo "  function validateAllowance()\r\n";
    echo "  {\r\n";
    echo "    year = this.frameContent.AllowanceForm.Year.value\r\n";
    echo "    no = this.frameContent.AllowanceForm.No.value\r\n";
    echo "    description = this.frameContent.AllowanceForm.Description.value\r\n";
    echo "    if (!(parseInt(year, 10) > 0))\r\n";
    echo "    {\r\n";
    echo "      alert('Årstallet skal være større end 0!')\r\n";
    echo "      return false\r\n";
    echo "    }\r\n";
    echo "    else if (!(parseInt(no, 10) > 0))\r\n";
    echo "    {\r\n";
    echo "      alert('Nummeret skal være større end 0!')\r\n";
    echo "      return false\r\n";
    echo "    }\r\n";
    echo "    else if (description.length == 0)\r\n";
    echo "    {\r\n";
    echo "      alert('Beskrivelsen skal indtastes!')\r\n";
    echo "      return false\r\n";
    echo "    }\r\n";
    for ($i = 1, $j = 0; $i <= 5; $i++, $j++)
    {
      if ($i == 1)
        echo "    else if (!validateAllowanceSchema(this.frameContent.AllowanceForm.Distance$i, this.frameContent.AllowanceForm.Allowance$i, null))\r\n";
      else
        echo "    else if (!validateAllowanceSchema(this.frameContent.AllowanceForm.Distance$i, this.frameContent.AllowanceForm.Allowance$i, this.frameContent.AllowanceForm.Distance$j))\r\n";
      echo "      return false\r\n";
    }
    for ($i = 1; $i <= 2; $i++)
    {
      echo "    else if (!validateAdditionSchema(this.frameContent.AllowanceForm.Addition$i))\r\n";
      echo "      return false\r\n";
    }
    echo "    else\r\n";
    echo "      return true\r\n";
    echo "  }\r\n\r\n";
    echo "  function validateAllowanceSchema(distanceCtrl, allowanceCtrl, prevDistanceCtrl)\r\n";
    echo "  {\r\n";
    echo "    if (parseInt(distanceCtrl.value, 10) < 0)\r\n";
    echo "    {\r\n";
    echo "      alert('Distancen må ikke være mindre end 0!')\r\n";
    echo "      return false\r\n";
    echo "    }\r\n";
    echo "    else if (parseInt(allowanceCtrl.value, 10) < 0)\r\n";
    echo "    {\r\n";
    echo "      alert('Fradraget må ikke være mindre end 0!')\r\n";
    echo "      return false\r\n";
    echo "    }\r\n";
    echo "    else if (parseInt(distanceCtrl.value, 10) > 0 && prevDistanceCtrl)\r\n";
    echo "    {\r\n";
    echo "      if (parseInt(distanceCtrl.value, 10) < parseInt(prevDistanceCtrl.value, 10))\r\n";
    echo "      {\r\n";
    echo "        alert('Distancen må ikke være mindre end den forrige distance!')\r\n";
    echo "        return false\r\n";
    echo "      }\r\n";
    echo "      else\r\n";
    echo "        return true\r\n";
    echo "    }\r\n";
    echo "    else\r\n";
    echo "      return true\r\n";
    echo "  }\r\n\r\n";
    echo "  function validateAdditionSchema(additionCtrl)\r\n";
    echo "  {\r\n";
    echo "    if (parseInt(additionCtrl.value, 10) < 0)\r\n";
    echo "    {\r\n";
    echo "      alert('Tillæget må ikke være mindre end 0!')\r\n";
    echo "      return false\r\n";
    echo "    }\r\n";
    echo "    else\r\n";
    echo "      return true\r\n";
    echo "  }\r\n\r\n";
    echo "  function resetAllowance(distance1, distance2, distance3, distance4, distance5)\r\n";
    echo "  {\r\n";
    for ($i = 1, $j = 0; $i <= 5; $i++, $j++)
    {
      if ($i == 1)
        echo "    this.frameContent.AllowanceForm.Distance$i.readOnly = false\r\n";
      else
        echo "    this.frameContent.AllowanceForm.Distance$i.readOnly = (distance$j == 0)\r\n";
      echo "    this.frameContent.AllowanceForm.Allowance$i.readOnly = (distance$i == 0)\r\n";
    }
    echo "    return true\r\n";
    echo "  }\r\n\r\n";
    echo "  function validateAllowanceLine()\r\n";
    echo "  {\r\n";
    echo "    year = this.frameContent.AllowanceLineForm.Year.value\r\n";
    echo "    no = this.frameContent.AllowanceLineForm.No.value\r\n";
    echo "    date = this.frameContent.AllowanceLineForm.Date.value\r\n";
    echo "    fromCountryCodeSelected = false\r\n";
    echo "    for (i = 0; i < this.frameContent.AllowanceLineForm.FromCountryCode.length && !fromCountryCodeSelected; i++)\r\n";
    echo "      fromCountryCodeSelected = this.frameContent.AllowanceLineForm.FromCountryCode.options[i].selected\r\n";
    echo "    fromZipCode = this.frameContent.AllowanceLineForm.FromZipCode.value\r\n";
    echo "    fromCityName = this.frameContent.AllowanceLineForm.FromCityName.value\r\n";
    echo "    toCountryCodeSelected = false\r\n";
    echo "    for (i = 0; i < this.frameContent.AllowanceLineForm.ToCountryCode.length && !toCountryCodeSelected; i++)\r\n";
    echo "      toCountryCodeSelected = this.frameContent.AllowanceLineForm.ToCountryCode.options[i].selected\r\n";
    echo "    toZipCode = this.frameContent.AllowanceLineForm.ToZipCode.value\r\n";
    echo "    toCityName = this.frameContent.AllowanceLineForm.ToCityName.value\r\n";
    echo "    if (!(parseInt(year, 10) > 0))\r\n";
    echo "    {\r\n";
    echo "      alert('Årstallet skal være større end 0!')\r\n";
    echo "      return false\r\n";
    echo "    }\r\n";
    echo "    else if (!(parseInt(no, 10) > 0))\r\n";
    echo "    {\r\n";
    echo "      alert('Nummeret skal være større end 0!')\r\n";
    echo "      return false\r\n";
    echo "    }\r\n";
    echo "    else if (!parent.validateDate(parseInt(date.substr(0, 4), 10), parseInt(date.substr(5, 2), 10), parseInt(date.substr(8, 2), 10)))\r\n";
    echo "      return false\r\n";
    echo "    else if (!fromCountryCodeSelected)\r\n";
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
    echo "    else if (!(parseInt(this.frameContent.AllowanceLineForm.Distance.value, 10) > 0))\r\n";
    echo "    {\r\n";
    echo "      alert('Distancen mellem de to steder eksisterer ikke!')\r\n";
    echo "      return false\r\n";
    echo "    }\r\n";
    echo "    else\r\n";
    echo "     return true\r\n";
    echo "  }\r\n\r\n";
    echo "  function resetAllowanceLine()\r\n";
    echo "  {\r\n";
    $DefaultCountryCode = (integer) get_system_country_code($SystemNo);
    echo "    parent.getZipCodeLengths($DefaultCountryCode, this.frameContent.AllowanceLineForm.FromZipCode, this.frameContent.AllowanceLineForm.FromCityName, $DefaultCountryCode, this.frameContent.AllowanceLineForm.ToZipCode, this.frameContent.AllowanceLineForm.ToCityName)\r\n";
    echo "    return true\r\n";
    echo "  }\r\n\r\n";
    echo "  function changeDistanceEditControl(no)\r\n";
    echo "  {\r\n";
    echo "    result = false\r\n";
    echo "    switch (no)\r\n";
    echo "    {\r\n";
    echo "      case 1:\r\n";
    echo "        result = changingDistanceEditControl(no, this.frameContent.AllowanceForm.Distance1, this.frameContent.AllowanceForm.Allowance1, this.frameContent.AllowanceForm.Distance2)\r\n";
    echo "        break\r\n\r\n";
    echo "      case 2:\r\n";
    echo "        result = changingDistanceEditControl(no, this.frameContent.AllowanceForm.Distance2, this.frameContent.AllowanceForm.Allowance2, this.frameContent.AllowanceForm.Distance3)\r\n";
    echo "        break\r\n\r\n";
    echo "      case 3:\r\n";
    echo "        result = changingDistanceEditControl(no, this.frameContent.AllowanceForm.Distance3, this.frameContent.AllowanceForm.Allowance3, this.frameContent.AllowanceForm.Distance4)\r\n";
    echo "        break\r\n\r\n";
    echo "      case 4:\r\n";
    echo "        result = changingDistanceEditControl(no, this.frameContent.AllowanceForm.Distance4, this.frameContent.AllowanceForm.Allowance4, this.frameContent.AllowanceForm.Distance5)\r\n";
    echo "        break\r\n\r\n";
    echo "      case 5:\r\n";
    echo "        result = changingDistanceEditControl(no, this.frameContent.AllowanceForm.Distance5, this.frameContent.AllowanceForm.Allowance5, null)\r\n";
    echo "        break\r\n";
    echo "    }\r\n";
    echo "    return result\r\n";
    echo "  }\r\n\r\n";
    echo "  function changeAllowanceEditControl(no)\r\n";
    echo "  {\r\n";
    echo "    result = false\r\n";
    echo "    switch (no)\r\n";
    echo "    {\r\n";
    echo "      case 1:\r\n";
    echo "        result = changingAllowanceEditControl(this.frameContent.AllowanceForm.Allowance1)\r\n";
    echo "        break\r\n\r\n";
    echo "      case 2:\r\n";
    echo "        result = changingAllowanceEditControl(this.frameContent.AllowanceForm.Allowance2)\r\n";
    echo "        break\r\n\r\n";
    echo "      case 3:\r\n";
    echo "        result = changingAllowanceEditControl(this.frameContent.AllowanceForm.Allowance3)\r\n";
    echo "        break\r\n\r\n";
    echo "      case 4:\r\n";
    echo "        result = changingAllowanceEditControl(this.frameContent.AllowanceForm.Allowance4)\r\n";
    echo "        break\r\n\r\n";
    echo "      case 5:\r\n";
    echo "        result = changingAllowanceEditControl(this.frameContent.AllowanceForm.Allowance5)\r\n";
    echo "        break\r\n";
    echo "    }\r\n";
    echo "    return result\r\n";
    echo "  }\r\n\r\n";
    echo "  function changeAdditionEditControl(no)\r\n";
    echo "  {\r\n";
    echo "    result = false\r\n";
    echo "    switch (no)\r\n";
    echo "    {\r\n";
    echo "      case 1:\r\n";
    echo "        result = changingAdditionEditControl(this.frameContent.AllowanceForm.Addition1)\r\n";
    echo "        break\r\n\r\n";
    echo "      case 2:\r\n";
    echo "        result = changingAdditionEditControl(this.frameContent.AllowanceForm.Addition2)\r\n";
    echo "        break\r\n";
    echo "    }\r\n";
    echo "    return result\r\n";
    echo "  }\r\n\r\n";
    echo "  function changeDateEditControl()\r\n";
    echo "  {\r\n";
    echo "    s = this.frameContent.AllowanceLineForm.Date.value\r\n";
    echo "    if (s.length == 8)\r\n";
    echo "    {\r\n";
    echo "      s = s.substr(0, 4) + '-' + s.substr(4, 2) + '-' + s.substr(6, 2)\r\n";
    echo "      this.frameContent.AllowanceLineForm.Date.value = s\r\n";
    echo "    }\r\n";
    echo "    if (parent.validateDate(parseInt(s.substr(0, 4), 10), parseInt(s.substr(5, 2), 10), parseInt(s.substr(8, 2), 10)))\r\n";
    echo "    {\r\n";
    echo "      parent.getDistanceAndAllowanceForDate(parseInt(this.frameContent.AllowanceLineForm.Year.value, 10), parseInt(this.frameContent.AllowanceLineForm.No.value, 10), s, this.frameContent.AllowanceLineForm.DateDistance, this.frameContent.AllowanceLineForm.DateAllowance)\r\n";
    echo "      return true\r\n";
    echo "    }\r\n";
    echo "    else\r\n";
    echo "      return false\r\n";
    echo "  }\r\n\r\n";
    echo "  function changeFromCountrySelectControl()\r\n";
    echo "  {\r\n";
    echo "    this.frameContent.AllowanceLineForm.Distance.value = ''\r\n";
    echo "    this.frameContent.AllowanceLineForm.Property1.checked = false\r\n";
    echo "    this.frameContent.AllowanceLineForm.Property2.checked = false\r\n";
    echo "    parent.getZipCodeLength(this.frameContent.AllowanceLineForm.FromCountryCode.value, this.frameContent.AllowanceLineForm.FromZipCode, this.frameContent.AllowanceLineForm.FromCityName)\r\n";
    echo "  }\r\n\r\n";
    echo "  function changeFromZipCodeEditControl()\r\n";
    echo "  {\r\n";
    echo "    parent.getCityNameAndDistance(this.frameContent.AllowanceLineForm.FromCountryCode.value, this.frameContent.AllowanceLineForm.FromZipCode.value, this.frameContent.AllowanceLineForm.FromCityName, this.frameContent.AllowanceLineForm.ToCountryCode.value, this.frameContent.AllowanceLineForm.ToZipCode.value, this.frameContent.AllowanceLineForm.Distance, this.frameContent.AllowanceLineForm.Property1, this.frameContent.AllowanceLineForm.Property2)\r\n";
    echo "  }\r\n\r\n";
    echo "  function changeToCountrySelectControl()\r\n";
    echo "  {\r\n";
    echo "    this.frameContent.AllowanceLineForm.Distance.value = ''\r\n";
    echo "    this.frameContent.AllowanceLineForm.Property1.checked = false\r\n";
    echo "    this.frameContent.AllowanceLineForm.Property2.checked = false\r\n";
    echo "    parent.getZipCodeLength(this.frameContent.AllowanceLineForm.ToCountryCode.value, this.frameContent.AllowanceLineForm.ToZipCode, this.frameContent.AllowanceLineForm.ToCityName)\r\n";
    echo "  }\r\n\r\n";
    echo "  function changeToZipCodeEditControl()\r\n";
    echo "  {\r\n";
    echo "    parent.getCityNameAndDistance(this.frameContent.AllowanceLineForm.ToCountryCode.value, this.frameContent.AllowanceLineForm.ToZipCode.value, this.frameContent.AllowanceLineForm.ToCityName, this.frameContent.AllowanceLineForm.FromCountryCode.value, this.frameContent.AllowanceLineForm.FromZipCode.value, this.frameContent.AllowanceLineForm.Distance, this.frameContent.AllowanceLineForm.Property1, this.frameContent.AllowanceLineForm.Property2)\r\n";
    echo "  }\r\n\r\n";
    echo "  function changingDistanceEditControl(no, distanceCtrl, allowanceCtrl, nextDistanceCtrl)\r\n";
    echo "  {\r\n";
    echo "    result = true\r\n";
    echo "    if (parseInt(distanceCtrl.value, 10) > 0)\r\n";
    echo "    {\r\n";
    echo "      allowanceCtrl.readOnly = false\r\n";
    echo "      if (nextDistanceCtrl)\r\n";
    echo "        nextDistanceCtrl.readOnly = false\r\n";
    echo "    }\r\n";
    echo "    else\r\n";
    echo "    {\r\n";
    echo "      distanceCtrl.value = ''\r\n";
    echo "      allowanceCtrl.value = ''\r\n";
    echo "      allowanceCtrl.readOnly = true\r\n";
    echo "      if (nextDistanceCtrl)\r\n";
    echo "      {\r\n";
    echo "        nextDistanceCtrl.value = ''\r\n";
    echo "        nextDistanceCtrl.readOnly = true\r\n";
    echo "        result = changeDistanceEditControl(no + 1)\r\n";
    echo "      }\r\n";
    echo "    }\r\n";
    echo "    return result\r\n";
    echo "  }\r\n\r\n";
    echo "  function changingAllowanceEditControl(allowanceCtrl)\r\n";
    echo "  {\r\n";
    echo "    if (!(parseInt(allowanceCtrl.value, 10) > 0))\r\n";
    echo "      allowanceCtrl.value = ''\r\n";
    echo "    return true\r\n";
    echo "  }\r\n\r\n";
    echo "  function changingAdditionEditControl(additionCtrl)\r\n";
    echo "  {\r\n";
    echo "    if (!(parseInt(additionCtrl.value, 10) > 0))\r\n";
    echo "      additionCtrl.value = ''\r\n";
    echo "    return true\r\n";
    echo "  }\r\n\r\n";
    echo "  function printAllowance()\r\n";
    echo "  {\r\n";
    echo "    if (this.frameContent.AllowanceForm.Year.type == 'hidden' && this.frameContent.AllowanceForm.No.type == 'hidden')\r\n";
    echo "    {\r\n";
    echo "      printWindow = window.open('', '', 'width=600,height=470,scrollbars')\r\n";
    echo "      if (printWindow)\r\n";
    echo "        printWindow.location = 'allowanc.php?Fun=3&Year=' + this.frameContent.AllowanceForm.Year.value + '&No=' + this.frameContent.AllowanceForm.No.value\r\n";
    echo "    }\r\n";
    echo "  }\r\n\r\n";
    echo "  function showAllowances()\r\n";
    echo "  {\r\n";
    echo "    this.location = 'allowanc.php?Fun=1'\r\n";
    echo "  }\r\n\r\n";
    echo "  function updateContent(year, no)\r\n";
    echo "  {\r\n";
    echo "    this.frameContent.location = 'allowanc.php?Fun=22&NewAllowance=0&Year=' + year + '&No=' + no\r\n";
    echo "  }\r\n\r\n";
    echo "  function getChildFrame(method)\r\n";
    echo "  {\r\n";
    echo "    return this.frameContent.name\r\n";
    echo "  }\r\n";
    echo "</script>\r\n";
    echo "<html>\r\n";
    echo "  <head>\r\n";
    echo "    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=windows-1252\">\r\n";
    echo "    <title>Kalender</title>\r\n";
    echo "  </head>\r\n";
    echo "  <frameset rows=\"65,*\" frameborder=\"0\" framespacing=\"0\" border=\"0\">\r\n";
    $NewFun = ($Fun * 10) + 1;
    echo "    <frame name=\"frameToolbar\" scrolling=\"no\" noresize src=\"allowanc.php?Fun=$NewFun\">\r\n";
    $NewFun = ($Fun * 10) + 2;
    echo "    <frame name=\"frameContent\" scrolling=\"auto\" noresize src=\"allowanc.php?Fun=$NewFun&NewAllowance=$NewAllowance$Parameters\">\r\n";
    echo "  </frameset>\r\n";
    echo "</html>\r\n";
  }

  function MakeAllowanceToolbar($AllowanceTableName, $AllowanceLineTableName, $SystemNo)
  {
    $AllowSelect = oswebdb_allow_select($AllowanceTableName) && oswebdb_allow_select($AllowanceLineTableName);
    $AllowInsert = oswebdb_allow_insert($AllowanceTableName) && oswebdb_allow_insert($AllowanceLineTableName);
    $AllowUpdate = oswebdb_allow_update($AllowanceTableName) && oswebdb_allow_update($AllowanceLineTableName);
    $AllowDelete = oswebdb_allow_delete($AllowanceTableName) && oswebdb_allow_delete($AllowanceLineTableName);
    $Print = "";
    if ($AllowSelect)
      $Print = MakeHtmlLink("", "javascript:parent.printAllowance()", "", "", "", "", "javascript:window.status='Udskriv kørselsfradrag'; return true;", "javascript:window.status=''; return true;", "Udskriv");
    MakeHtmlPageTop("Kørselsfradrag");
    echo "    <form name=\"AllowanceToolbar\" action=\"\">\r\n";
    echo "      <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">\r\n";
    echo "        <tr><td width=\"99%\" valign=\"middle\"><h2><i>Kørselsfradrag</i></h2></td><td width=\"1%\" valign=\"top\" align=\"rigth\" nowrap>$Print</td></tr>\r\n";
    echo "      </table>\r\n";
    echo "    </form>\r\n";
    MakeHtmlPageBottom();
  }

  function MakeAllowanceContent($AllowanceTableName, $AllowanceLineTableName, $SystemNo, $Fun, $NewAllowance, $Year, $No)
  {
    $AllowSelect = oswebdb_allow_select($AllowanceTableName) && oswebdb_allow_select($AllowanceLineTableName);
    $AllowInsert = oswebdb_allow_insert($AllowanceTableName) && oswebdb_allow_insert($AllowanceLineTableName);
    $AllowUpdate = oswebdb_allow_update($AllowanceTableName) && oswebdb_allow_update($AllowanceLineTableName);
    $AllowDelete = oswebdb_allow_delete($AllowanceTableName) && oswebdb_allow_delete($AllowanceLineTableName);
    if (isset($_POST["Insert"]) && isset($Year) && isset($No) && isset($_POST["Description"]))
    {
      if (!insert_allowance($SystemNo, $Year, $No, $_POST["Description"], $_POST["Distance1"], $_POST["Allowance1"], $_POST["Distance2"], $_POST["Allowance2"], $_POST["Distance3"], $_POST["Allowance3"], $_POST["Distance4"], $_POST["Allowance4"], $_POST["Distance5"], $_POST["Allowance5"], $_POST["Addition1"], $_POST["Addition2"]))
      {
        $Error = oswebdb_error();
        MakeHtmlPageTop("Kørselsfradrag");
        echo "    <h2><strong>$Error</strong></h2>\r\n";
        MakeHtmlPageBottom("");
        exit;
      }
    }
    else if (isset($_POST["Update"]) && isset($Year) && isset($No) && isset($_POST["Description"]))
    {
      if (!update_allowance($SystemNo, $Year, $No, $_POST["Description"], $_POST["Distance1"], $_POST["Allowance1"], $_POST["Distance2"], $_POST["Allowance2"], $_POST["Distance3"], $_POST["Allowance3"], $_POST["Distance4"], $_POST["Allowance4"], $_POST["Distance5"], $_POST["Allowance5"], $_POST["Addition1"], $_POST["Addition2"]))
      {
        $Error = oswebdb_error();
        MakeHtmlPageTop("Kørselsfradrag");
        echo "    <h2><strong>$Error</strong></h2>\r\n";
        MakeHtmlPageBottom("");
        exit;
      }
    }
    else if (isset($_POST["Delete"]) && isset($Year) && isset($No))
    {
      if (!delete_allowance($SystemNo, $Year, $No))
      {
        $Error = oswebdb_error();
        MakeHtmlPageTop("Kørselsfradrag");
        echo "    <h2><strong>$Error</strong></h2>\r\n";
        MakeHtmlPageBottom("");
        exit;
      }
      unset($Year);
      unset($No);
    }
    if ($NewAllowance)
      $Statement = "SELECT a.Year,a.No,a.Description,a.Distance1,a.Allowance1,a.Distance2,a.Allowance2,a.Distance3,a.Allowance3,a.Distance4,a.Allowance4,a.Distance5,a.Allowance5,a.Addition1,a.Addition2 FROM $AllowanceTableName AS a WHERE a.SystemNo=$SystemNo";
    else if (isset($Year) && isset($No))
      $Statement = "SELECT a.Year,a.No,a.Description,a.Distance1,a.Allowance1,a.Distance2,a.Allowance2,a.Distance3,a.Allowance3,a.Distance4,a.Allowance4,a.Distance5,a.Allowance5,a.Addition1,a.Addition2 FROM $AllowanceTableName AS a WHERE a.SystemNo=$SystemNo AND a.Year=$Year AND a.No=$No";
    if (($NewAllowance || (isset($Year) && isset($No))) && $AllowanceResult = oswebdb_query($Statement))
    {
      if ($NewAllowance || $AllowanceRow = oswebdb_fetch_row($AllowanceResult))
      {
        $TabIndex = 1;
        MakeHtmlPageTop("Kørselsfradrag");
        echo "    <table border=\"0\" cellpadding=\"1\" cellspacing=\"1\" width=\"100%\">\r\n";
        echo "      <form name=\"AllowanceForm\" action=\"allowanc.php\" method=\"post\">\r\n";
        echo "        <input type=\"hidden\" name=\"Fun\" value=\"$Fun\">\r\n";
        if (!$NewAllowance)
        {
          echo "        <input type=\"hidden\" name=\"Year\" value=\"$AllowanceRow[0]\">\r\n";
          echo "        <input type=\"hidden\" name=\"No\" value=\"$AllowanceRow[1]\">\r\n";
        }
        echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\" colspan=\"5\">";
        if ($NewAllowance)
        {
          if ($AllowInsert)
          {
            $InputSubmit = MakeHtmlInputSubmit("Insert", "Opret", !$AllowInsert, $TabIndex++, "javascript:return parent.validateAllowance()");
            $InputReset = MakeHtmlInputReset("Reset", "Fortryd", !$AllowInsert, $TabIndex++, "javascript:return parent.resetAllowance(0, 0, 0, 0, 0)");
            echo "$InputSubmit&nbsp;$InputReset&nbsp;";
          }
          if ($AllowSelect)
          {
            $InputShowAll = MakeHtmlInputSubmit("ShowAll", "Vis alle", !$AllowSelect, $TabIndex++, "javascript:parent.showAllowances()");
            echo "$InputShowAll&nbsp;";
          }
        }
        else
        {
          if ($AllowUpdate)
          {
            $InputSubmit = MakeHtmlInputSubmit("Update", "Opdatér", !$AllowUpdate, $TabIndex++, "javascript:return parent.validateAllowance()");
            $InputReset = MakeHtmlInputReset("Reset", "Fortryd", !$AllowUpdate, $TabIndex++, "javascript:return parent.resetAllowance($AllowanceRow[3], $AllowanceRow[5], $AllowanceRow[7], $AllowanceRow[9], $AllowanceRow[11])");
            echo "$InputSubmit&nbsp;$InputReset&nbsp;";
          }
          if ($AllowDelete)
          {
            $InputDelete = MakeHtmlInputSubmit("Delete", "Slet", !$AllowDelete, $TabIndex++, "javascript:return confirm('Bekræft sletning!')");
            echo "$InputDelete&nbsp;";
          }
          if ($AllowSelect)
          {
            $InputShowAll = MakeHtmlInputSubmit("ShowAll", "Vis alle", !$AllowSelect, $TabIndex++, "javascript:parent.showAllowances()");
            echo "$InputShowAll&nbsp;";
          }
        }
        echo "</td></tr>\r\n";
        if ($NewAllowance)
        {
          $NewYear = date("Y", time());
          $NewNo = "1";
          if ($NewNoResult = oswebdb_query("SELECT No FROM $AllowanceTableName WHERE SystemNo=$SystemNo AND Year=$NewYear ORDER BY No DESC"))
          {
            if ($NewNoRow = oswebdb_fetch_row($NewNoResult))
              $NewNo = $NewNoRow[0] + 1;
            oswebdb_free_result($NewNoResult);
          }
        }
        $Input = MakeHtmlInputText(($NewAllowance ? "Year" : ""), ($NewAllowance ? "$NewYear" : "$AllowanceRow[0]"), oswebdb_field_len($AllowanceResult, 0), oswebdb_field_len($AllowanceResult, 0), ($NewAllowance ? !$AllowInsert : 1), ($NewAllowance ? !$AllowInsert : 1), $TabIndex++, "");
        echo "        <tr><td width=\"1%\" align=\"right\" nowrap>År :</td><td width=\"99%\" colspan=\"5\">$Input</td></tr>\r\n";
        $Input = MakeHtmlInputText(($NewAllowance ? "No" : ""), ($NewAllowance ? "$NewNo" : "$AllowanceRow[1]"), oswebdb_field_len($AllowanceResult, 1), oswebdb_field_len($AllowanceResult, 1), ($NewAllowance ? !$AllowInsert : 1), ($NewAllowance ? !$AllowInsert : 1), $TabIndex++, "");
        echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Nummer :</td><td width=\"99%\" colspan=\"5\">$Input</td></tr>\r\n";
        $Input = MakeHtmlInputText("Description", ($NewAllowance ? "" : "$AllowanceRow[2]"), oswebdb_field_len($AllowanceResult, 2), oswebdb_field_len($AllowanceResult, 2), ($NewAllowance ? !$AllowInsert : !$AllowUpdate), ($NewAllowance ? !$AllowInsert : !$AllowUpdate), $TabIndex++, "");
        echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Beskrivelse :</td><td width=\"99%\" colspan=\"5\">$Input</td></tr>\r\n";
        echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"1%\" valign=\"top\" nowrap><table border=\"1\" cellpadding=\"2\" cellspacing=\"0\">\r\n";
        echo "          <tr><td nowrap><strong>Fradragsskala</strong></td><td nowrap><strong>Km</strong></td><td nowrap><strong>Øre</strong></td></tr>\r\n";
        for ($i = 1, $DistanceColumn = 3; $i <= 5; $i++, $DistanceColumn += 2)
        {
          $Distance = ($NewAllowance ? 0 : (integer) $AllowanceRow[$DistanceColumn]);
          $Allowance = ($NewAllowance ? 0 : (integer) $AllowanceRow[$DistanceColumn + 1]);
          if ($NewAllowance)
          {
            $DistanceEnabled = ($i == 1 ? $AllowInsert : 0);
            $AllowanceEnabled = 0;
          }
          else
          {
            $DistanceEnabled = ($i == 1 ? $AllowUpdate : $AllowUpdate && (integer) $AllowanceRow[$DistanceColumn - 2] > 0);
            $AllowanceEnabled = $AllowUpdate && $Distance > 0;
          }
          $InputDistance  = MakeHtmlInputText("Distance$i", ($Distance == 0 ? "" : "$Distance"), oswebdb_field_len($AllowanceResult, $DistanceColumn), oswebdb_field_len($AllowanceResult, $DistanceColumn), !$DistanceEnabled, ($NewAllowance ? !$AllowInsert : !$AllowUpdate), $TabIndex++, "javascript:return parent.changeDistanceEditControl($i)");
          $InputAllowance = MakeHtmlInputText("Allowance$i", ($Allowance == 0 ? "" : "$Allowance"), oswebdb_field_len($AllowanceResult, $DistanceColumn + 1), oswebdb_field_len($AllowanceResult, $DistanceColumn + 1), !$AllowanceEnabled, ($NewAllowance ? !$AllowInsert : !$AllowUpdate), $TabIndex++, "javascript:return parent.changeAllowanceEditControl($i)");
          echo "          <tr><td nowrap>Fradrag til og med</td><td nowrap><strong>$InputDistance</strong></td><td nowrap><strong>$InputAllowance</strong></td></tr>\r\n";
        }
        echo "        </table></td><td width=\"1%\" valign=\"top\" nowrap>&nbsp;&nbsp;&nbsp;&nbsp;</td><td width=\"1%\" valign=\"top\" nowrap><table border=\"1\" cellpadding=\"2\" cellspacing=\"0\">\r\n";
        echo "          <tr><td nowrap><strong>Tillæg</strong></td><td nowrap><strong>Øre</strong></td></tr>\r\n";
        $Addition = ($NewAllowance ? 0 : (integer) $AllowanceRow[13]);
        $InputAddition = MakeHtmlInputText("Addition1", ($Addition == 0 ? "" : "$Addition"), oswebdb_field_len($AllowanceResult, 13), oswebdb_field_len($AllowanceResult, 13), ($NewAllowance ? !$AllowInsert : !$AllowUpdate), ($NewAllowance ? !$AllowInsert : !$AllowUpdate), $TabIndex++, "javescript:return parent.changeAdditionEditControl(1)");
        echo "          <tr><td nowrap>Brotillæg, Storebælt</td><td nowrap>$InputAddition</td></tr>\r\n";
        $Addition = ($NewAllowance ? 0 : (integer) $AllowanceRow[14]);
        $InputAddition = MakeHtmlInputText("Addition2", ($Addition == 0 ? "" : "$Addition"), oswebdb_field_len($AllowanceResult, 14), oswebdb_field_len($AllowanceResult, 14), ($NewAllowance ? !$AllowInsert : !$AllowUpdate), ($NewAllowance ? !$AllowInsert : !$AllowUpdate), $TabIndex++, "javescript:return parent.changeAdditionEditControl(2)");
        echo "          <tr><td nowrap>Brotillæg, Øresund</td><td nowrap>$InputAddition</td></tr>\r\n";
        echo "        </table></td><td width=\"1%\" valign=\"top\" nowrap>&nbsp;&nbsp;&nbsp;&nbsp;</td><td width=\"95%\" valign=\"top\" nowrap><table border=\"1\" cellpadding=\"2\" cellspacing=\"0\">\r\n";
        echo "          <tr><td colspan=\"2\" nowrap><strong>Kørselsfradrag</strong></td></tr>\r\n";
        $Days = ($NewAllowance ? 0 : (integer) get_days_for_allowance($SystemNo, $Year, $No));
        $InputDays = MakeHtmlInputText("Days", ($Days == 0 ? "" : FormatNumber($Days, 0, 1)), 8, 8, 1, ($NewAllowance ? !$AllowInsert : !$AllowUpdate), $TabIndex++, "");
        echo "          <tr><td nowrap>Dage</td><td nowrap>$InputDays</td></tr>\r\n";
        $Distance = ($NewAllowance ? 0 : (integer) get_distance_for_allowance($SystemNo, $Year, $No));
        $InputDistance = MakeHtmlInputText("Distance", ($Distance == 0 ? "" : FormatNumber($Distance, 0, 1)), 8, 8, 1, ($NewAllowance ? !$AllowInsert : !$AllowUpdate), $TabIndex++, "");
        echo "          <tr><td nowrap>Distance</td><td nowrap>$InputDistance&nbsp;km</td></tr>\r\n";
        $Allowance = ($NewAllowance ? 0 : (float) get_allowance($SystemNo, $Year, $No));
        $InputAllowance = MakeHtmlInputText("Allowance", ($Allowance == 0 ? "" : FormatNumber($Allowance, 2, 1)), 9, 9, 1, ($NewAllowance ? !$AllowInsert : !$AllowUpdate), $TabIndex++, "");
        echo "          <tr><td nowrap>Fradrag</td><td nowrap>$InputAllowance&nbsp;kr</td></tr>\r\n";
        echo "        </table></td></tr>\r\n";
        echo "      </form>\r\n";
        if (!$NewAllowance && isset($Year) && isset($No))
        {
          $NewFun = $Fun + 1;
          $CountryCodeSystemNo = get_system_for_table($SystemNo, 2);
          $ZipCodeSystemNo = get_system_for_zipcodes($SystemNo);
          $DistanceSystemNo = get_system_for_distances($SystemNo);
          if ($AllowanceLineResult = oswebdb_query("SELECT a.Year,a.No,a.Description,l.LineNo,l.Date,l.FromCountryCode,l.FromZipCode,f.CityName,l.ToCountryCode,l.ToZipCode,t.CityName,d.Distance,d.Properties FROM $AllowanceTableName AS a,$AllowanceLineTableName AS l, Zipcodes AS f, Zipcodes AS t, Distances AS d WHERE a.SystemNo=$SystemNo AND a.Year=$Year AND a.No=$No AND l.SystemNo=a.SystemNo AND l.Year=a.Year AND l.No=a.No AND f.SystemNo=$ZipCodeSystemNo AND f.CountryCode=l.FromCountryCode AND f.ZipCode=l.FromZipCode AND t.SystemNo=$ZipCodeSystemNo AND t.CountryCode=l.ToCountryCode AND t.ZipCode=l.ToZipCode AND d.SystemNo=$DistanceSystemNo AND d.FromCountryCode=l.FromCountryCode AND d.FromZipCode=l.FromZipCode AND d.ToCountryCode=l.ToCountryCode AND d.ToZipCode=l.ToZipCode ORDER BY l.Date DESC,l.LineNo DESC"))
          {
            echo "      <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\" colspan=\"5\"><br></td></tr>\r\n";
            echo "      <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\" colspan=\"5\"><table border=\"1\" cellpadding=\"2\" cellspacing=\"0\">\r\n";
            $Input = ($AllowInsert ? MakeHtmlInputSubmit("NewAllowanceLine", "Opret", !$AllowInsert, $TabIndex++, "") : "&nbsp;");
            echo "        <form action=\"allowanc.php\" method=\"post\"><input type=\"hidden\" name=\"Fun\" value=\"$NewFun\"><input type=\"hidden\" name=\"NewAllowance\" value=\"$NewAllowance\"><input type=\"hidden\" name=\"Year\" value=\"$Year\"><input type=\"hidden\" name=\"No\" value=\"$No\"><tr><td width=\"1%\" align=\"right\" nowrap>$Input</td><td valign=\"middle\" nowrap><strong>Dato</strong></td><td valign=\"middle\" nowrap><strong>Fra sted</strong></td><td valign=\"middle\" nowrap><strong>Til sted</strong></td><td align=\"right\" valign=\"middle\" nowrap><strong>Distance</strong></td></tr></form>\r\n";
            while ($AllowanceLineRow = oswebdb_fetch_row($AllowanceLineResult))
            {
              $Input = ($AllowDelete ? MakeHtmlInputSubmit("Delete", "Slet", !$AllowDelete, $TabIndex++, "javascript:return confirm('Bekræft sletning!')") : "&nbsp;");
              $LineDateYear = substr($AllowanceLineRow[4], 0, 4);
              $LineDateMonth = substr($AllowanceLineRow[4], 5, 2);
              $LineDateDate = substr($AllowanceLineRow[4], 8, 2);
              $LineDate = "$LineDateDate/$LineDateMonth-$LineDateYear";
              $LineDistance = FormatNumber((integer) $AllowanceLineRow[11], 0, 1);
              echo "        <form action=\"allowanc.php\" method=\"post\"><input type=\"hidden\" name=\"Fun\" value=\"$NewFun\"><input type=\"hidden\" name=\"NewAllowance\" value=\"$NewAllowance\"><input type=\"hidden\" name=\"Year\" value=\"$AllowanceLineRow[0]\"><input type=\"hidden\" name=\"No\" value=\"$AllowanceLineRow[1]\"><input type=\"hidden\" name=\"LineNo\" value=\"$AllowanceLineRow[3]\"><tr><td width=\"1%\" align=\"right\" nowrap>$Input</td><td valign=\"middle\" nowrap>$LineDate</td><td valign=\"middle\" nowrap>$AllowanceLineRow[6]&nbsp;&nbsp;$AllowanceLineRow[7]</td><td valign=\"middle\" nowrap>$AllowanceLineRow[9]&nbsp;&nbsp;$AllowanceLineRow[10]</td><td align=\"right\" valign=\"middle\" nowrap>$LineDistance&nbsp;km</td></tr></form>\r\n";
            }
            echo "      </table></td></tr>\r\n";
            oswebdb_free_result($AllowanceLineResult);
          }
        }
        echo "    </table>\r\n";
        MakeHtmlPageBottom("");
      }
      else
        MakeHtmlPageReload("javascript:parent.showAllowances(); return true;");
      oswebdb_free_result($AllowanceResult);
    }
    else
      MakeHtmlPageReload("javascript:parent.showAllowances(); return true;");
  }

  function MakeAllowanceLines($AllowanceTableName, $AllowanceLineTableName, $SystemNo, $Fun, $NewAllowance, $Year, $No)
  {
    $AllowSelect = oswebdb_allow_select($AllowanceTableName) && oswebdb_allow_select($AllowanceLineTableName);
    $AllowInsert = oswebdb_allow_insert($AllowanceTableName) && oswebdb_allow_insert($AllowanceLineTableName);
    $AllowUpdate = oswebdb_allow_update($AllowanceTableName) && oswebdb_allow_update($AllowanceLineTableName);
    $AllowDelete = oswebdb_allow_delete($AllowanceTableName) && oswebdb_allow_delete($AllowanceLineTableName);
    $Load = (!$NewAllowance && isset($Year) && isset($No) && isset($_POST["NewAllowanceLine"]) ? "" : "javascript:parent.updateContent($Year, $No)");
    if (isset($_POST["Insert"]) && isset($Year) && isset($No) && isset($_POST["Date"]) && isset($_POST["FromCountryCode"]) && isset($_POST["FromZipCode"]) && isset($_POST["ToCountryCode"]) && isset($_POST["ToZipCode"]))
    {
      if (!insert_allowance_line($SystemNo, $Year, $No, $_POST["Date"], $_POST["FromCountryCode"], $_POST["FromZipCode"], $_POST["ToCountryCode"], $_POST["ToZipCode"]))
      {
        $Error = oswebdb_error();
        MakeHtmlPageTop("Kørselsfradrag");
        echo "    <h2><strong>$Error</strong></h2>\r\n";
        MakeHtmlPageBottom("");
        exit;
      }
    }
    else if (isset($_POST["Delete"]) && isset($Year) && isset($No) && isset($_POST["LineNo"]))
    {
      if (!delete_allowance_line($SystemNo, $Year, $No, $_POST["LineNo"]))
      {
        $Error = oswebdb_error();
        MakeHtmlPageTop("Kørselsfradrag");
        echo "    <h2><strong>$Error</strong></h2>\r\n";
        MakeHtmlPageBottom("");
        exit;
      }
      unset($LineNo);
    }
    else if (isset($_POST["ShowAll"]) && isset($LineNo))
      unset($LineNo);
    $TabIndex = 1; $TabAdjust = 1;
    MakeHtmlPageTopWithLoad("Kørselsfradrag", $Load);
    if (!$NewAllowance && isset($Year) && isset($No) && isset($_POST["NewAllowanceLine"]))
    {
      $CountryCodeSystemNo = get_system_for_table($SystemNo, 2);
      $ZipCodeSystemNo = get_system_for_zipcodes($SystemNo);
      $DistanceSystemNo = get_system_for_distances($SystemNo);
      if ($AllowanceLineResult = oswebdb_query("SELECT a.Year,a.No,a.Description,l.LineNo,l.Date,l.FromCountryCode,l.FromZipCode,f.CityName,l.ToCountryCode,l.ToZipCode,t.CityName,d.Distance,d.Properties FROM $AllowanceTableName AS a,$AllowanceLineTableName AS l, Zipcodes AS f, Zipcodes AS t, Distances AS d WHERE a.SystemNo=$SystemNo AND a.Year=$Year AND a.No=$No AND l.SystemNo=a.SystemNo AND l.Year=a.Year AND l.No=a.No AND f.SystemNo=$ZipCodeSystemNo AND f.CountryCode=l.FromCountryCode AND f.ZipCode=l.FromZipCode AND t.SystemNo=$ZipCodeSystemNo AND t.CountryCode=l.ToCountryCode AND t.ZipCode=l.ToZipCode AND d.SystemNo=$DistanceSystemNo AND d.FromCountryCode=l.FromCountryCode AND d.FromZipCode=l.FromZipCode AND d.ToCountryCode=l.ToCountryCode AND d.ToZipCode=l.ToZipCode ORDER BY l.Date DESC,l.LineNo DESC"))
      {
        $CountryCodeOptions = "";
        if ($CountryCodeResult = oswebdb_query("SELECT No,Description FROM Systemtables WHERE SystemNo=$CountryCodeSystemNo AND TableNo=2 ORDER BY Description"))
        {
          while ($CountryCodeRow = oswebdb_fetch_row($CountryCodeResult))
          {
            $CountryCodeOptions = "$CountryCodeOptions<option value=\"$CountryCodeRow[0]\"";
            if ($CountryCodeRow[0] == get_system_country_code($SystemNo))
              $CountryCodeOptions = "$CountryCodeOptions selected";
            $CountryCodeOptions = "$CountryCodeOptions>$CountryCodeRow[1]";
          }
          oswebdb_free_result($CountryCodeResult);
        }
        $DateDistance = get_distance_for_date($SystemNo, $Year, $No, date("Y-m-d", time()));
        $DateAllowance = get_allowance_for_date($SystemNo, $Year, $No, date("Y-m-d", time()));
        echo "    <form name=\"AllowanceLineForm\" action=\"allowanc.php\" method=\"post\">\r\n";
        echo "      <input type=\"hidden\" name=\"Fun\" value=\"$Fun\">\r\n";
        echo "      <input type=\"hidden\" name=\"NewAllowance\" value=\"$NewAllowance\">\r\n";
        echo "      <input type=\"hidden\" name=\"Year\" value=\"$Year\">\r\n";
        echo "      <input type=\"hidden\" name=\"No\" value=\"$No\">\r\n";
        echo "      <table border=\"0\" cellpadding=\"1\" cellspacing=\"1\" width=\"100%\">\r\n";
        echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\" colspan=\"4\">";
        if ($AllowInsert)
        {
          $InputSubmit = MakeHtmlInputSubmit("Insert", "Opret", !$AllowInsert, $TabIndex++, "javascript:return parent.validateAllowanceLine()");
          $InputReset = MakeHtmlInputReset("Reset", "Fortryd", !$AllowInsert, $TabIndex++, "javascript:return parent.resetAllowanceLine()");
          echo "$InputSubmit&nbsp;$InputReset&nbsp;";
        }
        if ($AllowSelect)
        {
          $InputShowAll = MakeHtmlInputSubmit("ShowAll", "Vis alle", !$AllowSelect, $TabIndex++, "");
          echo "$InputShowAll&nbsp;";
        }
        echo "</td></tr>\r\n";
        $Input = MakeHtmlInputText("", $Year, oswebdb_field_len($AllowanceLineResult, 0), oswebdb_field_len($AllowanceLineResult, 0), 1, 1, $TabIndex++, "");
        echo "        <tr><td width=\"1%\" align=\"right\" nowrap>År :</td><td width=\"99%\" colspan=\"4\" nowrap>$Input</td></tr>\r\n";
        $Input = MakeHtmlInputText("", $No, oswebdb_field_len($AllowanceLineResult, 1), oswebdb_field_len($AllowanceLineResult, 1), 1, 1, $TabIndex++, "");
        echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Nummer :</td><td width=\"99%\" colspan=\"4\" nowrap>$Input</td></tr>\r\n";
        $Input = MakeHtmlInputText("", get_allowance_description($SystemNo, $Year, $No), oswebdb_field_len($AllowanceLineResult, 2), oswebdb_field_len($AllowanceLineResult, 1), 1, 1, $TabIndex++, "");
        echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Beskrivelse :</td><td width=\"99%\" colspan=\"4\" nowrap>$Input</td></tr>\r\n";
        $Input = MakeHtmlInputText("Date", date("Y-m-d", time()), oswebdb_field_len($AllowanceLineResult, 4), oswebdb_field_len($AllowanceLineResult, 4), !$AllowInsert, !$AllowInsert, $TabIndex++, "javascript:return parent.changeDateEditControl()");
        echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Dato :</td><td width=\"99%\" colspan=\"4\" nowrap>$Input</td></tr>\r\n";
        $InputFromCountry = MakeHtmlSelect("FromCountryCode", 0, 0, !$AllowInsert, !$AllowInsert, $TabIndex++, "javascript:parent.changeFromCountrySelectControl()", $CountryCodeOptions);
        $InputFromZipCode = MakeHtmlInputText("FromZipCode", "", get_zipcode_length($SystemNo, get_system_country_code($SystemNo)), get_zipcode_length($SystemNo, get_system_country_code($SystemNo)), !$AllowInsert, !$AllowInsert, $TabIndex++, "javascript:parent.changeFromZipCodeEditControl()");
        $InputFromCityName = MakeHtmlInputText("FromCityName", "", get_city_name_length($SystemNo), get_city_name_length($SystemNo), 1, !$AllowInsert, $TabIndex++, "");
        echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Fra sted :</td><td width=\"99%\" colspan=\"4\" nowrap>$InputFromCountry&nbsp;&nbsp;$InputFromZipCode&nbsp;&nbsp;$InputFromCityName</td<</tr>\r\n";
        $InputToCountry = MakeHtmlSelect("ToCountryCode", 0, 0, !$AllowInsert, !$AllowInsert, $TabIndex++, "javascript:parent.changeToCountrySelectControl()", $CountryCodeOptions);
        $InputToZipCode = MakeHtmlInputText("ToZipCode", "", get_zipcode_length($SystemNo, get_system_country_code($SystemNo)), get_zipcode_length($SystemNo, get_system_country_code($SystemNo)), !$AllowInsert, !$AllowInsert, $TabIndex++, "javascript:parent.changeToZipCodeEditControl()");
        $InputToCityName = MakeHtmlInputText("ToCityName", "", get_city_name_length($SystemNo), get_city_name_length($SystemNo), 1, !$AllowInsert, $TabIndex++, "");
        echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Til sted :</td><td width=\"99%\" colspan=\"4\" nowrap>$InputToCountry&nbsp;&nbsp;$InputToZipCode&nbsp;&nbsp;$InputToCityName</td></tr>\r\n";
        echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"1%\" nowrap><strong>Distance:</strong></td><td width=\"1%\" nowrap>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td><td width=\"1%\" nowrap></td><td width=\"96%\" nowrap><strong>Dagens fradrag:</strong></td></tr>\r\n";
        $InputDistance = MakeHtmlInputText("Distance", "", oswebdb_field_len($AllowanceLineResult, 11), oswebdb_field_len($AllowanceLineResult, 11), 1, !$AllowInsert, $TabIndex++, "");
        $InputDateDistance = MakeHtmlInputText("DateDistance", ($DateDistance == 0 ? "" : FormatNumber((integer) $DateDistance, 0, 1)), (integer) (oswebdb_field_len($AllowanceLineResult, 11) * 1.5), (integer) (oswebdb_field_len($AllowanceLineResult, 11) * 1.5), 1, !$AllowInsert, $TabIndex + $TabAdjust, "");
        echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Distance :</td><td width=\"1%\" nowrap>$InputDistance&nbsp;km</td><td width=\"1%\" nowrap></td><td width=\"1%\" nowrap>Distance :</td><td width=\"96%\" nowrap>$InputDateDistance&nbsp;km</td></tr>\r\n";
        $InputProperty1 = MakeHtmlInputCheckBox("Property1", "", 0, 1, $TabIndex++, "", "Krydser Storebælt");
        $InputDateAllowance = MakeHtmlInputText("DateAllowance", ($DateAllowance == 0 ? "" : FormatNumber((float) $DateAllowance, 2, 1)), 7, 7, 1, !$AllowInsert, $TabIndex + $TabAdjust, "");
        echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Egenskaber :</td><td width=\"1%\" nowrap>$InputProperty1</td><td width=\"1%\" nowrap></td><td width=\"1%\" nowrap>Fradrag :</td><td width=\"96%\" nowrap>$InputDateAllowance&nbsp;kr</td></tr>\r\n";
        $InputProperty2 = MakeHtmlInputCheckBox("Property2", "", 0, 1, $TabIndex++, "", "Krydser Øresund");
        echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"1%\" nowrap>$InputProperty2</td><td width=\"1%\" nowrap></td><td width=\"1%\" nowrap></td><td width=\"96%\" nowrap></td></tr>\r\n";
        echo "      </table>\r\n";
        echo "    </form>\r\n";
        oswebdb_free_result($AllowanceLineResult);
      }
    }
    MakeHtmlPageBottom("");
  }

  function MakeAllowancePrint($AllowanceTableName, $AllowanceLineTableName, $SystemNo, $Fun, $NewAllowance, $Year, $No)
  {
    MakeHtmlPrintPageTop("Kørselsfradrag");
    $Print = MakeHtmlLink("", "javascript:window.print()", "", "", "", "", "javascript:window.status='Udskriv'; return true;", "javascript:window.status=''; return true;", "Udskriv");
    $Close = MakeHtmlLink("", "javascript:window.close()", "", "", "", "", "javascript:window.status='Luk vindue'; return true;", "javascript:window.status=''; return true;", "Luk");
    echo "    <table border=\"0\" cellpadding=\"1\" cellspacing=\"1\" width=\"100%\">\r\n";
    echo "      <tr><td colspan=\"2\" nowrap><h2><i>Kørselsfradrag</i></h2></td><td valign=\"top\" align=\"right\" nowrap>$Print&nbsp;|&nbsp;$Close</td></tr>\r\n";
    $ZipCodeSystemNo = get_system_for_zipcodes($SystemNo);
    $DistanceSystemNo = get_system_for_distances($SystemNo);
    if ($AllowanceResult = oswebdb_query("SELECT a.Year,a.No,a.Description,a.Distance1,a.Allowance1,a.Distance2,a.Allowance2,a.Distance3,a.Allowance3,a.Distance4,a.Allowance4,a.Distance5,a.Allowance5,a.Addition1,a.Addition2,l.Date,l.FromCountryCode,l.FromZipCode,f.CityName,l.ToCountryCode,l.ToZipCode,t.CityName,d.Distance,d.Properties FROM Allowances AS a, Allowancelines AS l, Zipcodes AS f, Zipcodes AS t, Distances AS d WHERE a.SystemNo=$SystemNo AND a.Year=$Year AND a.No=$No AND l.SystemNo=a.SystemNo AND l.Year=a.Year AND l.No=a.No AND f.SystemNo=$ZipCodeSystemNo AND f.CountryCode=l.FromCountryCode AND f.ZipCode=l.FromZipCode AND t.SystemNo=$ZipCodeSystemNo AND t.CountryCode=l.ToCountryCode AND t.ZipCode=l.ToZipCode AND d.SystemNo=$DistanceSystemNo AND d.FromCountryCode=l.FromCountryCode AND d.FromZipCode=l.FromZipCode AND d.ToCountryCode=l.ToCountryCode AND d.ToZipCode=l.ToZipCode ORDER BY l.Date,l.LineNo"))
    {
      $FirstRow = 1; $LastDate = ""; $DateLines = 0; $DateDistance = 0; $DateProperty1 = 0; $DateProperty2 = 0;
      while ($AllowanceRow = oswebdb_fetch_row($AllowanceResult))
      {
        if ($FirstRow)
        {
          echo "      <tr><td width=\"1%\" align=\"right\" nowrap>År :</td><td nowrap>$AllowanceRow[0]</td><td nowrap align=\"right\"></td></tr>\r\n";
          echo "      <tr><td width=\"1%\" align=\"right\" nowrap>Beskrivelse :</td><td nowrap>$AllowanceRow[2]</td><td nowrap align=\"right\"></td></tr>\r\n";
          echo "      <tr><td colspan=\"3\"><br></td></tr>\r\n";
          echo "      <tr><td colspan=\"3\"><table border=\"0\" cellpadding=\"1\" cellspacing=\"1\" width=\"100%\">\r\n";
          echo "        <tr><td width=\"1%\" valign=\"top\" nowrap><strong>Dato</strong></td><td colspan=\"2\" valign=\"top\"><strong>Tekst</strong></td><td width=\"1%\" align=\"right\" valign=\"top\" nowrap><strong>Fradrag</strong></td></tr>\r\n";
          $FirstRow = 0;
        }
        $DateDistance += $AllowanceRow[22];
        $DateProperty1 += (($AllowanceRow[23] & 1) ? 1 : 0);
        $DateProperty2 += (($AllowanceRow[23] & 2) ? 1 : 0);
        $Distance = FormatNumber($AllowanceRow[22], 0, 1);
        $Additions = "";
        if ($AllowanceRow[13] > 0 && ($AllowanceRow[23] & 1))
        {
          if (strlen($Additions) > 0)
            $Additions = "$Additions + Brotillæg, Storebælt";
          else
            $Additions = "Brotillæg, Storebælt";
        }
        if ($AllowanceRow[14] > 0 && ($AllowanceRow[23] & 2))
        {
          if (strlen($Additions) > 0)
            $Additions = "$Additions + Brotillæg, Øresund";
          else
            $Additions = "Brotillæg, Øresund";
        }
        if (strlen($Additions))
          $Additions = "&nbsp($Additions)";
        if (strcmp($AllowanceRow[15], $LastDate) != 0)
        {
          $LastDate = $AllowanceRow[15];
          $DateLines = oswebdb_get_rows("SELECT l.No,l.Year,l.LineNo,l.Date FROM $AllowanceLineTableName AS l WHERE l.SystemNo=$SystemNo AND l.Year=$Year AND l.No=$No AND l.Date=\"$AllowanceRow[15]\"");
          $LineDateYear = substr($AllowanceRow[15], 0, 4);
          $LineDateMonth = substr($AllowanceRow[15], 5, 2);
          $LineDateDate = substr($AllowanceRow[15], 8, 2);
          $LineDate = "$LineDateDate/$LineDateMonth-$LineDateYear";
          $Allowance = FormatNumber(get_allowance_for_date($SystemNo, $Year, $No, $AllowanceRow[15]), 2, 1);
          echo "        <tr><td width=\"1%\" valign=\"top\" nowrap>$LineDate&nbsp;&nbsp;</td><td valign=\"top\">$AllowanceRow[18] -> $AllowanceRow[21]$Additions</td><td width=\"1%\" align=\"right\" valign=\"top\" nowrap>$Distance&nbsp;km&nbsp;&nbsp;</td><td width=\"1%\" align=\"right\" valign=\"top\" nowrap>$Allowance&nbsp;kr</td></tr>\r\n";
        }
        else
          echo "        <tr><td width=\"1%\" valign=\"top\" nowrap></td><td valign=\"top\">$AllowanceRow[18] -> $AllowanceRow[21]$Additions</td><td width=\"1%\" align=\"right\" valign=\"top\" nowrap>$Distance&nbsp;km&nbsp;&nbsp;</td><td width=\"1%\" align=\"right\" valign=\"top\" nowrap></td></tr>\r\n";
        if (--$DateLines == 0)
        {
          $Distance = FormatNumber($DateDistance, 0, 1);
          echo "        <tr><td width=\"1%\" valign=\"top\" nowrap></td><td valign=\"top\">Distance</td><td width=\"1%\" align=\"right\" valign=\"top\" nowrap>$Distance&nbsp;km&nbsp;&nbsp;</td><td width=\"1%\" align=\"right\" valign=\"top\" nowrap></td></tr>\r\n";
          $AllowanceTotal = 0;
          for ($i = 1, $DistanceColumn = 3; $i <= 5; $i++, $DistanceColumn += 2)
          {
            if ($AllowanceRow[$DistanceColumn] - ($i > 1 ? $AllowanceRow[$DistanceColumn - 2] : 0) > 0 && $DateDistance > 0)
            {
              if ($DateDistance > $AllowanceRow[$DistanceColumn] - ($i > 1 ? $AllowanceRow[$DistanceColumn - 2] : 0))
              {
                $Distance = $AllowanceRow[$DistanceColumn] - ($i > 1 ? $AllowanceRow[$DistanceColumn - 2] : 0);
                $DateDistance -= $AllowanceRow[$DistanceColumn] - ($i > 1 ? $AllowanceRow[$DistanceColumn - 2] : 0);
              }
              else
              {
                $Distance = $DateDistance;
                $DateDistance = 0;
              }
              $Allowance = $AllowanceRow[$DistanceColumn + 1];
              $AllowanceTotal += ($Distance * $Allowance) / 100;
              $AllowanceSubtotal = FormatNumber(($Distance * $Allowance) / 100, 2, 1);
              $Distance = FormatNumber($Distance, 0, 1);
              $Allowance = FormatNumber($Allowance, 0, 1);
              echo "        <tr><td width=\"1%\" valign=\"top\" nowrap></td><td valign=\"top\">$Distance&nbsp;km&nbsp;á&nbsp;$Allowance&nbsp;øre</td><td width=\"1%\" align=\"right\" valign=\"top\" nowrap>$AllowanceSubtotal&nbsp;kr&nbsp;&nbsp;</td><td width=\"1%\" align=\"right\" valign=\"top\" nowrap></td></tr>\r\n";
            }
          }
          if ($AllowanceRow[13] > 0 && $DateProperty1 > 0)
          {
            $Allowance = $AllowanceRow[13];
            $AllowanceTotal += ($DateProperty1 * $Allowance) / 100;
            $AllowanceSubtotal = FormatNumber(($DateProperty1 * $Allowance) / 100, 2, 1);
            $DateProperty1 = FormatNumber($DateProperty1, 0, 1);
            $Allowance = FormatNumber($Allowance, 0, 1);
            echo "        <tr><td width=\"1%\" valign=\"top\" nowrap></td><td valign=\"top\">$DateProperty1&nbsp;*&nbsp;Brotillæg, Storebælt&nbsp;á&nbsp;$Allowance&nbsp;øre</td><td width=\"1%\" align=\"right\" valign=\"top\" nowrap>$AllowanceSubtotal&nbsp;kr&nbsp;&nbsp;</td><td width=\"1%\" align=\"right\" valign=\"top\" nowrap></td></tr>\r\n";
          }
          if ($AllowanceRow[14] > 0 && $DateProperty2 > 0)
          {
            $Allowance = $AllowanceRow[14];
            $AllowanceTotal += ($DateProperty2 * $Allowance) / 100;
            $AllowanceSubtotal = FormatNumber(($DateProperty2 * $Allowance) / 100, 2, 1);
            $DateProperty2 = FormatNumber($DateProperty2, 0, 1);
            $Allowance = FormatNumber($Allowance, 0, 1);
            echo "        <tr><td width=\"1%\" valign=\"top\" nowrap></td><td valign=\"top\">$DateProperty2&nbsp;*&nbsp;Brotillæg, Øresund&nbsp;á&nbsp;$Allowance&nbsp;øre</td><td width=\"1%\" align=\"right\" valign=\"top\" nowrap>$AllowanceSubtotal&nbsp;kr&nbsp;&nbsp;</td><td width=\"1%\" align=\"right\" valign=\"top\" nowrap></td></tr>\r\n";
          }
          $AllowanceTotal = FormatNumber($AllowanceTotal, 2, 1);
          echo "        <tr><td width=\"1%\" valign=\"top\" nowrap></td><td valign=\"top\">Dagens fradrag</td><td width=\"1%\" align=\"right\" valign=\"top\" nowrap>$AllowanceTotal&nbsp;kr&nbsp;&nbsp;</td><td width=\"1%\" align=\"right\" valign=\"top\" nowrap></td></tr>\r\n";
          echo "        <tr><td colspan=\"4\" nowrap><br></td></tr>\r\n";
          $DateDistance = 0;
          $DateProperty1 = 0;
          $DateProperty2 = 0;
        }
      }
      if (!$FirstRow)
      {
        $Allowance = FormatNumber(get_allowance($SystemNo, $Year, $No), 2, 1);
        echo "        <tr><td width=\"1%\" valign=\"top\" nowrap></td><td valign=\"top\">Fradrag</td><td width=\"1%\" align=\"right\" valign=\"top\" nowrap></td><td width=\"1%\" align=\"right\" valign=\"top\" nowrap>$Allowance&nbsp;kr&nbsp;&nbsp;</td></tr>\r\n";
        echo "      </table></td></tr>\r\n";
      }
      oswebdb_free_result($AllowanceResult);
    }
    echo "    </table>\r\n";
    MakeHtmlPrintPageBottom();
  }
?>
