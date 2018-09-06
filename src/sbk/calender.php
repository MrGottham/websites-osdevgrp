<?php
  // Include required files.
  require_once("misc.php");
  require_once("oswebdb/oswebdb.php");
  require_once("oswebdb/calender.php");

  // Set error reporting.
  error_reporting((int) GetConfigValue("ErrorReporting"));

  define("MAX_SIZE", 53);
  $AppointmentTableName = "Calapps";
  $MergeTableName = "Calmerge";
  $UserTableName = "Calusers";
  $SystemNo = GetConfigValue("SystemNo");
  if (isset($_GET["Fun"]))
    $Fun = $_GET["Fun"];
  else if (isset($_POST["Fun"]))
    $Fun = $_POST["Fun"];
  $Username = oswebdb_getusername();
  $Password = oswebdb_getpassword();
  if (oswebdb_connect($Username, $Password))
  {
    if (oswebdb_selectdb())
    {
      switch ($Fun)
      {
        case 1:
          $AppointmentBells = get_appointment_bells($SystemNo, date("Y-m-d", time()), date("H:i:s", time()), get_private_user_ids($SystemNo));
          if (strlen($AppointmentBells) > 0)
            MakeBells(4, $SystemNo, $AppointmentBells);
          else
            MakeCalender(2, $SystemNo, $_GET["Year"], $_GET["Month"], $_GET["Date"], $_GET["UserIds"]);
          break;

        case 2:
          MakeCalender($Fun, $SystemNo, $_GET["Year"], $_GET["Month"], $_GET["Date"], $_GET["UserIds"]);
          break;

        case 3:
          MakeAppointment($Fun, $_GET["Year"], $_GET["Month"], $_GET["Date"], $_GET["UserIds"]);
          break;

        case 4:
          if (!$_GET["$AppointmentBells"])
            $AppointmentBells = get_appointment_bells($SystemNo, date("Y-m-d", time()), date("H:i:s", time()), get_private_user_ids($SystemNo));
          MakeBells(4, $SystemNo, $AppointmentBells);
          break;

        case 5:
          MakeUsers($Fun, $SystemNo, $UserTableName);
          break;

        case 6:
          if (!rebuild_calender($SystemNo))
          {
            $Error = oswebdb_error();
            MakeHtmlPageTop("Kalender");
            echo "    <h2><strong>$Error</strong></h2>\r\n";
            MakeHtmlPageBottom("");
            exit;
          }
          MakeCalender(2, $SystemNo, $_GET["Year"], $_GET["Month"], $_GET["Date"], $_GET["UserIds"]);
          break;

        case 7:
          if (!export_calender($SystemNo, date("Y-m-d", time()), date("H:i:s", time()), get_private_user_ids($SystemNo)))
          {
            $Error = oswebdb_error();
            MakeHtmlPageTop("Kalender");
            echo "    <h2><strong>$Error</strong></h2>\r\n";
            MakeHtmlPageBottom("");
            exit;
          }
          break;

        case 21:
          MakeCalenderToolbar($SystemNo, $AppointmentTableName, $MergeTableName, $_GET["Year"], $_GET["Month"], $_GET["Date"]);
          break;

        case 22:
          MakeCalenderMonth($SystemNo, $_GET["Year"], $_GET["Month"], $_GET["UserIds"]);
          break;

        case 23:
          MakeCalenderUserList($SystemNo, $UserTableName, $_GET["UserIds"]);
          break;

        case 24:
          MakeCalenderAppointmentList($SystemNo, $AppointmentTableName, $MergeTableName, $Username, $Password, $_GET["Year"], $_GET["Month"], $_GET["Date"], $_GET["UserIds"]);
          break;

        case 25:
          MakeCalenderPrint($SystemNo, $Username, $Password, $_GET["Year"], $_GET["Month"], $_GET["Date"], $_GET["UserIds"]);
          break;

        case 31:
          MakeAppointmentContent($SystemNo, $AppointmentTableName, $MergeTableName, $Username, $Password, $_GET["Year"], $_GET["Month"], $_GET["Date"], $_GET["UserIds"]);
          break;

        case 32:
          MakeCalenderUserList($SystemNo, $UserTableName, $_GET["UserIds"]);
          break;

        case 33:
          $ScheduledFrom = 8;
          if ($_GET["ScheduledFrom"])
            $ScheduledFrom = $_GET["ScheduledFrom"];
          MakeAppointmentScheduled($SystemNo, $_GET["Year"], $_GET["Month"], $_GET["Date"], $_GET["UserIds"], $ScheduledFrom);
          break;

        case 41:
          MakeBellsToolbar($AppointmentTableName, $MergeTableName);
          break;

        case 42:
          MakeBellsContent($Fun, $SystemNo, $AppointmentTableName, $MergeTableName, $Username, $Password, $_GET["AppointmentBells"]);
          break;

        case 43:
          MakeBellsPrint($SystemNo, $Username, $Password);
          break;
      }
    }
    oswebdb_close();
  }

  function MakeCalender($Fun, $SystemNo, $Year, $Month, $Date, $UserIds)
  {
    if ($_GET["Insert"] && $_GET["AppointmentDate"] && $_GET["FromTime"] && $_GET["ToTime"] && strlen($UserIds) > 0)
    {
      if (!insert_appointment($SystemNo, $_GET["AppointmentDate"], $_GET["FromTime"], $_GET["ToTime"], $_GET["Properties"], $_GET["Subject"], $_GET["Note"], $UserIds))
      {
        $Error = oswebdb_error();
        MakeHtmlPageTop("Kalender");
        echo "    <h2><strong>$Error</strong></h2>\r\n";
        MakeHtmlPageBottom("");
        exit;
      }
    }
    else if ($_GET["Update"] && $_GET["CalId"] && $_GET["AppointmentDate"] && $_GET["FromTime"] && $_GET["ToTime"] && strlen($UserIds) > 0)
    {
      if (!update_appointment($SystemNo, $_GET["CalId"], $_GET["AppointmentDate"], $_GET["FromTime"], $_GET["ToTime"], $_GET["Properties"], $_GET["Subject"], $_GET["Note"], $UserIds))
      {
        $Error = oswebdb_error();
        MakeHtmlPageTop("Kalender");
        echo "    <h2><strong>$Error</strong></h2>\r\n";
        MakeHtmlPageBottom("");
        exit;
      }
    }
    else if ($_GET["Delete"] && $_GET["CalId"] && $_GET["DeleteUserIds"])
    {
      if (!delete_appointment($SystemNo, $_GET["Delete"], $_GET["CalId"], $_GET["DeleteUserIds"]))
      {
        $Error = oswebdb_error();
        MakeHtmlPageTop("Kalender");
        echo "    <h2><strong>$Error</strong></h2>\r\n";
        MakeHtmlPageBottom("");
        exit;
      }
    }
    echo "<script language=\"JavaScript\">\r\n";
    echo "  function gotoToDay()\r\n";
    echo "  {\r\n";
    echo "    updateCalender(1 + 2, parent.getToDay(), parent.getUserIds())\r\n";
    echo "  }\r\n\r\n";
    echo "  function changeMonth(offset)\r\n";
    echo "  {\r\n";
    echo "    updateCalender(1 + 2, parent.changeMonth(offset), parent.getUserIds())\r\n";
    echo "  }\r\n\r\n";
    echo "  function setDate(year, month, date)\r\n";
    echo "  {\r\n";
    echo "    updateCalender(1 + 2, parent.setDate(year, month - 1, date), parent.getUserIds())\r\n";
    echo "  }\r\n\r\n";
    echo "  function bookAppointment()\r\n";
    echo "  {\r\n";
    echo "    parent.openCalender(3)\r\n";
    echo "  }\r\n\r\n";
    echo "  function modifyAppointment(appointmentDate, appointmentUserIds, appointmentId)\r\n";
    echo "  {\r\n";
    echo "    parent.modifyAppointment(appointmentDate, appointmentUserIds, appointmentId)\r\n";
    echo "  }\r\n\r\n";
    echo "  function printAppointments()\r\n";
    echo "  {\r\n";
    echo "    year = (parent.getDate()).getFullYear()\r\n";
    echo "    month = (parent.getDate()).getMonth() + 1\r\n";
    echo "    date = (parent.getDate()).getDate()\r\n";
    echo "    printWindow = window.open('', '', 'width=600,height=470,scrollbars')\r\n";
    echo "    if (printWindow)\r\n";
    echo "      printWindow.location = 'calender.php?Fun=25&Year=' + year + '&Month=' + month + '&Date=' + date + '&UserIds=' + parent.getUserIds()\r\n";
    echo "  }\r\n\r\n";
    echo "  function exportAppointments()\r\n";
    echo "  {\r\n";
    echo "    parent.openCalender(7)\r\n";
    echo "  }\r\n\r\n";
    echo "  function showBells()\r\n";
    echo "  {\r\n";
    echo "    parent.openCalender(4)\r\n";
    echo "  }\r\n\r\n";
    echo "  function changeUserEditControl()\r\n";
    echo "  {\r\n";
    echo "    s = ',' + this.frameListOfUsers.UserListForm.UserEdit.value.toUpperCase() + ','\r\n";
    echo "    for (i = 0; i < this.frameListOfUsers.UserListForm.UserListBox.length; i++)\r\n";
    echo "    {\r\n";
    echo "      t = this.frameListOfUsers.UserListForm.UserListBox.options[i].text.toUpperCase()\r\n";
    echo "      if (t.lastIndexOf('(') >= 0 && t.lastIndexOf(')') >= 0)\r\n";
    echo "        t = t.substring(t.lastIndexOf('(') + 1, t.lastIndexOf(')'))\r\n";
    echo "      t = ',' + t + ','\r\n";
    echo "      this.frameListOfUsers.UserListForm.UserListBox.options[i].selected = (s.indexOf(t) >= 0)\r\n";
    echo "    }\r\n";
    echo "    changeUserListBoxControl()\r\n";
    echo "  }\r\n\r\n";
    echo "  function changeUserListBoxControl()\r\n";
    echo "  {\r\n";
    echo "    s = ''\r\n";
    echo "    userIds = ''\r\n";
    echo "    for (i = 0; i < this.frameListOfUsers.UserListForm.UserListBox.length; i++)\r\n";
    echo "    {\r\n";
    echo "      if (this.frameListOfUsers.UserListForm.UserListBox.options[i].selected)\r\n";
    echo "      {\r\n";
    echo "        t = this.frameListOfUsers.UserListForm.UserListBox.options[i].text.toUpperCase()\r\n";
    echo "        if (t.lastIndexOf('(') >= 0 && t.lastIndexOf(')') >= 0)\r\n";
    echo "          t = t.substring(t.lastIndexOf('(') + 1, t.lastIndexOf(')'))\r\n";
    echo "        if (s.length > 0)\r\n";
    echo "          s = s + ','\r\n";
    echo "        s = s + t\r\n";
    echo "        if (userIds.length > 0)\r\n";
    echo "          userIds = userIds + ','\r\n";
    echo "        userIds = userIds + this.frameListOfUsers.UserListForm.UserListBox.options[i].value\r\n";
    echo "      }\r\n";
    echo "    }\r\n";
    echo "    this.frameListOfUsers.UserListForm.UserEdit.value = s\r\n";
    echo "    updateCalender(2, parent.getDate(), parent.setUserIds(userIds))\r\n";
    echo "  }\r\n\r\n";
    echo "  function updateCalender(updateFrames, changeToDate, userIds)\r\n";
    echo "  {\r\n";
    echo "    fun = $Fun\r\n";
    echo "    year = changeToDate.getFullYear()\r\n";
    echo "    month = changeToDate.getMonth() + 1\r\n";
    echo "    date = changeToDate.getDate()\r\n";
    echo "    if (updateFrames & 1)\r\n";
    echo "      this.frameToolbar.location = 'calender.php?Fun=' + ((fun * 10) + 1) + '&Year=' + year + '&Month=' + month + '&Date=' + date\r\n";
    echo "    if (updateFrames & 2)\r\n";
    echo "    {\r\n";
    echo "      this.frameMonth.location = 'calender.php?Fun=' + ((fun * 10) + 2) + '&Year=' + year + '&Month=' + month + '&UserIds=' + userIds\r\n";
    echo "      this.frameListOfApps.location = 'calender.php?Fun=' + ((fun * 10) + 4) + '&Year=' + year + '&Month=' + month + '&Date=' + date + '&UserIds=' + userIds\r\n";
    echo "    }\r\n";
    echo "  }\r\n";
    echo "</script>\r\n";
    echo "<html>\r\n";
    echo "  <head>\r\n";
    echo "    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=windows-1252\">\r\n";
    echo "    <title>Kalender</title>\r\n";
    echo "  </head>\r\n";
    echo "  <frameset rows=\"40,*\" frameborder=\"0\" framespacing=\"0\" border=\"0\">\r\n";
    $NewFun = ($Fun * 10) + 1;
    echo "    <frame name=\"frameToolbar\" scrolling=\"no\" noresize src=\"calender.php?Fun=$NewFun&Year=$Year&Month=$Month&Date=$Date\">\r\n";
    echo "    <frameset rows=\"250,*\" frameborder=\"0\" framespacing=\"0\" border=\"0\">\r\n";
    echo "      <frameset cols=\"350,*\" frameborder=\"0\" framespacing=\"0\" border=\"0\">\r\n";
    $NewFun = ($Fun * 10) + 2;
    echo "        <frame name=\"frameMonth\" scrolling=\"no\" noresize src=\"calender.php?Fun=$NewFun&Year=$Year&Month=$Month&UserIds=$UserIds\">\r\n";
    $NewFun = ($Fun * 10) + 3;
    echo "        <frame name=\"frameListOfUsers\" scrolling=\"no\" noresize src=\"calender.php?Fun=$NewFun&UserIds=$UserIds\">\r\n";
    echo "      </frameset>\r\n";
    $NewFun = ($Fun * 10) + 4;
    echo "      <frame name=\"frameListOfApps\" scrolling=\"auto\" noresize src=\"calender.php?Fun=$NewFun&Year=$Year&Month=$Month&Date=$Date&UserIds=$UserIds\">\r\n";
    echo "    </frameset>\r\n";
    echo "  </frameset>\r\n";
    echo "</html>\r\n";
  }

  function MakeCalenderToolbar($SystemNo, $AppointmentTableName, $MergeTableName, $Year, $Month, $Date)
  {
    MakeHtmlPageTop("Kalender");
    echo "    <form>\r\n";
    echo "      <table border=\"0\" cellpadding=\"1\" cellspacing=\"1\" width=\"100%\">\r\n";
    $DateString = get_date_as_string($Year, $Month, $Date);
    $Print = "";
    if (oswebdb_allow_select($AppointmentTableName) && oswebdb_allow_select($MergeTableName))
    {
      $Print = MakeHtmlLink("", "javascript:parent.printAppointments()", "", "", "", "", "javascript:window.status='Udskriv aftaler'; return true;", "javascript:window.status=''; return true;", "Udskriv");
      $Print = "$Print&nbsp;|&nbsp;";
    }
    $Export = "";
    if (oswebdb_allow_update($AppointmentTableName) && oswebdb_allow_update($MergeTableName) && strlen(get_private_user_ids($SystemNo)) > 0)
    {
      $Export = MakeHtmlLink("", "javascript:parent.exportAppointments()", "", "", "", "", "javascript:window.status='Eksportér aftaler'; return true;", "javascript:window.status=''; return true;", "Exportér");
      $Export = "$Export&nbsp;|&nbsp;";
    }
    $Book = "";
    if (oswebdb_allow_insert($AppointmentTableName) && oswebdb_allow_insert($MergeTableName) && oswebdb_allow_update($AppointmentTableName) && oswebdb_allow_update($MergeTableName))
    {
      $Book = MakeHtmlLink("", "javascript:parent.bookAppointment()", "", "", "", "", "javascript:window.status='Book aftale'; return true;", "javascript:window.status=''; return true;", "Book aftale");
      $Book = "$Book&nbsp;|&nbsp;";
    }
    $Bells = "";
    if (oswebdb_allow_select($AppointmentTableName) && oswebdb_allow_select($MergeTableName) && strlen(get_private_user_ids($SystemNo)) > 0)
    {
      $Bells = MakeHtmlLink("", "javascript:parent.showBells()", "", "", "", "", "javascript:window.status='Alarmer'; return true;", "javascript:window.status=''; return true;", "Alarmer");
      $Bells = "$Bells&nbsp;|&nbsp;";
    }
    $PrevMonth = MakeHtmlLink("", "javascript:parent.changeMonth(-1)", "", "", "", "", "javascript:window.status='Forrige måned'; return true;", "javascript:window.status=''; return true;", "Forrige måned");
    $ToDay = MakeHtmlLink("", "javascript:parent.gotoToDay()", "", "", "", "", "javascript:window.status='I dag'; return true;", "javascript:window.status=''; return true;", "I dag");
    $NextMonth = MakeHtmlLink("", "javascript:parent.changeMonth(1)", "", "", "", "", "javascript:window.status='Næste måned'; return true;", "javascript:window.status=''; return true;", "Næste måned");
    echo "        <tr><td width=\"99%\" valign=\"middle\" align=\"center\" nowrap><strong>$DateString</strong></td><td width=\"1%\" valign=\"middle\" align=\"right\" nowrap>$Print$Export$Book$Bells$PrevMonth&nbsp;|&nbsp;$ToDay&nbsp;|&nbsp;$NextMonth</td></tr>\r\n";
    echo "      </table>\r\n";
    echo "    </form>\r\n";
    MakeHtmlPageBottom();
  }

  function MakeCalenderMonth($SystemNo, $Year, $Month, $UserIds)
  {
    echo "<style type=\"text/css\">\r\n";
    echo "  .calender, .calender TH, .calender TD\r\n";
    echo "  {\r\n";
    echo "    background-color:white;\r\n";
    echo "    color:black;\r\n";
    echo "  }\r\n\r\n";
    echo "  .workday, .workday A\r\n";
    echo "  {\r\n";
    echo "    background-color:white;\r\n";
    echo "    color:black;\r\n";
    echo "  }\r\n\r\n";
    echo "  .holiday, .holiday A\r\n";
    echo "  {\r\n";
    echo "    background-color:white;\r\n";
    echo "    color:red;\r\n";
    echo "  }\r\n";
    echo "</style>\r\n";
    MakeHtmlPageTop("Kalender");
    echo "    <table border=\"0\" cellpadding=\"6\" cellspacing=\"0\" width=\"100%\" bgcolor=\"#FFFFFF\" class=\"calender\">\r\n";
    echo "      <tr><td>Man</td><td>Tir</td><td>Ons</td><td>Tor</td><td>Fre</td><td>Lør</td><td>Søn</td><td>Uge</td></tr>\r\n";
    $Date = 1;
    for ($i = 0; $i < 6; $i++)
    {
      echo "      <tr>";
      for ($j = 0; $j < 7; $j++)
      {
        $x = get_days_in_month($Year, $Month);
        if ($j == 0 && $Date <= get_days_in_month($Year, $Month))
          $WeekNo = get_week_no($Year, $Month, $Date);
        if ($i == 0 && $j < 6 && get_day_of_week($Year, $Month, 1) == 0)
          echo "<td></td>";
        else if ($i == 0 && $j + 1 < get_day_of_week($Year, $Month, 1))
          echo "<td></td>";
        else if ($Date <= get_days_in_month($Year, $Month))
        {
          $Class = "workday";
          if ($j == 6 || get_holiday($Year, $Month, $Date, $HolidayName))
            $Class = "holiday";
          $DT = date("Y-m-d", mktime(0, 0, 0, $Month, $Date, $Year));
          echo "<td><a href=\"javascript:parent.setDate($Year, $Month, $Date)\" onMouseOver=\"javascript:window.status='$DT'; return true;\" onMouseOut=\"javascript:window.status=''; return true;\" class=\"$Class\">$Date</a>";
          if (strlen(get_appointment_ids($SystemNo, $DT, $UserIds)) > 0)
            echo " *";
          echo "</td>";
          if (++$Date > get_days_in_month($Year, $Month))
          {
            $Date = 1;
            if (++$Month > 12)
            {
              $Month = 1;
              $Year++;
            }
          }
        }
        else
          echo "<td></td>";
      }
      echo "<td>";
      if ($WeekNo != $LastWeekNo)
      {
        echo "$WeekNo";
        $LastWeekNo = $WeekNo;
      }
      echo "</td></tr>\r\n";
    }
    echo "    </table>\r\n";
    MakeHtmlPageBottom();
  }

  function MakeCalenderUserList($SystemNo, $UserTableName, $UserIds)
  {
    if (isset($UserIds))
      $UserIds = explode(",", $UserIds);
    if ($Result = oswebdb_query("SELECT UserId,Initials,Name FROM $UserTableName WHERE SystemNo=$SystemNo ORDER BY Name,Initials,UserId"))
    {
      $TabIndex = 1;
      MakeHtmlPageTop("Kalender");
      echo "    <form name=\"UserListForm\" action=\"javascript:parent.changeUserEditControl()\">\r\n";
      echo "      <table border=\"0\" cellpadding=\"1\" cellspacing=\"1\" width=\"100%\">\r\n";
      $Options = ""; $Initials = "";
      while ($Row = oswebdb_fetch_row($Result))
      {
        $Options = "$Options<option value=\"$Row[0]\"";
        $i = 0; $Found = 0;
        while ($i < count($UserIds) && !$Found)
        {
          $Found = $UserIds[$i] == $Row[0];
          $i++;
        }
        if ($Found)
        {
          $Options = "$Options selected";
          if (strlen($Initials) > 0)
            $Initials = "$Initials,$Row[1]";
          else
            $Initials = "$Row[1]";
        }
        $Options = "$Options>$Row[2] ($Row[1])";
      }
      $FirstInput = MakeHtmlSelect("UserListBox", 1, 12, 0, 0, $TabIndex++, "javascript:parent.changeUserListBoxControl()", $Options);
      $SecondInput = MakeHtmlInputText("UserEdit", $Initials, 0, 9999, 0, 0, $TabIndex++, "javascript:parent.changeUserEditControl()");
      echo "        <tr><td width=\"100%\">$FirstInput</td></tr>\r\n";
      echo "        <tr><td width=\"100%\">Brugere&nbsp;:&nbsp;$SecondInput</td></tr>\r\n";
      echo "      </table>\r\n";
      echo "    </form>\r\n";
      MakeHtmlPageBottom();
      oswebdb_free_result($Result);
    }
  }

  function MakeCalenderAppointmentList($SystemNo, $AppointmentTableName, $MergeTableName, $Username, $Password, $Year, $Month, $Date, $UserIds)
  {
    $AllowUpdate = oswebdb_allow_update($AppointmentTableName) && oswebdb_allow_update($MergeTableName);
    $AllowDelete = oswebdb_allow_delete($AppointmentTableName) && oswebdb_allow_delete($MergeTableName);
    $CalIds = explode(",", get_appointment_ids($SystemNo, date("Y-m-d", mktime(0, 0, 0, $Month, $Date, $Year)), $UserIds));
    $PrivateUserIds = get_private_user_ids($SystemNo);
    MakeHtmlPageTop("Kalender");
    echo "    <form name=\"Appointments\" action=\"\">\r\n";
    echo "      <table border=\"1\" cellpadding=\"2\" cellspacing=\"0\">\r\n";
    echo "        <tr><td nowrap><strong>Tid</strong></td><td nowrap><strong>Deltagere</strong></td><td nowrap>&nbsp;</td><td wrap><strong>Emne</strong></td></tr>\r\n";
    $i = 0;
    while ($i < count($CalIds))
    {
      if (strlen($CalIds[$i]) > 0)
      {
        $AppointmentContent = get_appointment_content($SystemNo, $CalIds[$i], $Username, $Password, $PrivateUserIds);
        $Time = "$AppointmentContent[4]-$AppointmentContent[5]";
        if ($AppointmentContent[2] && ($AllowUpdate || $AllowDelete))
          $Time = "<a href=\"javascript:parent.modifyAppointment('$AppointmentContent[3]', '$AppointmentContent[6]', $AppointmentContent[1])\" onMouseOver=\"javascript:window.status='Ret aftale'; return true;\" onMouseOut=\"javascript:window.status=''; return true;\">$Time</a>";
        echo "        <tr><td valign=\"top\" nowrap>$Time</td><td valign=\"top\" nowrap>$AppointmentContent[7]</td><td valign=\"top\" nowrap>$AppointmentContent[9]</td><td valign=\"top\" wrap>$AppointmentContent[10]</td></tr>\r\n";
      }
      $i++;
    }
    echo "      </table>\r\n";
    echo "    </form>\r\n";
    MakeHtmlPageBottom();
  }

  function MakeCalenderPrint($SystemNo, $Username, $Password, $Year, $Month, $Date, $UserIds)
  {
    $PrivateUserIds = get_private_user_ids($SystemNo);
    MakeHtmlPrintPageTop("Kalenderudskrift af aftaler");
    echo "    <table border=\"0\" cellpadding=\"1\" cellspacing=\"1\" width=\"100%\">\r\n";
    $Print = MakeHtmlLink("", "javascript:window.print()", "", "", "", "", "javascript:window.status='Udskriv'; return true;", "javascript:window.status=''; return true;", "Udskriv");
    $Close = MakeHtmlLink("", "javascript:window.close()", "", "", "", "", "javascript:window.status='Luk vindue'; return true;", "javascript:window.status=''; return true;", "Luk");
    echo "      <tr><td colspan=\"3\" nowrap><h2><i>Kalenderudskrift af aftaler</i></h2></td><td valign=\"top\" align=\"right\" nowrap>$Print&nbsp;|&nbsp;$Close</td></tr>\r\n";
    echo "      <tr><td colspan=\"4\"><br></td></tr>\r\n";
    $DateCount = get_days_in_month($Year, $Month);
    while ($DateCount > 0)
    {
      $CalIds = get_appointment_ids($SystemNo, date("Y-m-d", mktime(0, 0, 0, $Month, $Date, $Year)), $UserIds);
      if (strlen($CalIds) > 0)
      {
        $DateHeader = get_date_as_string($Year, $Month, $Date);
        echo "      <tr><td colspan=\"4\"><strong>$DateHeader</strong></td></tr>\r\n";
        $CalIds = explode(",", $CalIds); $i = 0;
        while ($i < count($CalIds))
        {
          if (strlen($CalIds[$i]) > 0)
          {
            $AppointmentContent = get_appointment_content($SystemNo, $CalIds[$i], $Username, $Password, $PrivateUserIds);
            $Content = "$AppointmentContent[10]";
            if ($AppointmentContent[2] && strlen($AppointmentContent[11]) > 0)
              $Content = "$Content<br>$AppointmentContent[11]";
            echo "      <tr><td valign=\"top\" nowrap>$AppointmentContent[4]-$AppointmentContent[5]</td><td valign=\"top\" nowrap>$AppointmentContent[7]</td><td valign=\"top\" nowrap>$AppointmentContent[9]</td><td valign=\"top\" wrap>$Content</td></tr>\r\n";
          }
          $i++;
        }
        echo "      <tr><td colspan=\"4\"><br></td></tr>\r\n";
      }
      $DT = inc_date(getdate(mktime(0, 0, 0, $Month, $Date, $Year)), 1);
      $Year = $DT['year'];
      $Month = $DT['mon'];
      $Date = $DT['mday'];
      $DateCount--;
    }
    echo "    </table>\r\n";
    MakeHtmlPrintPageBottom();
  }

  function MakeAppointment($Fun, $Year, $Month, $Date, $UserIds)
  {
    $CalId = "";
    if (isset($_GET["CalId"]))
    {
      $CalId = $_GET["CalId"];
      $CalId = "&CalId=$CalId";
    }
    echo "<script language=\"JavaScript\">\r\n";
    echo "  function bookAppointment(fun)\r\n";
    echo "  {\r\n";
    echo "    bookDate = this.frameAppointment.ContentForm.Date.value\r\n";
    echo "    year = parseInt(bookDate.substr(0, 4), 10)\r\n";
    echo "    month = parseInt(bookDate.substr(5, 2), 10)\r\n";
    echo "    date = parseInt(bookDate.substr(8, 2), 10)\r\n";
    echo "    fromTime = this.frameAppointment.ContentForm.FromTime.value\r\n";
    echo "    fromHour = parseInt(fromTime.substr(0, 2), 10)\r\n";
    echo "    fromMinute = parseInt(fromTime.substr(3, 2), 10)\r\n";
    echo "    toTime = this.frameAppointment.ContentForm.ToTime.value\r\n";
    echo "    toHour = parseInt(toTime.substr(0, 2), 10)\r\n";
    echo "    toMinute = parseInt(toTime.substr(3, 2), 10)\r\n";
    echo "    properties = 0\r\n";
    echo "    if (this.frameAppointment.ContentForm.Public.checked)\r\n";
    echo "      properties += parseInt(this.frameAppointment.ContentForm.Public.value, 10)\r\n";
    echo "    if (this.frameAppointment.ContentForm.Private.checked)\r\n";
    echo "      properties += parseInt(this.frameAppointment.ContentForm.Private.value, 10)\r\n";
    echo "    if (this.frameAppointment.ContentForm.Bell.checked)\r\n";
    echo "      properties += parseInt(this.frameAppointment.ContentForm.Bell.value, 10)\r\n";
    echo "    if (this.frameAppointment.ContentForm.Done.checked)\r\n";
    echo "      properties += parseInt(this.frameAppointment.ContentForm.Done.value, 10)\r\n";
    echo "    if (this.frameAppointment.ContentForm.Export.checked)\r\n";
    echo "      properties += parseInt(this.frameAppointment.ContentForm.Export.value, 10)\r\n";
    echo "    if (this.frameAppointment.ContentForm.Exported.checked)\r\n";
    echo "      properties += parseInt(this.frameAppointment.ContentForm.Exported.value, 10)\r\n";
    echo "    subject = escape(this.frameAppointment.ContentForm.Subject.value)\r\n";
    echo "    note = escape(this.frameAppointment.ContentForm.Note.value)\r\n";
    echo "    userIds = ''\r\n";
    echo "    for (i = 0; i < this.frameListOfUsers.UserListForm.UserListBox.length; i++)\r\n";
    echo "    {\r\n";
    echo "      if (this.frameListOfUsers.UserListForm.UserListBox.options[i].selected)\r\n";
    echo "      {\r\n";
    echo "        if (userIds.length > 0)\r\n";
    echo "          userIds = userIds + ','\r\n";
    echo "        userIds = userIds + this.frameListOfUsers.UserListForm.UserListBox.options[i].value\r\n";
    echo "      }\r\n";
    echo "    }\r\n";
    echo "    switch (fun)\r\n";
    echo "    {\r\n";
    echo "      case 1:\r\n";
    echo "        if (validateAppointment(year, month, date, fromHour, fromMinute, toHour, toMinute, subject, userIds))\r\n";
    echo "          parent.createAppointment(bookDate, fromTime, toTime, properties, subject, note, userIds)\r\n";
    echo "        break\r\n\r\n";
    echo "      case 2:\r\n";
    echo "        if (validateAppointment(year, month, date, fromHour, fromMinute, toHour, toMinute, subject, userIds))\r\n";
    echo "        {\r\n";
    echo "          appointmentId = this.frameAppointment.ContentForm.CalId.value\r\n";
    echo "          parent.updateAppointment(appointmentId, bookDate, fromTime, toTime, properties, subject, note, userIds)\r\n";
    echo "        }\r\n";
    echo "        break\r\n\r\n";
    echo "      case 3:\r\n";
    echo "        resetAppointment()\r\n";
    echo "        appointmentId = this.frameAppointment.ContentForm.CalId.value\r\n";
    echo "        bookedUserIds = this.frameAppointment.ContentForm.UserIds.value + ','\r\n";
    echo "        privateUserIds = this.frameAppointment.ContentForm.PrivateUserIds.value + ','\r\n";
    echo "        multi = 0\r\n";
    echo "        while ((i = bookedUserIds.indexOf(',')) >= 0 && !multi)\r\n";
    echo "        {\r\n";
    echo "          userId = bookedUserIds.substring(0, i + 1)\r\n";
    echo "          multi = privateUserIds.indexOf(userId) < 0\r\n";
    echo "          bookedUserIds = bookedUserIds.substring(i + 1, bookedUserIds.length)\r\n";
    echo "        }\r\n";
    echo "        if (multi)\r\n";
    echo "        {\r\n";
    echo "          if (confirm('Slet aftale for alle bookede brugere?'))\r\n";
    echo "            parent.deleteAppointment(appointmentId, bookDate, parent.getUserIds(), 2, this.frameAppointment.ContentForm.UserIds.value)\r\n";
    echo "          else if (confirm('Slet aftale for dig selv?'))\r\n";
    echo "            parent.deleteAppointment(appointmentId, bookDate, parent.getUserIds(), 1, this.frameAppointment.ContentForm.PrivateUserIds.value)\r\n";
    echo "        }\r\n";
    echo "        else if (confirm('Slet aftale?'))\r\n";
    echo "          parent.deleteAppointment(appointmentId, bookDate, parent.getUserIds(), 1, this.frameAppointment.ContentForm.PrivateUserIds.value)\r\n";
    echo "        break\r\n";
    echo "    }\r\n";
    echo "  }\r\n\r\n";
    echo "  function validateAppointment(year, month, date, fromHour, fromMinute, toHour, toMinute, subject, userIds)\r\n";
    echo "  {\r\n";
    echo "    if (!parent.validateDate(year, month, date))\r\n";
    echo "      return false\r\n";
    echo "    else if (!parent.validateTime(fromHour, fromMinute, 'starttidspunktet'))\r\n";
    echo "      return false\r\n";
    echo "    else if (!parent.validateTime(toHour, toMinute, 'sluttidspunktet'))\r\n";
    echo "      return false\r\n";
    echo "    else if (subject.length == 0)\r\n";
    echo "    {\r\n";
    echo "      alert('Emnet skal indtastes!')\r\n";
    echo "      return false\r\n";
    echo "    }\r\n";
    echo "    else if (userIds.length == 0)\r\n";
    echo "    {\r\n";
    echo "      alert('Brugere skal vælges!')\r\n";
    echo "      return false\r\n";
    echo "    }\r\n";
    echo "    else\r\n";
    echo "      return true\r\n";
    echo "  }\r\n\r\n";
    echo "  function resetAppointment()\r\n";
    echo "  {\r\n";
    echo "    userIds = this.frameAppointment.ContentForm.UserIds.value + ','\r\n";
    echo "    for (i = 0; i < this.frameListOfUsers.UserListForm.UserListBox.length; i++)\r\n";
    echo "      this.frameListOfUsers.UserListForm.UserListBox.options[i].selected = userIds.indexOf(this.frameListOfUsers.UserListForm.UserListBox.options[i].value + ',') >= 0\r\n";
    echo "    changeUserListBoxControl()\r\n";
    echo "  }\r\n\r\n";
    echo "  function showCalender(showDate)\r\n";
    echo "  {\r\n";
    echo "    year = parseInt(showDate.substr(0, 4), 10)\r\n";
    echo "    month = parseInt(showDate.substr(5, 2), 10)\r\n";
    echo "    date = parseInt(showDate.substr(8, 2), 10)\r\n";
    echo "    userIds = ''\r\n";
    echo "    for (i = 0; i < this.frameListOfUsers.UserListForm.UserListBox.length; i++)\r\n";
    echo "    {\r\n";
    echo "      if (this.frameListOfUsers.UserListForm.UserListBox.options[i].selected)\r\n";
    echo "      {\r\n";
    echo "        if (userIds.length > 0)\r\n";
    echo "          userIds = userIds + ','\r\n";
    echo "        userIds = userIds + this.frameListOfUsers.UserListForm.UserListBox.options[i].value\r\n";
    echo "      }\r\n";
    echo "    }\r\n";
    echo "    if (parent.validateDate(year, month, date))\r\n";
    echo "      parent.showCalender(showDate, userIds)\r\n";
    echo "  }\r\n\r\n";
    echo "  function changeDateEditControl()\r\n";
    echo "  {\r\n";
    echo "    s = this.frameAppointment.ContentForm.Date.value\r\n";
    echo "    if (s.length == 8)\r\n";
    echo "    {\r\n";
    echo "      s = s.substr(0, 4) + '-' + s.substr(4, 2) + '-' + s.substr(6, 2)\r\n";
    echo "      this.frameAppointment.ContentForm.Date.value = s\r\n";
    echo "    }\r\n";
    echo "    year = parseInt(s.substr(0, 4), 10)\r\n";
    echo "    month = parseInt(s.substr(5, 2), 10)\r\n";
    echo "    date = parseInt(s.substr(8, 2), 10)\r\n";
    echo "    if (parent.validateDate(year, month, date))\r\n";
    echo "    {\r\n";
    echo "      updateAppointment(parent.setDate(year, month - 1, date), parent.getUserIds())\r\n";
    echo "      return true\r\n";
    echo "    }\r\n";
    echo "    else\r\n";
    echo "      return false\r\n";
    echo "  }\r\n\r\n";
    echo "  function changeFromTimeEditControl()\r\n";
    echo "  {\r\n";
    echo "    s = this.frameAppointment.ContentForm.FromTime.value\r\n";
    echo "    if (s.length == 4)\r\n";
    echo "    {\r\n";
    echo "      s = s.substr(0, 2) + ':' + s.substr(2, 2)\r\n";
    echo "      this.frameAppointment.ContentForm.FromTime.value = s\r\n";
    echo "    }\r\n";
    echo "    hour = parseInt(s.substr(0, 2), 10)\r\n";
    echo "    minute = parseInt(s.substr(3, 2), 10)\r\n";
    echo "    return parent.validateTime(hour, minute, 'starttidspunktet')\r\n";
    echo "  }\r\n\r\n";
    echo "  function changeToTimeEditControl()\r\n";
    echo "  {\r\n";
    echo "    s = this.frameAppointment.ContentForm.ToTime.value\r\n";
    echo "    if (s.length == 4)\r\n";
    echo "    {\r\n";
    echo "      s = s.substr(0, 2) + ':' + s.substr(2, 2)\r\n";
    echo "      this.frameAppointment.ContentForm.ToTime.value = s\r\n";
    echo "    }\r\n";
    echo "    hour = parseInt(s.substr(0, 2), 10)\r\n";
    echo "    minute = parseInt(s.substr(3, 2), 10)\r\n";
    echo "    return parent.validateTime(hour, minute, 'sluttidspunktet')\r\n";
    echo "  }\r\n\r\n";
    echo "  function changeBellCheckBoxControl()\r\n";
    echo "  {\r\n";
    echo "    if (this.frameAppointment.ContentForm.Bell.checked)\r\n";
    echo "      this.frameAppointment.ContentForm.Done.checked = false\r\n";
    echo "  }\r\n\r\n";
    echo "  function changeDoneCheckBoxControl()\r\n";
    echo "  {\r\n";
    echo "    if (this.frameAppointment.ContentForm.Done.checked)\r\n";
    echo "      this.frameAppointment.ContentForm.Bell.checked = false\r\n";
    echo "  }\r\n\r\n";
    echo "  function changeExportCheckBoxControl()\r\n";
    echo "  {\r\n";
    echo "    if (this.frameAppointment.ContentForm.Export.checked)\r\n";
    echo "      this.frameAppointment.ContentForm.Exported.checked = false\r\n";
    echo "  }\r\n\r\n";
    echo "  function changeUserEditControl()\r\n";
    echo "  {\r\n";
    echo "    s = ',' + this.frameListOfUsers.UserListForm.UserEdit.value.toUpperCase() + ','\r\n";
    echo "    for (i = 0; i < this.frameListOfUsers.UserListForm.UserListBox.length; i++)\r\n";
    echo "    {\r\n";
    echo "      t = this.frameListOfUsers.UserListForm.UserListBox.options[i].text.toUpperCase()\r\n";
    echo "      if (t.lastIndexOf('(') >= 0 && t.lastIndexOf(')') >= 0)\r\n";
    echo "        t = t.substring(t.lastIndexOf('(') + 1, t.lastIndexOf(')'))\r\n";
    echo "      t = ',' + t + ','\r\n";
    echo "      this.frameListOfUsers.UserListForm.UserListBox.options[i].selected = (s.indexOf(t) >= 0)\r\n";
    echo "    }\r\n";
    echo "    changeUserListBoxControl()\r\n";
    echo "  }\r\n\r\n";
    echo "  function changeUserListBoxControl()\r\n";
    echo "  {\r\n";
    echo "    s = ''\r\n";
    echo "    userIds = ''\r\n";
    echo "    privateUserIds = this.frameAppointment.ContentForm.PrivateUserIds.value + ','\r\n";
    echo "    disablePrivate = privateUserIds.length == 0\r\n";
    echo "    for (i = 0; i < this.frameListOfUsers.UserListForm.UserListBox.length; i++)\r\n";
    echo "    {\r\n";
    echo "      if (this.frameListOfUsers.UserListForm.UserListBox.options[i].selected)\r\n";
    echo "      {\r\n";
    echo "        t = this.frameListOfUsers.UserListForm.UserListBox.options[i].text.toUpperCase()\r\n";
    echo "        if (t.lastIndexOf('(') >= 0 && t.lastIndexOf(')') >= 0)\r\n";
    echo "          t = t.substring(t.lastIndexOf('(') + 1, t.lastIndexOf(')'))\r\n";
    echo "        if (s.length > 0)\r\n";
    echo "          s = s + ','\r\n";
    echo "        s = s + t\r\n";
    echo "        if (userIds.length > 0)\r\n";
    echo "          userIds = userIds + ','\r\n";
    echo "        userIds = userIds + this.frameListOfUsers.UserListForm.UserListBox.options[i].value\r\n";
    echo "        if (!disablePrivate)\r\n";
    echo "          disablePrivate = privateUserIds.indexOf(this.frameListOfUsers.UserListForm.UserListBox.options[i].value + ',') == -1\r\n";
    echo "      }\r\n";
    echo "    }\r\n";
    echo "    if (disablePrivate || userIds.length == 0)\r\n";
    echo "    {\r\n";
    echo "      this.frameAppointment.ContentForm.Private.checked = false\r\n";
    echo "      this.frameAppointment.ContentForm.Private.disabled = true\r\n";
    echo "    }\r\n";
    echo "    else\r\n";
    echo "      this.frameAppointment.ContentForm.Private.disabled = false\r\n";
    echo "    this.frameListOfUsers.UserListForm.UserEdit.value = s\r\n";
    echo "    updateAppointment(parent.getDate(), parent.setUserIds(userIds))\r\n";
    echo "  }\r\n\r\n";
    echo "  function changeScheduledFromEditControl()\r\n";
    echo "  {\r\n";
    echo "    updateAppointment(parent.getDate(), parent.getUserIds())\r\n";
    echo "  }\r\n\r\n";
    echo "  function updateAppointment(scheduledForDate, userIds)\r\n";
    echo "  {\r\n";
    echo "    fun = $Fun\r\n";
    echo "    year = scheduledForDate.getFullYear()\r\n";
    echo "    month = scheduledForDate.getMonth() + 1\r\n";
    echo "    date = scheduledForDate.getDate()\r\n";
    echo "    scheduledFrom = parseInt(this.frameScheduled.ScheduledForm.ScheduledFrom.value, 10)\r\n";
    echo "    this.frameScheduled.location = 'calender.php?Fun=' + ((fun * 10) + 3) + '&Year=' + year + '&Month=' + month + '&Date=' + date + '&UserIds=' + userIds + '&ScheduledFrom=' + scheduledFrom\r\n";
    echo "  }\r\n";
    echo "</script>\r\n";
    echo "<html>\r\n";
    echo "  <head>\r\n";
    echo "    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=windows-1252\">\r\n";
    echo "    <title>Kalender</title>\r\n";
    echo "  </head>\r\n";
    echo "  <frameset rows=\"280,*\" frameborder=\"0\" framespacing=\"0\" border=\"0\">\r\n";
    $NewFun = ($Fun * 10) + 1;
    echo "    <frame name=\"frameAppointment\" scrolling=\"no\" noresize src=\"calender.php?Fun=$NewFun&Year=$Year&Month=$Month&Date=$Date&UserIds=$UserIds$CalId\">\r\n";
    echo "      <frameset cols=\"260,*\" frameborder=\"0\" framspacing=\"0\" border=\"0\">\r\n";
    $NewFun = ($Fun * 10) + 2;
    echo "        <frame name=\"frameListOfUsers\" scrolling=\"auto\" noresize src=\"calender.php?Fun=$NewFun&UserIds=$UserIds\">\r\n";
    $NewFun = ($Fun * 10) + 3;
    echo "        <frame name=\"frameScheduled\" scrolling=\"auto\" noresize src=\"calender.php?Fun=$NewFun&Year=$Year&Month=$Month&Date=$Date&UserIds=$UserIds\">\r\n";
    echo "      </frameset>\r\n";
    echo "  </frameset>\r\n";
    echo "</html>\r\n";
  }

  function MakeAppointmentContent($SystemNo, $AppointmentTableName, $MergeTableName, $Username, $Password, $Year, $Month, $Date, $UserIds)
  {
    $AllowSelect = oswebdb_allow_select($AppointmentTableName) && oswebdb_allow_select($MergeTableName);
    $AllowInsert = oswebdb_allow_insert($AppointmentTableName) && oswebdb_allow_insert($MergeTableName);
    $AllowUpdate = oswebdb_allow_update($AppointmentTableName) && oswebdb_allow_update($MergeTableName);
    $AllowDelete = oswebdb_allow_delete($AppointmentTableName) && oswebdb_allow_delete($MergeTableName);
    $NewAppointment = !isset($_GET["CalId"]);
    if ($NewAppointment)
      $Statement = "SELECT CalId,Date,FromTime,ToTime,Properties,Subject,Note FROM $AppointmentTableName";
    else if (isset($_GET["CalId"]))
    {
      $CalId = $_GET["CalId"];
      $Statement = "SELECT CalId,Date,FromTime,ToTime,Properties,Subject,Note FROM $AppointmentTableName WHERE SystemNo=$SystemNo AND CalId=$CalId";
    }
    if (($NewAppointment || isset($CalId)) && $Result = oswebdb_query($Statement))
    {
      if ($NewAppointment || $Row = oswebdb_fetch_row($Result))
      {
        $LoggedOn = isset($Username) && isset($Password);
        $PrivateUserIds = get_private_user_ids($SystemNo);
        $DisablePrivate = strlen($PrivateUserIds) == 0;
        $UserIds = explode(",", $UserIds); $i = 0;
        while (!$DisablePrivate && $i < count($UserIds))
        {
          $DisablePrivate = strstr("$PrivateUserIds,", "$UserIds[$i],") == false;
          $i++;
        }
        $UserIds = implode(",", $UserIds);
        $TabIndex = 1; $TabAdjust = 8; $RowSpan = $TabAdjust;
        MakeHtmlPageTop("Kalender");
        echo "    <form name=\"ContentForm\" action=\"javascript:parent.bookAppointment(0)\">\r\n";
        if (isset($Row))
        {
          echo "      <input type=\"hidden\" name=\"CalId\" value=\"$Row[0]\">\r\n";
          echo "      <input type=\"hidden\" name=\"UserIds\" value=\"$UserIds\">\r\n";
        }
        echo "      <input type=\"hidden\" name=\"PrivateUserIds\" value=\"$PrivateUserIds\">\r\n";
        echo "      <table border=\"0\" cellpadding=\"1\" cellspacing=\"1\" width=\"100%\">\r\n";
        echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\" colspan=\"3\">";
        if (isset($Row))
        {
          if ($AllowUpdate)
          {
            $UpdateInput = MakeHtmlInputButton("", "Book", !$AllowInsert, $TabIndex++, "javascript:parent.bookAppointment(2)");
            $ResetInput = MakeHtmlInputReset("", "Fortryd", !$AllowInsert, $TabIndex++, "javascript:parent.resetAppointment()");
            echo "$UpdateInput&nbsp;$ResetInput&nbsp;";
          }
          if ($AllowDelete)
          {
            $DeleteInput = MakeHtmlInputButton("", "Slet", !$AllowInsert, $TabIndex++, "javascript:parent.bookAppointment(3)");
            echo "$DeleteInput&nbsp;";
          }
        }
        else if ($AllowInsert)
        {
          $InsertInput = MakeHtmlInputButton("", "Book", !$AllowInsert, $TabIndex++, "javascript:parent.bookAppointment(1)");
          echo "$InsertInput&nbsp;";
        }
        if ($AllowSelect)
        {
          $ShowCalenderInput = MakeHtmlInputButton("", "Vis kalender", !$AllowSelect, $TabIndex++, "javascript:parent.showCalender(ContentForm.Date.value)");
          echo "$ShowCalenderInput&nbsp;";
        }
        echo "</td></tr>\r\n";
        $FirstInput = MakeHtmlInputText("Date", ($NewAppointment ? date("Y-m-d", mktime(0, 0, 0, $Month, $Date, $Year)) : $Row[1]), oswebdb_field_len($Result, 1), oswebdb_field_len($Result, 1), ($NewAppointment ? !$AllowInsert : !$AllowUpdate), ($NewAppointment ? !$AllowInsert : !$AllowUpdate), $TabIndex++, "javascript:return parent.changeDateEditControl()");
        $SecondInput = MakeHtmlInputText("Subject", ($NewAppointment ? "" : $Row[5]), oswebdb_field_len($Result, 5), oswebdb_field_len($Result, 5), ($NewAppointment ? !$AllowInsert : !$AllowUpdate), ($NewAppointment ? !$AllowInsert : !$AllowUpdate), $TabIndex + $TabAdjust, "");
        echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Dato :</td><td width=\"24%\" nowrap>$FirstInput</td><td width=\"1%\" align=\"right\" nowrap>Emne :</td><td width=\"74%\">$SecondInput</td></tr>\r\n";
        if (($Length = oswebdb_field_len($Result, 2)) > 5);
          $Length = 5;
        $FirstInput = MakeHtmlInputText("FromTime", ($NewAppointment ? "##:##" : substr($Row[2], 0, 5)), $Length, $Length, ($NewAppointment ? !$AllowInsert : !$AllowUpdate), ($NewAppointment ? !$AllowInsert : !$AllowUpdate), $TabIndex++, "javascript:return parent.changeFromTimeEditControl()");
        $SecondInput = MakeHtmlTextArea("Note", ($RowSpan * 1.5), 40, "", ($NewAppointment ? !$AllowInsert : !$AllowUpdate), ($NewAppointment ? !$AllowInsert : !$AllowUpdate), $TabIndex + $TabAdjust, "", ($NewAppointment ? "" : $Row[6]));
        echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Fra :</td><td width=\"24%\" nowrap>$FirstInput</td><td width=\"1%\" align=\"right\" nowrap>Tekst :</td><td width=\"74%\" valign=\"top\" rowspan=\"$RowSpan\">$SecondInput</td></tr>\r\n";
        if (($Length = oswebdb_field_len($Result, 3)) > 5);
          $Length = 5;
        $FirstInput = MakeHtmlInputText("ToTime", ($NewAppointment ? "##:##" : substr($Row[3], 0, 5)), $Length, $Length, ($NewAppointment ? !$AllowInsert : !$AllowUpdate), ($NewAppointment ? !$AllowInsert : !$AllowUpdate), $TabIndex++, "javascript:return parent.changeToTimeEditControl()");
        echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Til :</td><td width=\"24%\" nowrap>$FirstInput</td><td width=\"1%\" align=\"right\" nowrap></td></tr>\r\n";
        $FirstInput = MakeHtmlInputCheckBox("Public", 1, ($NewAppointment ? !$LoggedOn : ($Row[4] & 1)), ($NewAppointment ? !$AllowInsert : !$AllowUpdate) || !$LoggedOn, $TabIndex++, "", "Offentliggørelse");
        echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"24%\" nowrap>$FirstInput</td><td width=\"1%\" align=\"right\" nowrap></td></tr>\r\n";
        $FirstInput = MakeHtmlInputCheckBox("Private", 2, ($NewAppointment ? 0 : ($Row[4] & 2)), ($NewAppointment ? !$AllowInsert : !$AllowUpdate) || $DisablePrivate, $TabIndex++, "", "Privat");
        echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"24%\" nowrap>$FirstInput</td><td width=\"1%\" align=\"right\" nowrap></td></tr>\r\n";
        $FirstInput = MakeHtmlInputCheckBox("Bell", 4, ($NewAppointment ? 0 : ($Row[4] & 4)), ($NewAppointment ? !$AllowInsert : !$AllowUpdate), $TabIndex++, "javascript:parent.changeBellCheckBoxControl()", "Alarm");
        echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"24%\" nowrap>$FirstInput</td><td width=\"1%\" align=\"right\" nowrap></td></tr>\r\n";
        $FirstInput = MakeHtmlInputCheckBox("Done", 8, ($NewAppointment ? 0 : ($Row[4] & 8)), ($NewAppointment ? !$AllowInsert : !$AllowUpdate), $TabIndex++, "javascript:parent.changeDoneCheckBoxControl()", "Udført");
        echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"24%\" nowrap>$FirstInput</td><td width=\"1%\" align=\"right\" nowrap></td></tr>\r\n";
        $FirstInput = MakeHtmlInputCheckBox("Export", 16, ($NewAppointment ? 0 : ($Row[4] & 16)), ($NewAppointment ? !$AllowInsert : !$AllowUpdate), $TabIndex++, "javascript:parent.changeExportCheckBoxControl()", "Eksportér");
        echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"24%\" nowrap>$FirstInput</td><td width=\"1%\" align=\"right\" nowrap></td></tr>\r\n";
        $FirstInput = MakeHtmlInputCheckBox("Exported", 32, ($NewAppointment ? 0 : ($Row[4] & 32)), 1, $TabIndex++, "", "Eksporteret");
        echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"24%\" nowrap>$FirstInput</td><td width=\"1%\" align=\"right\" nowrap></td></tr>\r\n";
        echo "      </table>\r\n";
        echo "    </form>\r\n";
        MakeHtmlPageBottom();
      }
      else
      {
        $ShowDate = date("Y-m-d", mktime(0, 0, 0, $Month, $Date, $Year));
        MakeHtmlPageReload("javascript:parent.showCalender('$ShowDate'); return true;");
      }
      oswebdb_free_result($Result);
    }
  }

  function MakeAppointmentScheduled($SystemNo, $Year, $Month, $Date, $UserIds, $ScheduledFrom)
  {
    $TabIndex = 1;
    echo "<style type=\"text/css\">\r\n";
    echo "  .scheduler, .scheduler TH\r\n";
    echo "  {\r\n";
    echo "    background-color:#FDF992;\r\n";
    echo "    color:#000000;\r\n";
    echo "  }\r\n\r\n";
    echo "  .booked, .booked TH, .booked TD\r\n";
    echo "  {\r\n";
    echo "    background-color:#CC3300;\r\n";
    echo "    color:#000000;\r\n";
    echo "  }\r\n";
    echo "</style>\r\n";
    MakeHtmlPageTop("Kalender");
    echo "    <form name=\"ScheduledForm\" action=\"\">\r\n";
    echo "      <table border=\"1\" cellpadding=\"2\" cellspacing=\"0\" bordercolor=\"#CC3300\" class=\"scheduler\">\n";
    $Input = MakeHtmlInputText("ScheduledFrom", "$ScheduledFrom", 2, 2, 0, 0, $TabIndex++, "javascript:parent.changeScheduledFromEditControl()");
    echo "        <tr><td nowrap>&nbsp;</td><td colspan=\"4\">$Input</td>";
    $TM = ($ScheduledFrom * 60) + 120;
    while ($TM < 1440)
    {
      $Hour = ($TM - ($TM % 60)) / 60;
      echo "<td colspan=\"4\" nowrap><strong>$Hour</strong></td>";
      $TM += 120;
    }
    echo "</tr>\r\n";
    $UserIds = explode(",", $UserIds); $i = 0;
    while ($i < count($UserIds))
    {
      if (strlen($UserIds[$i]) > 0)
      {
        $Name = get_name_from_user_id($SystemNo, $UserIds[$i]);
        $AppointmentIds = explode(",", get_appointment_ids($SystemNo, date("Y-m-d", mktime(0, 0, 0, $Month, $Date, $Year)), $UserIds[$i]));
        echo "        <tr><td nowrap>$Name</td>";
        $TM = $ScheduledFrom * 60; $j = 0;
        while ($j < count($AppointmentIds))
        {
          if (strlen($AppointmentIds[$j]) > 0)
          {
            $AppointmentContent = get_appointment_content($SystemNo, $AppointmentIds[$j], NULL, NULL, NULL);
            $FromTime = (((integer) substr($AppointmentContent[4], 0, 2)) * 60) + ((integer) substr($AppointmentContent[4], 3, 2));
            $ToTime = (((integer) substr($AppointmentContent[5], 0, 2)) * 60) + ((integer) substr($AppointmentContent[5], 3, 2));
            while ($TM < $FromTime - 29)
            {
              echo "<td nowrap>&nbsp</td>";
              $TM += 30;
            }
            while ($TM <= $ToTime)
            {
              echo "<td nowrap class=\"booked\">&nbsp</td>";
              $TM += 30;
            }
          }
          $j++;
        }
        while ($TM < 1440)
        {
          echo "<td colspan nowrap>&nbsp;</td>";
          $TM += 30;
        }
        echo "</tr>\r\n";
      }
      $i++;
    }
    echo "      </table>\r\n";
    echo "    </form>\r\n";
    MakeHtmlPageBottom();
  }

  function MakeBells($Fun, $SystemNo, $AppointmentBells)
  {
    echo "<script language=\"JavaScript\">\r\n";
    echo "  function printBells()\r\n";
    echo "  {\r\n";
    echo "    printWindow = window.open('', '', 'width=600,height=470,scrollbars')\r\n";
    echo "    if (printWindow)\r\n";
    echo "      printWindow.location = 'calender.php?Fun=43'\r\n";
    echo "  }\r\n\r\n";
    echo "  function showCalender()\r\n";
    echo "  {\r\n";
    echo "    parent.openCalender(2)\r\n";
    echo "  }\r\n\r\n";
    echo "  function modifyAppointment(appointmentDate, appointmentUserIds, appointmentId)\r\n";
    echo "  {\r\n";
    echo "    parent.modifyAppointment(appointmentDate, appointmentUserIds, appointmentId)\r\n";
    echo "  }\r\n\r\n";
    echo "  function changeBellCheckBoxControl(appointmentId, userIds, properties)\r\n";
    echo "  {\r\n";
    echo "    if ((properties & 4) == 0)\r\n";
    echo "    {\r\n";
    echo "      properties += 4\r\n";
    echo "      if (properties & 8)\r\n";
    echo "        properties -= 8\r\n";
    echo "    }\r\n";
    echo "    else\r\n";
    echo "      properties -= 4\r\n";
    echo "    updateAppointmentUserProperties(appointmentId, userIds, properties)\r\n";
    echo "  }\r\n\r\n";
    echo "  function changeDoneCheckBoxControl(appointmentId, userIds, properties)\r\n";
    echo "  {\r\n";
    echo "    if ((properties & 8) == 0)\r\n";
    echo "    {\r\n";
    echo "      properties += 8\r\n";
    echo "      if (properties & 4)\r\n";
    echo "        properties -= 4\r\n";
    echo "    }\r\n";
    echo "    else\r\n";
    echo "      properties -= 8\r\n";
    echo "    updateAppointmentUserProperties(appointmentId, userIds, properties)\r\n";
    echo "  }\r\n\r\n";
    echo "  function updateAppointmentUserProperties(appointmentId, userIds, properties)\r\n";
    echo "  {\r\n";
    echo "    fun = $Fun\r\n";
    echo "    this.frameBells.location = 'calender.php?Fun=' + ((fun * 10) + 2) + '&CalId=' + appointmentId + '&UserIds=' + userIds + '&Properties=' + properties\r\n";
    echo "  }\r\n";
    echo "</script>\r\n";
    echo "<html>\r\n";
    echo "  <head>\r\n";
    echo "    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=windows-1252\">\r\n";
    echo "    <title>Kalender</title>\r\n";
    echo "  </head>\r\n";
    echo "  <frameset rows=\"70,*\" frameborder=\"0\" framespacing=\"0\" border=\"0\">\r\n";
    $NewFun = ($Fun * 10) + 1;
    echo "    <frame name=\"frameToolbar\" scrolling=\"no\" noresize src=\"calender.php?Fun=$NewFun\">\r\n";
    $NewFun = ($Fun * 10) + 2;
    echo "    <frame name=\"frameBells\" scrolling=\"auto\" noresize src=\"calender.php?Fun=$NewFun&AppointmentBells=$AppointmentBells\">\r\n";
    echo "  </frameset>\r\n";
    echo "</html>\r\n";
  }

  function MakeBellsToolbar($AppointmentTableName, $MergeTableName)
  {
    $Print = "";
    $ShowCalender = "";
    if (oswebdb_allow_select($AppointmentTableName) && oswebdb_allow_select($MergeTableName))
    {
      $Print = MakeHtmlLink("", "javascript:parent.printBells()", "", "", "", "", "javascript:window.status='Udskriv alarmer'; return true;", "javascript:window.status=''; return true;", "Udskriv");
      $ShowCalender = MakeHtmlLink("", "javascript:parent.showCalender()", "", "", "", "", "javascript:window.status='Vis kalender'; return true;", "javascript:window.status=''; return true;", "Vis kalender");
    }
    if (strlen($ShowCalender) > 0)
      $Print = "$Print&nbsp;|&nbsp;";
    MakeHtmlPageTop("Kalender");
    echo "    <form name=\"BellsToolbar\" action=\"\">\r\n";
    echo "      <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">\r\n";
    echo "        <tr><td width=\"99%\" valign=\"middle\" nowrap><h2><i>Kalender - Alarmer</i></h2></td><td width=\"1%\" valign=\"top\" align=\"right\" nowrap>$Print$ShowCalender</td></tr>\r\n";
    echo "      </table>\r\n";
    echo "    </form>\r\n";
    MakeHtmlPageBottom();
  }

  function MakeBellsContent($Fun, $SystemNo, $AppointmentTableName, $MergeTableName, $Username, $Password, $AppointmentBells)
  {
    $PrivateUserIds = get_private_user_ids($SystemNo);
    if (isset($_GET["CalId"]) && isset($_GET["UserIds"]) && isset($_GET["Properties"]))
    {
      if (!update_appointment_user_properties($SystemNo, $_GET["CalId"], $_GET["UserIds"], $_GET["Properties"]))
      {
        $Error = oswebdb_error();
        MakeHtmlPageTop("Kalender");
        echo "    <h2><strong>$Error</strong></h2>\r\n";
        MakeHtmlPageBottom("");
        exit;
      }
      if (!isset($AppointmentBells))
        $AppointmentBells = get_appointment_bells($SystemNo, date("Y-m-d", time()), date("H:i:s", time()), $PrivateUserIds);
    }
    $TabIndex = 1;
    $ModifyAppointment = oswebdb_allow_update($AppointmentTableName) && oswebdb_allow_update($MergeTableName);
    MakeHtmlPageTop("Kalender");
    echo "    <form name=\"BellsForm\" action=\"\">\r\n";
    echo "      <table border=\"1\" cellpadding=\"2\" cellspacing=\"0\">\r\n";
    echo "        <tr><td nowrap><strong>Dato</strong></td><td nowrap><strong>Tid</strong></td><td nowrap><strong>Deltagere</strong><td nowrap><strong>&nbsp;</strong></td><td wrap><strong>Aftale</strong></tr>\r\n";
    $AppointmentBells = explode(",", $AppointmentBells); $i = 0;
    while ($i < count($AppointmentBells))
    {
      if (strlen($AppointmentBells[$i]) > 0)
      {
         $AppointmentContent = get_appointment_content($SystemNo, $AppointmentBells[$i], Username, Password, $PrivateUserIds);
         if ($AppointmentContent[2])
         {
           $Date = "$AppointmentContent[3]";
           if ($ModifyAppointment)
             $Date = MakeHtmlLink("", "javascript:parent.modifyAppointment('$AppointmentContent[3]', '$AppointmentContent[6]', $AppointmentContent[1])", "", "", "", "", "javascript:window.status='Ret aftale'; return true;", "javascript:window.status=''; return true;", "$Date");
           $Bell = MakeHtmlInputCheckBox("", "", ($AppointmentContent[8] & 4), !$ModifyAppointment, $TabIndex++, "javascript:parent.changeBellCheckBoxControl($AppointmentContent[1], '$PrivateUserIds', $AppointmentContent[8])", "Alarm");
           $Done = MakeHtmlInputCheckBox("", "", ($AppointmentContent[8] & 8), !$ModifyAppointment, $TabIndex++, "javascript:parent.changeDoneCheckBoxControl($AppointmentContent[1], '$PrivateUserIds', $AppointmentContent[8])", "Udført");
           $Content = $AppointmentContent[10];
           if (strlen($AppointmentContent[11]) > 0)
             $Content = "$Content<br>$AppointmentContent[11]";
           $Content = "$Content<br><br>$Bell<br>$Done";
           echo "        <tr><td valign=\"top\" nowrap>$Date</td><td valign=\"top\" nowrap>$AppointmentContent[4]-$AppointmentContent[5]</td><td valign=\"top\" nowrap>$AppointmentContent[7]</td><td valign=\"top\" nowrap>$AppointmentContent[9]</td><td valign=\"top\" wrap>$Content</td></tr>\r\n";
         }
      }
      $i++;
    }
    echo "      </table>\r\n";
    echo "    </form>\r\n";
    MakeHtmlPageBottom();
  }

  function MakeBellsPrint($SystemNo, $Username, $Password)
  {
    $PrivateUserIds = get_private_user_ids($SystemNo);
    MakeHtmlPrintPageTop("Kalenderudskrift af alarmer");
    echo "    <table border=\"0\" cellpadding=\"1\" cellspacing=\"1\" width=\"100%\">\r\n";
    $Print = MakeHtmlLink("", "javascript:window.print()", "", "", "", "", "javascript:window.status='Udskriv'; return true;", "javascript:window.status=''; return true;", "Udskriv");
    $Close = MakeHtmlLink("", "javascript:window.close()", "", "", "", "", "javascript:window.status='Luk vindue'; return true;", "javascript:window.status=''; return true;", "Luk");
    echo "      <tr><td colspan=\"4\" nowrap><h2><i>Kalenderudskrift af alarmer</i></h2></td><td valign=\"top\" align=\"right\" nowrap>$Print&nbsp;|&nbsp;$Close</td></tr>\r\n";
    echo "      <tr><td colspan=\"5\"><br></td></tr>\r\n";
    $AppointmentBells = explode(",", get_appointment_bells($SystemNo, date("Y-m-d", time()), date("H:i:s", time()), $PrivateUserIds)); $i = 0;
    while ($i < count($AppointmentBells))
    {
      if (strlen($AppointmentBells[$i]) > 0)
      {
        $AppointmentContent = get_appointment_content($SystemNo, $AppointmentBells[$i], $Username, $Password, $PrivateUserIds);
        if ($AppointmentContent[2])
        {
          $Content = "$AppointmentContent[10]";
          if (strlen($AppointmentContent[11]) > 0)
            $Content = "$Content<br>$AppointmentContent[11]";
          echo "      <tr><td valign=\"top\" nowrap>$AppointmentContent[3]</td><td valign=\"top\" nowrap>$AppointmentContent[4]-$AppointmentContent[5]</td><td valign=\"top\" nowrap>$AppointmentContent[7]</td><td valign=\"top\" nowrap>$AppointmentContent[9]</td><td valign=\"top\" wrap>$Content</td></tr>\r\n";
        }
      }
      $i++;
    }
    echo "    </table>\r\n";
    MakeHtmlPrintPageBottom();
  }

  function MakeUsers($Fun, $SystemNo, $UserTableName)
  {
    $AllowSelect = oswebdb_allow_select($UserTableName);
    $AllowInsert = oswebdb_allow_insert($UserTableName);
    $AllowUpdate = oswebdb_allow_update($UserTableName);
    $AllowDelete = oswebdb_allow_delete($UserTableName);
    $NewUser = isset($_POST["NewUser"]);
    if ($NewUser)
      $Statement = "SELECT UserId,UserName,Name,Initials FROM $UserTableName WHERE SystemNo=$SystemNo";
    else if (isset($_POST["UserId"]))
    {
      $UserId = $_POST["UserId"];
      $Statement = "SELECT UserId,UserName,Name,Initials FROM $UserTableName WHERE SystemNo=$SystemNo AND UserId=$UserId";
    }
    if (isset($_POST["Insert"]) && isset($UserId) && isset($_POST["Name"]) && isset($_POST["Initials"]))
    {
      if (!insert_user($SystemNo, $UserId, $_POST["UserName"], $_POST["Name"], $_POST["Initials"]))
      {
        $Error = oswebdb_error();
        MakeHtmlPageTop("Kalender");
        echo "    <h2><strong>$Error</strong></h2>\r\n";
        MakeHtmlPageBottom("");
        exit;
      }
    }
    else if (isset($_POST["Update"]) && isset($UserId) && isset($_POST["Name"]) && isset($_POST["Initials"]))
    {
      if (!update_user($SystemNo, $UserId, $_POST["UserName"], $_POST["Name"], $_POST["Initials"]))
      {
        $Error = oswebdb_error();
        MakeHtmlPageTop("Kalender");
        echo "    <h2><strong>$Error</strong></h2>\r\n";
        MakeHtmlPageBottom("");
        exit;
      }
    }
    else if (isset($_POST["Delete"]) && isset($UserId))
    {
      if (!delete_user($SystemNo, $UserId))
      {
        $Error = oswebdb_error();
        MakeHtmlPageTop("Kalender");
        echo "    <h2><strong>$Error</strong></h2>\r\n";
        MakeHtmlPageBottom("");
        exit;
      }
      unset($UserId);
    }
    else if (isset($_POST["ShowAll"]) && isset($UserId))
      unset($UserId);
    if (($NewUser || isset($UserId)) && $Result = oswebdb_query($Statement))
    {
      if ($NewUser || $Row = oswebdb_fetch_row($Result))
      {
        $TabIndex = 1;
        echo "<script language=\"JavaScript\">\r\n";
        echo "  function validateUser()\r\n";
        echo "  {\r\n";
        if ($NewUser)
        {
          echo "    userId = this.UserForm.UserId.value\r\n";
          echo "    name = this.UserForm.Name.value\r\n";
          echo "    initials = this.UserForm.Initials.value\r\n";
          echo "    if (userId.length == 0)\r\n";
          echo "    {\r\n";
          echo "      alert('Brugeridet skal indtastes!')\r\n";
          echo "      return false\r\n";
          echo "    }\r\n";
          echo "    else if (userId <= 0)\r\n";
          echo "    {\r\n";
          echo "      alert('Brugeridet være større end 0!')\r\n";
          echo "      return false\r\n";
          echo "    }\r\n";
          echo "    else if (name.length == 0)\r\n";
        }
        else
        {
          echo "    name = this.UserForm.Name.value\r\n";
          echo "    initials = this.UserForm.Initials.value\r\n";
          echo "    if (name.length == 0)\r\n";
        }
        echo "    {\r\n";
        echo "      alert('Navnet skal indtastes!')\r\n";
        echo "      return false\r\n";
        echo "    }\r\n";
        echo "    else if (initials.length == 0)\r\n";
        echo "    {\r\n";
        echo "      alert('Initialer skal indtastes!')\r\n";
        echo "      return false\r\n";
        echo "    }\r\n";
        echo "    else\r\n";
        echo "    {\r\n";
        echo "      this.UserForm.Initials.value = this.UserForm.Initials.value.toUpperCase()\r\n";
        echo "      return true\r\n";
        echo "    }\r\n";
        echo "  }\r\n";
        echo "</script>\r\n";
        MakeHtmlPageTop("Kalender");
        echo "    <form name=\"UserForm\" action=\"calender.php\" method=\"post\">\r\n";
        echo "      <input type=\"hidden\" name=\"Fun\" value=\"$Fun\">\r\n";
        if (!$NewUser)
          echo "      <input type=\"hidden\" name=\"UserId\" value=\"$Row[0]\">\r\n";
        echo "      <table border=\"0\" cellpadding=\"1\" cellspacing=\"1\" width=\"100%\">\r\n";
        echo "        <tr><td width=\"100%\" colspan=\"2\" valign=\"middle\"><h2><i>Kalender - brugere</i></h2></td></tr>\r\n";
        echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\">";
        if ($NewUser)
        {
          if ($AllowInsert)
          {
            $InputSubmit = MakeHtmlInputSubmit("Insert", "Opret", !$AllowInsert, $TabIndex++, "javascript:return validateUser()");
            $InputReset = MakeHtmlInputReset("Reset", "Fortryd", !$AllowInsert, $TabIndex++, "");
            echo "$InputSubmit&nbsp;$InputReset&nbsp;";
          }
          if ($AllowSelect)
          {
            $InputShowAll = MakeHtmlInputSubmit("ShowAll", "Vis alle", !$AllowSelect, $TabIndex++, "");
            echo "$InputShowAll&nbsp;";
          }
          $NewUserId = "10000001";
          if ($NewUserIdResult = oswebdb_query("SELECT UserId FROM $UserTableName WHERE SystemNo=$SystemNo ORDER BY UserId DESC"))
          {
            if ($NewUserIdRow = oswebdb_fetch_row($NewUserIdResult))
              $NewUserId = $NewUserIdRow[0] + 1;
            oswebdb_free_result($NewUserIdResult);
          }
          $Input = MakeHtmlInputText("UserId", "$NewUserId", oswebdb_field_len($Result, 0), oswebdb_field_len($Result, 0), !$AllowInsert, !$AllowInsert, $TabIndex++, "");
        }
        else
        {
          if ($AllowUpdate)
          {
            $InputSubmit = MakeHtmlInputSubmit("Update", "Opdatér", !$AllowUpdate, $TabIndex++, "javascript:return validateUser()");
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
          $Input = MakeHtmlInputText("", $Row[0], oswebdb_field_len($Result, 0), oswebdb_field_len($Result, 0), 1, 1, $TabIndex++, "");
        }
        echo "</td></tr>\r\n";
        echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Brugerid :</td><td width=\"99%\">$Input</td></tr>\r\n";
        $Input = MakeHtmlInputText("UserName", ($NewUser ? "" : $Row[1]), oswebdb_field_len($Result, 1), oswebdb_field_len($Result, 1), ($NewUser ? !$AllowInsert : !$AllowUpdate), ($NewUser ? !$AllowInsert : !$AllowUpdate), $TabIndex++, "");
        echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Brugernavn :</td><td width=\"99%\">$Input</td></tr>\r\n";
        $Input = MakeHtmlInputText("Name", ($NewUser ? "" : $Row[2]), oswebdb_field_len($Result, 2), oswebdb_field_len($Result, 2), ($NewUser ? !$AllowInsert : !$AllowUpdate), ($NewUser ? !$AllowInsert : !$AllowUpdate), $TabIndex++, "");
        echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Navn :</td><td width=\"99%\">$Input</td></tr>\r\n";
        $Input = MakeHtmlInputText("Initials", ($NewUser ? "" : $Row[3]), oswebdb_field_len($Result, 3), oswebdb_field_len($Result, 3), ($NewUser ? !$AllowInsert : !$AllowUpdate), ($NewUser ? !$AllowInsert : !$AllowUpdate), $TabIndex++, "javascript:this.value = this.value.toUpperCase()");
        echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Initialer :</td><td width=\"99%\">$Input</td></tr>\r\n";
        echo "      </table>\r\n";
        echo "    </form>\r\n";
        MakeHtmlPageBottom();
      }
      else
        MakeHtmlPageReload("javascript:parent.openCalender($Fun); return true;");
      oswebdb_free_result($Result);
    }
    else if ($Result = oswebdb_query("SELECT UserId,UserName,Name,Initials FROM $UserTableName WHERE SystemNo=$SystemNo ORDER BY Name,Initials,UserId"))
    {
      $TabIndex = 1;
      MakeHtmlPageTop("Kalender");
      echo "    <table border=\"0\" cellpadding=\"1\" cellspacing=\"1\" width=\"100%\">\r\n";
      echo "      <tr><td width=\"100%\" colspan=\"5\" valign=\"middle\"><h2><i>Kalender - brugere</i></h2></td></tr>\r\n";
      if ($AllowInsert)
      {
        echo "      <form action=\"calender.php\" method=\"post\">\r\n";
        echo "        <input type=\"hidden\" name=\"Fun\" value=\"$Fun\">\r\n";
        $Input = MakeHtmlInputSubmit("NewUser", "Opret", !$AllowInsert, $TabIndex++, "");
        echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\" colspan=\"4\">$Input</td></tr>\r\n";
        echo "      </form>\r\n";
      }
      echo "      <tr><td width=\"1%\" align=\"right\" nowrap></td><td nowrap><strong>Brugerid:</strong></td><td nowrap><strong>Brugernavn:</strong></td><td nowrap><strong>Navn:</strong></td><td nowrap><strong>Initialer:</strong></td></tr>\r\n";
      while ($Row = oswebdb_fetch_row($Result))
      {
        if ($AllowUpdate)
          $Input = MakeHtmlInputSubmit("", "Redigér", !$AllowUpdate, $TabIndex++, "");
        else
          $Input = MakeHtmlInputSubmit("", "Vis", !$AllowUpdate, $TabIndex++, "");
        echo "      <form action=\"calender.php\" method=\"post\"><input type=\"hidden\" name=\"Fun\" value=\"$Fun\"><input type=\"hidden\" name=\"UserId\" value=\"$Row[0]\"><tr><td width=\"%1\" align=\"right\" nowrap>$Input</td><td nowrap>$Row[0]</td><td nowrap>$Row[1]</td><td nowrap>$Row[2]</td><td nowrap>$Row[3]</td></tr></form>\r\n";
      }
      echo "    </table>\r\n";
      MakeHtmlPageBottom();
      oswebdb_free_result($Result);
    }
  }
?>
