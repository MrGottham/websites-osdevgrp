<?php
  // Include required files.
  require_once("misc.php");
  require_once("oswebdb/oswebdb.php");
  require_once("oswebdb/tables.php");
  require_once("oswebdb/websites.php");
  require_once("oswebdb/zipcodes.php");
  require_once("oswebdb/distance.php");
  require_once("oswebdb/calender.php");

  // Set error reporting.
  error_reporting((int) GetConfigValue("ErrorReporting"));

  // Make the html index page.
  MakeHtmlIndexPage(oswebdb_getusername(), oswebdb_getpassword());

  // Functions.
  function MakeHtmlIndexPage($UserName, $Password)
  {
    if (oswebdb_connect($UserName, $Password))
    {
      if (oswebdb_selectdb())
      {
        $SystemNo = (int) GetConfigValue("SystemNo");
        if ($SystemResult = oswebdb_query("SELECT MenuTitle,Properties,SeasonNo,Title,MetaDescription,MetaKeywords,Email FROM Systems WHERE SystemNo=$SystemNo"))
        {
          if ($SystemRow = oswebdb_fetch_row($SystemResult))
          {
            MakeJavaScripts($UserName, $Password, $SystemNo, $SystemRow[0], $SystemRow[1], $SystemRow[2]);
            echo "<html>\r\n";
            echo "  <head>\r\n";
            echo "    <meta name=\"Title\" content=\"$SystemRow[3]\">\r\n";
            echo "    <meta name=\"Description\" content=\"$SystemRow[4]\">\r\n";
            echo "    <meta name=\"Keywords\" content=\"$SystemRow[5]\">\r\n";
            echo "    <meta name=\"Robots\" content=\"index,follow\">\r\n";
            echo "    <meta name=\"DC.Title\" content=\"$SystemRow[3]\">\r\n";
            echo "    <meta name=\"DC.Description\" content=\"$SystemRow[4]\">\r\n";
            echo "    <meta name=\"DC.Subject\" content=\"$SystemRow[5]\">\r\n";
            echo "    <meta http-equiv=\"Reply-to\" content=\"$SystemRow[6]\">\r\n";
            echo "    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=windows-1252\">\r\n";
            echo "    <title>$SystemRow[3]</title>\r\n";
            echo "  </head>\r\n";
            echo "  <frameset onLoad=\"initializeTree()\" rows=\"99,1,*\" frameborder=\"0\" framespacing=\"0\" border=\"0\">\r\n";
            echo "    <frame name=\"frameTop\" scrolling=\"no\" noresize src=\"top.php\">\r\n";
            echo "    <frame name=\"frameScript\" scrolling=\"no\" noresize src=\"script.php\">\r\n";
            echo "    <frameset cols=\"230,*\" frameborder=\"0\" framespacing=\"0\" border=\"0\">\r\n";
            echo "      <frame name=\"frameMenu\" scrolling=\"auto\" src=\"menu.php\">\r\n";
            echo "      <frame name=\"framePage\" scrolling=\"auto\" src=\"home.php\">\r\n";
            echo "    </frameset>\r\n";
            echo "    <noframes>\r\n";
            echo "      <body>\r\n";
            echo "        <p>Denne Web-side bruger frames, hvilket din browser ikke understøtter.</p>\r\n";
            echo "      </body>\r\n";
            echo "    </noframes>\r\n";
            echo "  </frameset>\r\n";
            echo "</html>\r\n";
          }
          oswebdb_free_result($SystemResult);
        }
      }
      oswebdb_close();
    }
  }

  function MakeJavaScripts($UserName, $Password, $SystemNo, $MenuTitle, $Properties, $CurrentSeasonNo)
  {
    $BgColor = GetConfigValue("BgColor");
    $Text = GetConfigValue("Text");
    echo "<script language=\"JavaScript\">\r\n";
    if (($Properties & 8) && file_exists("calender.php"))
    {
      echo "  var calenderDate = 0\r\n";
      $CalenderUserIds = get_private_user_ids($SystemNo);
      echo "  var calenderUserIds = \"$CalenderUserIds\"\r\n";
    }
    echo "  var ns = 0\r\n";
    echo "  var ie = 0\r\n";
    echo "  var f0 = 0\r\n";
    echo "  var itemID = 0\r\n";
    echo "  var redraw = 1\r\n";
    echo "  var showTopLevel = 1\r\n";
    echo "  var closeMenus = 0\r\n";
    echo "  var timeOutID = 0\r\n";
    echo "  var bgBmp = \"\"\r\n";
    echo "  var bgColor = \"$BgColor\"\r\n";
    echo "  var textColor = \"$Text\"\r\n";
    echo "  var selectColor = \"$Text\"\r\n\r\n";
    echo "  function logon()\r\n";
    echo "  {\r\n";
    echo "    this.framePage.location = \"home.php?Logon=1\"\r\n";
    echo "  }\r\n\r\n";
    echo "  function logoff()\r\n";
    echo "  {\r\n";
    echo "    this.framePage.location = \"home.php?Logoff=1\"\r\n";
    echo "  }\r\n\r\n";
    echo "  function afterLogonAndLogoff()\r\n";
    echo "  {\r\n";
    echo "    reload()\r\n";
    echo "  }\r\n\r\n";
    echo "  function reload()\r\n";
    echo "  {\r\n";
    echo "    this.location = \"./\"\r\n";
    echo "  }\r\n\r\n";
    echo "  function changePassword(message)\r\n";
    echo "  {\r\n";
    echo "    if (message.length > 0)\r\n";
    echo "      this.framePage.location = 'home.php?ChangePassword=1&Message=' + message\r\n";
    echo "    else\r\n";
    echo "      this.framePage.location = 'home.php?ChangePassword=1'\r\n";
    echo "  }\r\n\r\n";
    echo "  function printWebsite(typeno, menuno, description)\r\n";
    echo "  {\r\n";
    echo "    printWindow = window.open('', '', 'width=600,height=470,scrollbars')\r\n";
    echo "    if (printWindow)\r\n";
    echo "      printWindow.location = 'home.php?TypeNo=' + typeno + '&MenuNo=' + menuno + '&Description=' + description + '&Print=1'\r\n";
    echo "  }\r\n\r\n";
    echo "  function printReport(reportNo, landscape, parameters)\r\n";
    echo "  {\r\n";
    echo "    width = 600\r\n";
    echo "    if (landscape)\r\n";
    echo "      width = 800\r\n";
    echo "    printWindow = window.open('', '', 'width=' + width + ',height=470,scrollbars')\r\n";
    echo "    if (printWindow)\r\n";
    echo "      printWindow.location = 'print.php?ReportNo=' + reportNo + '&Print=1' + parameters\r\n";
    echo "  }\r\n\r\n";
    echo "  function getChildFrame(method)\r\n";
    echo "  {\r\n";
    echo "    childFrame = ''\r\n";
    echo "    if (this.framePage.frames.length > 0)\r\n";
    echo "      childFrame = '.' + this.framePage.getChildFrame(method)\r\n";
    echo "    return childFrame\r\n";
    echo "  }\r\n\r\n";
    echo "  function getIntValue(object)\r\n";
    echo "  {\r\n";
    echo "    i = 0\r\n";
    echo "    s = object.value\r\n";
    echo "    if (s.length > 0)\r\n";
    echo "    {\r\n";
    echo "      s = s.replace('.', '')\r\n";
    echo "      s = s.replace(',', '.')\r\n";
    echo "      if (!(i = parseInt(s, 10)))\r\n";
    echo "        i = 0\r\n";
    echo "    }\r\n";
    echo "    return i\r\n";
    echo "  }\r\n\r\n";
    echo "  function getFloatValue(object)\r\n";
    echo "  {\r\n";
    echo "    f = 0\r\n";
    echo "    s = object.value\r\n";
    echo "    if (s.length > 0)\r\n";
    echo "    {\r\n";
    echo "      s = s.replace('.', '')\r\n";
    echo "      s = s.replace(',', '.')\r\n";
    echo "      if (!(f = parseFloat(s, 10)))\r\n";
    echo "        f = 0\r\n";
    echo "    }\r\n";
    echo "    return f\r\n";
    echo "  }\r\n\r\n";
    echo "  function setFloatValue(object, value, decimals)\r\n";
    echo "  {\r\n";
    echo "    object.value = ''\r\n";
    echo "    if (decimals > 0)\r\n";
    echo "      value = Math.round(value * Math.pow(10, decimals)) / Math.pow(10, decimals)\r\n";
    echo "    else\r\n";
    echo "      value = Math.round(value)\r\n";
    echo "    s = value.toString()\r\n";
    echo "    if (s.length > 0)\r\n";
    echo "    {\r\n";
    echo "      p = s.indexOf('.')\r\n";
    echo "      if (p > 0)\r\n";
    echo "      {\r\n";
    echo "        while (s.length - (s.indexOf('.') + 1) < decimals)\r\n";
    echo "          s = s + '0'\r\n";
    echo "      }\r\n";
    echo "      else\r\n";
    echo "      {\r\n";
    echo "        s = s + '.'\r\n";
    echo "        for (i = 0; i < decimals; i++)\r\n";
    echo "          s = s + '0'\r\n";
    echo "      }\r\n";
    echo "      s = s.replace('.', ',')\r\n";
    echo "      toPos = s.length\r\n";
    echo "      if (s.indexOf(',') > 0)\r\n";
    echo "        toPos = s.indexOf(',')\r\n";
    echo "      while ((toPos -= 3) > 0)\r\n";
    echo "      {\r\n";
    echo "        s = s.substr(0, toPos) + '.' + s.substr(toPos, s.length)\r\n";
    echo "        toPos = s.indexOf('.')\r\n";
    echo "      }\r\n";
    echo "      object.value = s\r\n";
    echo "    }\r\n";
    echo "  }\r\n\r\n";
    echo "  function setIntValue(object, value)\r\n";
    echo "  {\r\n";
    echo "    object.value = ''\r\n";
    echo "    s = value.toString()\r\n";
    echo "    if (s.length > 0)\r\n";
    echo "    {\r\n";
    echo "      toPos = s.length\r\n";
    echo "      while ((toPos -= 3) > 0)\r\n";
    echo "      {\r\n";
    echo "        s = s.substr(0, toPos) + '.' + s.substr(toPos, s.length)\r\n";
    echo "        toPos = s.indexOf('.')\r\n";
    echo "      }\r\n";
    echo "      object.value = s\r\n";
    echo "    }\r\n";
    echo "  }\r\n\r\n";
    if ((($Properties & 4) && file_exists("address.php")) || (($Properties & 16) && file_exists("distance.php") && file_exists("allowanc.php")))
    {
      echo "  function getCityName(countrycode, zipcode, ctrl)\r\n";
      echo "  {\r\n";
      echo "    ctrl.value = ''\r\n";
      echo "    this.frameScript.location.href = 'script.php?Method=1&CountryCode=' + countrycode + '&ZipCode=' + zipcode + '&Frame=parent.framePage' + getChildFrame(1) + '&Form=' + ctrl.form.name + '&Ctrl=' + ctrl.name\r\n";
      echo "  }\r\n\r\n";
      echo "  function getZipCodeLength(countrycode, zipcodectrl, citynamectrl)\r\n";
      echo "  {\r\n";
      echo "    zipcodectrl.value = ''\r\n";
      echo "    citynamectrl.value = ''\r\n";
      echo "    this.frameScript.location.href = 'script.php?Method=2&CountryCode=' + countrycode + '&Frame=parent.framePage' + getChildFrame(2) + '&Form=' + zipcodectrl.form.name + '&Ctrl=' + zipcodectrl.name\r\n";
      echo "  }\r\n\r\n";
      echo "  function getZipCodeLengths(fromcountrycode, fromzipcodectrl, fromcitynamectrl, tocountrycode, tozipcodectrl, tocitynamectrl)\r\n";
      echo "  {\r\n";
      echo "    fromzipcodectrl.value = ''\r\n";
      echo "    fromcitynamectrl.value = ''\r\n";
      echo "    tozipcodectrl.value = ''\r\n";
      echo "    tocitynamectrl.value = ''\r\n";
      echo "    this.frameScript.location.href = 'script.php?Method=4&FromCountryCode=' + fromcountrycode + '&ToCountryCode=' + tocountrycode + '&Frame=parent.framePage' + getChildFrame(4) + '&FromForm=' + fromzipcodectrl.form.name + '&FromCtrl=' + fromzipcodectrl.name + '&ToForm=' + tozipcodectrl.form.name + '&ToCtrl=' + tozipcodectrl.name\r\n";
      echo "  }\r\n\r\n";
    }
    if (($Properties & 4) && file_exists("address.php"))
    {
      echo "  function getAddressGroupPublic(groupno, ctrl)\r\n";
      echo "  {\r\n";
      echo "    ctrl.checked = false\r\n";
      echo "    this.frameScript.location.href = 'script.php?Method=8&GroupNo=' + groupno + '&Frame=parent.framePage' + getChildFrame(8) + '&Form=' + ctrl.form.name + '&Ctrl=' + ctrl.name\r\n";
      echo "  }\r\n\r\n";
    }
    echo "  function getEmail(email)\r\n";
    echo "  {\r\n";
    echo "    if (email.length > 7)\r\n";
    echo "    {\r\n";
    echo "      s = email.toLowerCase()\r\n";
    echo "      if (s.substr(0, 7) == 'mailto:')\r\n";
    echo "        email = email.substr(7, email.length - 7)\r\n";
    echo "    }\r\n";
    echo "    return email.toLowerCase()\r\n";
    echo "  }\r\n\r\n";
    echo "  function getWeb(web)\r\n";
    echo "  {\r\n";
    echo "    if (web.length > 8)\r\n";
    echo "    {\r\n";
    echo "      s = web.toLowerCase()\r\n";
    echo "      if (s.substr(0, 8) == 'https://')\r\n";
    echo "        web = web.substr(8, web.length - 8)\r\n";
    echo "    }\r\n";
    echo "    if (web.length > 7)\r\n";
    echo "    {\r\n";
    echo "      s = web.toLowerCase()\r\n";
    echo "      if (s.substr(0, 7) == 'http://')\r\n";
    echo "        web = web.substr(7, web.length - 7)\r\n";
    echo "    }\r\n";
    echo "    return web.toLowerCase()\r\n";
    echo "  }\r\n\r\n";
    if (($Properties & 4) && file_exists("matches.php"))
    {
      $DT = date("Y-m-d", time());
      echo "  function getSeasonStartDate(seasonno, ctrl)\r\n";
      echo "  {\r\n";
      echo "    ctrl.value = '$DT'\r\n";
      echo "    this.frameScript.location.href = 'script.php?Method=256&SeasonNo=' + seasonno   + '&Frame=parent.framePage' + getChildFrame(1) + '&Form=' + ctrl.form.name + '&Ctrl=' + ctrl.name\r\n";
      echo "  }\r\n\r\n";
      echo "  function getInternalMatchType(typeno, ctrl)\r\n";
      echo "  {\r\n";
      echo "    ctrl.checked = false\r\n";
      echo "    this.frameScript.location.href = 'script.php?Method=128&TypeNo=' + typeno  + '&Frame=parent.framePage' + getChildFrame(1) + '&Form=' + ctrl.form.name + '&Ctrl=' + ctrl.name\r\n";
      echo "  }\r\n\r\n";
    }
    echo "  function getDaysInMonth(year, month)\r\n";
    echo "  {\r\n";
    echo "    if (month == 1 || month == 3 || month == 5 || month == 7 || month == 8 || month == 10 || month == 12)\r\n";
    echo "      return 31\r\n";
    echo "    else if (month == 2)\r\n";
    echo "      return 28 + ((year % 4 == 0) && ((year % 100 != 0) || (year % 400 == 0)) && (year % 4000 != 0))\r\n";
    echo "    else\r\n";
    echo "      return 30\r\n";
    echo "  }\r\n\r\n";
    echo "  function validateDate(year, month, date)\r\n";
    echo "  {\r\n";
    echo "    if (!year)\r\n";
    echo "    {\r\n";
    echo "      alert('Årstallet skal indtastes!')\r\n";
    echo "      return false\r\n";
    echo "    }\r\n";
    echo "    else if (!year || parseInt(year) < 1901)\r\n";
    echo "    {\r\n";
    echo "      alert('Årstallet skal være større end eller lig med 1901!')\r\n";
    echo "      return false\r\n";
    echo "    }\r\n";
    echo "    else if (!month)\r\n";
    echo "    {\r\n";
    echo "      alert('Måneden skal indtastes!')\r\n";
    echo "      return false\r\n";
    echo "    }\r\n";
    echo "    else if (month < 1 || month > 12)\r\n";
    echo "    {\r\n";
    echo "      alert('Måneden skal være i intervallet mellem 1 og 12!')\r\n";
    echo "      return false\r\n";
    echo "    }\r\n";
    echo "    else if (!date)\r\n";
    echo "    {\r\n";
    echo "      alert('Datoen skal indtastes!')\r\n";
    echo "      return false\r\n";
    echo "    }\r\n";
    echo "    else if (date < 1 || date > getDaysInMonth(year, month))\r\n";
    echo "    {\r\n";
    echo "      alert('Datoen skal være i intervallet mellem 1 og ' + getDaysInMonth(year, month) + '!')\r\n";
    echo "      return false\r\n";
    echo "    }\r\n";
    echo "    else\r\n";
    echo "      return true\r\n";
    echo "  }\r\n\r\n";
    echo "  function validateTime(hour, minute, information)\r\n";
    echo "  {\r\n";
    echo "    if (!hour && hour != 0)\r\n";
    echo "    {\r\n";
    echo "      alert('Timen for ' + information + ' skal indtastes!')\r\n";
    echo "      return false\r\n";
    echo "    }\r\n";
    echo "    else if (hour < 0 || hour > 23)\r\n";
    echo "    {\r\n";
    echo "      alert('Timen for ' + information + ' skal være mellem 0 og 23!')\r\n";
    echo "      return false\r\n";
    echo "    }\r\n";
    echo "    else if (!minute && minute != 0)\r\n";
    echo "    {\r\n";
    echo "      alert('Minuttet for ' + information + ' skal indtastes!')\r\n";
    echo "      return false\r\n";
    echo "    }\r\n";
    echo "    else if (minute < 0 || minute > 59)\r\n";
    echo "    {\r\n";
    echo "      alert('Minuttet for ' + information + ' skal være mellem 0 og 59!')\r\n";
    echo "      return false\r\n";
    echo "    }\r\n";
    echo "    else\r\n";
    echo "      return true\r\n";
    echo "  }\r\n\r\n";
    if (($Properties & 8) && file_exists("calender.php"))
    {
      echo "  function openCalender(fun)\r\n";
      echo "  {\r\n";
      echo "    if (calenderDate == 0)\r\n";
      echo "      calenderDate = new Date()\r\n";
      echo "    year = calenderDate.getFullYear()\r\n";
      echo "    month = calenderDate.getMonth() + 1\r\n";
      echo "    date = calenderDate.getDate()\r\n";
      echo "    switch (fun)\r\n";
      echo "    {\r\n";
      echo "      case 1:\r\n";
      echo "        this.framePage.location = 'calender.php?Fun=' + fun + '&Year=' + year + '&Month=' + month + '&Date=' + date + '&UserIds=' + calenderUserIds\r\n";
      echo "        break\r\n\r\n";
      echo "      case 2:\r\n";
      echo "        this.framePage.location = 'calender.php?Fun=' + fun + '&Year=' + year + '&Month=' + month + '&Date=' + date + '&UserIds=' + calenderUserIds\r\n";
      echo "        break\r\n\r\n";
      echo "      case 3:\r\n";
      echo "        this.framePage.location = 'calender.php?Fun=' + fun + '&Year=' + year + '&Month=' + month + '&Date=' + date + '&UserIds=' + calenderUserIds\r\n";
      echo "        break\r\n\r\n";
      echo "      case 4:\r\n";
      echo "        this.framePage.location = 'calender.php?Fun=' + fun\r\n";
      echo "        break\r\n\r\n";
      echo "      case 5:\r\n";
      echo "        this.framePage.location = 'calender.php?Fun=' + fun\r\n";
      echo "        break\r\n\r\n";
      echo "      case 6:\r\n";
      echo "        this.framePage.location = 'calender.php?Fun=' + fun + '&Year=' + year + '&Month=' + month + '&Date=' + date + '&UserIds=' + calenderUserIds\r\n";
      echo "        break\r\n\r\n";
      echo "      case 7:\r\n";
      echo "        this.framePage.location = 'calender.php?Fun=' + fun\r\n";
      echo "        break\r\n";
      echo "    }\r\n";
      echo "  }\r\n\r\n";
      echo "  function showCalender(showDate, userIds)\r\n";
      echo "  {\r\n";
      echo "    year = parseInt(showDate.substr(0, 4), 10)\r\n";
      echo "    month = parseInt(showDate.substr(5, 2), 10)\r\n";
      echo "    date = parseInt(showDate.substr(8, 2), 10)\r\n";
      echo "    setDate(year, month - 1, date)\r\n";
      echo "    setUserIds(userIds)\r\n";
      echo "    openCalender(2)\r\n";
      echo "  }\r\n\r\n";
      echo "  function modifyAppointment(appointmentDate, appointmentUserIds, appointmentId)\r\n";
      echo "  {\r\n";
      echo "    year = parseInt(appointmentDate.substr(0, 4), 10)\r\n";
      echo "    month = parseInt(appointmentDate.substr(5, 2), 10)\r\n";
      echo "    date = parseInt(appointmentDate.substr(8, 2), 10)\r\n";
      echo "    setDate(year, month - 1, date)\r\n";
      echo "    setUserIds(appointmentUserIds)\r\n";
      echo "    this.framePage.location = 'calender.php?Fun=3&Year=' + year + '&Month=' + month + '&Date=' + date + '&UserIds=' + appointmentUserIds + '&CalId=' + appointmentId\r\n";
      echo "  }\r\n\r\n";
      echo "  function createAppointment(appointmentDate, fromTime, toTime, properties, subject, note, appointmentUserIds)\r\n";
      echo "  {\r\n";
      echo "    year = parseInt(appointmentDate.substr(0, 4), 10)\r\n";
      echo "    month = parseInt(appointmentDate.substr(5, 2), 10)\r\n";
      echo "    date = parseInt(appointmentDate.substr(8, 2), 10)\r\n";
      echo "    setDate(year, month - 1, date)\r\n";
      echo "    setUserIds(appointmentUserIds)\r\n";
      echo "    this.framePage.location = 'calender.php?Fun=2&Year=' + year + '&Month=' + month + '&Date=' + date + '&UserIds=' + appointmentUserIds + '&Insert=Insert&AppointmentDate=' + appointmentDate + '&FromTime=' + fromTime + '&ToTime=' + toTime + '&Properties=' + properties + '&Subject=' + subject + '&Note=' + note\r\n";
      echo "  }\r\n\r\n";
      echo "  function updateAppointment(appointmentId, appointmentDate, fromTime, toTime, properties, subject, note, appointmentUserIds)\r\n";
      echo "  {\r\n";
      echo "    year = parseInt(appointmentDate.substr(0, 4), 10)\r\n";
      echo "    month = parseInt(appointmentDate.substr(5, 2), 10)\r\n";
      echo "    date = parseInt(appointmentDate.substr(8, 2), 10)\r\n";
      echo "    setDate(year, month - 1, date)\r\n";
      echo "    setUserIds(appointmentUserIds)\r\n";
      echo "    this.framePage.location = 'calender.php?Fun=2&Year=' + year + '&Month=' + month + '&Date=' + date + '&UserIds=' + appointmentUserIds + '&Update=Update&CalId=' + appointmentId + '&AppointmentDate=' + appointmentDate + '&FromTime=' + fromTime + '&ToTime=' + toTime + '&Properties=' + properties + '&Subject=' + subject + '&Note=' + note\r\n";
      echo "  }\r\n\r\n";
      echo "  function deleteAppointment(appointmentId, appointmentDate, appointmentUserIds, method, deleteUserIds)\r\n";
      echo "  {\r\n";
      echo "    year = parseInt(appointmentDate.substr(0, 4), 10)\r\n";
      echo "    month = parseInt(appointmentDate.substr(5, 2), 10)\r\n";
      echo "    date = parseInt(appointmentDate.substr(8, 2), 10)\r\n";
      echo "    setDate(year, month - 1, date)\r\n";
      echo "    setUserIds(appointmentUserIds)\r\n";
      echo "    this.framePage.location = 'calender.php?Fun=2&Year=' + year + '&Month=' + month + '&Date=' + date + '&UserIds=' + appointmentUserIds + '&Delete=' + method + '&CalId=' + appointmentId + '&DeleteUserIds=' + deleteUserIds\r\n";
      echo "  }\r\n\r\n";
      echo "  function getToDay()\r\n";
      echo "  {\r\n";
      echo "    if (calenderDate != 0)\r\n";
      echo "    {\r\n";
      echo "      delete calenderDate\r\n";
      echo "      calenderDate = 0\r\n";
      echo "    }\r\n";
      echo "    calenderDate = new Date()\r\n";
      echo "    return calenderDate\r\n";
      echo "  }\r\n\r\n";
      echo "  function changeMonth(offset)\r\n";
      echo "  {\r\n";
      echo "    if (calenderDate == 0)\r\n";
      echo "      calenderDate = new Date()\r\n";
      echo "    year = calenderDate.getFullYear()\r\n";
      echo "    month = calenderDate.getMonth() + offset\r\n";
      echo "    if (month < 0)\r\n";
      echo "    {\r\n";
      echo "      month += 12\r\n";
      echo "      year--\r\n";
      echo "    }\r\n";
      echo "    else if (month > 11)\r\n";
      echo "    {\r\n";
      echo "      month -= 12\r\n";
      echo "      year++\r\n";
      echo "    }\r\n";
      echo "    return setDate(year, month, 1)\r\n";
      echo "  }\r\n\r\n";
      echo "  function getDate()\r\n";
      echo "  {\r\n";
      echo "    if (calenderDate == 0)\r\n";
      echo "      calenderDate = new Date()\r\n";
      echo "    return calenderDate\r\n";
      echo "  }\r\n\r\n";
      echo "  function setDate(year, month, date)\r\n";
      echo "  {\r\n";
      echo "    if (calenderDate == 0)\r\n";
      echo "      calenderDate = new Date()\r\n";
      echo "    calenderDate.setDate(date)\r\n";
      echo "    calenderDate.setMonth(month)\r\n";
      echo "    calenderDate.setYear(year)\r\n";
      echo "    return calenderDate\r\n";
      echo "  }\r\n\r\n";
      echo "  function getUserIds()\r\n";
      echo "  {\r\n";
      echo "    return calenderUserIds\r\n";
      echo "  }\r\n\r\n";
      echo "  function setUserIds(userIds)\r\n";
      echo "  {\r\n";
      echo "    calenderUserIds = userIds\r\n";
      echo "    return calenderUserIds\r\n";
      echo "  }\r\n\r\n";
    }
    if (($Properties & 16) && file_exists("allowanc.php"))
    {
      echo "  function getCityNameAndDistance(countrycode, zipcode, citynamectrl, othercountrycode, otherzipcode, distancectrl, property1ctrl, property2ctrl)\r\n";
      echo "  {\r\n";
      echo "    citynamectrl.value = ''\r\n";
      echo "    distancectrl.value = ''\r\n";
      echo "    property1ctrl.checked = false\r\n";
      echo "    property2ctrl.checked = false\r\n";
      echo "    this.frameScript.location.href = 'script.php?Method=17&CountryCode=' + countrycode + '&ZipCode=' + zipcode + '&Frame=parent.framePage' + getChildFrame(1) + '&Form=' + citynamectrl.form.name + '&Ctrl=' + citynamectrl.name + '&OtherCountryCode=' + othercountrycode + '&OtherZipCode=' + otherzipcode + '&DistanceCtrl=' + distancectrl.name + '&Property1Ctrl=' + property1ctrl.name + '&Property2Ctrl=' + property2ctrl.name\r\n";
      echo "  }\r\n\r\n";
      echo "  function getDistanceAndAllowanceForDate(year, no, date, distancectrl, allowancectrl)\r\n";
      echo "  {\r\n";
      echo "    distancectrl.value = ''\r\n";
      echo "    allowancectrl.value = ''\r\n";
      echo "    this.frameScript.location.href = 'script.php?Method=96&Year=' + year + '&No=' + no + '&Date=' + date + '&Frame=parent.framePage' + getChildFrame(1) + '&Form=' + distancectrl.form.name + '&DistanceCtrl=' + distancectrl.name + '&AllowanceCtrl=' + allowancectrl.name\r\n";
      echo "  }\r\n\r\n";
    }
    echo "  function generateTree()\r\n";
    echo "  {\r\n";
    echo "    if (navigator.appName == \"Netscape\")\r\n";
    echo "      ns = navigator.appVersion.substring(0, 1)\r\n";
    echo "    if (navigator.appName == \"Microsoft Internet Explorer\")\r\n";
    echo "      ie = navigator.appVersion.substring(0, 1)\r\n";
    echo "    f0 = f(0, 0, \"$MenuTitle\", \"home.php?Home=1\")\r\n";
    $AddressSubMenu = 0;
    $DebateSubMenu = 0;
    $CalenderSubMenu = 0;
    $MatchResultsSubMenu = 0;
    $AllowanceSubMenu = 0;
    $ConfigurationSubMenu = 0;
    $PrintSubMenu = 0;
    $ToolsSubMenu = 0;
    $TableSystemNo = get_system_for_table($SystemNo, 1);
    $MenuTypes = get_menu_types($SystemNo);
    $WebsiteFields = get_website_fields();
    $LastTypeNo = 0;
    $Statement = "SELECT No,Description FROM Systemtables WHERE SystemNo=$TableSystemNo AND TableNo=1 AND Public IN (1) ORDER BY Description,No";
    if (isset($UserName) && isset($Password))
      $Statement = "SELECT No,Description FROM Systemtables WHERE SystemNo=$TableSystemNo AND TableNo=1 AND Public IN (0,1) ORDER BY Description,No";
    if ($TableResult = oswebdb_query($Statement))
    {
      while ($TableRow = oswebdb_fetch_row($TableResult))
      {
        if (!$AddressSubMenu && strcmp("Adresser", $TableRow[1]) <= 0)
          $AddressSubMenu = MakeAddressSubMenu($UserName, $Password, $SystemNo, $Properties);
        if (!$DebateSubMenu && strcmp("Debatter", $TableRow[1]) <= 0)
          $DebateSubMenu = MakeDebateSubmenu($SystemNo, $Properties);
        if (!$CalenderSubMenu && strcmp("Kalender", $TableRow[1]) <= 0)
          $CalenderSubMenu = MakeCalenderSubMenu($UserName, $Password, $SystemNo, $Properties);
        if (!$MatchResultsSubMenu && strcmp("Kampresultater", $TableRow[1]) <= 0)
          $MatchResultsSubMenu = MakeMatchResultsSubMenu($UserName, $Password, $SystemNo, $Properties, $CurrentSeasonNo);
        if (!$AllowanceSubMenu && strcmp("Kørselsfradrag", $TableRow[1]) <= 0)
           $AllowanceSubMenu = MakeAllowanceSubMenu($UserName, $Password, $SystemNo, $Properties);
        if (!$ConfigurationSubMenu && strcmp("Opsætning", $TableRow[1]) <= 0)
          $ConfigurationSubMenu = MakeConfigurationSubMenu($UserName, $Password, $SystemNo, $Properties);
        if (!$PrintSubMenu && strcmp("Udskrifter", $TableRow[1]) <= 0)
          $PrintSubMenu = MakePrintSubMenu($UserName, $Password, $SystemNo, $Properties, $CurrentSeasonNo);
        if (!$ToolsSubMenu && strcmp("Værktøjer", $TableRow[1]) <= 0)
          $ToolsSubMenu = MakeToolsSubMenu($UserName, $Password, $Properties);
        if ($WebsiteResult = oswebdb_query("SELECT $WebsiteFields FROM Websites WHERE SystemNo=$SystemNo AND TypeNo IN ($MenuTypes) AND MenuNo=$TableRow[0] ORDER BY Description"))
        {
          $Header = 0;
          while ($WebsiteRow = oswebdb_fetch_row($WebsiteResult))
          {
            if ($WebsiteRow[0] != $LastTypeNo)
            {
              $TypeFields = get_table_field_value(get_system_for_table($SystemNo, 4), 4, $WebsiteRow[0], "ShowFields");
              $TypeProperties = get_table_field_value(get_system_for_table($SystemNo, 4), 4, $WebsiteRow[0], "Properties");
              $TypeText = get_table_field_value(get_system_for_table($SystemNo, 4), 4, $WebsiteRow[0], "Text3");
              $LastTypeNo = $WebsiteRow[0];
            }
            if (use_website($TypeFields, $WebsiteRow[4], $UserName, $Password, $WebsiteRow[5], $WebsiteRow[6], $WebsiteRow[7], $WebsiteRow[8], 1))
            {
              if (!$Header)
              {
                echo "    fx = a(f0, 1, f(0, 0, \"$TableRow[1]\", \"\"))\r\n";
                $Header = 1;
              }
              $ExternalWebsite = external_website($TypeFields, $TypeProperties, $WebsiteRow[13], $WebsiteRow[16]);
              $WebsiteDescription = get_website_description($TypeFields, $TypeProperties, $WebsiteRow[2], $WebsiteRow[3], $TypeText);
              $WebsiteLink = get_website_link($TypeFields, $TypeProperties, $WebsiteRow[0], $WebsiteRow[1], $WebsiteRow[2], $WebsiteRow[13], $WebsiteRow[16], $WebsiteRow[17]);
              $NewWebsite = new_website($WebsiteRow[3]);
              echo "    a(fx, 0, d($ExternalWebsite, \"$WebsiteDescription\", \"$WebsiteLink\", $NewWebsite))\r\n";
            }
          }
          oswebdb_free_result($WebsiteResult);
        }
      }
      oswebdb_free_result($TableResult);
    }
    if (!$AddressSubMenu)
      $AddressSubMenu = MakeAddressSubMenu($UserName, $Password, $SystemNo, $Properties);
    if (!$DebateSubMenu)
      $DebateSubMenu = MakeDebateSubmenu($SystemNo, $Properties);
    if (!$CalenderSubMenu)
      $CalenderSubMenu = MakeCalenderSubMenu($UserName, $Password, $SystemNo, $Properties);
    if (!$MatchResultsSubMenu)
      $MatchResultsSubMenu = MakeMatchResultsSubMenu($UserName, $Password, $SystemNo, $Properties, $CurrentSeasonNo);
    if (!$AllowanceSubMenu)
      $AllowanceSubMenu = MakeAllowanceSubMenu($UserName, $Password, $SystemNo, $Properties);
    if (!$ConfigurationSubMenu)
      $ConfigurationSubMenu = MakeConfigurationSubMenu($UserName, $Password, $SystemNo, $Properties);
    if (!$PrintSubMenu)
      $PrintSubMenu = MakePrintSubMenu($UserName, $Password, $SystemNo, $Properties, $CurrentSeasonNo);
    if (!$ToolsSubMenu)
      $ToolsSubMenu = MakeToolsSubMenu($UserName, $Password, $Properties);
    if ($WebsiteResult = oswebdb_query("SELECT $WebsiteFields FROM Websites WHERE SystemNo=$SystemNo AND TypeNo IN ($MenuTypes) AND MenuNo=0 ORDER BY Description"))
    {
      while ($WebsiteRow = oswebdb_fetch_row($WebsiteResult))
      {
        if ($WebsiteRow[0] != $LastTypeNo)
        {
          $TypeFields = get_table_field_value(get_system_for_table($SystemNo, 4), 4, $WebsiteRow[0], "ShowFields");
          $TypeProperties = get_table_field_value(get_system_for_table($SystemNo, 4), 4, $WebsiteRow[0], "Properties");
          $TypeText = get_table_field_value(get_system_for_table($SystemNo, 4), 4, $WebsiteRow[0], "Text3");
          $LastTypeNo = $WebsiteRow[0];
        }
        if (use_website($TypeFields, $WebsiteRow[4], $UserName, $Password, $WebsiteRow[5], $WebsiteRow[6], $WebsiteRow[7], $WebsiteRow[8], 1))
        {
          $ExternalWebsite = external_website($TypeFields, $TypeProperties, $WebsiteRow[13], $WebsiteRow[16]);
          $WebsiteDescription = get_website_description($TypeFields, $TypeProperties, $WebsiteRow[2], $WebsiteRow[3], $TypeText);
          $WebsiteLink = get_website_link($TypeFields, $TypeProperties, $WebsiteRow[0], $WebsiteRow[1], $WebsiteRow[2], $WebsiteRow[13], $WebsiteRow[16], $WebsiteRow[17]);
          $NewWebsite = new_website($WebsiteRow[3]);
          echo "    a(f0, 0, d($ExternalWebsite, \"$WebsiteDescription\", \"$WebsiteLink\", $NewWebsite))\r\n";
        }
      }
      oswebdb_free_result($WebsiteResult);
    }
    echo "  }\r\n\r\n";
    echo "  function f(opened, ext, name, site)\r\n";
    echo "  {\r\n";
    echo "    var Aux = new Array\r\n";
    echo "    Aux[0] = opened\r\n";
    echo "    Aux[1] = ext\r\n";
    echo "    Aux[2] = name\r\n";
    echo "    Aux[3] = site\r\n";
    echo "    Aux[4] = ++itemID\r\n";
    echo "    return Aux\r\n";
    echo "  }\r\n\r\n";
    echo "  function a(parent, type, child)\r\n";
    echo "  {\r\n";
    echo "    parent[parent.length] = type\r\n";
    echo "    parent[parent.length] = child\r\n";
    echo "    return child\r\n";
    echo "  }\r\n\r\n";
    echo "  function d(icon, docDescription, link, newLink)\r\n";
    echo "  {\r\n";
    echo "    var sA = \"\"\r\n";
    echo "    var sB = \"\"\r\n";
    echo "    var sz = 2\r\n";
    echo "    var returnString = \"\"\r\n";
    echo "    script = 0\r\n";
    echo "    if (link.length >= 11)\r\n";
    echo "      script = (link.substr(0, 11)).toLowerCase() == 'javascript:'\r\n";
    echo "    if (newLink)\r\n";
    echo "    {\r\n";
    echo "      if (script)\r\n";
    echo "        sA = \"<strong><a href=\\\"\" + link + \"\\\" onMouseOver=\\\"window.status='\" + docDescription.replace(/'/g, '\\\\\'') + \"'; return true;\\\" onMouseOut=\\\"window.status=''; return true;\\\"\"\r\n";
    echo "      else\r\n";
    echo "        sA = \"<strong><a href=\\\"javascript:parent.openDoc(\" + icon + \", '\" + link + \"')\\\" onMouseOver=\\\"window.status='\" + docDescription.replace(/'/g, '\\\\\'') + \"'; return true;\\\" onMouseOut=\\\"window.status=''; return true;\\\"\"\r\n";
    echo "    }\r\n";
    echo "    else if (script)\r\n";
    echo "      sA = \"<a href=\\\"\" + link + \"\\\" onMouseOver=\\\"window.status='\" + docDescription.replace(/'/g, '\\\\\'') + \"'; return true;\\\" onMouseOut=\\\"window.status=''; return true;\\\"\"\r\n";
    echo "    else\r\n";
    echo "      sA = \"<a href=\\\"javascript:parent.openDoc(\" + icon + \", '\" + link + \"')\\\" onMouseOver=\\\"window.status='\" + docDescription.replace(/'/g, '\\\\\'') + \"'; return true;\\\" onMouseOut=\\\"window.status=''; return true;\\\"\"\r\n";
    echo "    if (icon == 0)\r\n";
    echo "    {\r\n";
    echo "      if (newLink)\r\n";
    echo "        returnString = sA + \"><img src=\\\"doc.gif\\\" border=\\\"0\\\"></a></strong></td>\"\r\n";
    echo "      else\r\n";
    echo "        returnString = sA + \"><img src=\\\"doc.gif\\\" border=\\\"0\\\"></a></td>\"\r\n";
    echo "    }\r\n";
    echo "    else if (newLink)\r\n";
    echo "      returnString = sA + \"><img src=\\\"link.gif\\\" border=\\\"0\\\"></a></strong></td>\"\r\n";
    echo "    else\r\n";
    echo "      returnString = sA + \"><img src=\\\"link.gif\\\" border=\\\"0\\\"></a></td>\"\r\n";
    echo "    if ((ns >= 4) || (ie >= 4))\r\n";
    echo "      sA = sA + \" STYLE=\\\"text-decoration:none\\\">\"\r\n";
    echo "    else\r\n";
    echo "      sA = sA + \">\"\r\n";
    echo "    sA = \"<font size=\\\"\" + sz + \"\\\">\" + sA\r\n";
    echo "    sB = \"</font>\"\r\n";
    echo "    if (ie >= 4)\r\n";
    echo "    {\r\n";
    echo "      sA = sA + \"<div onMouseOver=\\\"this.style.color='\" + selectColor + \"'\\\" onMouseOut=\\\"this.style.color='\" + textColor + \"'\\\">\"\r\n";
    echo "      sB = \"</div>\" + sB\r\n";
    echo "    }\r\n";
    echo "    returnString = returnString + \"<td nowrap>\" + sA + docDescription + sB + \"</a></td>\"\r\n";
    echo "    return returnString\r\n";
    echo "  }\r\n\r\n";
    echo "  function clickOnFolderRec(folderNode, folderID)\r\n";
    echo "  {\r\n";
    echo "    var i = 0\r\n";
    echo "    var j = 0\r\n";
    echo "    var o = 0\r\n";
    echo "    if (folderNode[4] == folderID)\r\n";
    echo "    {\r\n";
    echo "      if (folderNode[0])\r\n";
    echo "      {\r\n";
    echo "        closeFolders(folderNode)\r\n";
    echo "        o = 1\r\n";
    echo "        redraw = 1\r\n";
    echo "      }\r\n";
    echo "      else if (folderNode.length > 5)\r\n";
    echo "      {\r\n";
    echo "        folderNode[0] = 1\r\n";
    echo "        o = 1\r\n";
    echo "        redraw = 1\r\n";
    echo "        openDoc(folderNode[1], folderNode[3])\r\n";
    echo "      }\r\n";
    echo "      else\r\n";
    echo "      {\r\n";
    echo "        o = 1\r\n";
    echo "        openDoc(folderNode[1], folderNode[3])\r\n";
    echo "      }\r\n";
    echo "    }\r\n";
    echo "    else\r\n";
    echo "    {\r\n";
    echo "      for (i = 5; (i < folderNode.length) && ((closeMenus) || (o == 0)); i++)\r\n";
    echo "      {\r\n";
    echo "        if (folderNode[i++])\r\n";
    echo "        {\r\n";
    echo "          j = clickOnFolderRec(folderNode[i], folderID)\r\n";
    echo "          if ((j == 0) && (closeMenus))\r\n";
    echo "            folderNode[i][0] = 0\r\n";
    echo "          o = o || j\r\n";
    echo "        }\r\n";
    echo "      }\r\n";
    echo "    }\r\n";
    echo "    return o\r\n";
    echo "  }\r\n\r\n";
    echo "  function closeFolders(folderNode)\r\n";
    echo "  {\r\n";
    echo "    var i = 0\r\n";
    echo "    for (i = 5; i < folderNode.length; i++)\r\n";
    echo "    {\r\n";
    echo "      if (folderNode[i++])\r\n";
    echo "        closeFolders(folderNode[i])\r\n";
    echo "    }\r\n";
    echo "    folderNode[0] = 0\r\n";
    echo "  }\r\n\r\n";
    echo "  function openDoc(blank, link)\r\n";
    echo "  {\r\n";
    echo "    if (blank == 0)\r\n";
    echo "    {\r\n";
    echo "      if (link != \"\")\r\n";
    echo "        this.framePage.location = link\r\n";
    echo "    }\r\n";
    echo "    else if (blank == 1)\r\n";
    echo "    {\r\n";
    echo "      if (link != \"\")\r\n";
    echo "        window.open(link)\r\n";
    echo "    }\r\n";
    echo "    else if (blank == 2)\r\n";
    echo "    {\r\n";
    echo "      if (link != \"\")\r\n";
    echo "        this.location = link\r\n";
    echo "    }\r\n";
    echo "  }\r\n\r\n";
    echo "  function openBranch(folderID)\r\n";
    echo "  {\r\n";
    echo "    clickOnFolderRec(f0, folderID)\r\n";
    echo "    if (redraw == 1)\r\n";
    echo "    {\r\n";
    echo "      timeOutID = setTimeout(\"redrawTree()\", 100)\r\n";
    echo "      redraw = 0\r\n";
    echo "    }\r\n";
    echo "  }\r\n\r\n";
    echo "  function redrawTree()\r\n";
    echo "  {\r\n";
    echo "    var doc = this.frameMenu.window.document\r\n";
    echo "    var bgGround = \"\"\r\n";
    echo "    doc.clear()\r\n";
    echo "    doc.writeln(\"<html>\")\r\n";
    echo "    if (bgBmp != \"\")\r\n";
    echo "      bgGround = \" background='\" + bgBmp + \"'\"\r\n";
    echo "    doc.writeln(\"  <body bgcolor='\" + bgColor + \"' text='\" + textColor + \"' link='\" + textColor + \"' vlink='\" + textColor + \"'\" + bgGround + \">\")\r\n";
    echo "    redrawNode(f0, doc, 0, 1, \"\")\r\n";
    echo "    doc.writeln(\"  </body>\")\r\n";
    echo "    doc.writeln(\"</html>\")\r\n";
    echo "    doc.close()\r\n";
    echo "  }\r\n\r\n";
    echo "  function redrawNode(folderNode, doc, level, lastNode, leftSide)\r\n";
    echo "  {\r\n";
    echo "    var i = 0\r\n";
    echo "    if ((level != 0) || (showTopLevel == 1))\r\n";
    echo "    {\r\n";
    echo "      doc.writeln(\"    <table border=\\\"0\\\" cellspacing=\\\"0\\\" cellpadding=\\\"0\\\">\")\r\n";
    echo "      doc.write(\"      <tr><td valign=\\\"middle\\\" nowrap>\")\r\n";
    echo "      doc.write(leftSide)\r\n";
    echo "      if (level > 0)\r\n";
    echo "      {\r\n";
    echo "        if (lastNode)\r\n";
    echo "        {\r\n";
    echo "          doc.write(\"<img src=\\\"lastnode.gif\\\">\")\r\n";
    echo "          leftSide = leftSide + \"<img src=\\\"blank.gif\\\">\"\r\n";
    echo "        }\r\n";
    echo "        else\r\n";
    echo "        {\r\n";
    echo "          doc.write(\"<img src=\\\"node.gif\\\">\")\r\n";
    echo "          leftSide = leftSide + \"<img src=\\\"vertline.gif\\\">\"\r\n";
    echo "        }\r\n";
    echo "      }\r\n";
    echo "      displayIconAndLabel(level, folderNode, doc)\r\n";
    echo "      doc.writeln(\"</tr>\")\r\n";
    echo "      doc.writeln(\"    </table>\")\r\n";
    echo "    }\r\n";
    echo "    if ((folderNode.length > 5) && folderNode[0])\r\n";
    echo "    {\r\n";
    echo "      for (i = 5; i < folderNode.length - 1; i++)\r\n";
    echo "      {\r\n";
    echo "        if (folderNode[i++])\r\n";
    echo "        {\r\n";
    echo "          level = level + 1\r\n";
    echo "          if (i == folderNode.length - 1)\r\n";
    echo "            redrawNode(folderNode[i], doc, level, 1, leftSide)\r\n";
    echo "          else\r\n";
    echo "            redrawNode(folderNode[i], doc, level, 0, leftSide)\r\n";
    echo "          level = level - 1\r\n";
    echo "        }\r\n";
    echo "        else\r\n";
    echo "        {\r\n";
    echo "          doc.writeln(\"    <table border=\\\"0\\\" cellspacing=\\\"0\\\" cellpadding=\\\"0\\\" valign=\\\"middle\\\">\")\r\n";
    echo "          doc.write(\"      <tr><td nowrap>\")\r\n";
    echo "          doc.write(leftSide)\r\n";
    echo "          if (i == folderNode.length - 1)\r\n";
    echo "            doc.write(\"<img src=\\\"lastnode.gif\\\">\")\r\n";
    echo "          else\r\n";
    echo "            doc.write(\"<img src=\\\"node.gif\\\">\")\r\n";
    echo "          doc.write(folderNode[i])\r\n";
    echo "          doc.writeln(\"</tr>\")\r\n";
    echo "          doc.writeln(\"    </table>\")\r\n";
    echo "        }\r\n";
    echo "      }\r\n";
    echo "    }\r\n";
    echo "  }\r\n\r\n";
    echo "  function displayIconAndLabel(level, folderNode, doc)\r\n";
    echo "  {\r\n";
    echo "    var sA = \"\"\r\n";
    echo "    var sB = \"\"\r\n";
    echo "    var sz = 2\r\n";
    echo "    var bd = 1\r\n";
    echo "    sA = \"<a href=\\\"javascript:parent.openBranch(\" + folderNode[4] + \")\\\" onMouseOver=\\\"window.status='\" + folderNode[2] + \"'; return true;\\\" onMouseOut=\\\"window.status=''; return true;\\\"\"\r\n";
    echo "    if (folderNode.length > 5)\r\n";
    echo "    {\r\n";
    echo "      if (folderNode[0])\r\n";
    echo "        doc.write(sA + \"><img src=\\\"open.gif\\\" border=\\\"noborder\\\"></a>\")\r\n";
    echo "      else\r\n";
    echo "        doc.write(sA + \"><img src=\\\"closed.gif\\\" border=\\\"noborder\\\"></a>\")\r\n";
    echo "    }\r\n";
    echo "    else\r\n";
    echo "    {\r\n";
    echo "      doc.write(sA + \"><img src=\\\"doc.gif\\\" border=\\\"noborder\\\"></a>\")\r\n";
    echo "      bd = 0\r\n";
    echo "    }\r\n";
    echo "    doc.write(\"</td><td valign=\\\"middle\\\" align=\\\"left\\\" nowrap>\")\r\n";
    echo "    if ((ie >= 4) || (ns >= 4))\r\n";
    echo "      sA = sA + \" STYLE=\\\"text-decoration:none\\\">\"\r\n";
    echo "    else\r\n";
    echo "      sA = sA + \">\"\r\n";
    echo "    sB = \"\"\r\n";
    echo "    if (level < 3)\r\n";
    echo "      sz = 4 - level\r\n";
    echo "    if (bd == 1)\r\n";
    echo "    {\r\n";
    echo "      sA = \"<font size=\\\"\" + sz + \"\\\"><strong>\" + sA\r\n";
    echo "      sB = \"</strong></font>\"\r\n";
    echo "    }\r\n";
    echo "    else\r\n";
    echo "    {\r\n";
    echo "      sA = \"<font size=\\\"\" + sz + \"\\\">\" + sA\r\n";
    echo "      sB = \"</font>\"\r\n";
    echo "    }\r\n";
    echo "    if (ie >= 4)\r\n";
    echo "    {\r\n";
    echo "      sA = sA + \"<div onMouseOver=\\\"this.style.color='\" + selectColor + \"'\\\" onMouseOut=\\\"this.style.color='\" + textColor + \"'\\\">\"\r\n";
    echo "      sB = \"</div></a>\" + sB\r\n";
    echo "    }\r\n";
    echo "    else\r\n";
    echo "      sB = \"</a>\" + sB\r\n";
    echo "    doc.write(sA + folderNode[2] + sB + \"</td>\")\r\n";
    echo "  }\r\n\r\n";
    echo "  function initializeTree()\r\n";
    echo "  {\r\n";
    echo "    if (itemID == 0)\r\n";
    echo "      generateTree()\r\n";
    echo "    openBranch(2 - showTopLevel)\r\n";
    echo "  }\r\n\r\n";
    echo "  function getCookie(name)\r\n";
    echo "  {\r\n";
    echo "    var search = name + \"=\"\r\n";
    echo "    if (document.cookie.length > 0)\r\n";
    echo "    {\r\n";
    echo "      offset = document.cookie.indexOf(search)\r\n";
    echo "      if (offset != -1)\r\n";
    echo "      {\r\n";
    echo "        offset += search.length\r\n";
    echo "        end = document.cookie.indexOf(\";\", offset)\r\n";
    echo "        if (end == -1)\r\n";
    echo "          end = document.cookie.length\r\n";
    echo "        return unescape(document.cookie.substring(offset, end))\r\n";
    echo "      }\r\n";
    echo "    }\r\n";
    echo "    return \"\"\r\n";
    echo "  }\r\n\r\n";
    echo "  function setCookie(name, value, expire)\r\n";
    echo "  {\r\n";
    echo "    document.cookie = name + \"=\" + escape(value) + ((expire == null) ? \"\" : (\"; expires=\" + expire.toGMTString()))\r\n";
    echo "  }\r\n";
    echo "</script>\r\n";
  }

  function MakeAddressSubMenu($UserName, $Password, $SystemNo, $Properties)
  {
    $Result = 0;
    if (($Properties & 4) && file_exists("address.php"))
    {
      $AddressMenu = 0;
      $GroupSystemNo = get_system_for_table($SystemNo, 3);
      $GroupStatement = "SELECT No,Description FROM Systemtables WHERE SystemNo=$GroupSystemNo AND TableNo=3 AND Public IN (1) ORDER BY Description,No";
      if (isset($UserName) && isset($Password))
        $GroupStatement = "SELECT No,Description FROM Systemtables WHERE SystemNo=$GroupSystemNo AND TableNo=3 AND Public IN (0,1) ORDER BY Description,No";
      if ($GroupResult = oswebdb_query($GroupStatement))
      {
        while ($GroupRow = oswebdb_fetch_row($GroupResult))
        {
          $Header = 0;
          $AddressStatement = "SELECT No,Name FROM Addresses WHERE SystemNo=$SystemNo AND GroupNo=$GroupRow[0] AND Public IN (1) ORDER BY Name,Phone1";
          if (isset($UserName) && isset($Password))
            $AddressStatement = "SELECT No,Name FROM Addresses WHERE SystemNo=$SystemNo AND GroupNo=$GroupRow[0] AND Public IN (0,1) ORDER BY Name,Phone1";
          if ($AddressResult = oswebdb_query($AddressStatement))
          {
            while ($AddressRow = oswebdb_fetch_row($AddressResult))
            {
              if (!$AddressMenu)
              {
                echo "    fx = a(f0, 1, f(0, 0, \"Adresser\", \"address.php\"))\r\n";
                $AddressMenu = 1;
              }
              if (!$Header)
              {
                echo "    fy = a(fx, 1, f(0, 0, \"$GroupRow[1]\", \"address.php?GroupNo=$GroupRow[0]\"))\r\n";
                $Header = 1;
              }
              echo "    a(fy, 0, d(0, \"$AddressRow[1]\", \"address.php?No=$AddressRow[0]\", 0))\r\n";
            }
            oswebdb_free_result($AddressResult);
          }
        }
        oswebdb_free_result($GroupResult);
      }
      $AddressStatement = "SELECT No,Name FROM Addresses WHERE SystemNo=$SystemNo AND GroupNo=0 AND Public IN (1) ORDER BY Name,Phone1";
      if (isset($UserName) && isset($Password))
        $AddressStatement = "SELECT No,Name FROM Addresses WHERE SystemNo=$SystemNo AND GroupNo=0 AND Public IN (0,1) ORDER BY Name,Phone1";
      if ($AddressResult = oswebdb_query($AddressStatement))
      {
        while ($AddressRow = oswebdb_fetch_row($AddressResult))
        {
          if (!$AddressMenu)
          {
            echo "    fx = a(f0, 1, f(0, 0, \"Adresser\", \"address.php\"))\r\n";
            $AddressMenu = 1;
          }
          echo "    a(fx, 0, d(0, \"$AddressRow[1]\", \"address.php?No=$AddressRow[0]\", 0))\r\n";
        }
        oswebdb_free_result($AddressResult);
      }
      $Result = 1;
    }
    return $Result;
  }

  function MakeDebateSubMenu($SystemNo, $Properties)
  {
    $Result = 0;
    if (($Properties & 2) && file_exists("debate.php"))
    {
      if ($DebateResult = oswebdb_query("SELECT Username,Date,Time,Subject FROM Debate WHERE SystemNo=$SystemNo AND ParentUsername IS NULL AND ParentDate IS NULL AND ParentTime IS NULL ORDER BY Date DESC,Time DESC,Username"))
      {
        $DebateMenu = 0; $ToDay = getdate();
        while ($DebateRow = oswebdb_fetch_row($DebateResult))
        {
          if (!$DebateMenu)
          {
            echo "    fx = a(f0, 1, f(0, 0, \"Debatter\", \"debate.php\"))\r\n";
            $DebateMenu = 1;
          }
          $Created = strtolower(date("j. F Y H:i", mktime(substr($DebateRow[2], 0, 2), substr($DebateRow[2], 3, 2), substr($DebateRow[2], 6, 2), substr($DebateRow[1], 5, 2), substr($DebateRow[1], 8, 2), substr($DebateRow[1], 0, 4))));
          $Text = "$DebateRow[3]&nbsp;($DebateRow[0]&nbsp;$Created)";
          $NewDebate = (integer) (mktime(0, 0, 0, substr($DebateRow[1], 5, 2), substr($DebateRow[1], 8, 2) + 7, substr($DebateRow[1], 0, 4)) > mktime(0, 0, 0, $ToDay['mon'], $ToDay['mday'], $ToDay['year']));
          echo "    a(fx, 0, d(0, \"$Text\", \"debate.php?DebateUsername=$DebateRow[0]&DebateDate=$DebateRow[1]&DebateTime=$DebateRow[2]\", $NewDebate))\r\n";
        }
        oswebdb_free_result($DebateResult);
      }
      $Result = 1;
    }
    return $Result;
  }

  function MakeCalenderSubMenu($UserName, $Password, $SystemNo, $Properties)
  {
    $Result = 0;
    if (($Properties & 8) && file_exists("calender.php"))
    {
      $AppointmentPrivilege = oswebdb_allow_insert("Calapps") && oswebdb_allow_insert("Calmerge") && oswebdb_allow_update("Calapps") && oswebdb_allow_update("Calmerge");
      $RebuildPrivilege = $AppointmentPrivilege && oswebdb_allow_delete("Calapps") && oswebdb_allow_delete("Calmerge") && isset($UserName) && isset($Password);
      $UserPrivilege = oswebdb_privileges("Calusers") > 1;
      echo "    fx = a(f0, 1, f(0, 0, \"Kalender\", \"javascript:parent.openCalender(1)\"))\r\n";
      if ($AppointmentPrivilege && strlen(get_private_user_ids($SystemNo)) > 0)
        echo "    a(fx, 0, d(0, \"Alarmer\", \"javascript:parent.openCalender(4)\", 0))\r\n";
      if ($AppointmentPrivilege)
        echo "    a(fx, 0, d(0, \"Book aftale\", \"javascript:parent.openCalender(3)\", 0))\r\n";
      if ($UserPrivilege)
        echo "    a(fx, 0, d(0, \"Brugere\", \"javascript:parent.openCalender(5)\", 0))\r\n";
      if ($AppointmentPrivilege && strlen(get_private_user_ids($SystemNo)) > 0)
        echo "    a(fx, 0, d(0, \"Eksportér\", \"javascript:parent.openCalender(7)\", 0))\r\n";
      if ($RebuildPrivilege)
        echo "    a(fx, 0, d(0, \"Genopbyg\", \"javascript:parent.openCalender(6)\", 0))\r\n";
      echo "    a(fx, 0, d(0, \"Kalender\", \"javascript:parent.openCalender(2)\", 0))\r\n";
      $Result = 1;
    }
    return $Result;
  }

  function MakeMatchResultsSubMenu($UserName, $Password, $SystemNo, $Properties, $CurrentSeasonNo)
  {
    $Result = 0;
    if (isset($UserName) && isset($Password) && ($Properties & 4) && file_exists("matches.php"))
    {
      $SeasonSystemNo = get_system_for_table($SystemNo, 7);
      $MatchSystemNo = get_system_for_table($SystemNo, 9);
      $PrevSeasonNo = (integer) get_table_field_value($SeasonSystemNo, 7, $CurrentSeasonNo, "GroupNo");
      $SubMenu = 0;
      if (oswebdb_privileges("Addressmatches") > 1 && oswebdb_get_rows("SELECT No,Description FROM Systemtables WHERE SystemNo=$MatchSystemNo AND TableNo=9") > 0 && oswebdb_get_rows("SELECT a.No,a.Name,al.TypeNo,al.Average FROM Addresses AS a, Addresslinks AS al WHERE a.SystemNo=$SystemNo AND al.SystemNo=a.SystemNo AND al.AddressNo=a.No AND al.TableNo=8 AND al.SeasonNo IN ($PrevSeasonNo,$CurrentSeasonNo)") > 0)
        $SubMenu += 1;
      if (oswebdb_privileges("Addressmatches") > 1 && oswebdb_get_rows("SELECT s.No,s.Description,m.No,m.Description,a.No,a.Name,am.MatchId FROM Systemtables AS s, Systemtables AS m, Addresses AS a, Addressmatches AS am, Addresslinks AS al WHERE s.SystemNo=$SeasonSystemNo AND s.TableNo=7 AND m.SystemNo=$MatchSystemNo AND m.TableNo=9 AND a.SystemNo=$SystemNo AND am.SystemNo=a.SystemNo AND am.AddressNo=a.No AND am.TableNo=9 AND am.SeasonNo=s.No AND am.TypeNo=m.No AND al.SystemNo=a.SystemNo AND al.AddressNo=a.No AND al.TableNo=8 AND al.SeasonNo=s.No AND al.TypeNo=m.GroupNo GROUP BY s.No,m.No ORDER BY s.No DESC,m.No") > 0)
        $SubMenu += 2;
      if ($SubMenu > 0)
      {
        echo "    fx = a(f0, 1, f(0, 0, \"Kampresultater\", \"\"))\r\n";
        if ($SubMenu & 1)
          echo "    a(fx, 0, d(0, \"Kampregistrering\", \"matches.php?Fun=1\", 0))\r\n";
        if ($SubMenu & 2)
        {
          if ($MatchResult = oswebdb_query("SELECT s.No,s.Description,m.No,m.Description,a.No,a.Name,am.MatchId FROM Systemtables AS s, Systemtables AS m, Addresses AS a, Addressmatches AS am, Addresslinks AS al WHERE s.SystemNo=$SeasonSystemNo AND s.TableNo=7 AND m.SystemNo=$MatchSystemNo AND m.TableNo=9 AND a.SystemNo=$SystemNo AND am.SystemNo=a.SystemNo AND am.AddressNo=a.No AND am.TableNo=9 AND am.SeasonNo=s.No AND am.TypeNo=m.No AND al.SystemNo=a.SystemNo AND al.AddressNo=a.No AND al.TableNo=8 AND al.SeasonNo=s.No AND al.TypeNo=m.GroupNo GROUP BY s.No,m.No ORDER BY s.No DESC,m.No"))
          {
            $LastSeasonNo = 0;
            while ($MatchRow = oswebdb_fetch_row($MatchResult))
            {
              if ($MatchRow[0] != $LastSeasonNo)
              {
                echo "    fy = a(fx, 1, f(0, 0, \"$MatchRow[1]\", \"\"))\r\n";
                $LastSeasonNo = $MatchRow[0];
              }
              echo "    a(fy, 0, d(0, \"$MatchRow[3]\", \"matches.php?SeasonNo=$MatchRow[0]&TypeNo=$MatchRow[2]&Fun=2\", 0))\r\n";
            }
            oswebdb_free_result($MatchResult);
          }
        }
      }
      $Result = 1;
    }
    return $Result;
  }

  function MakeAllowanceSubMenu($UserName, $Password, $SystemNo, $Properties)
  {
    $Result = 0;
    if (isset($UserName) && isset($Password) && ($Properties & 16) && file_exists("allowanc.php"))
    {
      if (oswebdb_privileges("Allowances") > 1 && oswebdb_privileges("Allowancelines") > 1)
      {
        $AllowanceMenu = 0;
        if ($AllowanceResult = oswebdb_query("SELECT Year,No,Description FROM Allowances WHERE SystemNo=$SystemNo ORDER BY Year DESC,Description,No"))
        {
          while ($AllowanceRow = oswebdb_fetch_row($AllowanceResult))
          {
            if (!$AllowanceMenu)
            {
              echo "    fx = a(f0, 1, f(0, 0, \"Kørselsfradrag\", \"allowanc.php\"))\r\n";
              $AllowanceMenu = 1;
            }
            echo "    a(fx, 0, d(0, \"$AllowanceRow[0]&nbsp;&nbsp;$AllowanceRow[2]\", \"allowanc.php?Fun=2&Year=$AllowanceRow[0]&No=$AllowanceRow[1]\", 0))\r\n";
          }
        }
      }
      $Result = 1;
    }
    return $Result;
  }

  function MakeConfigurationSubMenu($UserName, $Password, $SystemNo, $Properties)
  {
    $Result = 0;
    if (isset($UserName) && isset($Password))
    {
      $SubMenu = 0;
      if (oswebdb_privileges("Zipcodes") > 1 && (($Properties & 4) || ($Properties & 16)) && file_exists("zipcodes.php"))
        $SubMenu += 1;
      if (oswebdb_privileges("Websites") > 1 && oswebdb_privileges("Webcontent") > 1 && file_exists("websites.php"))
        $SubMenu += 2;
      if (oswebdb_privileges("Systemtables") > 1 && file_exists("tables.php"))
        $SubMenu += 4;
      if (oswebdb_privileges("Systems") > 1 && file_exists("systems.php"))
        $SubMenu += 8;
      if (oswebdb_privileges("Distances") > 1 && ($Properties & 16) && file_exists("distance.php"))
        $SubMenu += 16;
      if ((oswebdb_is_administrator($SystemNo) || oswebdb_is_configurator($SystemNo)) && ($SubMenu & 2) && file_exists("upload.php"))
      	$SubMenu += 32;
      if ($SubMenu > 0)
      {
        echo "    fx = a(f0, 1, f(0, 0, \"Opsætning\", \"\"))\r\n";
        if ($SubMenu & 16)
        {
          echo "    fy = a(fx, 1, f(0, 0, \"Distancer\", \"distance.php\"))\r\n";
          $TableSystemNo = get_system_for_table($SystemNo, 2);
          $ZipCodeSystemNo = get_system_for_zipcodes($SysemNo);
          $DistanceSystemNo = get_system_for_distances(SystemNo);
          if ($TableResult = oswebdb_query("SELECT No,Description FROM Systemtables WHERE SystemNo=$TableSystemNo AND TableNo=2 ORDER BY Description,No"))
          {
            while ($TableRow = oswebdb_fetch_row($TableResult))
            {
              echo "    fz = a(fy, 1, f(0, 0, \"$TableRow[1]\", \"distance.php?FromCountryCode=$TableRow[0]\"))\r\n";
              if ($DistanceResult = oswebdb_query("SELECT d.FromCountryCode,d.FromZipCode,z.CityName FROM Distances AS d, Zipcodes AS z WHERE d.SystemNo=$DistanceSystemNo AND d.FromCountryCode=$TableRow[0] AND z.SystemNo=$ZipCodeSystemNo AND z.CountryCode=d.FromCountryCode AND z.ZipCode=d.FromZipCode GROUP BY d.SystemNo,d.FromCountryCode,d.FromZipCode ORDER BY z.CityName,d.FromCountryCode,d.FromZipCode"))
              {
                while ($DistanceRow = oswebdb_fetch_row($DistanceResult))
                  echo "    a(fz, 0, d(0, \"$DistanceRow[1]&nbsp;&nbsp;$DistanceRow[2]\", \"distance.php?FromCountryCode=$DistanceRow[0]&FromZipCode=$DistanceRow[1]\", 0))\r\n";
                oswebdb_free_result($DistanceResult);
              }
            }
            oswebdb_free_result($TableResult);
          }
        }
        if ($SubMenu & 1)
        {
          echo "    fy = a(fx, 1, f(0, 0, \"Postnumre\", \"zipcodes.php\"))\r\n";
          $TableSystemNo = get_system_for_table($SystemNo, 2);
          $ZipCodeSystemNo = get_system_for_zipcodes($SysemNo);
          if ($TableResult = oswebdb_query("SELECT No,Description FROM Systemtables WHERE SystemNo=$TableSystemNo AND TableNo=2 ORDER BY Description,No"))
          {
            while ($TableRow = oswebdb_fetch_row($TableResult))
            {
              echo "    fz = a(fy, 1, f(0, 0, \"$TableRow[1]\", \"zipcodes.php?CountryCode=$TableRow[0]\"))\r\n";
              if ($ZipCodeResult = oswebdb_query("SELECT CountryCode,ZipCode,CityName FROM Zipcodes WHERE SystemNo=$ZipCodeSystemNo AND CountryCode=$TableRow[0] ORDER BY CityName,ZipCode"))
              {
                while ($ZipCodeRow = oswebdb_fetch_row($ZipCodeResult))
                  echo "    a(fz, 0, d(0, \"$ZipCodeRow[1]&nbsp;&nbsp;$ZipCodeRow[2]\", \"zipcodes.php?CountryCode=$ZipCodeRow[0]&ZipCode=$ZipCodeRow[1]\", 0))\r\n";
                oswebdb_free_result($ZipCodeResult);
              }
            }
            oswebdb_free_result($TableResult);
          }
        }
        if ($SubMenu & 2)
        {
          echo "    fy = a(fx, 1, f(0, 0, \"Sider\", \"websites.php\"))\r\n";
          $TypeSystemNo = get_system_for_table($SystemNo, 4);
          $MenuSystemNo = get_system_for_table($SystemNo, 1);
          if ($TypeResult = oswebdb_query("SELECT No,Description FROM Systemtables WHERE SystemNo=$TypeSystemNo AND TableNo=4 ORDER BY Description,No"))
          {
            while ($TypeRow = oswebdb_fetch_row($TypeResult))
            {
              $TypeFields = get_table_field_value($TypeSystemNo, 4, $TypeRow[0], "ShowFields");
              $TypeProperties = get_table_field_value($TypeSystemNo, 4, $TypeRow[0], "Properties");;
              $TypeText = get_table_field_value($TypeSystemNo, 4, $TypeRow[0], "Text3");
              $WebsiteFields = get_website_fields();
              $WebsiteOrder = get_website_order($TypeFields);
              echo "    fz = a(fy, 1, f(0, 0, \"$TypeRow[1]\", \"websites.php?TypeNo=$TypeRow[0]\"))\r\n";
              if ($TypeFields & 1)
              {
                $MenuStatement = "SELECT No,Description FROM Systemtables WHERE SystemNo=$MenuSystemNo AND TableNo=1 AND Public IN (1) ORDER BY Description,No";
                if (isset($UserName) && isset($Password))
                  $MenuStatement = "SELECT No,Description FROM Systemtables WHERE SystemNo=$MenuSystemNo AND TableNo=1 AND Public IN (0,1) ORDER BY Description,No";
                if ($MenuResult = oswebdb_query($MenuStatement))
                {
                  while ($MenuRow = oswebdb_fetch_row($MenuResult))
                  {
                    $Header = 0;
                    if ($WebsiteResult = oswebdb_query("SELECT $WebsiteFields FROM Websites WHERE SystemNo=$SystemNo AND TypeNo=$TypeRow[0] AND MenuNo=$MenuRow[0] ORDER BY $WebsiteOrder"))
                    {
                      while ($WebsiteRow = oswebdb_fetch_row($WebsiteResult))
                      {
                        if (use_website($TypeFields, $WebsiteRow[4], $UserName, $Password, 1, NULL, NULL, $WebsiteRow[8], 1))
                        {
                          if (!$Header)
                          {
                            echo "    fw = a(fz, 1, f(0, 0, \"$MenuRow[1]\", \"\"))\r\n";
                            $Header = 1;
                          }
                          $WebsiteNew = new_website($WebsiteRow[3]);
                          $WebsiteDescription = get_website_description($TypeFields, $TypeProperties, $WebsiteRow[2], $WebsiteRow[3], $TypeText);
                          echo "    a(fw, 0, d(0, \"$WebsiteDescription\", \"websites.php?TypeNo=$WebsiteRow[0]&MenuNo=$WebsiteRow[1]&Description=$WebsiteRow[2]\", $WebsiteNew))\r\n";
                        }
                      }
                      oswebdb_free_result($WebsiteResult);
                    }
                  }
                  oswebdb_free_result($MenuResult);
                }
                if ($WebsiteResult = oswebdb_query("SELECT $WebsiteFields FROM Websites WHERE SystemNo=$SystemNo AND TypeNo=$TypeRow[0] AND MenuNo=0 ORDER BY $WebsiteOrder"))
                {
                  while ($WebsiteRow = oswebdb_fetch_row($WebsiteResult))
                  {
                    if (use_website($TypeFields, $WebsiteRow[4], $UserName, $Password, 1, NULL, NULL, $WebsiteRow[8], 1))
                    {
                      $WebsiteNew = new_website($WebsiteRow[3]);
                      $WebsiteDescription = get_website_description($TypeFields, $TypeProperties, $WebsiteRow[2], $WebsiteRow[3], $TypeText);
                      echo "    a(fz, 0, d(0, \"$WebsiteDescription\", \"websites.php?TypeNo=$WebsiteRow[0]&MenuNo=$WebsiteRow[1]&Description=$WebsiteRow[2]\", $WebsiteNew))\r\n";
                    }
                  }
                  oswebdb_free_result($WebsiteResult);
                }
              }
              else if ($WebsiteResult = oswebdb_query("SELECT $WebsiteFields FROM Websites WHERE SystemNo=$SystemNo AND TypeNo=$TypeRow[0] AND MenuNo=0 ORDER BY $WebsiteOrder"))
              {
                while ($WebsiteRow = oswebdb_fetch_row($WebsiteResult))
                {
                  if (use_website($TypeFields, $WebsiteRow[4], $UserName, $Password, 1, NULL, NULL, $WebsiteRow[8], 1))
                  {
                    $WebsiteNew = new_website($WebsiteRow[3]);
                    $WebsiteDescription = get_website_description($TypeFields, $TypeProperties, $WebsiteRow[2], $WebsiteRow[3], $TypeText);
                    echo "    a(fz, 0, d(0, \"$WebsiteDescription\", \"websites.php?TypeNo=$WebsiteRow[0]&MenuNo=$WebsiteRow[1]&Description=$WebsiteRow[2]\", $WebsiteNew))\r\n";
                  }
                }
                oswebdb_free_result($WebsiteResult);
              }
            }
            oswebdb_free_result($TypeResult);
          }
        }
        if ($SubMenu & 4)
        {
          echo "    fy = a(fx, 1, f(0, 0, \"Tabeller\", \"tables.php\"))\r\n";
          $TableSystemNo = get_system_for_table($SystemNo, 0);
          if ($TableResult = oswebdb_query("SELECT TableNo,No,Description,Common FROM Systemtables WHERE SystemNo=$TableSystemNo AND TableNo=0 ORDER BY Description,No"))
          {
            while ($TableRow = oswebdb_fetch_row($TableResult))
            {
               $IncludeTable = 1;
               switch ($TableRow[1])
               {
                 case 2:
                   // Country codes.
                   $IncludeTable = ($Properties & 4);
                   break;

                 case 3:
                   // Address groups (only with addresses).
                   $IncludeTable = ($Properties & 4);
                   break;

                 case 8:
                   // Types of averages (only with addresses).
                   $IncludeTable = ($Properties & 4);
                   break;

                 case 9:
                   // Matchtypes (only with addresses).
                   $IncludeTable = ($Properties & 4);
                   break;
               }
               $IncludeTable = $IncludeTable && is_table_accessible($SystemNo, $TableRow[1]);
               if ($IncludeTable)
               {
                 echo "    fz = a(fy, 1, f(0, 0, \"$TableRow[2]\", \"tables.php?TableNo=$TableRow[1]\"))\r\n";
                 $TableSystemNo = ((int) $TableRow[3]) ? 0 : $SystemNo;
                 if ($ValueResult = oswebdb_query("SELECT TableNo,No,Description FROM Systemtables WHERE SystemNo=$TableSystemNo AND TableNo=$TableRow[1] ORDER BY Description,No"))
                 {
                   while ($ValueRow = oswebdb_fetch_row($ValueResult))
                     echo "    a(fz, 0, d(0, \"$ValueRow[2]\", \"tables.php?TableNo=$ValueRow[0]&No=$ValueRow[1]\", 0))\r\n";
                   oswebdb_free_result($ValueResult);
                 }
               }
            }
            oswebdb_free_result($TableResult);
          }
        }
        if ($SubMenu & 32)
        {
          echo "    fy = a(fx, 1, f(0, 0, \"Upload\", \"upload.php\"))\r\n";
          echo "    a(fy, 0, d(0, \"Billede\", \"upload.php?UploadType=1\", 0))\r\n";
          echo "    a(fy, 0, d(0, \"Dokument\", \"upload.php?UploadType=2\", 0))\r\n";
        }
        if ($SubMenu & 8)
          echo "    a(fx, 0, d(0, \"Systemparametre\", \"systems.php\", 0))\r\n";
      }
      $Result = 1;
    }
    return $Result;
  }

  function MakePrintSubMenu($UserName, $Password, $SystemNo, $Properties, $CurrentSeasonNo)
  {
    $Result = 0;
    if (file_exists("print.php"))
    {
      $Print = 0;
      $GroupSystemNo = (integer) get_system_for_table($SystemNo, 3);
      $SeasonSystemNo = (integer) get_system_for_table($SystemNo, 7);
      $AverageSystemNo = (integer) get_system_for_table($SystemNo, 8);
      $MatchSystemNo = (integer) get_system_for_table($SystemNo, 9);
      $Statement = "SELECT a.Name,a.Phone1,al.TypeNo,at.Description,at.Length,al.Average,al.Distance,g.No,g.Description,g.Properties FROM Addresses AS a, Addresslinks AS al, Systemtables AS at, Systemtables AS g WHERE a.SystemNo=$SystemNo AND a.Public IN (1) AND al.SystemNo=a.SystemNo AND al.AddressNo=a.No AND al.SeasonNo=$CurrentSeasonNo AND at.SystemNo=$AverageSystemNo AND at.TableNo=8 AND at.No=al.TypeNo AND g.SystemNo=$GroupSystemNo AND g.TableNo=3 AND g.No=a.GroupNo AND (g.Properties & 2) AND (g.Properties & 4) ORDER BY al.TypeNo,al.Average DESC,a.Name";
      if (isset($UserName) && isset($Password))
        $Statement = "SELECT a.Name,a.Phone1,al.TypeNo,at.Description,at.Length,al.Average,al.Distance,g.No,g.Description,g.Properties FROM Addresses AS a, Addresslinks AS al, Systemtables AS at, Systemtables AS g WHERE a.SystemNo=$SystemNo AND a.Public IN (0,1) AND al.SystemNo=a.SystemNo AND al.AddressNo=a.No AND al.SeasonNo=$CurrentSeasonNo AND at.SystemNo=$AverageSystemNo AND at.TableNo=8 AND at.No=al.TypeNo AND g.SystemNo=$GroupSystemNo AND g.TableNo=3 AND g.No=a.GroupNo AND (g.Properties & 2) AND (g.Properties & 4) ORDER BY al.TypeNo,al.Average DESC,a.Name";
      if (oswebdb_get_rows($Statement) > 0)
        $Print += 1;
      if (oswebdb_get_rows("SELECT s.No,s.Description,m.No,m.Description,a.No,a.Name,am.MatchId FROM Systemtables AS s, Systemtables AS m, Addresses AS a, Addressmatches AS am, Addresslinks AS al WHERE s.SystemNo=$SeasonSystemNo AND s.TableNo=7 AND m.SystemNo=$MatchSystemNo AND m.TableNo=9 AND a.SystemNo=$SystemNo AND am.SystemNo=a.SystemNo AND am.AddressNo=a.No AND am.TableNo=9 AND am.SeasonNo=s.No AND am.TypeNo=m.No AND al.SystemNo=a.SystemNo AND al.AddressNo=a.No AND al.TableNo=8 AND al.SeasonNo=s.No AND al.TypeNo=m.GroupNo GROUP BY s.No,m.No ORDER BY s.No DESC,m.No") > 0)
        $Print += 2;
      if ($Print > 0)
      {
        echo "    fx = a(f0, 1, f(0, 0, \"Udskrifter\", \"\"))\r\n";
        if ($Print & 1)
          echo "    a(fx, 0, d(0, \"Gennemsnit\", \"print.php?ReportNo=1\", 0))\r\n";
        if ($Print & 2)
        {
          if ($MatchResult = oswebdb_query("SELECT s.No,s.Description,m.No,m.Description,a.No,a.Name,am.MatchId FROM Systemtables AS s, Systemtables AS m, Addresses AS a, Addressmatches AS am, Addresslinks AS al WHERE s.SystemNo=$SeasonSystemNo AND s.TableNo=7 AND m.SystemNo=$MatchSystemNo AND m.TableNo=9 AND a.SystemNo=$SystemNo AND am.SystemNo=a.SystemNo AND am.AddressNo=a.No AND am.TableNo=9 AND am.SeasonNo=s.No AND am.TypeNo=m.No AND al.SystemNo=a.SystemNo AND al.AddressNo=a.No AND al.TableNo=8 AND al.SeasonNo=s.No AND al.TypeNo=m.GroupNo GROUP BY s.No,m.No ORDER BY s.No DESC,m.No"))
          {
            echo "    fy = a(fx, 1, f(0, 0, \"Kampresultater\", \"\"))\r\n";
            $LastSeasonNo = 0;
            while ($MatchRow = oswebdb_fetch_row($MatchResult))
            {
              if ($MatchRow[0] != $LastSeasonNo)
              {
                echo "    fz = a(fy, 1, f(0, 0, \"$MatchRow[1]\", \"\"))\r\n";
                $LastSeasonNo = $MatchRow[0];
              }
              echo "    a(fz, 0, d(0, \"$MatchRow[3]\", \"print.php?ReportNo=2&SeasonNo=$MatchRow[0]&TypeNo=$MatchRow[2]\", 0))\r\n";
            }
            oswebdb_free_result($MatchResult);
          }
        }
      }
      $Result = 1;
    }
    return $Result;
  }

  function MakeToolsSubMenu($UserName, $Password, $Properties)
  {
    $Result = 0;
    if (isset($UserName) && isset($Password))
    {
      $SubMenu = 0;
      if (($Properties & 1) && file_exists("dbadmin.php"))
        $SubMenu += 1;
      if ($SubMenu > 0)
      {
        echo "    fx = a(f0, 1, f(0, 0, \"Værktøjer\", \"\"))\r\n";
        if ($SubMenu & 1)
          echo "    a(fx, 0, d(0, \"Databaseadministration\", \"dbadmin.php\", 0))\r\n";
      }
      $Result = 1;
    }
    return $Result;
  }
?>
