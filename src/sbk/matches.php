<?php
  // Include required files.
  require_once("misc.php");
  require_once("oswebdb/oswebdb.php");
  require_once("oswebdb/systems.php");
  require_once("oswebdb/tables.php");
  require_once("oswebdb/address.php");

  // Set error reporting.
  error_reporting((int) GetConfigValue("ErrorReporting"));

  $AddressTableName = "Addresses";
  $AddressLinkTableName = "Addresslinks";
  $AddressMatchTableName = "Addressmatches";
  $SystemNo = GetConfigValue("SystemNo");
  $SeasonTableNo = 7;
  $AverageTableNo = 8;
  $TypeTableNo = 9;
  if (isset($_GET["SeasonNo"]))
    $SeasonNo = $_GET["SeasonNo"];
  else if (isset($_POST["SeasonNo"]))
    $SeasonNo = $_POST["SeasonNo"];
  if (isset($_GET["TypeNo"]))
    $TypeNo = $_GET["TypeNo"];
  else if (isset($_POST["TypeNo"]))
    $TypeNo = $_POST["TypeNo"];
  if (isset($_GET["Date"]))
    $Date = $_GET["Date"];
  else if (isset($_POST["Date"]))
    $Date = $_POST["Date"];
  if (isset($_GET["Fun"]))
    $Fun = $_GET["Fun"];
  else if (isset($_POST["Fun"]))
    $Fun = $_POST["Fun"];
  if (oswebdb_authorize(oswebdb_getusername(), oswebdb_getpassword()))
  {
    if (oswebdb_selectdb())
    {
      if (!isset($SeasonNo))
      {
        $SeasonNo = (integer) get_system_season_no($SystemNo);
      }
      if (!isset($TypeNo))
      {
        $TypeNo = 0;
      }
      if (!isset($Date))
      {
        $Date = date("Y-m-d", time());
      }
      switch ($Fun)
      {
        case 1:
          MakeMatchRegistration($SystemNo, $SeasonTableNo, $SeasonNo, $TypeNo, $Date, $Fun);
          break;

        case 2:
          MakeShowRegistrations($SystemNo, $SeasonTableNo, $SeasonNo, $TypeNo, $Fun);
          break;

        case 11:
          MakeMatchRegistrationDefaults($AddressMatchTableName, $SystemNo, $SeasonTableNo, $TypeTableNo, $SeasonNo, $TypeNo, $Date, $Fun);
          break;

        case 12:
          MakeMatchRegistrationSelectPlayer($AddressTableName, $AddressLinkTableName, $AddressMatchTableName, $SystemNo, $SeasonTableNo, $AverageTableNo, $TypeTableNo, $SeasonNo, $TypeNo, 1, $Fun);
          break;

        case 13:
          MakeMatchRegistrationSelectPlayer($AddressTableName, $AddressLinkTableName, $AddressMatchTableName, $SystemNo, $SeasonTableNo, $AverageTableNo, $TypeTableNo, $SeasonNo, $TypeNo, 2, $Fun);
          break;

        case 14:
          $AddressNo = 0;
          if (isset($_GET["AddressNo"]))
            $AddressNo = $_GET["AddressNo"];
          if ($AddressNo > 0 && $SeasonNo > 0 && $TypeNo > 0 && isset($_GET["Date"]) && isset($_GET["Score"]) && isset($_GET["Entries"]) && isset($_GET["Series"]) && isset($_GET["Point"]) && isset($_GET["Matches"]))
          {
            if (!insert_address_match($SystemNo, $AddressNo, $TypeTableNo, $SeasonNo, $TypeNo, $_GET["Date"], $_GET["Score"], $_GET["Entries"], $_GET["Series"], $_GET["Point"], $_GET["Matches"]))
            {
              $Error = oswebdb_error();
              MakeHtmlPageTop("Kampresultater");
              echo "    <h2><strong>$Error</strong></h2>\r\n";
              MakeHtmlPageBottom("");
              exit;
            }
          }
          MakeMatchRegistrationPlayerValues($AddressTableName, $AddressLinkTableName, $AddressMatchTableName, $SystemNo, $SeasonTableNo, $AverageTableNo, $TypeTableNo, $SeasonNo, $TypeNo, 1, $AddressNo);
          break;

        case 15:
          $AddressNo = 0;
          if (isset($_GET["AddressNo"]))
            $AddressNo = $_GET["AddressNo"];
          if ($AddressNo > 0 && $SeasonNo > 0 && $TypeNo > 0 && isset($_GET["Date"]) && isset($_GET["Score"]) && isset($_GET["Entries"]) && isset($_GET["Series"]) && isset($_GET["Point"]) && isset($_GET["Matches"]))
          {
            if (!insert_address_match($SystemNo, $AddressNo, $TypeTableNo, $SeasonNo, $TypeNo, $_GET["Date"], $_GET["Score"], $_GET["Entries"], $_GET["Series"], $_GET["Point"], $_GET["Matches"]))
            {
              $Error = oswebdb_error();
              MakeHtmlPageTop("Kampresultater");
              echo "    <h2><strong>$Error</strong></h2>\r\n";
              MakeHtmlPageBottom("");
              exit;
            }
          }
          MakeMatchRegistrationPlayerValues($AddressTableName, $AddressLinkTableName, $AddressMatchTableName, $SystemNo, $SeasonTableNo, $AverageTableNo, $TypeTableNo, $SeasonNo, $TypeNo, 2, $AddressNo);
          break;

        case 21:
          MakeMatchRegistrationDefaults($AddressMatchTableName, $SystemNo, $SeasonTableNo, $TypeTableNo, $SeasonNo, $TypeNo, $Date, $Fun);
          break;

        case 22:
          MakeMatchRegistrationSelectPlayer($AddressTableName, $AddressLinkTableName, $AddressMatchTableName, $SystemNo, $SeasonTableNo, $AverageTableNo, $TypeTableNo, $SeasonNo, $TypeNo, 1, $Fun);
          break;

        case 23:
          $AddressNo = 0;
          if (isset($_GET["AddressNo"]))
            $AddressNo = $_GET["AddressNo"];
          if ($AddressNo > 0 && $SeasonNo > 0 && $TypeNo > 0 && isset($_GET["MatchId"]) && isset($_GET["Date"]) && isset($_GET["Score"]) && isset($_GET["Entries"]) && isset($_GET["Series"]) && isset($_GET["Point"]) && isset($_GET["Matches"]))
          {
            if (!update_address_match($SystemNo, $AddressNo, $TypeTableNo, $SeasonNo, $TypeNo, $_GET["MatchId"], $_GET["Date"], $_GET["Score"], $_GET["Entries"], $_GET["Series"], $_GET["Point"], $_GET["Matches"]))
            {
              $Error = oswebdb_error();
              MakeHtmlPageTop("Kampresultater");
              echo "    <h2><strong>$Error</strong></h2>\r\n";
              MakeHtmlPageBottom("");
              exit;
            }
          }
          else if ($AddressNo > 0 && $SeasonNo > 0 && $TypeNo > 0 && isset($_GET["MatchId"]))
          {
            if (!delete_address_match($SystemNo, $AddressNo, $TypeTableNo, $SeasonNo, $TypeNo, $_GET["MatchId"]))
            {
              $Error = oswebdb_error();
              MakeHtmlPageTop("Kampresultater");
              echo "    <h2><strong>$Error</strong></h2>\r\n";
              MakeHtmlPageBottom("");
              exit;
            }
          }
          MakePlayerRegistrations($AddressTableName, $AddressLinkTableName, $AddressMatchTableName, $SystemNo, $SeasonTableNo, $AverageTableNo, $TypeTableNo, $SeasonNo, $TypeNo, $AddressNo);
          break;

        default:
          MakeMatchRegistration($SystemNo, $SeasonTableNo, $SeasonNo, $TypeNo, $Date, 1);
          break;
      }
    }
    oswebdb_close();
  }

  function MakeMatchRegistration($SystemNo, $SeasonTableNo, $SeasonNo, $TypeNo, $Date, $Fun)
  {
    $SeasonSystemNo = get_system_for_table($SystemNo, $SeasonTableNo);
    echo "<script language=\"JavaScript\">\r\n";
    echo "  function createRegistration()\r\n";
    echo "  {\r\n";
    echo "    seasonNo = getSelectedSeasonNo()\r\n";
    echo "    typeNo = getSelectedTypeNo()\r\n";
    echo "    if (!(seasonNo > 0))\r\n";
    echo "    {\r\n";
    echo "      alert('Der skal vælges en sæson!')\r\n";
    echo "    }\r\n";
    echo "    else if (!(typeNo > 0))\r\n";
    echo "    {\r\n";
    echo "      alert('Der skal vælges en kamptype!')\r\n";
    echo "    }\r\n";
    echo "    else if (!(validateDate(frameDefaults.DefaultsForm.Date.value, seasonNo)))\r\n";
    echo "    {\r\n";
    echo "    }\r\n";
    echo "    else if (!(validatePlayer(1, frameSelectPlayer1.SelectPlayerForm)))\r\n";
    echo "    {\r\n";
    echo "    }\r\n";
    echo "    else if (!(validatePlayer(2, frameSelectPlayer2.SelectPlayerForm)))\r\n";
    echo "    {\r\n";
    echo "    }\r\n";
    echo "    else\r\n";
    echo "    {\r\n";
    $NewFun = ($Fun * 10) + 4;
    echo "      playerValuesForm = getPlayerValuesForm(1)\r\n";
    echo "      framePlayer1Values.location = 'matches.php?SeasonNo=' + getSelectedSeasonNo() + '&TypeNo=' + getSelectedTypeNo() + '&AddressNo=' + getSelectedAddressNo(frameSelectPlayer1.SelectPlayerForm) + '&Fun=$NewFun&Date=' + frameDefaults.DefaultsForm.Date.value + '&Score=' + getScore(playerValuesForm) + '&Entries=' + getEntries(playerValuesForm) + '&Series=' + getSeries(playerValuesForm) + '&Point=' + getPoint(playerValuesForm) + '&Matches=' + getMatches(playerValuesForm)\r\n";
    echo "      if (isInternalMatchType())\r\n";
    echo "      {\r\n";
    $NewFun = ($Fun * 10) + 5;
    echo "        playerValuesForm = getPlayerValuesForm(2)\r\n";
    echo "        framePlayer2Values.location = 'matches.php?SeasonNo=' + getSelectedSeasonNo() + '&TypeNo=' + getSelectedTypeNo() + '&AddressNo=' + getSelectedAddressNo(frameSelectPlayer2.SelectPlayerForm) + '&Fun=$NewFun&Date=' + frameDefaults.DefaultsForm.Date.value + '&Score=' + getScore(playerValuesForm) + '&Entries=' + getEntries(playerValuesForm) + '&Series=' + getSeries(playerValuesForm) + '&Point=' + getPoint(playerValuesForm) + '&Matches=' + getMatches(playerValuesForm)\r\n";
    echo "      }\r\n";
    echo "    }\r\n";
    echo "  }\r\n\r\n";
    echo "  function showRegistrations()\r\n";
    echo "  {\r\n";
    echo "    this.location = 'matches.php?SeasonNo=' + getSelectedSeasonNo() + '&TypeNo=' + getSelectedTypeNo() + '&Fun=2'\r\n";
    echo "  }\r\n\r\n";
    echo "  function validateDate(matchDate, seasonNo)\r\n";
    echo "  {\r\n";
    echo "    year = parseInt(matchDate.substr(0, 4), 10)\r\n";
    echo "    month = parseInt(matchDate.substr(5, 2), 10)\r\n";
    echo "    date = parseInt(matchDate.substr(8, 2), 10)\r\n";
    echo "    if (parent.validateDate(year, month, date))\r\n";
    echo "    {\r\n";
    echo "      b = true\r\n";
    if ($SeasonResult = oswebdb_query("SELECT No,Description,FromDate,ToDate FROM Systemtables WHERE SystemNo=$SeasonSystemNo AND TableNo=$SeasonTableNo ORDER BY No,Description"))
    {
      if (oswebdb_num_rows($SeasonResult) > 0)
      {
        echo "      switch (seasonNo)\r\n";
        echo "      {\r\n";
        while ($SeasonRow = oswebdb_fetch_row($SeasonResult))
        {
          echo "        case $SeasonRow[0]: // $SeasonRow[1] ($SeasonRow[2] -> $SeasonRow[3])\r\n";
          echo "          checkDate = new Date(year, month, date)\r\n";
          $Year = substr($SeasonRow[2], 0, 4);
          $Month = substr($SeasonRow[2], 5, 2);
          $Day = substr($SeasonRow[2], 8, 2);
          echo "          fromDate = new Date(parseInt('$Year', 10), parseInt('$Month', 10), parseInt('$Day', 10))\r\n";
          $Year = substr($SeasonRow[3], 0, 4);
          $Month = substr($SeasonRow[3], 5, 2);
          $Day = substr($SeasonRow[3], 8, 2);
          echo "          toDate = new Date(parseInt('$Year', 10), parseInt('$Month', 10), parseInt('$Day', 10))\r\n";
          echo "          b = (checkDate >= fromDate) && (checkDate <= toDate)\r\n";
          echo "          break\r\n\r\n";
        }
        echo "        default:\r\n";
        echo "          b = true\r\n";
        echo "      }\r\n";
      }
      oswebdb_free_result($SeasonResult);
    }
    echo "      if (!b)\r\n";
    echo "        alert('Datoen skal være i periodeintervallet for sæsonen!')\r\n";
    echo "      return b\r\n";
    echo "    }\r\n";
    echo "    else\r\n";
    echo "      return false\r\n";
    echo "  }\r\n\r\n";
    echo "  function validatePlayer(playerNo, selectPlayerForm)\r\n";
    echo "  {\r\n";
    echo "    if (playerNo == 1 || (playerNo == 2 && isInternalMatchType()))\r\n";
    echo "    {\r\n";
    echo "      addressNo = getSelectedAddressNo(selectPlayerForm)\r\n";
    echo "      playerValuesForm = getPlayerValuesForm(playerNo)\r\n";
    echo "      if (!(addressNo > 0))\r\n";
    echo "      {\r\n";
    echo "        alert('Der skal vælges en spiller ' + playerNo + '!')\r\n";
    echo "        return false\r\n";
    echo "      }\r\n";
    echo "      else if (!(getEntries(playerValuesForm) > 0))\r\n";
    echo "      {\r\n";
    echo "        alert('Indgange for spiller ' + playerNo + ' skal være større end 0!')\r\n";
    echo "        return false\r\n";
    echo "      }\r\n";
    echo "      else if (!(getMatches(playerValuesForm) > 0))\r\n";
    echo "      {\r\n";
    echo "        alert('Kampe for spiller ' + playerNo + ' skal være større end 0!')\r\n";
    echo "        return false\r\n";
    echo "      }\r\n";
    echo "      else\r\n";
    echo "        return true\r\n";
    echo "    }\r\n";
    echo "    else\r\n";
    echo "      return true\r\n";
    echo "  }\r\n\r\n";
    echo "  function changeSeasonNo()\r\n";
    echo "  {\r\n";
    echo "    seasonNo = getSelectedSeasonNo()\r\n";
    echo "    parent.getSeasonStartDate(seasonNo, frameDefaults.DefaultsForm.Date)\r\n";
    echo "    updatePlayersToSelect(seasonNo, getSelectedTypeNo())\r\n";
    echo "  }\r\n\r\n";
    echo "  function changeTypeNo()\r\n";
    echo "  {\r\n";
    echo "    typeNo = getSelectedTypeNo()\r\n";
    echo "    parent.getInternalMatchType(typeNo, frameDefaults.DefaultsForm.Internal)\r\n";
    echo "    updatePlayersToSelect(getSelectedSeasonNo(), typeNo)\r\n";
    echo "  }\r\n\r\n";
    echo "  function changeDate()\r\n";
    echo "  {\r\n";
    echo "    s = frameDefaults.DefaultsForm.Date.value\r\n";
    echo "    if (s.length == 8)\r\n";
    echo "    {\r\n";
    echo "      s = s.substr(0, 4) + '-' + s.substr(4, 2) + '-' + s.substr(6, 2)\r\n";
    echo "      frameDefaults.DefaultsForm.Date.value = s\r\n";
    echo "    }\r\n";
    echo "    return validateDate(frameDefaults.DefaultsForm.Date.value, getSelectedSeasonNo())\r\n";
    echo "  }\r\n\r\n";
    echo "  function changeAddressNo(playerNo)\r\n";
    echo "  {\r\n";
    echo "    seasonNo= getSelectedSeasonNo()\r\n";
    echo "    typeNo = getSelectedTypeNo()\r\n";
    echo "    switch (playerNo)\r\n";
    echo "    {\r\n";
    echo "      case 1:\r\n";
    echo "        updatePlayerValues(seasonNo, typeNo, playerNo, getSelectedAddressNo(frameSelectPlayer1.SelectPlayerForm))\r\n";
    echo "        break\r\n\r\n";
    echo "      case 2:\r\n";
    echo "        updatePlayerValues(seasonNo, typeNo, playerNo, getSelectedAddressNo(frameSelectPlayer2.SelectPlayerForm))\r\n";
    echo "        break\r\n";
    echo "    }\r\n";
    echo "  }\r\n\r\n";
    echo "  function changeScore(playerNo, playerValuesForm)\r\n";
    echo "  {\r\n";
    echo "    playerValuesForm.MatchAverage.value = ''\r\n";
    echo "    playerValuesForm.ProcentAverage.value = ''\r\n";
    echo "    average = getAverage(playerValuesForm)\r\n";
    echo "    score = getScore(playerValuesForm)\r\n";
    echo "    entries = getEntries(playerValuesForm)\r\n";
    echo "    if (score > 0 && entries > 0)\r\n";
    echo "    {\r\n";
    echo "      matchAverage = score / entries\r\n";
    echo "      if (matchAverage > 0 && average > 0)\r\n";
    echo "      {\r\n";
    echo "        procentAverage = (matchAverage * 100) / average\r\n";
    echo "        parent.setFloatValue(playerValuesForm.ProcentAverage, procentAverage, getDecimals(playerValuesForm))\r\n";
    echo "      }\r\n";
    echo "      parent.setFloatValue(playerValuesForm.MatchAverage, matchAverage, getDecimals(playerValuesForm))\r\n";
    echo "    }\r\n";
    echo "    if (score > 0 && isInternalMatchType())\r\n";
    echo "    {\r\n";
    echo "      distance = getDistance(playerValuesForm)\r\n";
    echo "      if (score >= distance && distance > 0)\r\n";
    echo "      {\r\n";
    echo "        opponentPlayerNo = getOpponentPlayerNo(playerNo)\r\n";
    echo "        opponentValuesForm = getPlayerValuesForm(opponentPlayerNo)\r\n";
    echo "        opponentScore = getScore(opponentValuesForm)\r\n";
    echo "        opponentDistance = getDistance(opponentValuesForm)\r\n";
    echo "        if (opponentScore >= opponentDistance && opponentDistance > 0)\r\n";
    echo "        {\r\n";
    echo "          parent.setIntValue(playerValuesForm.Point, 1)\r\n";
    echo "          parent.setIntValue(opponentValuesForm.Point, 1)\r\n";
    echo "        }\r\n";
    echo "        else\r\n";
    echo "        {\r\n";
    echo "          parent.setIntValue(playerValuesForm.Point, 2)\r\n";
    echo "          parent.setIntValue(opponentValuesForm.Point, 0)\r\n";
    echo "        }\r\n";
    echo "      }\r\n";
    echo "      else\r\n";
    echo "        parent.setIntValue(playerValuesForm.Point, 0)\r\n";
    echo "    }\r\n";
    echo "  }\r\n\r\n";
    echo "  function changeEntries(playerNo, playerValuesForm)\r\n";
    echo "  {\r\n";
    echo "    playerValuesForm.MatchAverage.value = ''\r\n";
    echo "    playerValuesForm.ProcentAverage.value = ''\r\n";
    echo "    average = getAverage(playerValuesForm)\r\n";
    echo "    score = getScore(playerValuesForm)\r\n";
    echo "    entries = getEntries(playerValuesForm)\r\n";
    echo "    if (score > 0 && entries > 0)\r\n";
    echo "    {\r\n";
    echo "      matchAverage = score / entries\r\n";
    echo "      if (matchAverage > 0 && average > 0)\r\n";
    echo "      {\r\n";
    echo "        procentAverage = (matchAverage * 100) / average\r\n";
    echo "        parent.setFloatValue(playerValuesForm.ProcentAverage, procentAverage, getDecimals(playerValuesForm))\r\n";
    echo "      }\r\n";
    echo "      parent.setFloatValue(playerValuesForm.MatchAverage, matchAverage, getDecimals(playerValuesForm))\r\n";
    echo "    }\r\n";
    echo "    if (entries > 0 && isInternalMatchType())\r\n";
    echo "    {\r\n";
    echo "      opponentPlayerNo = getOpponentPlayerNo(playerNo)\r\n";
    echo "      opponentValuesForm = getPlayerValuesForm(opponentPlayerNo)\r\n";
    echo "      if (getEntries(opponentValuesForm) == 0)\r\n";
    echo "      {\r\n";
    echo "        parent.setIntValue(opponentValuesForm.Entries, entries)\r\n";
    echo "        changeEntries(opponentPlayerNo, opponentValuesForm)\r\n";
    echo "      }\r\n";
    echo "    }\r\n";
    echo "  }\r\n\r\n";
    echo "  function changePoint(playerNo, playerValuesForm)\r\n";
    echo "  {\r\n";
    echo "    point = getPoint(playerValuesForm)\r\n";
    echo "    if (isInternalMatchType())\r\n";
    echo "    {\r\n";
    echo "      opponentPlayerNo = getOpponentPlayerNo(playerNo)\r\n";
    echo "      opponentValuesForm = getPlayerValuesForm(opponentPlayerNo)\r\n";
    echo "      if (point == 0)\r\n";
    echo "        parent.setIntValue(opponentValuesForm.Point, 2)\r\n";
    echo "      else if (point == 1)\r\n";
    echo "        parent.setIntValue(opponentValuesForm.Point, 1)\r\n";
    echo "      else if (point > 1)\r\n";
    echo "        parent.setIntValue(opponentValuesForm.Point, 0)\r\n";
    echo "    }\r\n";
    echo "  }\r\n\r\n";
    echo "  function changeMatches(playerNo, playerValuesForm)\r\n";
    echo "  {\r\n";
    echo "    matches = getMatches(playerValuesForm)\r\n";
    echo "    if (isInternalMatchType())\r\n";
    echo "    {\r\n";
    echo "      opponentPlayerNo = getOpponentPlayerNo(playerNo)\r\n";
    echo "      opponentValuesForm = getPlayerValuesForm(opponentPlayerNo)\r\n";
    echo "      parent.setIntValue(opponentValuesForm.Matches, matches)\r\n";
    echo "    }\r\n";
    echo "  }\r\n\r\n";
    echo "  function getSelectedSeasonNo()\r\n";
    echo "  {\r\n";
    echo "    seasonNo = 0\r\n";
    echo "    for (i = 0; i < frameDefaults.DefaultsForm.SeasonNo.length; i++)\r\n";
    echo "    {\r\n";
    echo "      if (frameDefaults.DefaultsForm.SeasonNo.options[i].selected)\r\n";
    echo "        seasonNo = parseInt(frameDefaults.DefaultsForm.SeasonNo.options[i].value, 10)\r\n";
    echo "    }\r\n";
    echo "    return seasonNo\r\n";
    echo "  }\r\n\r\n";
    echo "  function getSelectedTypeNo()\r\n";
    echo "  {\r\n";
    echo "    typeNo = 0\r\n";
    echo "    for (i = 0; i < frameDefaults.DefaultsForm.TypeNo.length; i++)\r\n";
    echo "    {\r\n";
    echo "      if (frameDefaults.DefaultsForm.TypeNo.options[i].selected)\r\n";
    echo "        typeNo = parseInt(frameDefaults.DefaultsForm.TypeNo.options[i].value, 10)\r\n";
    echo "    }\r\n";
    echo "    return typeNo\r\n";
    echo "  }\r\n\r\n";
    echo "  function isInternalMatchType()\r\n";
    echo "  {\r\n";
    echo "    return frameDefaults.DefaultsForm.Internal.checked\r\n";
    echo "  }\r\n\r\n";
    echo "  function getSelectedAddressNo(selectPlayerForm)\r\n";
    echo "  {\r\n";
    echo "    addressNo = 0\r\n";
    echo "    for (i = 0; i < selectPlayerForm.AddressNo.length; i++)\r\n";
    echo "    {\r\n";
    echo "      if (selectPlayerForm.AddressNo.options[i].selected)\r\n";
    echo "        addressNo = parseInt(selectPlayerForm.AddressNo.options[i].value, 10)\r\n";
    echo "    }\r\n";
    echo "    return addressNo\r\n";
    echo "  }\r\n\r\n";
    echo "  function getOpponentPlayerNo(playerNo)\r\n";
    echo "  {\r\n";
    echo "    return (playerNo == 1) + 1\r\n";
    echo "  }\r\n\r\n";
    echo "  function getPlayerValuesForm(playerNo)\r\n";
    echo "  {\r\n";
    echo "    form = null\r\n";
    echo "    switch (playerNo)\r\n";
    echo "    {\r\n";
    echo "      case 1:\r\n";
    echo "        form = framePlayer1Values.PlayerValuesForm\r\n";
    echo "        break\r\n\r\n";
    echo "      case 2:\r\n";
    echo "        form = framePlayer2Values.PlayerValuesForm\r\n";
    echo "        break\r\n";
    echo "    }\r\n";
    echo "    return form\r\n";
    echo "  }\r\n\r\n";
    echo "  function getAverage(playerValuesForm)\r\n";
    echo "  {\r\n";
    echo "    return parseFloat(playerValuesForm.Average.value, 10)\r\n";
    echo "  }\r\n\r\n";
    echo "  function getDecimals(playerValuesForm)\r\n";
    echo "  {\r\n";
    echo "    return parseInt(playerValuesForm.Decimals.value, 10)\r\n";
    echo "  }\r\n\r\n";
    echo "  function getDistance(playerValuesForm)\r\n";
    echo "  {\r\n";
    echo "    return parseInt(playerValuesForm.Distance.value, 10)\r\n";
    echo "  }\r\n\r\n";
    echo "  function getScore(playerValuesForm)\r\n";
    echo "  {\r\n";
    echo "    return parent.getIntValue(playerValuesForm.Score)\r\n";
    echo "  }\r\n\r\n";
    echo "  function getEntries(playerValuesForm)\r\n";
    echo "  {\r\n";
    echo "    return parent.getIntValue(playerValuesForm.Entries)\r\n";
    echo "  }\r\n\r\n";
    echo "  function getSeries(playerValuesForm)\r\n";
    echo "  {\r\n";
    echo "    return parent.getIntValue(playerValuesForm.Series)\r\n";
    echo "  }\r\n\r\n";
    echo "  function getPoint(playerValuesForm)\r\n";
    echo "  {\r\n";
    echo "    return parent.getIntValue(playerValuesForm.Point)\r\n";
    echo "  }\r\n\r\n";
    echo "  function getMatches(playerValuesForm)\r\n";
    echo "  {\r\n";
    echo "    return parent.getIntValue(playerValuesForm.Matches)\r\n";
    echo "  }\r\n\r\n";
    echo "  function updatePlayersToSelect(seasonNo, typeNo)\r\n";
    echo "  {\r\n";
    $NewFun = ($Fun * 10) + 2;
    echo "    frameSelectPlayer1.location = 'matches.php?SeasonNo=' + seasonNo + '&TypeNo=' + typeNo + '&Fun=$NewFun'\r\n";
    $NewFun = ($Fun * 10) + 3;
    echo "    frameSelectPlayer2.location = 'matches.php?SeasonNo=' + seasonNo + '&TypeNo=' + typeNo + '&Fun=$NewFun'\r\n";
    echo "    updatePlayerValues(seasonNo, typeNo, 1, 0)\r\n";
    echo "    updatePlayerValues(seasonNo, typeNo, 2, 0)\r\n";
    echo "  }\r\n\r\n";
    echo "  function updatePlayerValues(seasonNo, typeNo, playerNo, addressNo)\r\n";
    echo "  {\r\n";
    echo "    switch (playerNo)\r\n";
    echo "    {\r\n";
    echo "      case 1:\r\n";
    $NewFun = ($Fun * 10) + 4;
    echo "        framePlayer1Values.location = 'matches.php?SeasonNo=' + seasonNo + '&TypeNo=' + typeNo + '&AddressNo=' + addressNo + '&Fun=$NewFun'\r\n";
    echo "        break\r\n\r\n";
    echo "      case 2:\r\n";
    $NewFun = ($Fun * 10) + 5;
    echo "        framePlayer2Values.location = 'matches.php?SeasonNo=' + seasonNo + '&TypeNo=' + typeNo + '&AddressNo=' + addressNo + '&Fun=$NewFun'\r\n";
    echo "        break\r\n";
    echo "    }\r\n";
    echo "  }\r\n\r\n";
    echo "  function getChildFrame(method)\r\n";
    echo "  {\r\n";
    echo "    childFrame = ''\r\n";
    echo "    switch (method)\r\n";
    echo "    {\r\n";
    echo "      case 1:\r\n";
    echo "        childFrame = frameDefaults.name\r\n";
    echo "        break\r\n";
    echo "    }\r\n";
    echo "    return childFrame\r\n";
    echo "  }\r\n";
    echo "</script>\r\n";
    echo "<html>\r\n";
    echo "  <head>\r\n";
    echo "    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=windows-1252\">\r\n";
    echo "    <title>Kampresultater</title>\r\n";
    echo "  </head>\r\n";
    echo "  <frameset rows=\"180,70,*\" frameborder=\"0\" framespacing=\"0\" border=\"0\">\r\n";
    $NewFun = ($Fun * 10) + 1;
    echo "    <frame name=\"frameDefaults\" scrolling=\"no\" noresize src=\"matches.php?SeasonNo=$SeasonNo&TypeNo=$TypeNo&Date=$Date&Fun=$NewFun\">\r\n";
    echo "    <frameset cols=\"350,*\" frameborder=\"0\" framespacing=\"0\" border=\"0\">\r\n";
    $NewFun = ($Fun * 10) + 2;
    echo "      <frame name=\"frameSelectPlayer1\" scrolling=\"no\" noresize src=\"matches.php?SeasonNo=$SeasonNo&TypeNo=$TypeNo&Fun=$NewFun\">\r\n";
    $NewFun = ($Fun * 10) + 3;
    echo "      <frame name=\"frameSelectPlayer2\" scrolling=\"no\" noresize src=\"matches.php?SeasonNo=$SeasonNo&TypeNo=$TypeNo&Fun=$NewFun\">\r\n";
    echo "    </frameset>\r\n";
    echo "    <frameset cols=\"350,*\" frameborder=\"0\" framespacing=\"0\" border=\"0\">\r\n";
    $NewFun = ($Fun * 10) + 4;
    echo "      <frame name=\"framePlayer1Values\" scrolling=\"auto\" noresize src=\"matches.php?SeasonNo=$SeasonNo&TypeNo=$TypeNo&AddressNo=0&Fun=$NewFun\">\r\n";
    $NewFun = ($Fun * 10) + 5;
    echo "      <frame name=\"framePlayer2Values\" scrolling=\"auto\" noresize src=\"matches.php?SeasonNo=$SeasonNo&TypeNo=$TypeNo&AddressNo=0&Fun=$NewFun\">\r\n";
    echo "    </frameset>\r\n";
    echo "  </frameset>\r\n";
    echo "</html>\r\n";
  }

  function MakeMatchRegistrationDefaults($AddressMatchTableName, $SystemNo, $SeasonTableNo, $TypeTableNo, $SeasonNo, $TypeNo, $Date, $Fun)
  {
    $SeasonSystemNo = get_system_for_table($SystemNo, $SeasonTableNo);
    if ($Fun == 11)
    {
      $CurrentSeasonNo = (integer) get_system_season_no($SystemNo);
      $PrevSeasonNo = (integer) get_table_field_value($SystemNo, $SeasonTableNo, $CurrentSeasonNo, "GroupNo");
      $SeasonStatement = "SELECT No,Description FROM Systemtables WHERE SystemNo=$SeasonSystemNo AND TableNo=$SeasonTableNo AND No IN ($CurrentSeasonNo,$PrevSeasonNo) ORDER BY Description,No";
    }
    else if ($Fun == 21)
    {
      $SeasonStatement = "SELECT No,Description FROM Systemtables WHERE SystemNo=$SeasonSystemNo AND TableNo=$SeasonTableNo ORDER BY Description,No";
    }
    else
      $SeasonStatement = "";
    $TypeSystemNo = get_system_for_table($SystemNo, $TypeTableNo);
    $TypeProperties = (integer) get_table_field_value($TypeSystemNo, $TypeTableNo, $TypeNo, "Properties");
    $AllowSelect = oswebdb_allow_select($AddressMatchTableName);
    $AllowInsert = oswebdb_allow_insert($AddressMatchTableName);
    $AllowUpdate = oswebdb_allow_update($AddressMatchTableName);
    $AllowDelete = oswebdb_allow_delete($AddressMatchTableName);
    $TabIndex = 1;
    MakeHtmlPageTop("Kampresultater");
    echo "    <form name=\"DefaultsForm\" action=\"javascript:parent.createRegistration()\">\r\n";
    echo "      <table border=\"0\" cellpadding=\"1\" cellspacing=\"1\" width=\"100%\">\r\n";
    echo "        <tr><td width=\"100%\" colspan=\"2\" valign=\"middle\"><h2><i>Kampregistrering</i></h2></td></tr>\r\n";
    echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\">";
    if ($Fun == 11)
    {
      if ($AllowInsert)
      {
        $Input = MakeHtmlInputButton("", "Opret", !$AllowInsert, $TabIndex++, "javascript:parent.createRegistration()");
        echo "$Input&nbsp;";
      }
      if ($AllowSelect)
      {
        $Input = MakeHtmlInputButton("", "Vis registreringer", !$AllowSelect, $TabIndex++, "javascript:parent.showRegistrations()");
        echo "$Input&nbsp;";
      }
    }
    else if ($Fun == 21)
    {
      if ($AllowInsert)
      {
        $Input = MakeHtmlInputButton("", "Kampregistrering", !$AllowInsert, $TabIndex++, "javascript:parent.makeRegistration()");
        echo "$Input&nbsp;";
      }
    }
    echo "</td></tr>\r\n";
    $Options = "<option value=\"0\"";
    if ($SeasonNo == 0)
      $Options = "$Options selected";
    $Options = "$Options>(Ingen)";
    if ($SeasonResult = oswebdb_query($SeasonStatement))
    {
      while ($SeasonRow = oswebdb_fetch_row($SeasonResult))
      {
        $Options = "$Options<option value=\"$SeasonRow[0]\"";
        if ($SeasonNo == $SeasonRow[0])
          $Options = "$Options selected";
        $Options = "$Options>$SeasonRow[1]";
      }
      oswebdb_free_result($SeasonResult);
    }
    $Input = MakeHtmlSelect("SeasonNo", 0, 0, !$AllowInsert, !$AllowInsert, $TabIndex++, "javascript:parent.changeSeasonNo()", $Options);
    echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Sæson :</td><td width=\"99%\">$Input</td></tr>\r\n";
    $Options = "<option value=\"0\"";
    if ($TypeNo == 0)
      $Options = "$Options selected";
    $Options = "$Options>(Ingen)";
    if ($TypeResult = oswebdb_query("SELECT No,Description FROM Systemtables WHERE SystemNo=$TypeSystemNo AND TableNo=$TypeTableNo ORDER BY Description,No"))
    {
      while ($TypeRow = oswebdb_fetch_row($TypeResult))
      {
        $Options = "$Options<option value=\"$TypeRow[0]\"";
        if ($TypeNo == $TypeRow[0])
          $Options = "$Options selected";
        $Options = "$Options>$TypeRow[1]";
      }
      oswebdb_free_result($TypeResult);
    }
    $FirstInput = MakeHtmlSelect("TypeNo", 0, 0, !$AllowInsert, !$AllowInsert, $TabIndex++, "javascript:parent.changeTypeNo()", $Options);
    $SecondInput = MakeHtmlInputCheckBox("Internal", "1", ($TypeProperties & 1), 1, $TabIndex++, "", "Intern kamptype");
    echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Kamptype :</td><td width=\"99%\">$FirstInput&nbsp;$SecondInput</td></tr>\r\n";
    if ($Fun == 11)
    {
      $Input = MakeHtmlInputText("Date", $Date, 10, 10, !$AllowInsert, !$AllowInsert, $TabIndex++, "javascript:return parent.changeDate()");
      echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Kampdato :</td><td width=\"99%\">$Input</td></tr>\r\n";
    }
    echo "      </table>\r\n";
    echo "    </form>\r\n";
    MakeHtmlPageBottom();
  }

  function MakeMatchRegistrationSelectPlayer($AddressTableName, $AddressLinkTableName, $AddressMatchTableName, $SystemNo, $SeasonTableNo, $AverageTableNo, $TypeTableNo, $SeasonNo, $TypeNo, $PlayerNo, $Fun)
  {
    $TypeSystemNo = get_system_for_table($SystemNo, $TypeTableNo);
    $AllowSelect = oswebdb_allow_select($AddressMatchTableName);
    $AllowInsert = oswebdb_allow_insert($AddressMatchTableName);
    $AllowUpdate = oswebdb_allow_update($AddressMatchTableName);
    $AllowDelete = oswebdb_allow_delete($AddressMatchTableName);
    $TabIndex = 1;
    MakeHtmlPageTop("Kampresultater");
    echo "    <form name=\"SelectPlayerForm\" action=\"javascript:parent.createRegistration()\">\r\n";
    echo "      <input type=\"hidden\" name=\"SeasonNo\" value=\"$SeasonNo\">\r\n";
    echo "      <input type=\"hidden\" name=\"TypeNo\" value=\"$TypeNo\">\r\n";
    if ($TypeResult = oswebdb_query("SELECT No,Description,Properties,GroupNo FROM Systemtables WHERE SystemNo=$TypeSystemNo AND TableNo=$TypeTableNo AND No=$TypeNo"))
    {
      if ($TypeRow = oswebdb_fetch_row($TypeResult))
      {
        $TypeProperties = (integer) $TypeRow[2];
        echo "      <table border=\"0\" cellpadding=\"1\" cellspacing=\"1\" width=\"100%\">\r\n";
        if ($Fun == 12 || $Fun == 13)
          echo "        <tr><td width=\"100%\"><strong>Spiller $PlayerNo:</strong></td></tr>\r\n";
        else if ($Fun == 22)
          echo "        <tr><td width=\"100%\"><strong>Spiller:</strong></td></tr>\r\n";
        else
          echo "        <tr><td width=\"100%\"><strong>Spiller:</strong></td></tr>\r\n";
        $Options = "<option value=\"0\" selected>(Ingen)";
        if ($PlayerNo == 1 || ($PlayerNo == 2 && ($TypeProperties & 1)))
        {
          if ($PlayerResult = oswebdb_query("SELECT a.No,a.Name FROM $AddressTableName AS a, $AddressLinkTableName AS al WHERE a.SystemNo=$SystemNo AND al.SystemNo=a.SystemNo AND al.AddressNo=a.No AND al.TableNo=$AverageTableNo AND al.SeasonNo=$SeasonNo AND al.TypeNo=$TypeRow[3] ORDER BY a.Name,a.No"))
          {
            while ($PlayerRow = oswebdb_fetch_row($PlayerResult))
              $Options = "$Options<option value=\"$PlayerRow[0]\">$PlayerRow[1]";
            oswebdb_free_result($PlayerResult);
          }
        }
        $Input = MakeHtmlSelect("AddressNo", 0, 0, !($AllowInsert && ($PlayerNo == 1 || ($PlayerNo == 2 && ($TypeProperties & 1)))), !($AllowInsert && ($PlayerNo == 1 || ($PlayerNo == 2 && ($TypeProperties & 1)))), $TabIndex++, "javascript:parent.changeAddressNo($PlayerNo)", $Options);
        echo "        <tr><td width=\"100%\">$Input</td></tr>\r\n";
        echo "      </table>\r\n";
      }
      oswebdb_free_result($TypeResult);
    }
    echo "    </form>\r\n";
    MakeHtmlPageBottom();
  }

  function MakeMatchRegistrationPlayerValues($AddressTableName, $AddressLinkTableName, $AddressMatchTableName, $SystemNo, $SeasonTableNo, $AverageTableNo, $TypeTableNo, $SeasonNo, $TypeNo, $PlayerNo, $AddressNo)
  {
    $TypeSystemNo = get_system_for_table($SystemNo, $TypeTableNo);
    $AverageSystemNo = get_system_for_table($SystemNo, $AverageTableNo);
    $AllowSelect = oswebdb_allow_select($AddressMatchTableName);
    $AllowInsert = oswebdb_allow_insert($AddressMatchTableName);
    $AllowUpdate = oswebdb_allow_update($AddressMatchTableName);
    $AllowDelete = oswebdb_allow_delete($AddressMatchTableName);
    $TabIndex = 1; $TabAdjust = 4; $Found = 0;
    MakeHtmlPageTop("Kampresultater");
    echo "    <form name=\"PlayerValuesForm\" action=\"javascript:parent.createRegistration()\">\r\n";
    echo "      <input type=\"hidden\" name=\"SeasonNo\" value=\"$SeasonNo\">\r\n";
    echo "      <input type=\"hidden\" name=\"TypeNo\" value=\"$TypeNo\">\r\n";
    echo "      <input type=\"hidden\" name=\"AddressNo\" value=\"$AddressNo\">\r\n";
    if ($PlayerResult = oswebdb_query("SELECT a.No,a.Name,al.Average,al.Distance,mt.Properties,at.Length FROM $AddressTableName AS a, $AddressLinkTableName AS al, Systemtables AS mt, Systemtables AS at WHERE a.SystemNo=$SystemNo AND a.No=$AddressNo AND al.SystemNo=a.SystemNo AND al.AddressNo=a.No AND al.TableNo=$AverageTableNo AND al.SeasonNo=$SeasonNo AND al.TypeNo=mt.GroupNo AND mt.SystemNo=$TypeSystemNo AND mt.TableNo=$TypeTableNo AND mt.No=$TypeNo AND at.SystemNo=$AverageSystemNo AND at.TableNo=$AverageTableNo AND at.No=mt.GroupNo"))
    {
      if ($PlayerRow = oswebdb_fetch_row($PlayerResult))
      {
        $TypeProperties = $PlayerRow[4];
        if ($MatchResult = oswebdb_query("SELECT AddressNo,TableNo,SeasonNo,TypeNo,MatchId,Date,Score,Entries,Series,Point,Matches FROM $AddressMatchTableName WHERE SystemNo=$SystemNo AND AddressNo=$PlayerRow[0] AND TableNo=$TypeTableNo AND SeasonNo=$SeasonNo AND TypeNo=$TypeNo ORDER BY Date DESC, MatchId DESC"))
        {
          $Found = 1;
          echo "      <input type=\"hidden\" name=\"Average\" value=\"$PlayerRow[2]\">\r\n";
          echo "      <input type=\"hidden\" name=\"Decimals\" value=\"$PlayerRow[5]\">\r\n";
          echo "      <input type=\"hidden\" name=\"Distance\" value=\"$PlayerRow[3]\">\r\n";
          echo "      <table border=\"0\" cellpadding=\"1\" cellspacing=\"1\" width=\"100%\">\r\n";
          $FirstInput = MakeHtmlInputText("Score", "", oswebdb_field_len($MatchResult, 6), oswebdb_field_len($MatchResult, 6), !$AllowInsert, !$AllowInsert, $TabIndex++, "javascript:parent.changeScore($PlayerNo, this.form)");
          $SecondInput = MakeHtmlInputText("", ($TypeProperties & 1) ? FormatNumber($PlayerRow[3], 0, 1) : "", oswebdb_field_len($PlayerResult, 3), oswebdb_field_len($PlayerResult, 3), 1, !$AllowInsert, $TabIndex + $TabAdjust, "");
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Score :</td><td width=\"49%\" nowrap>$FirstInput</td><td width=\"1%\" align=\"right\" nowrap>Distance :</td><td width=\"49%\" nowrap>$SecondInput</td></tr>\r\n";
          $FirstInput = MakeHtmlInputText("Entries", "", oswebdb_field_len($MatchResult, 7), oswebdb_field_len($MatchResult, 7), !$AllowInsert, !$AllowInsert, $TabIndex++, "javascript:parent.changeEntries($PlayerNo, this.form)");
          $SecondInput = MakeHtmlInputText("MatchAverage", "", oswebdb_field_len($PlayerResult, 2), oswebdb_field_len($PlayerResult, 2), 1, !$AllowInsert, $TabIndex + $TabAdjust, "");
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Indgange :</td><td width=\"49%\" nowrap>$FirstInput</td><td width=\"1%\" align=\"right\" nowrap>Gennemsnit :</td><td width=\"49%\" nowrap>$SecondInput</td></tr>\r\n";
          $FirstInput = MakeHtmlInputText("Series", "", oswebdb_field_len($MatchResult, 8), oswebdb_field_len($MatchResult, 8), !$AllowInsert, !$AllowInsert, $TabIndex++, "");
          $SecondInput = MakeHtmlInputText("ProcentAverage", "", oswebdb_field_len($PlayerResult, 2), oswebdb_field_len($PlayerResult, 2), 1, !$AllowInsert, $TabIndex + $TabAdjust, "");
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Serie :</td><td width=\"49%\" nowrap>$FirstInput</td><td width=\"1%\" align=\"right\" nowrap>%-gennemsnit :</td><td width=\"49%\" nowrap>$SecondInput</td></tr>\r\n";
          $FirstInput = MakeHtmlInputText("Point", "", oswebdb_field_len($MatchResult, 9), oswebdb_field_len($MatchResult, 9), !$AllowInsert || ($TypeProperties & 1) == 0, !$AllowInsert || ($TypeProperties & 1) == 0, $TabIndex++, "javascript:parent.changePoint($PlayerNo, this.form)");
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Point :</td><td width=\"99%\" colspan=\"3\" nowrap>$FirstInput</td></tr>\r\n";
          $FirstInput = MakeHtmlInputText("Matches", "1", oswebdb_field_len($MatchResult, 10), oswebdb_field_len($MatchResult, 10), !$AllowInsert, !$AllowInsert, $TabIndex++, "javascript:parent.changeMatches($PlayerNo, this.form)");
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Kampe :</td><td width=\"99%\" colspan=\"3\" nowrap>$FirstInput</td></tr>\r\n";
          echo "      </table>\r\n";
          oswebdb_free_result($MatchResult);
        }
      }
      oswebdb_free_result($PlayerResult);
    }
    if (!$Found)
    {
      echo "      <input type=\"hidden\" name=\"Average\" value=\"0\">\r\n";
      echo "      <input type=\"hidden\" name=\"Decimals\" value=\"0\">\r\n";
      echo "      <input type=\"hidden\" name=\"Distance\" value=\"0\">\r\n";
      echo "      <input type=\"hidden\" name=\"Score\" value=\"0\">\r\n";
      echo "      <input type=\"hidden\" name=\"Entries\" value=\"0\">\r\n";
      echo "      <input type=\"hidden\" name=\"MatchAverage\" value=\"0\">\r\n";
      echo "      <input type=\"hidden\" name=\"Series\" value=\"0\">\r\n";
      echo "      <input type=\"hidden\" name=\"ProcentAverage\" value=\"0\">\r\n";
      echo "      <input type=\"hidden\" name=\"Point\" value=\"0\">\r\n";
      echo "      <input type=\"hidden\" name=\"Matches\" value=\"0\">\r\n";
    }
    echo "    </form>\r\n";
    MakeHtmlPageBottom();
  }

  function MakeShowRegistrations($SystemNo, $SeasonTableNo, $SeasonNo, $TypeNo, $Fun)
  {
    $SeasonSystemNo = get_system_for_table($SystemNo, $SeasonTableNo);
    echo "<script language=\"JavaScript\">\r\n";
    echo "  function makeRegistration()\r\n";
    echo "  {\r\n";
    echo "    this.location = 'matches.php?SeasonNo=' + getSelectedSeasonNo() + '&TypeNo=' + getSelectedTypeNo() + '&Fun=1'\r\n";
    echo "  }\r\n\r\n";
    echo "  function modifyRegistration(matchRegistrationForm)\r\n";
    echo "  {\r\n";
    echo "    if (!(getSeasonNo(matchRegistrationForm) > 0))\r\n";
    echo "    {\r\n";
    echo "      alert('Der skal vælges en sæson!')\r\n";
    echo "    }\r\n";
    echo "    else if (!(getTypeNo(matchRegistrationForm) > 0))\r\n";
    echo "    {\r\n";
    echo "      alert('Der skal vælges en kamptype!')\r\n";
    echo "    }\r\n";
    echo "    else if (!(getAddressNo(matchRegistrationForm) > 0))\r\n";
    echo "    {\r\n";
    echo "      alert('Der skal vælges en spiller!')\r\n";
    echo "    }\r\n";
    echo "    else if (!(validateDate(getDate(matchRegistrationForm), getSeasonNo(matchRegistrationForm))))\r\n";
    echo "    {\r\n";
    echo "    }\r\n";
    echo "    else if (!(getEntries(matchRegistrationForm) > 0))\r\n";
    echo "    {\r\n";
    echo "      alert('Indgange skal være større end 0!')\r\n";
    echo "    }\r\n";
    echo "    else if (!(getMatches(matchRegistrationForm) > 0))\r\n";
    echo "    {\r\n";
    echo "      alert('Kampe skal være større end 0!')\r\n";
    echo "    }\r\n";
    echo "    else\r\n";
    $NewFun = ($Fun * 10) + 3;
    echo "      framePlayerRegistrations.location = 'matches.php?SeasonNo=' + getSeasonNo(matchRegistrationForm) + '&TypeNo=' + getTypeNo(matchRegistrationForm) + '&AddressNo=' + getAddressNo(matchRegistrationForm) + '&Fun=$NewFun&MatchId=' + getMatchId(matchRegistrationForm) + '&Date=' + getDate(matchRegistrationForm) + '&Score=' + getScore(matchRegistrationForm) + '&Entries=' + getEntries(matchRegistrationForm) + '&Series=' + getSeries(matchRegistrationForm) + '&Point=' + getPoint(matchRegistrationForm) + '&Matches=' + getMatches(matchRegistrationForm)\r\n";
    echo "  }\r\n\r\n";
    echo "  function deleteRegistration(matchRegistrationForm)\r\n";
    echo "  {\r\n";
    echo "    if (!(getSeasonNo(matchRegistrationForm) > 0))\r\n";
    echo "    {\r\n";
    echo "      alert('Der skal vælges en sæson!')\r\n";
    echo "    }\r\n";
    echo "    else if (!(getTypeNo(matchRegistrationForm) > 0))\r\n";
    echo "    {\r\n";
    echo "      alert('Der skal vælges en kamptype!')\r\n";
    echo "    }\r\n";
    echo "    else if (!(getAddressNo(matchRegistrationForm) > 0))\r\n";
    echo "    {\r\n";
    echo "      alert('Der skal vælges en spiller!')\r\n";
    echo "    }\r\n";
    echo "    else if (!(validateDate(getDate(matchRegistrationForm), getSeasonNo(matchRegistrationForm))))\r\n";
    echo "    {\r\n";
    echo "    }\r\n";
    echo "    else if (!(getEntries(matchRegistrationForm) > 0))\r\n";
    echo "    {\r\n";
    echo "      alert('Indgange skal være større end 0!')\r\n";
    echo "    }\r\n";
    echo "    else if (!(getMatches(matchRegistrationForm) > 0))\r\n";
    echo "    {\r\n";
    echo "      alert('Kampe skal være større end 0!')\r\n";
    echo "    }\r\n";
    echo "    else if (confirm('Bekræft sletning!'))\r\n";
    $NewFun = ($Fun * 10) + 3;
    echo "      framePlayerRegistrations.location = 'matches.php?SeasonNo=' + getSeasonNo(matchRegistrationForm) + '&TypeNo=' + getTypeNo(matchRegistrationForm) + '&AddressNo=' + getAddressNo(matchRegistrationForm) + '&Fun=$NewFun&MatchId=' + getMatchId(matchRegistrationForm)\r\n";
    echo "  }\r\n\r\n";
    echo "  function validateDate(matchDate, seasonNo)\r\n";
    echo "  {\r\n";
    echo "    year = parseInt(matchDate.substr(0, 4), 10)\r\n";
    echo "    month = parseInt(matchDate.substr(5, 2), 10)\r\n";
    echo "    date = parseInt(matchDate.substr(8, 2), 10)\r\n";
    echo "    if (parent.validateDate(year, month, date))\r\n";
    echo "    {\r\n";
    echo "      b = true\r\n";
    if ($SeasonResult = oswebdb_query("SELECT No,Description,FromDate,ToDate FROM Systemtables WHERE SystemNo=$SeasonSystemNo AND TableNo=$SeasonTableNo ORDER BY No,Description"))
    {
      if (oswebdb_num_rows($SeasonResult) > 0)
      {
        echo "      switch (seasonNo)\r\n";
        echo "      {\r\n";
        while ($SeasonRow = oswebdb_fetch_row($SeasonResult))
        {
          echo "        case $SeasonRow[0]: // $SeasonRow[1] ($SeasonRow[2] -> $SeasonRow[3])\r\n";
          echo "          checkDate = new Date(year, month, date)\r\n";
          $Year = substr($SeasonRow[2], 0, 4);
          $Month = substr($SeasonRow[2], 5, 2);
          $Day = substr($SeasonRow[2], 8, 2);
          echo "          fromDate = new Date(parseInt('$Year', 10), parseInt('$Month', 10), parseInt('$Day', 10))\r\n";
          $Year = substr($SeasonRow[3], 0, 4);
          $Month = substr($SeasonRow[3], 5, 2);
          $Day = substr($SeasonRow[3], 8, 2);
          echo "          toDate = new Date(parseInt('$Year', 10), parseInt('$Month', 10), parseInt('$Day', 10))\r\n";
          echo "          b = (checkDate >= fromDate) && (checkDate <= toDate)\r\n";
          echo "          break\r\n\r\n";
        }
        echo "        default:\r\n";
        echo "          b = true\r\n";
        echo "      }\r\n";
      }
      oswebdb_free_result($SeasonResult);
    }
    echo "      if (!b)\r\n";
    echo "        alert('Datoen skal være i periodeintervallet for sæsonen!')\r\n";
    echo "      return b\r\n";
    echo "    }\r\n";
    echo "    else\r\n";
    echo "      return false\r\n";
    echo "  }\r\n\r\n";
    echo "  function changeSeasonNo()\r\n";
    echo "  {\r\n";
    echo "    seasonNo = getSelectedSeasonNo()\r\n";
    echo "    updatePlayersToSelect(seasonNo, getSelectedTypeNo())\r\n";
    echo "  }\r\n\r\n";
    echo "  function changeTypeNo()\r\n";
    echo "  {\r\n";
    echo "    typeNo = getSelectedTypeNo()\r\n";
    echo "    parent.getInternalMatchType(typeNo, frameDefaults.DefaultsForm.Internal)\r\n";
    echo "    updatePlayersToSelect(getSelectedSeasonNo(), typeNo)\r\n";
    echo "  }\r\n\r\n";
    echo "  function changeAddressNo(playerNo)\r\n";
    echo "  {\r\n";
    echo "    seasonNo= getSelectedSeasonNo()\r\n";
    echo "    typeNo = getSelectedTypeNo()\r\n";
    echo "    updatePlayerRegistrations(seasonNo, typeNo, getSelectedAddressNo(frameSelectPlayer.SelectPlayerForm))\r\n";
    echo "  }\r\n\r\n";
    echo "  function changeDate(matchRegistrationForm)\r\n";
    echo "  {\r\n";
    echo "    s = matchRegistrationForm.Date.value\r\n";
    echo "    if (s.length == 8)\r\n";
    echo "    {\r\n";
    echo "      s = s.substr(0, 4) + '-' + s.substr(4, 2) + '-' + s.substr(6, 2)\r\n";
    echo "      matchRegistrationForm.Date.value = s\r\n";
    echo "    }\r\n";
    echo "    return validateDate(matchRegistrationForm.Date.value, getSelectedSeasonNo())\r\n";
    echo "  }\r\n\r\n";
    echo "  function changeScore(matchRegistrationForm)\r\n";
    echo "  {\r\n";
    echo "    matchRegistrationForm.MatchAverage.value = ''\r\n";
    echo "    matchRegistrationForm.ProcentAverage.value = ''\r\n";
    echo "    average = getAverage(matchRegistrationForm)\r\n";
    echo "    score = getScore(matchRegistrationForm)\r\n";
    echo "    entries = getEntries(matchRegistrationForm)\r\n";
    echo "    if (score > 0 && entries > 0)\r\n";
    echo "    {\r\n";
    echo "      matchAverage = score / entries\r\n";
    echo "      if (matchAverage > 0 && average > 0)\r\n";
    echo "      {\r\n";
    echo "        procentAverage = (matchAverage * 100) / average\r\n";
    echo "        parent.setFloatValue(matchRegistrationForm.ProcentAverage, procentAverage, getDecimals(matchRegistrationForm))\r\n";
    echo "      }\r\n";
    echo "      parent.setFloatValue(matchRegistrationForm.MatchAverage, matchAverage, getDecimals(matchRegistrationForm))\r\n";
    echo "    }\r\n";
    echo "    if (score > 0 && isInternalMatchType())\r\n";
    echo "    {\r\n";
    echo "      distance = getDistance(matchRegistrationForm)\r\n";
    echo "      if (score >= distance && distance > 0)\r\n";
    echo "        parent.setIntValue(matchRegistrationForm.Point, 2)\r\n";
    echo "      else\r\n";
    echo "        parent.setIntValue(matchRegistrationForm.Point, 0)\r\n";
    echo "    }\r\n";
    echo "  }\r\n\r\n";
    echo "  function changeEntries(matchRegistrationForm)\r\n";
    echo "  {\r\n";
    echo "    matchRegistrationForm.MatchAverage.value = ''\r\n";
    echo "    matchRegistrationForm.ProcentAverage.value = ''\r\n";
    echo "    average = getAverage(matchRegistrationForm)\r\n";
    echo "    score = getScore(matchRegistrationForm)\r\n";
    echo "    entries = getEntries(matchRegistrationForm)\r\n";
    echo "    if (score > 0 && entries > 0)\r\n";
    echo "    {\r\n";
    echo "      matchAverage = score / entries\r\n";
    echo "      if (matchAverage > 0 && average > 0)\r\n";
    echo "      {\r\n";
    echo "        procentAverage = (matchAverage * 100) / average\r\n";
    echo "        parent.setFloatValue(matchRegistrationForm.ProcentAverage, procentAverage, getDecimals(matchRegistrationForm))\r\n";
    echo "      }\r\n";
    echo "      parent.setFloatValue(matchRegistrationForm.MatchAverage, matchAverage, getDecimals(matchRegistrationForm))\r\n";
    echo "    }\r\n";
    echo "  }\r\n\r\n";
    echo "  function getSelectedSeasonNo()\r\n";
    echo "  {\r\n";
    echo "    seasonNo = 0\r\n";
    echo "    for (i = 0; i < frameDefaults.DefaultsForm.SeasonNo.length; i++)\r\n";
    echo "    {\r\n";
    echo "      if (frameDefaults.DefaultsForm.SeasonNo.options[i].selected)\r\n";
    echo "        seasonNo = parseInt(frameDefaults.DefaultsForm.SeasonNo.options[i].value, 10)\r\n";
    echo "    }\r\n";
    echo "    return seasonNo\r\n";
    echo "  }\r\n\r\n";
    echo "  function getSelectedTypeNo()\r\n";
    echo "  {\r\n";
    echo "    typeNo = 0\r\n";
    echo "    for (i = 0; i < frameDefaults.DefaultsForm.TypeNo.length; i++)\r\n";
    echo "    {\r\n";
    echo "      if (frameDefaults.DefaultsForm.TypeNo.options[i].selected)\r\n";
    echo "        typeNo = parseInt(frameDefaults.DefaultsForm.TypeNo.options[i].value, 10)\r\n";
    echo "    }\r\n";
    echo "    return typeNo\r\n";
    echo "  }\r\n\r\n";
    echo "  function isInternalMatchType()\r\n";
    echo "  {\r\n";
    echo "    return frameDefaults.DefaultsForm.Internal.checked\r\n";
    echo "  }\r\n\r\n";
    echo "  function getSelectedAddressNo(selectPlayerForm)\r\n";
    echo "  {\r\n";
    echo "    addressNo = 0\r\n";
    echo "    for (i = 0; i < selectPlayerForm.AddressNo.length; i++)\r\n";
    echo "    {\r\n";
    echo "      if (selectPlayerForm.AddressNo.options[i].selected)\r\n";
    echo "        addressNo = parseInt(selectPlayerForm.AddressNo.options[i].value, 10)\r\n";
    echo "    }\r\n";
    echo "    return addressNo\r\n";
    echo "  }\r\n\r\n";
    echo "  function getAddressNo(matchRegistrationForm)\r\n";
    echo "  {\r\n";
    echo "    return parseInt(matchRegistrationForm.AddressNo.value, 10)\r\n";
    echo "  }\r\n\r\n";
    echo "  function getSeasonNo(matchRegistrationForm)\r\n";
    echo "  {\r\n";
    echo "    return parseInt(matchRegistrationForm.SeasonNo.value, 10)\r\n";
    echo "  }\r\n\r\n";
    echo "  function getTypeNo(matchRegistrationForm)\r\n";
    echo "  {\r\n";
    echo "    return parseInt(matchRegistrationForm.TypeNo.value, 10)\r\n";
    echo "  }\r\n\r\n";
    echo "  function getMatchId(matchRegistrationForm)\r\n";
    echo "  {\r\n";
    echo "    return parseInt(matchRegistrationForm.MatchId.value, 10)\r\n";
    echo "  }\r\n\r\n";
    echo "  function getAverage(matchRegistrationForm)\r\n";
    echo "  {\r\n";
    echo "    return parseFloat(matchRegistrationForm.Average.value, 10)\r\n";
    echo "  }\r\n\r\n";
    echo "  function getDecimals(matchRegistrationForm)\r\n";
    echo "  {\r\n";
    echo "    return parseInt(matchRegistrationForm.Decimals.value, 10)\r\n";
    echo "  }\r\n\r\n";
    echo "  function getDistance(matchRegistrationForm)\r\n";
    echo "  {\r\n";
    echo "    return parseInt(matchRegistrationForm.Distance.value, 10)\r\n";
    echo "  }\r\n\r\n";
    echo "  function getDate(matchRegistrationForm)\r\n";
    echo "  {\r\n";
    echo "    return matchRegistrationForm.Date.value\r\n";
    echo "  }\r\n\r\n";
    echo "  function getScore(matchRegistrationForm)\r\n";
    echo "  {\r\n";
    echo "    return parent.getIntValue(matchRegistrationForm.Score)\r\n";
    echo "  }\r\n\r\n";
    echo "  function getEntries(matchRegistrationForm)\r\n";
    echo "  {\r\n";
    echo "    return parent.getIntValue(matchRegistrationForm.Entries)\r\n";
    echo "  }\r\n\r\n";
    echo "  function getSeries(matchRegistrationForm)\r\n";
    echo "  {\r\n";
    echo "    return parent.getIntValue(matchRegistrationForm.Series)\r\n";
    echo "  }\r\n\r\n";
    echo "  function getPoint(matchRegistrationForm)\r\n";
    echo "  {\r\n";
    echo "    return parent.getIntValue(matchRegistrationForm.Point)\r\n";
    echo "  }\r\n\r\n";
    echo "  function getMatches(matchRegistrationForm)\r\n";
    echo "  {\r\n";
    echo "    return parent.getIntValue(matchRegistrationForm.Matches)\r\n";
    echo "  }\r\n\r\n";
    echo "  function updatePlayersToSelect(seasonNo, typeNo)\r\n";
    echo "  {\r\n";
    $NewFun = ($Fun * 10) + 2;
    echo "    frameSelectPlayer.location = 'matches.php?SeasonNo=' + seasonNo + '&TypeNo=' + typeNo + '&Fun=$NewFun'\r\n";
    echo "    updatePlayerRegistrations(seasonNo, typeNo, 0)\r\n";
    echo "  }\r\n\r\n";
    echo "  function updatePlayerRegistrations(seasonNo, typeNo, addressNo)\r\n";
    echo "  {\r\n";
    $NewFun = ($Fun * 10) + 3;
    echo "    framePlayerRegistrations.location = 'matches.php?SeasonNo=' + seasonNo + '&TypeNo=' + typeNo + '&AddressNo=' + addressNo + '&Fun=$NewFun'\r\n";
    echo "  }\r\n\r\n";
    echo "  function getChildFrame(method)\r\n";
    echo "  {\r\n";
    echo "    childFrame = ''\r\n";
    echo "    switch (method)\r\n";
    echo "    {\r\n";
    echo "      case 1:\r\n";
    echo "        childFrame = frameDefaults.name\r\n";
    echo "        break\r\n";
    echo "    }\r\n";
    echo "    return childFrame\r\n";
    echo "  }\r\n";
    echo "</script>\r\n";
    echo "<html>\r\n";
    echo "  <head>\r\n";
    echo "    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=windows-1252\">\r\n";
    echo "    <title>Kampresultater</title>\r\n";
    echo "  </head>\r\n";
    echo "  <frameset rows=\"155,70,*\" frameborder=\"0\" framespacing=\"0\" border=\"0\">\r\n";
    $NewFun= ($Fun * 10) + 1;
    echo "    <frame name=\"frameDefaults\" scrolling=\"no\" noresize src=\"matches.php?SeasonNo=$SeasonNo&TypeNo=$TypeNo&Fun=$NewFun\">\r\n";
    $NewFun = ($Fun * 10) + 2;
    echo "    <frame name=\"frameSelectPlayer\" scrolling=\"no\" noresize src=\"matches.php?SeasonNo=$SeasonNo&TypeNo=$TypeNo&Fun=$NewFun\">\r\n";
    $NewFun = ($Fun * 10) + 3;
    echo "    <frame name=\"framePlayerRegistrations\" scrolling=\"auto\" noresize src=\"matches.php?SeasonNo=$SeasonNo&TypeNo=$TypeNo&AddressNo=0&Fun=$NewFun\">\r\n";
    echo "  </frameset>\r\n";
    echo "</html>\r\n";
  }

  function MakePlayerRegistrations($AddressTableName, $AddressLinkTableName, $AddressMatchTableName, $SystemNo, $SeasonTableNo, $AverageTableNo, $TypeTableNo, $SeasonNo, $TypeNo, $AddressNo)
  {
    $TypeSystemNo = get_system_for_table($SystemNo, $TypeTableNo);
    $AverageSystemNo = get_system_for_table($SystemNo, $AverageTableNo);
    $AllowSelect = oswebdb_allow_select($AddressMatchTableName);
    $AllowInsert = oswebdb_allow_insert($AddressMatchTableName);
    $AllowUpdate = oswebdb_allow_update($AddressMatchTableName);
    $AllowDelete = oswebdb_allow_delete($AddressMatchTableName);
    $TabIndex = 1;
    MakeHtmlPageTop("Kampresultater");
    if ($PlayerResult = oswebdb_query("SELECT a.No,a.Name,al.Average,al.Distance,mt.Properties,at.Length FROM $AddressTableName AS a, $AddressLinkTableName AS al, Systemtables AS mt, Systemtables AS at WHERE a.SystemNo=$SystemNo AND a.No=$AddressNo AND al.SystemNo=a.SystemNo AND al.AddressNo=a.No AND al.TableNo=$AverageTableNo AND al.SeasonNo=$SeasonNo AND al.TypeNo=mt.GroupNo AND mt.SystemNo=$TypeSystemNo AND mt.TableNo=$TypeTableNo AND mt.No=$TypeNo AND at.SystemNo=$AverageSystemNo AND at.TableNo=$AverageTableNo AND at.No=mt.GroupNo"))
    {
      if ($PlayerRow = oswebdb_fetch_row($PlayerResult))
      {
        $TypeProperties = $PlayerRow[4];
        echo "    <table border=\"1\" cellpadding=\"2\" cellspacing=\"0\">\r\n";
        echo "      <tr>\r\n";
        if ($AllowUpdate || $AllowDelete)
          echo "        <td align=\"right\" nowrap>&nbsp;</td>\r\n";
        echo "        <td nowrap><strong>Dato</strong></td>\r\n";
        echo "        <td nowrap><strong>Score</strong></td>\r\n";
        echo "        <td nowrap><strong>Indgange</strong></td>\r\n";
        echo "        <td nowrap><strong>Serie</strong></td>\r\n";
        echo "        <td nowrap><strong>Gennemsnit</strong></td>\r\n";
        echo "        <td nowrap><strong>%-gennemsnit</strong></td>\r\n";
        echo "        <td nowrap><strong>Point</strong></td>\r\n";
        echo "        <td nowrap><strong>Kampe</strong></td>\r\n";
        echo "      </tr>\r\n";
        if ($MatchResult = oswebdb_query("SELECT AddressNo,TableNo,SeasonNo,TypeNo,MatchId,Date,Score,Entries,Series,Point,Matches,Score/Entries,((Score/Entries)*100)/$PlayerRow[2] FROM $AddressMatchTableName WHERE SystemNo=$SystemNo AND AddressNo=$PlayerRow[0] AND TableNo=$TypeTableNo AND SeasonNo=$SeasonNo AND TypeNo=$TypeNo ORDER BY Date DESC, MatchId DESC"))
        {
          while ($MatchRow = oswebdb_fetch_row($MatchResult))
          {
             echo "      <form name=\"MatchRegistrationForm\" action=\"javascript:parent.modifyRegistration(0)\">\r\n";
             echo "        <input type=\"hidden\" name=\"AddressNo\" value=\"$MatchRow[0]\">\r\n";
             echo "        <input type=\"hidden\" name=\"SeasonNo\" value=\"$MatchRow[2]\">\r\n";
             echo "        <input type=\"hidden\" name=\"TypeNo\" value=\"$MatchRow[3]\">\r\n";
             echo "        <input type=\"hidden\" name=\"MatchId\" value=\"$MatchRow[4]\">\r\n";
             echo "        <input type=\"hidden\" name=\"Average\" value=\"$PlayerRow[2]\">\r\n";
             echo "        <input type=\"hidden\" name=\"Decimals\" value=\"$PlayerRow[5]\">\r\n";
             echo "        <input type=\"hidden\" name=\"Distance\" value=\"$PlayerRow[3]\">\r\n";
             echo "        <tr>\r\n";
             if ($AllowUpdate || $AllowDelete)
             {
               $InputUpdate = MakeHtmlInputButton("", "Opdatér", !$AllowUpdate, $TabIndex++, "javascript:parent.modifyRegistration(this.form)");
               $InputDelete = MakeHtmlInputButton("", "Slet", !$AllowDelete, $TabIndex++, "javascript:parent.deleteRegistration(this.form)");
               $InputSpace = "";
               if ($AllowUpdate && $AllowDelete)
                 $InputSpace = "&nbsp;";
               echo "          <td align=\"right\" nowrap>$InputUpdate$InputSpace$InputDelete</td>\r\n";
             }
             $Input = MakeHtmlInputText("Date", $MatchRow[5], oswebdb_field_len($MatchResult, 5), oswebdb_field_len($MatchResult, 5), !$AllowUpdate, !$AllowUpdate, $TabIndex++, "javascript:return parent.changeDate(this.form)");
             echo "          <td nowrap>$Input</td>\r\n";
             $Input = MakeHtmlInputText("Score", FormatNumber($MatchRow[6], 0, 1), oswebdb_field_len($MatchResult, 6), oswebdb_field_len($MatchResult, 6), !$AllowUpdate, !$AllowUpdate, $TabIndex++, "javascript:parent.changeScore(this.form)");
             echo "          <td nowrap>$Input</td>\r\n";
             $Input = MakeHtmlInputText("Entries", FormatNumber($MatchRow[7], 0, 1), oswebdb_field_len($MatchResult, 7), oswebdb_field_len($MatchResult, 7), !$AllowUpdate, !$AllowUpdate, $TabIndex++, "javascript:parent.changeEntries(this.form)");
             echo "          <td nowrap>$Input</td>\r\n";
             $Input = MakeHtmlInputText("Series", FormatNumber($MatchRow[8], 0, 1), oswebdb_field_len($MatchResult, 8), oswebdb_field_len($MatchResult, 8), !$AllowUpdate, !$AllowUpdate, $TabIndex++, "");
             echo "          <td nowrap>$Input</td>\r\n";
             $Input = MakeHtmlInputText("MatchAverage", FormatNumber($MatchRow[11], $PlayerRow[5], 1), oswebdb_field_len($PlayerResult, 2), oswebdb_field_len($PlayerResult, 2), 1, !$AllowUpdate, $TabIndex++, "");
             echo "          <td nowrap>$Input</td>\r\n";
             $Input = MakeHtmlInputText("ProcentAverage", FormatNumber($MatchRow[12], $PlayerRow[5], 1), oswebdb_field_len($PlayerResult, 2), oswebdb_field_len($PlayerResult, 2), 1, !$AllowUpdate, $TabIndex++, "");
             echo "          <td nowrap>$Input</td>\r\n";
             $Input = MakeHtmlInputText("Point", (($TypeProperties & 1) == 0 ? "" : FormatNumber($MatchRow[9], 0, 1)), oswebdb_field_len($MatchResult, 9), oswebdb_field_len($MatchResult, 9), !$AllowUpdate || ($TypeProperties & 1) == 0, !$AllowUpdate || ($TypeProperties & 1) == 0, $TabIndex++, "");
             echo "          <td nowrap>$Input</td>\r\n";
             $Input = MakeHtmlInputText("Matches", FormatNumber($MatchRow[10], 0, 1), oswebdb_field_len($MatchResult, 10), oswebdb_field_len($MatchResult, 10), !$AllowUpdate, !$AllowUpdate, $TabIndex++, "");
             echo "          <td nowrap>$Input</td>\r\n";
             echo "        </tr>\r\n";
             echo "      </form>\r\n";
          }
          oswebdb_free_result($MatchResult);
        }
        echo "    </table>\r\n";
      }
      oswebdb_free_result($PlayerResult);
    }
    MakeHtmlPageBottom();
  }
?>
