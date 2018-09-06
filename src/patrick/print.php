<?php
  // Include required files.
  require_once("misc.php");
  require_once("oswebdb/oswebdb.php");
  require_once("oswebdb/systems.php");
  require_once("oswebdb/tables.php");

  // Set error reporting.
  error_reporting((int) GetConfigValue("ErrorReporting"));

  // Make a printable html page with information from the oswebdb database.
  $SystemNo = GetConfigValue("SystemNo");
  if (isset($_GET["ReportNo"]))
    $ReportNo = $_GET["ReportNo"];
  if (isset($_POST["ReportNo"]))
    $ReportNo = $_POST["ReportNo"];
  if (isset($_GET["Print"]))
    $Print = $_GET["Print"];
  if (isset($_POST["Print"]))
    $Print = $_POST["Print"];
  $UserName = oswebdb_getusername();
  $Password = oswebdb_getpassword();
  if (oswebdb_connect($UserName, $Password))
  {
    if (oswebdb_selectdb())
    {
      switch ($ReportNo)
      {
        case 1:
          $Landscape = 0;
          $Description = "Gennemsnit";
          $Parameters = "''";
          break;

        case 2:
          $Landscape = 1;
          $Description = "Kampresultater";
          $SeasonNo = 0;
          if (isset($_GET["SeasonNo"]))
            $SeasonNo = $_GET["SeasonNo"];
          $TypeNo = 0;
          if (isset($_GET["TypeNo"]))
            $TypeNo = $_GET["TypeNo"];
          $Parameters = "'&SeasonNo=$SeasonNo&TypeNo=$TypeNo'";
          break;

        default:
          $Landscape = 0;
          $Description = "Udskrivning";
          if ($Result = oswebdb_query("SELECT Title FROM Systems WHERE SystemNo=$SystemNo"))
          {
            if ($Row = oswebdb_fetch_row($Result))
              $Description = $Row[0];
            oswebdb_free_result($Result);
          }
          $Parameters = "''";
      }
      $Functions = "";
      if (isset($Print))
      {
        $f1 = MakeHtmlLink("", "javascript:window.print()", "", "", "", "", "javascript:window.status='Udskriv'; return true;", "javascript:window.status=''; return true;", "Udskriv");
        $f2 = MakeHtmlLink("", "javascript:window.close()", "", "", "", "", "javascript:window.status='Luk'; return true;", "javascript:window.status=''; return true;", "Luk");
        if (strlen($Functions) > 0)
          $Functions = "$Functions | $f1 | $f2";
        else
          $Functions = "$f1 | $f2";
      }
      else
      {
        $f = MakeHtmlLink("", "javascript:parent.printReport($ReportNo, $Landscape, $Parameters)", "", "", "", "", "javascript:window.status='Udskriv'; return true;", "javascript:window.status=''; return true;", "Udskriv");
        if (strlen($Functions) > 0)
          $Functions = "$Functions | $f";
        else
          $Functions = "$f";
      }
      if (isset($Print))
        MakeHtmlPrintPageTop($Description);
      else
        MakeHtmlPageTop($Description);
      echo "    <table border=\"0\" cellpadding=\"2\" cellspacing=\"2\" width=\"100%\">\r\n";
      switch ($ReportNo)
      {
        case 1:
          MakeAverageReport($UserName, $Password, $SystemNo, $ReportNo, $Print, $Description, $Functions);
          break;

        case 2:
          MakeMatchResultReport($UserName, $Password, $SystemNo, $ReportNo, $Print, $Description, $Functions, $SeasonNo, $TypeNo);
          break;
      }
      echo "    </table>\r\n";
      if (isset($Print))
        MakeHtmlPrintPageBottom();
      else
        MakeHtmlPageBottom();
    }
    oswebdb_close();
  }

  function MakeAverageReport($UserName, $Password, $SystemNo, $ReportNo, $Print, $Description, $Functions)
  {
    $SystemProperties = (integer) get_system_properties($SystemNo);
    $CurrentSeasonNo = (integer) get_system_season_no($SystemNo);
    if (($SystemProperties & 4) && ($CurrentSeasonNo > 0))
    {
      $GroupSystemNo = (integer) get_system_for_table($SystemNo, 3);
      $SeasonSystemNo = (integer) get_system_for_table($SystemNo, 7);
      $AverageSystemNo = (integer) get_system_for_table($SystemNo, 8);
      $SeasonDescription = get_table_field_value($SeasonSystemNo, 7, $CurrentSeasonNo, "Description");
      $Statement = "SELECT a.Name,a.Phone1,al.TypeNo,at.Description,at.Length,al.Average,al.Distance,g.No,g.Description,g.Properties FROM Addresses AS a, Addresslinks AS al, Systemtables AS at, Systemtables AS g WHERE a.SystemNo=$SystemNo AND a.Public IN (1) AND al.SystemNo=a.SystemNo AND al.AddressNo=a.No AND al.SeasonNo=$CurrentSeasonNo AND at.SystemNo=$AverageSystemNo AND at.TableNo=8 AND at.No=al.TypeNo AND g.SystemNo=$GroupSystemNo AND g.TableNo=3 AND g.No=a.GroupNo AND (g.Properties & 2) AND (g.Properties & 4) ORDER BY al.TypeNo,al.Average DESC,a.Name";
      if (isset($UserName) && isset($Password))
        $Statement = "SELECT a.Name,a.Phone1,al.TypeNo,at.Description,at.Length,al.Average,al.Distance,g.No,g.Description,g.Properties FROM Addresses AS a, Addresslinks AS al, Systemtables AS at, Systemtables AS g WHERE a.SystemNo=$SystemNo AND a.Public IN (0,1) AND al.SystemNo=a.SystemNo AND al.AddressNo=a.No AND al.SeasonNo=$CurrentSeasonNo AND at.SystemNo=$AverageSystemNo AND at.TableNo=8 AND at.No=al.TypeNo AND g.SystemNo=$GroupSystemNo AND g.TableNo=3 AND g.No=a.GroupNo AND (g.Properties & 2) AND (g.Properties & 4) ORDER BY al.TypeNo,al.Average DESC,a.Name";
      echo "      <tr><td width=\"97%\"><h2><i>$Description</i></h2></td><td width=\"3%\" colspan=\"3\" valign=\"top\" align=\"right\" nowrap>$Functions</td></tr>\r\n";
      echo "      <tr><td width=\"100%\" colspan=\"5\"><h3><i>$SeasonDescription</i></h3></td></tr>\r\n";
      echo "      <tr><td width=\"97%\"><h3><strong>Navn</strong></h3></td><td width=\"1%\" nowrap><h3><strong>Telefon</strong></h3></td><td width=\"1%\" align=\"right\" nowrap><h3><strong>&nbsp;Gennemsnit</strong></h3></td><td width=\"1%\" align=\"right\" nowrap><h3><strong>&nbsp;Intern distance</strong></h3></td></tr>\r\n";
      if ($Result = oswebdb_query($Statement))
      {
        $LastTypeNo = 0;
        while ($Row = oswebdb_fetch_row($Result))
        {
          if (((integer) $Row[2]) != $LastTypeNo)
          {
            if ($LastTypeNo > 0)
              echo "      <tr><td width=\"100%\" colspan=\"5\"><br></td></tr>\r\n";
            echo "      <tr><td width=\"100%\" colspan=\"5\"><h3><strong>$Row[3]</strong></h3></td></tr>\r\n";
            $LastTypeNo = (integer) $Row[2];
          }
          $Name = "&nbsp;";
          if (strlen($Row[0]) > 0)
            $Name = $Row[0];
          $Phone = "&nbsp;";
          if (strlen($Row[1]) > 0)
            $Phone = $Row[1];
          $Average = "&nbsp;";
          if (((float) $Row[5]) > 0)
            $Average = FormatNumber($Row[5], $Row[4], 1);
          $Distance = "&nbsp;";
          if (((integer) $Row[6]) > 0)
            $Distance = FormatNumber($Row[6], 0, 1);
          echo "      <tr><td width=\"97%\"><h3>$Name</h3></td><td width=\"1%\" nowrap><h3>$Phone</h3></td><td width=\"1%\" align=\"right\" nowrap><h3>$Average</h3></td><td width=\"1%\" align=\"right\" nowrap><h3>$Distance</h3></td></tr>\r\n";
        }
        oswebdb_free_result($Result);
      }
    }
  }

  function MakeMatchResultReport($UserName, $Password, $SystemNo, $ReportNo, $Print, $Description, $Functions, $SeasonNo, $TypeNo)
  {
    $SeasonSystemNo = get_system_for_table($SystemNo, 7);
    $AverageSystemNo = get_system_for_table($SystemNo, 8);
    $MatchSystemNo = get_system_for_table($SystemNo, 9);
    if ($MatchResult = oswebdb_query("SELECT s.No,s.Description,m.No,m.Description,m.Properties,at.No,at.Description,at.Length,a.No,a.Name,al.Average,al.Distance,SUM(am.Score),SUM(am.Entries),MAX(am.Series),SUM(am.Point),SUM(am.Point>1),SUM(am.Point=1),SUM(am.Point<1),SUM(am.Matches) FROM Systemtables AS s, Systemtables AS m, Systemtables AS at, Addresses AS a, Addresslinks AS al, Addressmatches AS am WHERE s.SystemNo=$SeasonSystemNo AND s.TableNo=7 AND s.No=$SeasonNo AND m.SystemNo=$MatchSystemNo AND m.TableNo=9 AND m.No=$TypeNo AND at.SystemNo=$AverageSystemNo AND at.TableNo=8 AND at.No=m.GroupNo AND a.SystemNo=$SystemNo AND al.SystemNo=a.SystemNo AND al.AddressNo=a.No AND al.TableNo=8 AND al.SeasonNo=s.No AND al.TypeNo=at.No AND am.SystemNo=a.SystemNo AND am.AddressNo=a.No AND am.TableNo=9 AND am.SeasonNo=s.No AND am.TypeNo=m.No GROUP BY a.No ORDER BY al.Average DESC,a.Name,a.No"))
    {
      $First = 1;
      while ($MatchRow = oswebdb_fetch_row($MatchResult))
      {
        if ($First)
        {
          if ($MatchRow[4] & 1)
          {
            echo "      <tr><td width=\"97%\" colspan=\"8\"><h2><i>$MatchRow[3]</i></h2></td><td width=\"3%\" colspan=\"5\" valign=\"top\" align=\"right\" nowrap>$Functions</td></tr>\r\n";
            echo "      <tr><td width=\"100%\" colspan=\"13\"><h3><i>$MatchRow[1]</i></h3></td></tr>\r\n";
            echo "      <tr><td width=\"93%\" nowrap><strong>Navn</strong></td><td width=\"1%\" align=\"right\" nowrap><strong>Tilm. snit</strong></td><td width=\"1%\" align=\"right\" nowrap><strong>Distance</strong></td><td width=\"1%\" align=\"right\" nowrap><strong>Score</strong></td><td width=\"1%\" align=\"right\" nowrap><strong>Indgange</strong></td><td width=\"1%\" align=\"right\" nowrap><strong>Serie</strong></td><td width=\"1%\" align=\"right\" nowrap><strong>Snit</strong></td><td width=\"1%\" align=\"right\" nowrap><strong>%-snit</strong></td><td width=\"1%\" align=\"right\" nowrap><strong>Point</strong></td><td width=\"1%\" align=\"right\" nowrap><strong>V</strong></td><td width=\"1%\" align=\"right\" nowrap><strong>U</strong></td><td width=\"1%\" align=\"right\" nowrap><strong>T</strong></td><td width=\"1%\" align=\"right\" nowrap><strong>Kampe</strong></td></tr>\r\n";
          }
          else
          {
            echo "      <tr><td width=\"97%\" colspan=\"5\"><h2><i>$MatchRow[3]</i></h2></td><td width=\"3%\" colspan=\"3\" valign=\"top\" align=\"right\" nowrap>$Functions</td></tr>\r\n";
            echo "      <tr><td width=\"100%\" colspan=\"8\"><h3><i>$MatchRow[1]</i></h3></td></tr>\r\n";
            echo "      <tr><td width=\"93%\" nowrap><strong>Navn</strong></td><td width=\"1%\" align=\"right\" nowrap><strong>Tilm. snit</strong></td><td width=\"1%\" align=\"right\" nowrap><strong>Score</strong></td><td width=\"1%\" align=\"right\" nowrap><strong>Indgange</strong></td><td width=\"1%\" align=\"right\" nowrap><strong>Serie</strong></td><td width=\"1%\" align=\"right\" nowrap><strong>Snit</strong></td><td width=\"1%\" align=\"right\" nowrap><strong>%-snit</strong></td><td width=\"1%\" align=\"right\" nowrap><strong>Kampe</strong></td></tr>\r\n";
          }
          $First = 0;
        }
        $Name = $MatchRow[9];
        $Average = FormatNumber($MatchRow[10], $MatchRow[7], 1);
        $Distance = FormatNumber($MatchRow[11], 0, 1);
        $Score = FormatNumber($MatchRow[12], 0, 1);
        $Entries = FormatNumber($MatchRow[13], 0, 1);
        $Series = FormatNumber($MatchRow[14], 0, 1);
        $MatchAverage = FormatNumber($MatchRow[12] / $MatchRow[13], $MatchRow[7], 1);
        $ProcentAverage = FormatNumber((($MatchRow[12] / $MatchRow[13]) * 100) / $MatchRow[10], $MatchRow[7], 1);
        $Point = "&nbsp";
        $Wound = "&nbsp";
        $Equal = "&nbsp";
        $Lost = "&nbsp";
        if ($MatchRow[4] & 1)
        {
          $Point = FormatNumber($MatchRow[15], 0, 1);
          $Wound = FormatNumber($MatchRow[16], 0, 1);
          $Equal = FormatNumber($MatchRow[17], 0, 1);
          $Lost = FormatNumber($MatchRow[18], 0, 1);
        }
        $Matches = FormatNumber($MatchRow[19], 0, 1);
        if ($MatchRow[4] & 1)
          echo "      <tr><td width=\"93%\" nowrap>$Name</td><td width=\"1%\" align=\"right\" nowrap>$Average</td><td width=\"1%\" align=\"right\" nowrap>$Distance</td><td width=\"1%\" align=\"right\" nowrap>$Score</td><td width=\"1%\" align=\"right\" nowrap>$Entries</td><td width=\"1%\" align=\"right\" nowrap>$Series</td><td width=\"1%\" align=\"right\" nowrap>$MatchAverage</td><td width=\"1%\" align=\"right\" nowrap>$ProcentAverage</td><td width=\"1%\" align=\"right\" nowrap>$Point</td><td width=\"1%\" align=\"right\" nowrap>$Wound</td><td width=\"1%\" align=\"right\" nowrap>$Equal</td><td width=\"1%\" align=\"right\" nowrap>$Lost</td><td width=\"1%\" align=\"right\" nowrap>$Matches</td></tr>\r\n";
        else
          echo "      <tr><td width=\"93%\" nowrap>$Name</td><td width=\"1%\" align=\"right\" nowrap>$Average</td><td width=\"1%\" align=\"right\" nowrap>$Score</td><td width=\"1%\" align=\"right\" nowrap>$Entries</td><td width=\"1%\" align=\"right\" nowrap>$Series</td><td width=\"1%\" align=\"right\" nowrap>$MatchAverage</td><td width=\"1%\" align=\"right\" nowrap>$ProcentAverage</td><td width=\"1%\" align=\"right\" nowrap>$Matches</td></tr>\r\n";
      }
      oswebdb_free_result($MatchResult);
    }
  }
?>
