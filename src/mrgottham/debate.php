<?php
  // Include required files.
  require_once("misc.php");
  require_once("oswebdb/oswebdb.php");
  require_once("oswebdb/debate.php");

  // Set error reporting.
  error_reporting((int) GetConfigValue("ErrorReporting"));

  $TableName = "Debate";
  $SystemNo = GetConfigValue("SystemNo");
  $Username = oswebdb_getusername();
  $Password = oswebdb_getpassword();
  if (isset($_GET["DebateUsername"]))
    $DebateUsername = $_GET["DebateUsername"];
  else if (isset($_POST["DebateUsername"]))
    $DebateUsername = $_POST["DebateUsername"];
  if (isset($_GET["DebateDate"]))
    $DebateDate = $_GET["DebateDate"];
  else if (isset($_POST["DebateDate"]))
    $DebateDate = $_POST["DebateDate"];
  if (isset($_GET["DebateTime"]))
    $DebateTime = $_GET["DebateTime"];
  else if (isset($_POST["DebateTime"]))
    $DebateTime = $_POST["DebateTime"];
  if (isset($_POST["Search"]))
    $Search = $_POST["Search"];
  if (isset($_POST["SearchFor"]))
    $SearchFor = $_POST["SearchFor"];
  if (oswebdb_connect($Username, $Password))
  {
    if (oswebdb_selectdb())
    {
      $AllowSelect = oswebdb_allow_select($TableName);
      $AllowInsert = oswebdb_allow_insert($TableName);
      $AllowUpdate = oswebdb_allow_update($TableName);
      $AllowDelete = oswebdb_allow_delete($TableName);
      $Statement = "SELECT Username,Email,Date,Time,Subject FROM $TableName WHERE SystemNo=$SystemNo AND ParentUsername IS NULL AND ParentDate IS NULL AND ParentTime IS NULL ORDER BY Date DESC,Time DESC,Username";
      if (isset($Search) && isset($SearchFor))
        $Statement = "SELECT Username,Email,Date,Time,Subject FROM $TableName WHERE SystemNo=$SystemNo AND (Subject LIKE \"$SearchFor\" OR Subject LIKE \"%$SearchFor\" OR Subject LIKE \"%$SearchFor%\" OR Subject LIKE \"$SearchFor%\") ORDER BY Date DESC,Time DESC,Username";
      if (isset($_POST["Insert"]) && isset($_POST["Username"]))
      {
        $DebateUsername = $_POST["Username"];
        $DebateDate = date("Y-m-d", time());
        $DebateTime = date("H:i:s", time());
        if (!insert_debate($SystemNo, $DebateUsername, $_POST["Email"], $DebateDate, $DebateTime, $_POST["Subject"], $_POST["Content"], $_POST["Web"], $_POST["ParentUsername"], $_POST["ParentDate"], $_POST["ParentTime"]))
        {
          $Error = oswebdb_error();
          MakeHtmlPageTop("Debatter");
          echo "    <h2><strong>$Error</strong></h2>\r\n";
          MakeHtmlPageBottom("");
          exit;
        }
      }
      else if (isset($_POST["Delete"]) && isset($DebateUsername) && isset($DebateDate) && isset($DebateTime))
      {
        if (!delete_debate($SystemNo, $DebateUsername, $DebateDate, $DebateTime))
        {
          $Error = oswebdb_error();
          MakeHtmlPageTop("Debatter");
          echo "    <h2><strong>$Error</strong></h2>\r\n";
          MakeHtmlPageBottom("");
          exit;
        }
        if (isset($DebateUsername))
          unset($DebateUsername);
        if (isset($DebateDate))
          unset($DebateDate);
        if (isset($DebateTime))
          unset($DebateTime);
      }
      else if (isset($Search) && isset($SearchFor) && isset($DebateUsername) && isset($DebateDate) && isset($DebateTime))
      {
        if (isset($DebateUsername))
          unset($DebateUsername);
        if (isset($DebateDate))
          unset($DebateDate);
        if (isset($DebateTime))
          unset($DebateTime);
      }
      else if (isset($_POST["ShowAll"]))
      {
        if (isset($_POST["NewDebate"]))
          unset($_POST["NewDebate"]);
        if (isset($_POST["NewAnswer"]))
          unset($_POST["NewAnswer"]);
        if (isset($DebateUsername))
          unset($DebateUsername);
        if (isset($DebateDate))
          unset($DebateDate);
        if (isset($DebateTime))
          unset($DebateTime);
        if (isset($Search))
          unset($Search);
        if (isset($SearchFor))
          unset($SearchFor);
      }
      if ((isset($_POST["NewDebate"]) || isset($_POST["NewAnswer"])) && $Result = oswebdb_query("SELECT Username,Email,Subject,Content,Reference FROM $TableName WHERE SystemNo=$SystemNo"))
      {
        echo "<script language=\"JavaScript\">\r\n";
        echo "  function validateDebate()\r\n";
        echo "  {\r\n";
        echo "    username = this.DebateForm.Username.value\r\n";
        echo "    subject = this.DebateForm.Subject.value\r\n";
        echo "    content = this.DebateForm.Content.value\r\n";
        echo "    if (username.length == 0)\r\n";
        echo "    {\r\n";
        echo "      alert('Navnet skal indtastes!')\r\n";
        echo "      return false\r\n";
        echo "    }\r\n";
        echo "    else if (subject.length == 0)\r\n";
        echo "    {\r\n";
        echo "      alert('Emnet skal indtastes!')\r\n";
        echo "      return false\r\n";
        echo "    }\r\n";
        echo "    else if (content.length == 0)\r\n";
        echo "    {\r\n";
        echo "      alert('Indholdet skal indtastes!')\r\n";
        echo "      return false\r\n";
        echo "    }\r\n";
        echo "    else\r\n";
        echo "    {\r\n";
        echo "      this.DebateForm.Email.value = parent.getEmail(this.DebateForm.Email.value)\r\n";
        echo "      this.DebateForm.Web.value = parent.getWeb(this.DebateForm.Web.value)\r\n";
        echo "      return true\r\n";
        echo "    }\r\n";
        echo "  }\r\n";
        echo "</script>\r\n";
        $TabIndex = 1; $Subject = "";
        MakeHtmlPageTop("Debatter");
        echo "    <form name=\"DebateForm\" action=\"debate.php\" method=\"post\">\r\n";
        if (isset($_POST["NewAnswer"]) && isset($DebateUsername) && isset($DebateDate) && isset($DebateTime))
        {
          echo "      <input type=\"hidden\" name=\"ParentUsername\" value=\"$DebateUsername\">\r\n";
          echo "      <input type=\"hidden\" name=\"ParentDate\" value=\"$DebateDate\">\r\n";
          echo "      <input type=\"hidden\" name=\"ParentTime\" value=\"$DebateTime\">\r\n";
        }
        echo "      <table border=\"0\" cellpadding=\"1\" cellspacing=\"1\" width=\"100%\">\r\n";
        echo "        <tr><td width=\"100%\" colspan=\"2\" valign=\"middle\"><h2><i>Debatter</i></h2></td></tr>\r\n";
        echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\">";
        if ($AllowInsert)
        {
          $InputSubmit = MakeHtmlInputSubmit("Insert", "Opdater", !$AllowInsert, $TabIndex++, "javascript:return validateDebate()");
          $InputReset = MakeHtmlInputReset("", "Fortryd", !$AllowInsert, $TabIndex++, "");
          echo "$InputSubmit&nbsp;$InputReset&nbsp;";
        }
        if ($AllowSelect)
        {
          $InputShowAll = MakeHtmlInputSubmit("ShowAll", "Vis alle", !$AllowSelect, $TabIndex++, "");
          $InputSearch = MakeHtmlInputSubmit("Search", "Søg", !$AllowSelect, $TabIndex++, "javascript:return SearchFor.value.length > 0");
          $InputSearchFor = MakeHtmlInputText("SearchFor", $SearchFor, 30, 30, !$AllowSelect, !$AllowSelect, $TabIndex++, "");
          echo "$InputShowAll&nbsp;$InputSearch&nbsp;$InputSearchFor&nbsp;";
        }
        echo "</td></tr>\r\n";
        if (isset($_POST["NewAnswer"]) && isset($DebateUsername) && isset($DebateDate) && isset($DebateTime))
        {
          if ($ParentResult = oswebdb_query("SELECT Username,Email,Date,Time,Subject,Content,Reference FROM $TableName WHERE SystemNo=$SystemNo AND Username=\"$DebateUsername\" AND Date=\"$DebateDate\" AND Time=\"$DebateTime\""))
          {
            if ($ParentRow = oswebdb_fetch_row($ParentResult))
            {
              $Created = strtolower(date("j. F Y (H:i)", mktime(substr($ParentRow[3], 0, 2), substr($ParentRow[3], 3, 2), substr($ParentRow[3], 6, 2), substr($ParentRow[2], 5, 2), substr($ParentRow[2], 8, 2), substr($ParentRow[2], 0, 4))));
              $Name = $ParentRow[0];
              if (strlen($ParentRow[1]) > 0)
              {
                $Email = MakeHtmlLink("", "mailto:$ParentRow[1]", "", "", "", "", "javascript:window.status='Email'; return true;", "javascript:window.status=''; return true;", "$ParentRow[1]");
                $Name = "$Name&nbsp;($Email)";
              }
              $Subject = $ParentRow[4];
              if (substr($Subject, 0, 4) != "RE: ")
                $Subject = "RE: $Subject";
              $Content = str_replace("\r\n", "<br>", $ParentRow[5]);
              $Reference = "";
              if (strlen($ParentRow[6]) > 0)
                $Reference = MakeHtmlLink("", "http://$ParentRow[6]", "window", "", "", "", "javascript:window.status='Web'; return true;", "javascript:window.status=''; return true;", "$ParentRow[6]");
              echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Oprettet :</td><td width=\"99%\">$Created</td></tr>\r\n";
              echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Navn :</td><td width=\"99%\">$Name</td></tr>\r\n";
              echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Emne :</td><td width=\"99%\">$ParentRow[4]</td></tr>\r\n";
              echo "        <tr><td width=\"1%\" valign=\"top\" align=\"right\" nowrap>Indhold :</td><td width=\"99%\">$Content</td></tr>\r\n";
              if (strlen($Reference) > 0)
                echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Web :</td><td width=\"99%\">$Reference</td></tr>\r\n";
            }
            oswebdb_free_result($ParentResult);
          }
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\"><strong>Besvarelse af debat:</strong></td></tr>\r\n";
        }
        else
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\"><strong>Oprettelse af ny debat:</strong></td></tr>\r\n";
        $Input = MakeHtmlInputText("Username", $Username, oswebdb_field_len($Result, 0), oswebdb_field_len($Result, 0), !$AllowInsert, !$AllowInsert, $TabIndex++, "");
        echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Navn :</td><td width=\"99%\">$Input</td></tr>\r\n";
        $Input = MakeHtmlInputText("Email", "", oswebdb_field_len($Result, 1), oswebdb_field_len($Result, 1), !$AllowInsert, !$AllowInsert, $TabIndex++, "javascript:this.value = parent.getEmail(this.value)");
        echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Email :</td><td width=\"99%\">$Input</td></tr>\r\n";
        $Input = MakeHtmlInputText("Subject", $Subject, oswebdb_field_len($Result, 2), oswebdb_field_len($Result, 2), !$AllowInsert, !$AllowInsert, $TabIndex++, "");
        echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Emne :</td><td width=\"99%\">$Input</td></tr>\r\n";
        $Input = MakeHtmlTextArea("Content", 10, 60, "", !$AllowInsert, !$AllowInsert, $TabIndex++, "", "");
        echo "        <tr><td width=\"1%\" valign=\"top\" align=\"right\" nowrap>Indhold :</td><td width=\"99%\">$Input</td></tr>\r\n";
        $Input = MakeHtmlInputText("Web", "", oswebdb_field_len($Result, 4), oswebdb_field_len($Result, 4), !$AllowInsert, !$AllowInsert, $TabIndex++, "javascript:this.value = parent.getWeb(this.value)");
        echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Web :</td><td width=\"99%\">$Input</td></tr>\r\n";
        echo "      </table>\r\n";
        echo "    </form>\r\n";
        MakeHtmlPageBottom();
        oswebdb_free_result($Result);
      }
      else if (isset($DebateUsername) && isset($DebateDate) && isset($DebateTime) && ($Result = oswebdb_query("SELECT Username,Email,Date,Time,Subject,Content,Reference,ParentUsername,ParentDate,ParentTime FROM $TableName WHERE SystemNo=$SystemNo AND Username=\"$DebateUsername\" AND Date=\"$DebateDate\" AND Time=\"$DebateTime\"")))
      {
        if ($Row = oswebdb_fetch_row($Result))
        {
          $Created = strtolower(date("j. F Y (H:i)", mktime(substr($Row[3], 0, 2), substr($Row[3], 3, 2), substr($Row[3], 6, 2), substr($Row[2], 5, 2), substr($Row[2], 8, 2), substr($Row[2], 0, 4))));
          $Name = $Row[0];
          if (strlen($Row[1]) > 0)
          {
            $Email = MakeHtmlLink("", "mailto:$Row[1]", "", "", "", "", "javascript:window.status='Email'; return true;", "javascript:window.status=''; return true;", "$Row[1]");
            $Name = "$Name&nbsp;($Email)";
          }
          $Content = str_replace("\r\n", "<br>", $Row[5]);
          $Reference = "";
          if (strlen($Row[6]) > 0)
            $Reference = MakeHtmlLink("", "http://$Row[6]", "window", "", "", "", "javascript:window.status='Web'; return true;", "javascript:window.status=''; return true;", "$Row[6]");
          $TabIndex = 1;
          MakeHtmlPageTop("Debatter");
          echo "    <table border=\"0\" cellpadding=\"1\" cellspacing=\"1\" width=\"100%\">\r\n";
          echo "      <tr><td width=\"100%\" colspan=\"2\" valign=\"middle\"><h2><i>Debatter</i></h2></td></tr>\r\n";
          echo "      <form action=\"debate.php\" method=\"post\">\r\n";
          echo "        <input type=\"hidden\" name=\"DebateUsername\" value=\"$Row[0]\">\r\n";
          echo "        <input type=\"hidden\" name=\"DebateDate\" value=\"$Row[2]\">\r\n";
          echo "        <input type=\"hidden\" name=\"DebateTime\" value=\"$Row[3]\">\r\n";
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\">";
          if ($AllowInsert)
          {
            $InputNew = MakeHtmlInputSubmit("NewDebate", "Opret ny", !$AllowInsert, $TabIndex++, "");
            $InputAnswer = MakeHtmlInputSubmit("NewAnswer", "Besvar", !$AllowInsert, $TabIndex++, "");
            echo "$InputNew&nbsp;$InputAnswer&nbsp;";

          }
          if ($AllowDelete)
          {
            $InputDelete = MakeHtmlInputSubmit("Delete", "Slet", !$AllowDelete, $TabIndex++, "javascript:return confirm('Bekræft sletning!')");
            echo "$InputDelete&nbsp;";
          }
          if ($AllowSelect)
          {
            $InputShowAll = MakeHtmlInputSubmit("ShowAll", "Vis alle", !$AllowSelect, $TabIndex++, "");
            $InputSearch = MakeHtmlInputSubmit("Search", "Søg", !$AllowSelect, $TabIndex++, "javascript:return SearchFor.value.length > 0");
            $InputSearchFor = MakeHtmlInputText("SearchFor", $SearchFor, 30, 30, !$AllowSelect, !$AllowSelect, $TabIndex++, "");
            echo "$InputShowAll&nbsp;$InputSearch&nbsp;$InputSearchFor&nbsp;";
          }
          echo "</td></tr>\r\n";
          echo "      </form>\r\n";
          echo "      <tr><td width=\"1%\" align=\"right\" nowrap>Oprettet :</td><td width=\"99%\">$Created</td></tr>\r\n";
          echo "      <tr><td width=\"1%\" align=\"right\" nowrap>Navn :</td><td width=\"99%\">$Name</td></tr>\r\n";
          echo "      <tr><td width=\"1%\" align=\"right\" nowrap>Emne :</td><td width=\"99%\">$Row[4]</td></tr>\r\n";
          echo "      <tr><td width=\"1%\" valign=\"top\" align=\"right\" nowrap>Indhold :</td><td width=\"99%\">$Content</td></tr>\r\n";
          if (strlen($Reference) > 0)
            echo "      <tr><td width=\"1%\" align=\"right\" nowrap>Web :</td><td width=\"99%\">$Reference</td></tr>\r\n";
          echo "      <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\"><br></td></tr>\r\n";
          echo "      <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\"><strong>Debat:</strong></td></tr>\r\n";
          echo "      <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\">\r\n";
          if ($ParentResult = oswebdb_query("SELECT Username,Email,Date,Time,Subject FROM $TableName WHERE SystemNo=$SystemNo AND Username=\"$Row[7]\" AND Date=\"$Row[8]\" AND Time=\"$Row[9]\""))
          {
            if ($ParentRow = oswebdb_fetch_row($ParentResult))
              MakeDebateTree($SystemNo, $ParentRow[0], $ParentRow[1], $ParentRow[2], $ParentRow[3], $ParentRow[4], 1, 1, 1, $TableName);
            else
              MakeDebateTree($SystemNo, $Row[0], $Row[1], $Row[2], $Row[3], $Row[4], 1, 1, 1, $TableName);
            oswebdb_free_result($ParentResult);
          }
          echo "      </td></tr>\r\n";
          echo "    </table>\r\n";
          MakeHtmlPageBottom();
        }
        else if (isset($Search) && isset($SearchFor))
          MakeHtmlPageReload("javascript:this.location = 'debate.php?Search=$Search&SearchFor=$SearchFor'; return true;");
        else
          MakeHtmlPageReload("javascript:this.location = 'debate.php'; return true;");
        oswebdb_free_result($Result);
      }
      else if ($Result = oswebdb_query($Statement))
      {
        $TabIndex = 1; $Header = 0;
        MakeHtmlPageTop("Debatter");
        echo "    <table border=\"0\" cellpadding=\"1\" cellspacing=\"1\" width=\"100%\">\r\n";
        echo "      <tr><td width=\"100%\" valign=\"middle\"><h2><i>Debatter</i></h2></td></tr>\r\n";
        echo "      <form action=\"debate.php\" method=\"post\">\r\n";
        echo "        <tr><td width=\"100%\">";
        if ($AllowInsert)
        {
          $Input = MakeHtmlInputSubmit("NewDebate", "Opret ny", !$AllowInsert, $TabIndex++, "");
          echo "$Input&nbsp;";
        }
        if ($AllowSelect)
        {
          if (isset($Search) && isset($SearchFor))
          {
            $InputShowAll = MakeHtmlInputSubmit("ShowAll", "Vis alle", !$AllowSelect, $TabIndex++, "");
            echo "$InputShowAll&nbsp;";
          }
          $InputSearch = MakeHtmlInputSubmit("Search", "Søg", !$AllowSelect, $TabIndex++, "javascript:return SearchFor.value.length > 0");
          $InputSearchFor = MakeHtmlInputText("SearchFor", $SearchFor, 30, 30, !$AllowSelect, !$AllowSelect, $TabIndex++, "");
          echo "$InputSearch&nbsp;$InputSearchFor&nbsp;";
        }
        echo "</td></tr>\r\n";
        echo "      </form>\r\n";
        $i = oswebdb_num_rows($Result);
        while ($Row = oswebdb_fetch_row($Result))
        {
          if (!$Header)
          {
            echo "      <tr><td width=\"100%\"><strong>Debatter:</strong></td></tr>\r\n";
            echo "      <tr><td widht=\"100%\">\r\n";
            $Header = 1;
          }
          MakeDebateTree($SystemNo, $Row[0], $Row[1], $Row[2], $Row[3], $Row[4], 1, --$i == 0, !(isset($Search) && isset($SearchFor)), $TableName);
        }
        if ($Header)
          echo "      </td></tr>\r\n";
        echo "    </table>\r\n";
        MakeHtmlPageBottom();
        oswebdb_free_result($Result);
      }
    }
    oswebdb_close();
  }

  function MakeDebateTree($SystemNo, $Username, $Email, $Date, $Time, $Subject, $NodeValue, $LastNode, $MakeChildTree, $TableName)
  {
    echo "        <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td valign=\"middle\" nowrap>";
    $i = 1;
    while (($i * 2) <= $NodeValue)
    {
      if ($NodeValue & $i)
        echo "<img src=\"vertline.gif\">";
      else
        echo "<img src=\"blank.gif\">";
      $i = $i * 2;
    }
    if ($LastNode)
    {
      $NodeValue -= $i;
      echo "<img src=\"lastnode.gif\">";
    }
    else
      echo "<img src=\"node.gif\">";
    $Link = "debate.php?DebateUsername=$Username&DebateDate=$Date&DebateTime=$Time";
    $ToDay = getdate();
    $Created = strtolower(date("j. F Y H:i", mktime(substr($Time, 0, 2), substr($Time, 3, 2), substr($Time, 6, 2), substr($Date, 5, 2), substr($Date, 8, 2), substr($Date, 0, 4))));
    $Text = "$Subject&nbsp;($Username&nbsp;$Created)";
    if (mktime(0, 0, 0, substr($Date, 5, 2), substr($Date, 8, 2) + 7, substr($Date, 0, 4)) > mktime(0, 0, 0, $ToDay['mon'], $ToDay['mday'], $ToDay['year']))
      $Text = "<strong>$Text</strong>";
    $WindowStatus = addslashes($Subject);
    $LinkImage = MakeHtmlLink("", "$Link", "", "", "", "", "javascript:window.status='$WindowStatus'; return true;", "javascript:window.status=''; return true;", "<img src=\"doc.gif\" border=\"0\">");
    $LinkText = MakeHtmlLink("", "$Link", "", "", "text-decoration:none", "", "javascript:window.status='$WindowStatus'; return true;", "javascript:window.status=''; return true;", "$Text");
    echo "$LinkImage</td><td valign=\"middle\" nowrap><font size=\"2\">$LinkText</font></td></tr></table>\r\n";
    if ($MakeChildTree)
    {
      if ($Result = oswebdb_query("SELECT Username,Email,Date,Time,Subject FROM $TableName WHERE SystemNo=$SystemNo AND ParentUsername=\"$Username\" AND ParentDate=\"$Date\" AND ParentTime=\"$Time\" ORDER BY Date DESC,Time DESC,Username"))
      {
        $j = oswebdb_num_rows($Result);
        while ($Row = oswebdb_fetch_row($Result))
          MakeDebateTree($SystemNo, $Row[0], $Row[1], $Row[2], $Row[3], $Row[4], $NodeValue + ($i * 2), --$j == 0, $MakeChildTree, $TableName);
        oswebdb_free_result($Result);
      }
    }
  }
?>
