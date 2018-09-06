<?php
  // Include required files.
  require_once("misc.php");
  require_once("oswebdb/oswebdb.php");
  require_once("oswebdb/tables.php");
  require_once("oswebdb/websites.php");

  // Set error reporting.
  error_reporting((int) GetConfigValue("ErrorReporting"));

  // Make the html page with information from the oswebdb database.
  $UserName = oswebdb_getusername();
  $Password = oswebdb_getpassword();
  if (oswebdb_connect($UserName, $Password))
  {
    if (oswebdb_selectdb())
    {
      $SystemNo = (int) GetConfigValue("SystemNo");
      if ($_GET["Logon"] == 1)
      {
        if ($Result = oswebdb_query("SELECT Title FROM Systems WHERE SystemNo=$SystemNo"))
        {
          if ($Row = oswebdb_fetch_row($Result))
          {
            if (!isset($UserName) && !isset($Password))
            {
              $HostName = GetConfigValue("HostName");
              MakeHtmlPageTop($Row[0]);
              echo "    <form action=\"home.php\" method=\"post\">\r\n";
              echo "      <input type=\"hidden\" name=\"Logon\" value=\"2\">\r\n";
              echo "      <table border=\"0\" cellpadding=\"1\" cellspacing=\"1\" width=\"100%\">\r\n";
              echo "        <tr><td width=\"100%\" colspan=\"2\"><h2><i>Log på $HostName</i></h2></td></tr>\r\n";
              $Input = MakeHtmlInputText("UserName", "", 16, 16, 0, 0, 1, "");
              echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Brugernavn :</td><td width=\"99%\">$Input</td></tr>\r\n";
              $Input = MakeHtmlInputPassword("Password", "", 16, 16, 0, 0, 2, "");
              echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Adgangskode :</td width=\"99%\"><td>$Input</td></tr>\r\n";
              $Input = MakeHtmlInputSubmit("", "Log på", 0, 3, "");
              echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td width=\"99%\"><td>$Input</td></tr>\r\n";
              echo "      </table>\r\n";
              echo "    </form>\r\n";
              MakeHtmlPageBottom();
            }
            else
              MakeHtmlPageReload("javascript:parent.afterLogonAndLogoff(); return true;");
          }
        }
        oswebdb_free_result($Result);
      }
      else if ($_POST["Logon"] == 2)
      {
        oswebdb_close();
        if (oswebdb_authorize($_POST["UserName"], $_POST["Password"]))
          MakeHtmlPageReload("javascript:parent.afterLogonAndLogoff(); return true;");
      }
      else if ($_GET["Logoff"] == 1)
      {
        oswebdb_clearusername();
        oswebdb_clearpassword();
        MakeHtmlPageReload("javascript:parent.afterLogonAndLogoff(); return true;");
      }
      else if ($_GET["ChangePassword"] == 1)
      {
        if ($Result = oswebdb_query("SELECT Title FROM Systems WHERE SystemNo=$SystemNo"))
        {
          if ($Row = oswebdb_fetch_row($Result))
          {
      	    if (isset($UserName) && isset($Password))
      	    {
              $HostName = GetConfigValue("HostName");
              MakeHtmlPageTop($Row[0]);
              echo "    <form action=\"home.php\" method=\"post\">\r\n";
              echo "      <input type=\"hidden\" name=\"ChangePassword\" value=\"2\">\r\n";
              echo "      <input type=\"hidden\" name=\"UserName\" value=\"$UserName\">\r\n";
              echo "      <table border=\"0\" cellpadding=\"1\" cellspacing=\"1\" width=\"100%\">\r\n";
              echo "        <tr><td width=\"100%\" colspan=\"2\"><h2><i>Skift adgangskode på $HostName</i></h2></td></tr>\r\n";
              if (isset($_GET["Message"]))
              {
              	$Message = $_GET["Message"];
                echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\">$Message</td></tr>\r\n";
              }
              $Input = MakeHtmlInputText("", "$UserName", 16, 16, 1, 1, 1, "");
              echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Brugernavn :</td><td width=\"99%\">$Input</td></tr>\r\n";
              $Input = MakeHtmlInputPassword("OldPassword", "", 16, 16, 0, 0, 2, "");
              echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Nuværende :</td width=\"99%\"><td>$Input</td></tr>\r\n";
              $Input = MakeHtmlInputPassword("NewPassword1", "", 16, 16, 0, 0, 3, "");
              echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Ny :</td width=\"99%\"><td>$Input</td></tr>\r\n";
              $Input = MakeHtmlInputPassword("NewPassword2", "", 16, 16, 0, 0, 4, "");
              echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Gentag ny :</td width=\"99%\"><td>$Input</td></tr>\r\n";
              $Input = MakeHtmlInputSubmit("", "Skift", 0, 5, "");
              echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td width=\"99%\"><td>$Input</td></tr>\r\n";
              echo "      </table>\r\n";
              echo "    </form>\r\n";
              MakeHtmlPageBottom();
      	    }
            else
              MakeHtmlPageReload("javascript:parent.afterLogonAndLogoff(); return true;");
          }
        }
        oswebdb_free_result($Result);
      }
      else if ($_POST["ChangePassword"] == 2 && isset($_POST["UserName"]) && isset($_POST["OldPassword"]) && isset($_POST["NewPassword1"]) && isset($_POST["NewPassword2"]))
      {
      	if ($_POST["OldPassword"] != $Password)
        	MakeHtmlPageReload("javascript:parent.changePassword('Forkert adgangskode!'); return true;");
        else if ($_POST["NewPassword1"] == "" || $_POST["NewPassword2"] == "")
          MakeHtmlPageReload("javascript:parent.changePassword('Den nye adgangskode skal indtastes!'); return true;");
        else if ($_POST["NewPassword1"] != $_POST["NewPassword2"])
          MakeHtmlPageReload("javascript:parent.changePassword('Den nye adgangskode er forkert indtastet!'); return true;");
        else if (!oswebdb_change_password($_POST["UserName"], $_POST["NewPassword1"]))
          MakeHtmlPageReload("javascript:parent.changePassword('Kunne ikke skifte adgangskode!'); return true;");
        else
          MakeHtmlPageReload("javascript:parent.changePassword('Adgangskoden er skiftet!'); return true;");
      }
      else if ($_GET["Home"] == 1)
      {
        if ($Result = oswebdb_query("SELECT Title FROM Systems WHERE SystemNo=$SystemNo"))
        {
          if ($Row = oswebdb_fetch_row($Result))
          {
            $NewsTypes = get_news_types($SystemNo);
            $NewsFields = get_website_fields();
            $ShowOnHomepageTypes = get_showonhomepage_types($SystemNo);
            $ShowOnHomepageFields = get_website_fields();
            $LastTypeNo = 0;
            $FirstNews = 1;
            MakeHtmlPageTop($Row[0]);
            if (($NewsResult = oswebdb_query("SELECT $NewsFields FROM Websites WHERE SystemNo=$SystemNo AND TypeNo IN ($NewsTypes) ORDER BY Created DESC,Description")) && ($ShowOnHomepageResult = oswebdb_query("SELECT $ShowOnHomepageFields FROM Websites WHERE SystemNo=$SystemNo AND TypeNo IN ($ShowOnHomepageTypes) ORDER BY Description")))
            {
              echo "    <table border=\"0\" cellpadding=\"2\" cellspacing=\"2\" width=\"100%\">\r\n";
              echo "      <tr><td width=\"99%\" valign=\"top\" align=\"left\">\r\n";
              while ($NewsRow = oswebdb_fetch_row($NewsResult))
              {
                if ($NewsRow[0] != $LastTypeNo)
                {
                  $TypeFields = get_table_field_value(get_system_for_table($SystemNo, 4), 4, $NewsRow[0], "ShowFields");
                  $TypeProperties = get_table_field_value(get_system_for_table($SystemNo, 4), 4, $NewsRow[0], "Properties");
                  $TypeText = get_table_field_value(get_system_for_table($SystemNo, 4), 4, $NewsRow[0], "Text3");
                  $LastTypeNo = $NewsRow[0];
                }
                if (use_website($TypeFields, $NewsRow[4], $UserName, $Password, $NewsRow[5], $NewsRow[6], $NewsRow[7], $NewsRow[8], $NewsRow[15]))
                {
                  $Header = get_website_description($TypeFields, $TypeProperties, $NewsRow[2], $NewsRow[3], $TypeText);
                  if (strlen($Header) > 0)
                    $Header = "<h2><i>$Header</i></h2>";
                  $Content = "";
                  if (($TypeFields & 512) && strlen($NewsRow[16]) > 0)
                  {
                    $Link = MakeHtmlLink("", "$NewsRow[16]", "window", "", "", "", "javascript:window.status='$NewsRow[2]'; return true", "javascript:window.status=''; return true;", "$NewsRow[16]");
                    if (strlen($Content) > 0)
                      $Content = "$Content<br>Dokument: $Link";
                    else
                      $Content = "Dokument: $Link";
                  }
                  if (($TypeFields & 64) && strlen($NewsRow[13]) > 0)
                  {
                    $Link = MakeHtmlLink("", "http://$NewsRow[13]", "window", "", "", "", "javascript:window.status='$NewsRow[2]'; return true", "javascript:window.status=''; return true;", "$NewsRow[13]");
                    if (strlen($Content) > 0)
                      $Content = "$Content<br>Web: $Link";
                    else
                      $Content = "Web: $Link";
                  }
                  if (($TypeFields & 128) && strlen($NewsRow[14]) > 0)
                  {
                    $NewsContent = $NewsRow[14];
                    $Pos = strpos($NewsContent, "\r\n");
                    while ($Pos)
                    {
                      $s1 = substr($NewsContent, 0, $Pos);
                      $s2 = substr($NewsContent, $Pos + 2, strlen($NewsContent) - ($Pos + 1));
                      $NewsContent = "$s1";
                      if (strlen($s2) > 0)
                        $NewsContent = "$NewsContent<br>$s2";
                      $Pos = strpos($NewsContent, "\r\n");
                    }
                    if (strlen($NewsContent) >= 4)
                    {
                      while (substr($NewsContent, strlen($NewsContent) - 4, 4) == "<br>")
                        $NewsContent = substr($NewsContent, 0, strlen($NewsContent) - 4);
                    }
                    if (strlen($Content) > 0)
                      $Content = "$NewsContent<br><br>$Content";
                    else
                      $Content = "$NewsContent";
                  }
                  $Content = "$Header$Content";
                  echo "        <table border=\"0\" cellpadding=\"2\" cellspacing=\"2\" width=\"100%\">\r\n";
                  if (($TypeFields & 32) && strlen($NewsRow[9]) && file_exists($NewsRow[9]))
                  {
                    $Picture = "<img src=\"$NewsRow[9]\" border=\"0\">";
                    if (strlen($NewsRow[10]) && file_exists($NewsRow[10]))
                      $Picture = MakeHtmlLink("", "$NewsRow[9]", "window", "", "", "", "javascript:window.status='$NewsRow[2]'; return true", "javascript:window.status=''; return true;", "<img src=\"$NewsRow[10]\" border=\"0\">");
                    switch ($NewsRow[11])
                    {
                      case 1:
                        $Align = "left";
                        break;

                      case 2:
                        $Align = "center";
                        break;

                      case 4:
                        $Align = "right";
                        break;

                      default:
                        $Align = "left";
                    }
                    switch ($NewsRow[12])
                    {
                      case 1:
                        $VAlign = "top";
                        break;

                      case 2:
                        $VAlign = "middle";
                        break;

                      case 4:
                        $VAlign = "bottom";
                        break;

                      default:
                        $VAlign = "top";
                    }
                    switch ($NewsRow[11])
                    {
                      case 1:
                        if (!$FirstNews)
                          echo "          <tr><td width=\"100%\" valign=\"top\" align=\"left\" colspan=\"2\"><br></td></tr>\r\n";
                        echo "          <tr><td width=\"1%\" valign=\"$VAlign\" align=\"$Align\" nowrap>$Picture</td><td width=\"99%\" valign=\"top\" align=\"left\">$Content</td></tr>\r\n";
                        break;

                      case 2:
                        if (!$FirstNews)
                          echo "          <tr><td width=\"100%\" valign=\"top\" align=\"left\" colspan=\"2\"><br></td></tr>\r\n";
                        echo "          <tr><td width=\"1%\" valign=\"$VAlign\" align=\"$Align\" nowrap>$Picture</td><td width=\"99%\" valign=\"top\" align=\"left\">$Content</td></tr>\r\n";
                        break;

                      case 4:
                        if (!$FirstNews)
                          echo "          <tr><td width=\"100%\" valign=\"top\" align=\"left\" colspan=\"2\"><br></td></tr>\r\n";
                        echo "          <tr><td width=\"99%\" valign=\"top\" align=\"left\">$Content</td><td width=\"1%\" valign=\"$VAlign\" align=\"$Align\" nowrap>$Picture</td></tr>\r\n";
                        break;

                      default:
                        if (!$FirstNews)
                          echo "          <tr><td width=\"100%\" valign=\"top\" align=\"left\" colspan=\"2\"><br></td></tr>\r\n";
                        echo "          <tr><td width=\"1%\" valign=\"$VAlign\" align=\"$Align\" nowrap>$Picture</td><td width=\"99%\" valign=\"top\" align=\"left\">$Content</td></tr>\r\n";
                    }
                  }
                  else
                  {
                    if (!$FirstNews)
                      echo "          <tr><td width=\"100%\" valign=\"top\" align=\"left\"><br></td></tr>\r\n";
                    echo "          <tr><td width=\"100%\" valign=\"top\" align=\"left\">$Content</td></tr>\r\n";
                  }
                  echo "        </table>\r\n";
                  $FirstNews = 0;
                }
              }
              echo "      </td><td width=\"1%\" valign=\"top\" align=\"left\" nowrap>\r\n";
              while ($ShowOnHomepageRow = oswebdb_fetch_row($ShowOnHomepageResult))
              {
                if ($ShowOnHomepageRow[0] != $LastTypeNo)
                {
                  $TypeFields = get_table_field_value(get_system_for_table($SystemNo, 4), 4, $ShowOnHomepageRow[0], "ShowFields");
                  $TypeProperties = get_table_field_value(get_system_for_table($SystemNo, 4), 4, $ShowOnHomepageRow[0], "Properties");
                  $TypeText = get_table_field_value(get_system_for_table($SystemNo, 4), 4, $ShowOnHomepageRow[0], "Text3");
                  $LastTypeNo = $ShowOnHomepageRow[0];
                }
                if (use_website($TypeFields, $ShowOnHomepageRow[4], $UserName, $Password, $ShowOnHomepageRow[5], $ShowOnHomepageRow[6], $ShowOnHomepageRow[7], $ShowOnHomepageRow[8], $ShowOnHomepageRow[15]))
                {
                  $ShowOnHomepageDescription = get_website_description($TypeFields, $TypeProperties, $ShowOnHomepageRow[2], $ShowOnHomepageRow[3], $TypeText);
                  $ShowOnHomepageLink = get_website_link($TypeFields, $TypeProperties, $ShowOnHomepageRow[0], $ShowOnHomepageRow[1], $ShowOnHomepageRow[2], $ShowOnHomepageRow[13], $ShowOnHomepageRow[16], $ShowOnHomepageRow[17]);
                  $ExternalShowOnHomepage = external_website($TypeFields, $TypeProperties, $ShowOnHomepageRow[13], $ShowOnHomepageRow[16]);
                  $ShowOnHomepageHtmlLink = MakeHtmlLink("", $ShowOnHomepageLink, ($ExternalShowOnHomepage ? "window" : ""), "", "", "", "javascript:window.status='$ShowOnHomepageRow[2]'; return true;", "javascript:window.status=''; return true;", $ShowOnHomepageDescription);
                  if (new_website($ShowOnHomepageRow[3]))
                    $ShowOnHomepageHtmlLink = "<strong>$ShowOnHomepageHtmlLink</strong>";
                  echo "        <table border=\"0\" cellpadding=\"2\" cellspacing=\"2\" width=\"100%\">\r\n";
                  echo "          <tr><td width=\"100%\" nowrap>$ShowOnHomepageHtmlLink</td></tr>\r\n";
                  echo "        </table>\r\n";
                }
              }
              echo "      </td></tr>\r\n";
              echo "    </table>\r\n";
              oswebdb_free_result($NewsResult);
              oswebdb_free_result($ShowOnHomepageResult);
            }
            MakeHtmlPageBottom();
          }
        }
        oswebdb_free_result($Result);
      }
      else if (isset($_GET["TypeNo"]) && isset($_GET["MenuNo"]) && isset($_GET["Description"]))
      {
        $TypeSystemNo = get_system_for_table($SystemNo, 4);
        $TypeNo = $_GET["TypeNo"];
        $TypeFields = get_table_field_value($TypeSystemNo, 4, $TypeNo, "ShowFields");
        $TypeProperties = get_table_field_value($TypeSystemNo, 4, $TypeNo, "Properties");
        $TypeText = get_table_field_value($TypeSystemNo, 4, $TypeNo, "Text3");
        $ContentTypeNo = get_table_field_value($TypeSystemNo, 4, $TypeNo, "GroupNo");
        if ($ContentTypeNo > 0)
        {
          $ContentTypeSystemNo = get_system_for_table($SystemNo, 5);
          $ContentTypeFields = get_table_field_value($ContentTypeSystemNo, 5, $ContentTypeNo, "ShowFields");
          $ContentTypeProperties = get_table_field_value($ContentTypeSystemNo, 5, $ContentTypeNo, "Properties");
          $ContentTypeGroupNo = get_table_field_value($ContentTypeSystemNo, 5, $ContentTypeNo, "GroupNo");
          $ContentTypeGroupSystemNo = get_system_for_table($SystemNo, $ContentTypeGroupNo);
        }
        $MenuNo = $_GET["MenuNo"];
        $Description = $_GET["Description"];
        $WebsiteFields = get_website_fields();
        if (isset($_GET["Print"]))
          MakeHtmlPrintPageTop($Description);
        else
          MakeHtmlPageTop($Description);
        if ($WebsiteResult = oswebdb_query("SELECT $WebsiteFields FROM Websites WHERE SystemNo=$SystemNo AND TypeNo=$TypeNo AND MenuNo=$MenuNo AND Description=\"$Description\""))
        {
          if ($WebsiteRow = oswebdb_fetch_row($WebsiteResult))
          {
            if ($TypeProperties & 4)
            {
              $HtmlContent = $WebsiteRow[14];
              $Pos = strpos($HtmlContent, "\r\n");
              while ($Pos)
              {
                $s = substr($HtmlContent, 0, $Pos);
                echo "    $s\r\n";
                $HtmlContent = substr($HtmlContent, $Pos + 2, strlen($HtmlContent) - ($Pos + 1));
                $Pos = strpos($HtmlContent, "\r\n");
              }
              if (strlen($HtmlContent) > 0)
                echo "    $HtmlContent\r\n";
            }
            else
            {
              $Functions = "";
              if ($TypeProperties & 128)
              {
                if (isset($_GET["Print"]))
                {
                  $Print = MakeHtmlLink("", "javascript:window.print()", "", "", "", "", "javascript:window.status='Udskriv'; return true;", "javascript:window.status=''; return true;", "Udskriv");
                  $Close = MakeHtmlLink("", "javascript:window.close()", "", "", "", "", "javascript:window.status='Luk'; return true;", "javascript:window.status=''; return true;", "Luk");
                  if (strlen($Functions) > 0)
                    $Functions = "$Functions | $Print | $Close";
                  else
                    $Functions = "$Print | $Close";
                }
                else
                {
                  $Print = MakeHtmlLink("", "javascript:parent.printWebsite($WebsiteRow[0], $WebsiteRow[1], '$WebsiteRow[2]')", "", "", "", "", "javascript:window.status='Udskriv'; return true;", "javascript:window.status=''; return true;", "Udskriv");
                  if (strlen($Functions) > 0)
                    $Functions = "$Functions | $Print";
                  else
                    $Functions = "$Print";
                }
              }
              $Header = get_website_description($TypeFields, $TypeProperties, $WebsiteRow[2], $WebsiteRow[3], $TypeText);
              $Content = "";
              if (($TypeFields & 512) && strlen($WebsiteRow[16]) > 0)
              {
                $Link = MakeHtmlLink("", "$WebsiteRow[16]", "window", "", "", "", "javascript:window.status='$WebsiteRow[2]'; return true", "javascript:window.status=''; return true;", "$WebsiteRow[16]");
                if (strlen($Content) > 0)
                  $Content = "$Content<br>Dokument: $Link";
                else
                  $Content = "Dokument: $Link";
              }
              if (($TypeFields & 64) && strlen($WebsiteRow[13]) > 0)
              {
                $Link = MakeHtmlLink("", "http://$WebsiteRow[13]", "window", "", "", "", "javascript:window.status='$WebsiteRow[2]'; return true", "javascript:window.status=''; return true;", "$WebsiteRow[13]");
                if (strlen($Content) > 0)
                  $Content = "$Content<br>Web: $Link";
                else
                  $Content = "Web: $Link";
              }
              if (($TypeFields & 128) && strlen($WebsiteRow[14]) > 0)
              {
                $WebsiteContent = $WebsiteRow[14];
                $Pos = strpos($WebsiteContent, "\r\n");
                while ($Pos)
                {
                  $s1 = substr($WebsiteContent, 0, $Pos);
                  $s2 = substr($WebsiteContent, $Pos + 2, strlen($WebsiteContent) - ($Pos + 1));
                  $WebsiteContent = "$s1";
                  if (strlen($s2) > 0)
                    $WebsiteContent = "$WebsiteContent<br>$s2";
                  $Pos = strpos($WebsiteContent, "\r\n");
                }
                if (strlen($WebsiteContent) >= 4)
                {
                  while (substr($WebsiteContent, strlen($WebsiteContent) - 4, 4) == "<br>")
                    $WebsiteContent = substr($WebsiteContent, 0, strlen($WebsiteContent) - 4);
                }
                if (strlen($Content) > 0)
                  $Content = "$WebsiteContent<br><br>$Content";
                else
                  $Content = "$WebsiteContent";
              }
              echo "    <table border=\"0\" cellpadding=\"2\" cellspacing=\"2\" width=\"100%\">\r\n";
              echo "      <tr><td width=\"99%\" valign=\"middle\" align=\"left\" nowrap><h2><i>$Header</i></h2></td><td width=\"1%\" valign=\"top\" align=\"right\" nowrap>$Functions</td></tr>\r\n";
              if (($TypeFields & 32) && strlen($WebsiteRow[9]) && file_exists($WebsiteRow[9]))
              {
                $Picture = "<img src=\"$WebsiteRow[9]\" border=\"0\">";
                if (strlen($WebsiteRow[10]) && file_exists($WebsiteRow[10]))
                  $Picture = MakeHtmlLink("", "$WebsiteRow[9]", "window", "", "", "", "javascript:window.status='$WebsiteRow[2]'; return true", "javascript:window.status=''; return true;", "<img src=\"$WebsiteRow[10]\" border=\"0\">");
                switch ($WebsiteRow[11])
                {
                  case 1:
                    $Align = "left";
                    break;

                  case 2:
                    $Align = "center";
                    break;

                  case 4:
                    $Align = "right";
                    break;

                  default:
                    $Align = "left";
                }
                switch ($WebsiteRow[12])
                {
                  case 1:
                    $VAlign = "top";
                    break;

                  case 2:
                    $VAlign = "middle";
                    break;

                  case 4:
                    $VAlign = "bottom";
                    break;

                  default:
                    $VAlign = "top";
                }
                echo "      <tr><td width=\"100%\" colspan=\"2\">\r\n";
                echo "        <table border=\"0\" cellpadding=\"2\" cellspacing=\"2\" width=\"100%\">\r\n";
                if (strlen($Content) > 0)
                {
                  switch ($WebsiteRow[11])
                  {
                    case 1:
                      echo "          <tr><td width=\"1%\" valign=\"$VAlign\" align=\"$Align\" nowrap>$Picture</td><td width=\"99%\" valign=\"left\" align=\"top\">$Content</td></tr>\r\n";
                      break;

                    case 2:
                      switch ($WebsiteRow[12])
                      {
                        case 1:
                          echo "          <tr><td width=\"100%\" valign=\"$VAlign\" align=\"$Align\">$Picture</td></tr>\r\n";
                          echo "          <tr><td width=\"100%\" valign=\"$VAlign\" align=\"$Align\">$Content</td></tr>\r\n";
                          break;

                        case 2:
                          echo "          <tr><td width=\"100%\" valign=\"$VAlign\" align=\"$Align\">$Picture</td></tr>\r\n";
                          echo "          <tr><td width=\"100%\" valign=\"$VAlign\" align=\"$Align\">$Content</td></tr>\r\n";
                          break;

                        case 4:
                          echo "          <tr><td width=\"100%\" valign=\"$VAlign\" align=\"$Align\">$Content</td></tr>\r\n";
                          echo "          <tr><td width=\"100%\" valign=\"$VAlign\" align=\"$Align\">$Picture</td></tr>\r\n";
                          break;

                        default:
                          echo "          <tr><td width=\"100%\" valign=\"$VAlign\" align=\"$Align\">$Picture</td></tr>\r\n";
                          echo "          <tr><td width=\"100%\" valign=\"$VAlign\" align=\"$Align\">$Content</td></tr>\r\n";
                      }
                      break;

                    case 4:
                      echo "          <tr><td width=\"99%\" valign=\"left\" align=\"top\">$Content</td><td width=\"1%\" valign=\"$VAlign\" align=\"$Align\" nowrap>$Picture</td></tr>\r\n";
                      break;

                    default:
                      echo "          <tr><td width=\"1%\" valign=\"$VAlign\" align=\"$Align\" nowrap>$Picture</td><td width=\"99%\" valign=\"left\" align=\"top\">$Content</td></tr>\r\n";
                  }
                }
                else
                  echo "          <tr><td width=\"100%\" valign=\"$VAlign\" align=\"$Align\">$Picture</td></tr>\r\n";
                echo "        </table>\r\n";
                echo "      </td></tr>\r\n";
              }
              else if (strlen($Content) > 0)
                echo "      <tr><td width=\"100%\" colspan=\"2\">$Content</td></tr>\r\n";
              if ($ContentTypeNo > 0 && $WebcontentResult = oswebdb_query(get_content_statement($SystemNo, $WebsiteRow[0], $WebsiteRow[1], $WebsiteRow[2], $ContentTypeNo)))
              {
                if ($ContentTypeFields & 32)
                {
                  $Header = 0; $PictureNo = 0;
                  while ($WebcontentRow = oswebdb_fetch_row($WebcontentResult))
                  {
                    if (use_website($ContentTypeFields, 1, NULL, NULL, $WebcontentRow[5], $WebcontentRow[6], $WebcontentRow[7], NULL, 0) && strlen($WebcontentRow[8]) > 0 && file_exists($WebcontentRow[8]))
                    {
                      if (!$Header)
                      {
                        echo "      <tr><td width=\"100%\" colspan=\"2\"><table border=\"0\" cellpadding=\"2\" cellspacing=\"2\" width=\"100%\">\r\n";
                        echo "        <tr>\r\n";
                        $Header = 1;
                      }
                      else if (($PictureNo % 3) == 0)
                      {
                        echo "        <tr>\r\n";
                        echo "          <td colspan=\"3\"><br></td>\r\n";
                        echo "        </tr>\r\n";
                        echo "        <tr>\r\n";
                      }
                      $Created = "";
                      if ($ContentTypeFields & 2)
                      {
                        $Year = substr($WebcontentRow[4], 0, 4);
                        $Month = substr($WebcontentRow[4], 5, 2);
                        $Date = substr($WebcontentRow[4], 8, 2);
                        $Created = "<br>$Date/$Month-$Year";
                      }
                      $Picture = "<img src=\"$WebcontentRow[8]\" border=\"0\">$Created<br>$WebcontentRow[3]";
                      if (strlen($WebcontentRow[9]) > 0 && file_exists($WebcontentRow[9]))
                        $Picture = MakeHtmlLink("", "$WebcontentRow[8]", "window", "", "", "", "javascript:window.status='$WebcontentRow[3]'; return true", "javascript:window.status=''; return true;", "<img src=\"$WebcontentRow[9]\" border=\"0\">$Created<br>$WebcontentRow[3]");
                      switch ($WebcontentRow[10])
                      {
                        case 1:
                          $Align = "left";
                          break;

                        case 2:
                          $Align = "center";
                          break;

                        case 4:
                          $Align = "right";
                          break;

                        default:
                          $Align = "left";
                      }
                      switch ($WebcontentRow[11])
                      {
                        case 1:
                          $VAlign = "top";
                          break;

                        case 2:
                          $VAlign = "middle";
                          break;

                        case 4:
                          $VAlign = "bottom";
                          break;

                        default:
                          $VAlign = "top";
                      }
                      echo "          <td valign=\"$VAlign\" align=\"$Align\">$Picture</td>\r\n";
                      $PictureNo++;
                      if (($PictureNo % 3) == 0)
                        echo "        </tr>\r\n";
                    }
                  }
                  if ($Header)
                  {
                    while (($PictureNo % 3) != 0)
                    {
                      echo "          <td>&nbsp;</td>\r\n";
                      $PictureNo++;
                    }
                    if (($PictureNo % 3) == 0)
                      echo "        </tr>\r\n";
                    echo "      </table></td></tr>\r\n";
                  }
                }
                else if ($ContentTypeGroupNo > 0)
                {
                  $Header = 0; $LastGroup = NULL;
                  while ($WebcontentRow = oswebdb_fetch_row($WebcontentResult))
                  {
                    if (use_website($ContentTypeFields, 1, NULL, NULL, $WebcontentRow[5], $WebcontentRow[6], $WebcontentRow[7], NULL, 0))
                    {
                      if (!$Header)
                      {
                        echo "      <tr><td width=\"100%\" colspan=\"2\"><table border=\"0\" cellpadding=\"2\" cellspacing=\"2\" width=\"100%\">\r\n";
                        $Header = 1;
                      }
                      if ($WebcontentRow[13] == NULL)
                      {
                        echo "        <tr><td width=\"100%\" colspan=\"2\"><h3>$WebcontentRow[3]</h3></td></tr>\r\n";
                      }
                      else if ($WebcontentRow[13] != $LastGroup)
                      {
                        echo "        <tr><td width=\"100%\" colspan=\"2\"><h3>$WebcontentRow[13]</h3></td></tr>\r\n";
                        echo "        <tr><td width=\"1%\" nowrap>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td><td width=\"99%\"><h4>$WebcontentRow[3]</h4></td></tr>\r\n";
                        $LastGroup = $WebcontentRow[13];
                      }
                      else
                        echo "        <tr><td width=\"1%\" nowrap>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td><td width=\"99%\"><h4>$WebcontentRow[3]</h4></td></tr>\r\n";
                    }
                  }
                  if ($Header)
                    echo "      </table></td></tr>\r\n";
                }
                oswebdb_free_result($WebcontentResult);
              }
              echo "    </table>\r\n";
            }
          }
          oswebdb_free_result($WebsiteResult);
        }
        if (isset($_GET["Print"]))
          MakeHtmlPrintPageBottom();
        else
          MakeHtmlPageBottom();
      }
      else if ($Result = oswebdb_query("SELECT Title FROM Systems WHERE SystemNo=$SystemNo"))
      {
        if ($Row = oswebdb_fetch_row($Result))
        {
          MakeHtmlPageTop($Row[0]);
          MakeHtmlPageBottom();
        }
        oswebdb_free_result($Result);
      }
    }
    oswebdb_close();
  }
?>
