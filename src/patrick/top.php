<?php
  // Include required files.
  require_once("misc.php");
  require_once("oswebdb/oswebdb.php");
  require_once("oswebdb/tables.php");

  // Set error reporting.
  error_reporting((int) GetConfigValue("ErrorReporting"));

  // Make the html top page with information from the oswebdb database.
  $UserName = oswebdb_getusername();
  $Password = oswebdb_getpassword();
  if (oswebdb_connect($UserName, $Password))
  {
    if (oswebdb_selectdb())
    {
      $SystemNo = (int) GetConfigValue("SystemNo");
      if ($Result = oswebdb_query("SELECT Title,Properties,SeasonNo FROM Systems WHERE SystemNo=$SystemNo"))
      {
        if ($Row = oswebdb_fetch_row($Result))
        {
          $Information = "";
          $Addresses = "";
          $Debate = "";
          $Calender = "";
          $MatchResults = "";
          $Allowance = "";
          $ChangePassword = "";
          $Update = "";
          $Logon = "";
          if (($Row[1] & 4) && file_exists("address.php"))
          {
            $MakeAddressLink = 0;
            $GroupSystemNo = get_system_for_table($SystemNo, 3);
            $Statement = "SELECT a.No,a.Name,s.No,s.Description FROM Addresses AS a, Systemtables As s WHERE a.SystemNo=$SystemNo AND a.GroupNo=s.No AND a.Public IN (1) AND s.SystemNo=$GroupSystemNo AND s.TableNo=3 AND s.Public IN (1) GROUP BY a.No ORDER BY a.Name,a.Phone1";
            if (isset($UserName) && isset($Password))
              $Statement = "SELECT a.No,a.Name,s.No,s.Description FROM Addresses AS a, Systemtables As s WHERE a.SystemNo=$SystemNo AND a.GroupNo=s.No AND a.Public IN (0,1) AND s.SystemNo=$GroupSystemNo AND s.TableNo=3 AND s.Public IN (0,1) GROUP BY a.No ORDER BY a.Name,a.Phone1";
            if (oswebdb_get_rows($Statement) == 0)
            {
              $Statement = "SELECT No,Name FROM Addresses WHERE SystemNo=$SystemNo AND GroupNo=0 AND Public IN (1) ORDER BY Name,Phone1";
              if (isset($UserName) && isset($Password))
                $Statement = "SELECT No,Name FROM Addresses WHERE SystemNo=$SystemNo AND GroupNo=0 AND Public IN (0,1) ORDER BY Name,Phone1";
              if (oswebdb_get_rows($Statement) == 0)
                $MakeAddressLink = oswebdb_privileges("Addresses") > 1;
              else
                $MakeAddressLink = 1;
            }
            else
              $MakeAddressLink = 1;
            if ($MakeAddressLink)
            {
              $Addresses = MakeHtmlLink("", "javascript:parent.openDoc(0,'address.php')", "", "", "", "", "javascript:window.status='Adresser'; return true;", "javascript:window.status=''; return true;", "Adresser");
              $Addresses = "$Addresses&nbsp|&nbsp;";
            }
          }
          if (($Row[1] & 2) && file_exists("debate.php"))
          {
            $MakeDebateLink = 0;
            if (oswebdb_get_rows("SELECT Username,Email,Date,Time,Subject FROM Debate WHERE SystemNo=$SystemNo AND ParentUsername IS NULL AND ParentDate IS NULL AND ParentTime IS NULL ORDER BY Date DESC,Time DESC,Username") > 0)
              $MakeDebateLink = 1;
            else
              $MakeDebateLink = oswebdb_privileges("Debate") > 1;
            if ($MakeDebateLink)
            {
              $Debate = MakeHtmlLink("", "javascript:parent.openDoc(0,'debate.php')", "", "", "", "", "javascript:window.status='Debatter'; return true;", "javascript:window.status=''; return true;", "Debatter");
              $Debate = "$Debate&nbsp|&nbsp;";
            }
          }
          if (($Row[1] & 8) && file_exists("calender.php"))
          {
            $Calender = MakeHtmlLink("", "javascript:parent.openCalender(1)", "", "", "", "", "javascript:window.status='Kalender'; return true;", "javascript:window.status=''; return true;", "Kalender");
            $Calender = "$Calender&nbsp|&nbsp;";
          }
          if (isset($UserName) && isset($Password) && ($Row[1] & 4) && file_exists("matches.php"))
          {
            $SeasonSystemNo = get_system_for_table($SystemNo, 7);
            $MatchTypeSystemNo = get_system_for_table($SystemNo, 9);
            $PrevSeasonNo = (integer) get_table_field_value($SeasonSystemNo, 7, $Row[2], "GroupNo");
            if (oswebdb_privileges("Addressmatches") > 1 && oswebdb_get_rows("SELECT No,Description FROM Systemtables WHERE SystemNo=$MatchTypeSystemNo AND TableNo=9") > 0 && oswebdb_get_rows("SELECT a.No,a.Name,al.TypeNo,al.Average FROM Addresses AS a, Addresslinks AS al WHERE a.SystemNo=$SystemNo AND al.SystemNo=a.SystemNo AND al.AddressNo=a.No AND al.TableNo=8 AND al.SeasonNo IN ($PrevSeasonNo,$Row[2])") > 0)
            {
              $MatchResults = MakeHtmlLink("", "javascript:parent.openDoc(0,'matches.php?Fun=1')", "", "", "", "", "javascript:window.status='Kampregistrering'; return true;", "javascript:window.status=''; return true;", "Kampregistrering");
              $MatchResults = "$MatchResults&nbsp|&nbsp;";
            }
          }
          if (isset($UserName) && isset($Password) && ($Row[1] & 16) && file_exists("allowanc.php"))
          {
            if (oswebdb_privileges("Allowances") > 1 && oswebdb_privileges("Allowancelines") > 1)
            {
              $MakeAllowanceLink = 0;
              if (oswebdb_get_rows("SELECT Year,No,Description FROM Allowances WHERE SystemNo=$SystemNo ORDER BY Year DESC,Description,No") > 0)
                $MakeAllowanceLink = 1;
              else
                $MakeAllowanceLink = (oswebdb_privileges("Allowances") > 1) && (oswebdb_privileges("Allowancelines") > 1);
              if ($MakeAllowanceLink)
              {
                $Allowance = MakeHtmlLink("", "javascript:parent.openDoc(0,'allowanc.php')", "", "", "", "", "javascript:window.status='Kørselsfradrag'; return true;", "javascript:window.status=''; return true;", "Kørselsfradrag");
                $Allowance = "$Allowance&nbsp|&nbsp;";
              }
            }
          }
          if (isset($UserName) && isset($Password))
          {
            if (strlen($Information) > 0)
              $Information = "$Information, Bruger: $UserName";
            else
              $Information = "Bruger: $UserName";
            $ChangePassword = MakeHtmlLink("", "javascript:parent.changePassword('')", "", "", "", "", "javascript:window.status='Skift adgangskode'; return true;", "javascript:window.status=''; return true;", "Skift adgangskode");
            $ChangePassword = "$ChangePassword&nbsp|&nbsp";
            $Update = MakeHtmlLink("", "javascript:parent.reload()", "", "", "", "", "javascript:window.status='Opdatér'; return true;", "javascript:window.status=''; return true;", "Opdatér");
            $Update = "$Update&nbsp|&nbsp;";
            $Logon = MakeHtmlLink("", "javascript:parent.logoff()", "", "", "", "", "javascript:window.status='Log af'; return true;", "javascript:window.status=''; return true;", "Log af");
          }
          else
            $Logon = MakeHtmlLink("", "javascript:parent.logon()", "", "", "", "", "javascript:window.status='Log på'; return true;", "javascript:window.status=''; return true;", "Log på");
          MakeHtmlPageTop($Row[0]);
          echo "    <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">\r\n";
          echo "      <tr><td width=\"100%\" valign=\"middle\" align=\"center\" colspan=\"2\" nowrap><h1><strong>$Row[0]</strong></h1></td></tr>\r\n";
          echo "      <tr><td width=\"1%\" valign=\"middle\" align=\"left\" nowrap>$Information</td><td width=\"99%\" valign=\"middle\" align=\"right\" nowrap>$Addresses$Debate$Calender$MatchResults$Allowance$ChangePassword$Update$Logon</td></tr>\r\n";
          echo "    </table>\r\n";
          MakeHtmlPageBottom();
        }
        oswebdb_free_result($Result);
      }
    }
    oswebdb_close();
  }
?>
