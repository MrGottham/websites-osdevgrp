<?php
  // Include required files.
  require_once("misc.php");
  require_once("oswebdb/oswebdb.php");

  // Set error reporting.
  error_reporting((int) GetConfigValue("ErrorReporting"));

  define("MAX_SIZE", 64);
  if (isset($_GET["Fun"]))
    $Fun = $_GET["Fun"];
  else if (isset($_POST["Fun"]))
    $Fun = $_POST["Fun"];
  if (oswebdb_authorize(oswebdb_getusername(), oswebdb_getpassword()))
  {
    if (oswebdb_selectdb())
    {
      switch ($Fun)
      {
        case 1:
          MakeDatabaseAdministrationToolbar();
          break;

        case 2:
          MakeDatabaseAdministrationStatements($Fun);
          break;

        case 3:
          MakeDatabaseAdministrationUsers($Fun);
          break;

        case 4:
          MakeDatabaseAdministrationGrants($Fun);
          break;

        case 41:
          MakeDatabaseAdministrationGrantsUserList($Fun);
          break;

        case 42:
          MakeDatabaseAdministrationGrantsContent($Fun, $_GET["Host"], $_GET["User"]);
          break;

        default:
          MakeDatabaseAdministration(2);
          braek;
      }
    }
    oswebdb_close();
  }

  function MakeDatabaseAdministration($Fun)
  {
    echo "<script language=\"JavaScript\">\r\n";
    echo "  function openPage(fun)\r\n";
    echo "  {\r\n";
    echo "    this.framePage.location = 'dbadmin.php?Fun=' + fun\r\n";
    echo "  }\r\n\r\n";
    echo "  function validateStatementForm()\r\n";
    echo "  {\r\n";
    echo "    statement = this.framePage.StatementForm.Statement.value\r\n";
    echo "    if (statement.length == 0)\r\n";
    echo "    {\r\n";
    echo "      alert('Forespørgelsen skal indtastes!')\r\n";
    echo "      return false\r\n";
    echo "    }\r\n";
    echo "    else\r\n";
    echo "      return true\r\n";
    echo "  }\r\n\r\n";
    echo "  function validateUserForm(fun)\r\n";
    echo "  {\r\n";
    echo "    host = this.framePage.UserForm.Host.value\r\n";
    echo "    user = this.framePage.UserForm.User.value\r\n";
    echo "    if (host.length == 0)\r\n";
    echo "    {\r\n";
    echo "      alert('Host skal indtastes!')\r\n";
    echo "      return false\r\n";
    echo "    }\r\n";
    echo "    else if (user.length == 0)\r\n";
    echo "    {\r\n";
    echo "      alert('Bruger skal indtastes!')\r\n";
    echo "      return false\r\n";
    echo "    }\r\n";
    echo "    else\r\n";
    echo "      return true\r\n";
    echo "  }\r\n\r\n";
    echo "  function resetUserForm(fun)\r\n";
    echo "  {\r\n";
    echo "    if (fun == 2)\r\n";
    echo "    {\r\n";
    echo "      this.framePage.UserForm.ResetPassword.checked = false\r\n";
    echo "      changeResetPassword()\r\n";
    echo "    }\r\n";
    echo "  }\r\n\r\n";
    echo "  function changeResetPassword()\r\n";
    echo "  {\r\n";
    echo "    resetPassword = this.framePage.UserForm.ResetPassword.checked == false\r\n";
    echo "    if (resetPassword)\r\n";
    echo "      this.framePage.UserForm.Password.value = ''\r\n";
    echo "    this.framePage.UserForm.Password.readOnly = resetPassword\r\n";
    echo "    this.framePage.UserForm.Password.disabled = resetPassword\r\n";
    echo "  }\r\n";
    echo "</script>\r\n";
    echo "<html>\r\n";
    echo "  <head>\r\n";
    echo "    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=windows-1252\">\r\n";
    echo "    <title>Databaseadministration</title>\r\n";
    echo "  </head>\r\n";
    echo "  <frameset rows=\"40,*\" frameborder=\"0\" framespacing=\"0\" border=\"0\">\r\n";
    echo "    <frame name=\"frameToolbar\" scrolling=\"no\" noresize src=\"dbadmin.php?Fun=1\">\r\n";
    echo "    <frame name=\"framePage\" scrolling=\"auto\" noresize src=\"dbadmin.php?Fun=$Fun\">\r\n";
    echo "  </frameset>\r\n";
    echo "</html>\r\n";
  }

  function MakeDatabaseAdministrationToolbar()
  {
    MakeHtmlPageTop("Databaseadministration");
    $Users = "";
    if (oswebdb_allow_select(oswebdb_get_user_table_name()))
    {
      if (oswebdb_allow_insert(oswebdb_get_user_table_name()) ||  oswebdb_allow_update(oswebdb_get_user_table_name()) ||  oswebdb_allow_delete(oswebdb_get_user_table_name()))
      {
        $Users = MakeHtmlLink("", "javascript:parent.openPage(3)", "", "", "", "", "javascript:window.status='Brugere'; return true;", "javascript:window.status=''; return true;", "Brugere");
        $Users = "$Users&nbsp;|&nbsp;";
      }
      $Grants = MakeHtmlLink("", "javascript:parent.openPage(4)", "", "", "", "", "javascript:window.status='Rettigheder'; return true;", "javascript:window.status=''; return true;", "Rettigheder");
      $Grants = "$Grants&nbsp;|&nbsp;";
    }
    $Queries = MakeHtmlLink("", "javascript:parent.openPage(2)", "", "", "", "", "javascript:window.status='Forespørgelser'; return true;", "javascript:window.status=''; return true;", "Forespørgelser");
    echo "    <form>\r\n";
    echo "      <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">\r\n";
    echo "        <tr><td width=\"100%\" valign=\"middle\" align=\"right\">$Users$Grants$Queries</td></tr>\r\n";
    echo "      </table>\r\n";
    echo "    </form>\r\n";
    MakeHtmlPageBottom();
  }

  function MakeDatabaseAdministrationStatements($Fun)
  {
    $TabIndex = 1; $StatementExecuted = 0; $Options = "";
    MakeHtmlPageTop("Databaseadministration");
    echo "    <form name=\"StatementForm\" action=\"dbadmin.php\" method=\"post\">\r\n";
    echo "      <input type=\"hidden\" name=\"Fun\" value=\"$Fun\">\r\n";
    if (isset($_POST["Statement"]))
    {
      $Statement = $_POST["Statement"];
      if ($StatementResult = oswebdb_query_without_exit($Statement))
      {
        $StatementExecuted = 1;
        $Options = "$Options<option value=\"0\">$Statement";
        echo "      <input type=\"hidden\" name=\"ExecutedStatement0\" value=\"$Statement\">\r\n";
      }
      else
        $StatementError = oswebdb_error();
      for ($i = 0, $j = $i + $StatementExecuted; $j < 10; $i++, $j++)
      {
        if (isset($_POST["ExecutedStatement$i"]))
        {
          $Value = $_POST["ExecutedStatement$i"];
          $Options = "$Options<option value=\"$j\">$Value";
          echo "      <input type=\"hidden\" name=\"ExecutedStatement$j\" value=\"$Value\">\r\n";
        }
      }
    }
    echo "      <table border=\"0\" cellpadding=\"1\" cellspacing=\"1\" width=\"100%\">\r\n";
    echo "        <tr><td width=\"100%\" colspan=\"2\" valign=\"middle\"><h2><i>Forespørgelser</i></h2></td></tr>\r\n";
    if (isset($StatementError))
    {
      echo "        <tr><td width=\"100%\" colspan=\"2\">$StatementError</td></tr>\r\n";
      echo "        <tr><td width=\"100%\" colspan=\"2\"><br></td></tr>\r\n";
    }
    if ($StatementExecuted)
    {
      if (is_object($StatementResult) == false)
      {
        $Rows = oswebdb_affected_rows();
        if ($Rows == 1)
          echo "        <tr><td width=\"100%\" colspan=\"2\">Forespørgelse OK ($Rows række påvirket)</td></tr>\r\n";
        else
          echo "        <tr><td width=\"100%\" colspan=\"2\">Forespørgelse OK ($Rows rækker påvirket)</td></tr>\r\n";
        echo "        <tr><td width=\"100%\" colspan=\"2\"><br></td></tr>\r\n";
      }
      else
      {
        $NoOfFields = oswebdb_num_fields($StatementResult);
        echo "        <tr><td width=\"100%\" colspan=\"2\"><table border=\"1\" cellpadding=\"2\" cellspacing=\"0\">\r\n";
        echo "          <tr>";
        for ($i = 0; $i < $NoOfFields; $i++)
        {
          $FieldName = oswebdb_field_name($StatementResult, $i);
          echo "<td nowrap>$FieldName</td>";
        }
        echo "</tr>\r\n";
        while ($StatementRow = oswebdb_fetch_row($StatementResult))
        {
          echo "          <tr>";
          for ($i = 0; $i < $NoOfFields; $i++)
          {
            if ($i > sizeof($StatementRow) || strlen($StatementRow[$i]) == 0)
              echo "<td nowrap>&nbsp;</td>";
            else
              echo "<td nowrap>$StatementRow[$i]</td>";
          }
          echo "</tr>\r\n";
        }
        echo "        </table></td></tr>\r\n";
        echo "        <tr><td width=\"100%\" colspan=\"2\"><br></td></tr>\r\n";
        oswebdb_free_result($StatementResult);
      }
    }
    $Input = MakeHtmlInputText("Statement", "", 64, 2048, 0, 0, $TabIndex++, "");
    echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Forespørgelse :</td><td width=\"99%\">$Input</td></tr>\r\n";
    $Input = MakeHtmlInputSubmit("", "Udfør", 0, $TabIndex++, "javascript:return parent.validateStatementForm()");
    echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\">$Input</td></tr>\r\n";
    $Input = MakeHtmlSelect("", 0, 10, 0, 0, $TabIndex++, "javascript:StatementForm.Statement.value = this.options[this.value].text", $Options);
    echo "        <tr><td width=\"1%\" valign=\"top\" align=\"right\" nowrap>Udførte forespørgelser :</td><td width=\"99%\">$Input</td></tr>\r\n";
    echo "      </table>\r\n";
    echo "    </form>\r\n";
    MakeHtmlPageBottom();
  }

  function MakeDatabaseAdministrationUsers($Fun)
  {
    $NewUser = $_POST["NewUser"];
    $Host = $_POST["Host"];
    $User = $_POST["User"];
    $UserTableName = oswebdb_get_user_table_name();
    $DefaultHostname = oswebdb_privilege_hostname(1);
    $CurrentHostname = oswebdb_privilege_hostname(0);
    if (isset($_POST["Insert"]) && isset($Host) && isset($User))
    {
      if (!oswebdb_insert_user($Host, $User, $_POST["Password"], $_POST["SelectPrivilege"], $_POST["InsertPrivilege"], $_POST["UpdatePrivilege"], $_POST["DeletePrivilege"], $_POST["GrantPrivilege"]))
      {
        $Error = oswebdb_error();
        MakeHtmlPageTop("Databaseadministration");
        echo "    <h2><strong>$Error</strong></h2>\r\n";
        MakeHtmlPageBottom("");
        exit;
      }
    }
    else if (isset($_POST["Update"]) && isset($Host) && isset($User))
    {
      if (!oswebdb_update_user($Host, $User, $_POST["ResetPassword"], $_POST["Password"], $_POST["SelectPrivilege"], $_POST["InsertPrivilege"], $_POST["UpdatePrivilege"], $_POST["DeletePrivilege"], $_POST["GrantPrivilege"]))
      {
        $Error = oswebdb_error();
        MakeHtmlPageTop("Databaseadministration");
        echo "    <h2><strong>$Error</strong></h2>\r\n";
        MakeHtmlPageBottom("");
        exit;
      }
    }
    else if (isset($_POST["Delete"]) && isset($Host) && isset($User))
    {
      if (!oswebdb_delete_user($Host, $User))
      {
        $Error = oswebdb_error();
        MakeHtmlPageTop("Databaseadministration");
        echo "    <h2><strong>$Error</strong></h2>\r\n";
        MakeHtmlPageBottom("");
        exit;
      }
      unset($Host);
      unset($User);
    }
    else if (isset($_POST["ShowAll"]) && (isset($Host) || isset($User)))
    {
      if (isset($Host))
        unset($Host);
      if (isset($User))
        unset($User);
    }
    $AllowSelect = oswebdb_allow_select($UserTableName);
    $AllowInsert = oswebdb_allow_insert($UserTableName);
    $AllowUpdate = oswebdb_allow_update($UserTableName);
    $AllowDelete = oswebdb_allow_delete($UserTableName);
    if ($NewUser)
      $Statement = "SELECT Host,User,Password,Select_priv,Insert_priv,Update_priv,Delete_priv,Grant_priv FROM $UserTableName ORDER BY User,Host";
    else if (isset($Host) && isset($User))
      $Statement = "SELECT Host,User,Password,Select_priv,Insert_priv,Update_priv,Delete_priv,Grant_priv FROM $UserTableName WHERE Host=\"$Host\" AND User=\"$User\"";
    else
      $Statement = "SELECT Host,User FROM $UserTableName ORDER BY User,Host";
    if (($NewUser || (isset($Host) && isset($User))) && $Result = oswebdb_query($Statement))
    {
      if ($NewUser || $Row = oswebdb_fetch_row($Result))
      {
        $TabIndex = 1;
        MakeHtmlPageTop("Databaseadministration");
        echo "    <form name=\"UserForm\" action=\"dbadmin.php\" method=\"post\">\r\n";
        echo "      <input type=\"hidden\" name=\"Fun\" value=\"$Fun\">\r\n";
        if (!$NewUser)
        {
          echo "      <input type=\"hidden\" name=\"Host\" value=\"$Row[0]\">\r\n";
          echo "      <input type=\"hidden\" name=\"User\" value=\"$Row[1]\">\r\n";
        }
        echo "      <table border=\"0\" cellpadding=\"1\" cellspacing=\"1\" width=\"100%\">\r\n";
        echo "        <tr><td width=\"100%\" colspan=\"2\" valign=\"middle\"><h2><i>Brugere</i></h2></td></tr>\r\n";
        echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\">";
        if ($NewUser)
        {
          if ($AllowInsert)
          {
            $InputSubmit = MakeHtmlInputSubmit("Insert", "Opret", !$AllowInsert, $TabIndex++, "javascript:return parent.validateUserForm(1)");
            $InputReset = MakeHtmlInputReset("Reset", "Fortryd", !$AllowInsert, $TabIndex++, "javascript:parent.resetUserForm(1)");
            echo "$InputSubmit&nbsp;$InputReset&nbsp;";
          }
          if ($AllowSelect)
          {
            $Input = MakeHtmlInputSubmit("ShowAll", "Vis alle", !$AllowSelect, $TabIndex++, "");
            echo "$Input&nbsp;";
          }
          $InputHost = MakeHtmlInputText("Host", $DefaultHostname, oswebdb_field_len($Result, 0), oswebdb_field_len($Result, 0), !$AllowInsert, !$AllowInsert, $TabIndex++, "");
          $InputUser = MakeHtmlInputText("User", "", oswebdb_field_len($Result, 1), oswebdb_field_len($Result, 1), !$AllowInsert, !$AllowInsert, $TabIndex++, "");
        }
        else
        {
          if ($AllowUpdate)
          {
            $InputSubmit = MakeHtmlInputSubmit("Update", "Opdatér", !$AllowUpdate, $TabIndex++, "javascript:return parent.validateUserForm(2)");
            $InputReset = MakeHtmlInputReset("Reset", "Fortryd", !$AllowUpdate, $TabIndex++, "javascript:parent.resetUserForm(2)");
            echo "$InputSubmit&nbsp;$InputReset&nbsp;";
          }
          if ($AllowDelete)
          {
            $InputDelete = MakeHtmlInputSubmit("Delete", "Slet", !$AllowDelete, $TabIndex++, "javascript:return confirm('Bekræft sletning!')");
            echo "$InputDelete&nbsp;";
          }
          if ($AllowSelect)
          {
            $Input = MakeHtmlInputSubmit("ShowAll", "Vis alle", !$AllowSelect, $TabIndex++, "");
            echo "$Input&nbsp;";
          }
          $InputHost = MakeHtmlInputText("", ($Row[0] == $DefaultHostname ? $CurrentHostname : $Row[0]), oswebdb_field_len($Result, 0), oswebdb_field_len($Result, 0), 1, 1, $TabIndex++, "");
          $InputUser = MakeHtmlInputText("", $Row[1], oswebdb_field_len($Result, 1), oswebdb_field_len($Result, 1), 1, 1, $TabIndex++, "");
        }
        echo "</td></tr>\r\n";
        echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Host :</td><td width=\"99%\">$InputHost</td></tr>\r\n";
        echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Bruger :</td><td width=\"99%\">$InputUser</td></tr>\r\n";
        $InputPassword = MakeHtmlInputPassword("Password", "", oswebdb_field_len($Result, 2), oswebdb_field_len($Result, 2), ($NewUser ? !$AllowInsert : 1), ($NewUser ? !$AllowInsert : 1), $TabIndex++, "");
        $InputResetPassword = "";
        if (!$NewUser)
          $InputResetPassword = MakeHtmlInputCheckBox("ResetPassword", 1, 0, !$AllowUpdate, $TabIndex++, "javascript:parent.changeResetPassword()", "Ny adgangskode");
        echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Adgangskode :</td><td width=\"99%\">$InputPassword&nbsp;$InputResetPassword</td></tr>\r\n";
        echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\"><strong>Rettigheder på globalt niveau:</strong></td></tr>\r\n";
        $Input = MakeHtmlInputCheckBox("SelectPrivilege", 'Y', ($NewUser ? 1 : $Row[3] == 'Y'), ($NewUser ? !$AllowInsert : !$AllowUpdate), $TabIndex++, "", "Forespørge (SELECT)");
        echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\">$Input</td></tr>\r\n";
        $Input = MakeHtmlInputCheckBox("InsertPrivilege", 'Y', ($NewUser ? 0 : $Row[4] == 'Y'), ($NewUser ? !$AllowInsert : !$AllowUpdate), $TabIndex++, "", "Oprette (INSERT)");
        echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\">$Input</td></tr>\r\n";
        $Input = MakeHtmlInputCheckBox("UpdatePrivilege", 'Y', ($NewUser ? 0 : $Row[5] == 'Y'), ($NewUser ? !$AllowInsert : !$AllowUpdate), $TabIndex++, "", "Opdatere (UPDATE)");
        echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\">$Input</td></tr>\r\n";
        $Input = MakeHtmlInputCheckBox("DeletePrivilege", 'Y', ($NewUser ? 0 : $Row[6] == 'Y'), ($NewUser ? !$AllowInsert : !$AllowUpdate), $TabIndex++, "", "Slette (DELETE)");
        echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\">$Input</td></tr>\r\n";
        $Input = MakeHtmlInputCheckBox("GrantPrivilege", 'Y', ($NewUser ? 0 : $Row[7] == 'Y'), ($NewUser ? !$AllowInsert : !$AllowUpdate), $TabIndex++, "", "Tildele rettigheder (GRANT)");
        echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\">$Input</td></tr>\r\n";
        echo "      </table>\r\n";
        echo "    </form>\r\n";
        MakeHtmlPageBottom();
      }
      else
        MakeHtmlPageReload("javascript:this.location = 'dbadmin.php?Fun=$Fun'; return true;");
      oswebdb_free_result($Result);
    }
    else if ($Result = oswebdb_query($Statement))
    {
      $TabIndex = 1;
      MakeHtmlPageTop("Databaseadministration");
      echo "    <table border=\"0\" cellpadding=\"1\" cellspacing=\"1\" width=\"100%\">\r\n";
      echo "      <tr><td width=\"100%\" colspan=\"2\" valign=\"middle\"><h2><i>Brugere</i></h2></td></tr>\r\n";
      if ($AllowInsert)
      {
        $Input = MakeHtmlInputSubmit("NewUser", "Opret", !$AllowInsert, $TabIndex++, "");
        echo "      <form action=\"dbadmin.php\" method=\"post\">\r\n";
        echo "        <input type=\"hidden\" name=\"Fun\" value=\"$Fun\">\r\n";
        echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\">$Input</td></tr>\r\n";
        echo "      </form>\r\n";
      }
      while ($Row = oswebdb_fetch_row($Result))
      {
        if ($Row[0] == $DefaultHostname)
          $HostAndUsername = "$Row[1]@$CurrentHostname";
        else
          $HostAndUsername = "$Row[1]@$Row[0]";
        if ($AllowInsert)
          $Input = MakeHtmlInputSubmit("", "Redigér", !$AllowInsert, $TabIndex++, "");
        else
          $Input = MakeHtmlInputSubmit("", "Vis", 0, $TabIndex++, "");

        echo "      <form action=\"dbadmin.php\" method=\"post\"><input type=\"hidden\" name=\"Fun\" value=\"$Fun\"><input type=\"hidden\" name=\"Host\" value=\"$Row[0]\"><input type=\"hidden\" name=\"User\" value=\"$Row[1]\"><tr><td width=\"1%\" align=\"right\" nowrap>$Input</td><td width=\"99%\">$HostAndUsername</td></tr></form>\r\n";
      }
      echo "    </table>\r\n";
      MakeHtmlPageBottom();
      oswebdb_free_result($Result);
    }
  }

  function MakeDatabaseAdministrationGrants($Fun)
  {
    echo "<script language=\"JavaScript\">\r\n";
    echo "  function changePrivilege(checked, level, privilege)\r\n";
    echo "  {\r\n";
    echo "    fun = $Fun\r\n";
    echo "    host = this.frameGrants.GrantsForm.Host.value\r\n";
    echo "    user = this.frameGrants.GrantsForm.User.value\r\n";
    echo "    if (checked)\r\n";
    echo "      this.frameGrants.location = 'dbadmin.php?Fun=' + ((fun * 10) + 2) + '&Host=' + host + '&User=' + user + '&Grant=1&Level=' + level + '&Privilege=' + privilege\r\n";
    echo "    else\r\n";
    echo "      this.frameGrants.location = 'dbadmin.php?Fun=' + ((fun * 10) + 2) + '&Host=' + host + '&User=' + user + '&Revoke=1&Level=' + level + '&Privilege=' + privilege\r\n";
    echo "  }\r\n\r\n";
    echo "  function changeUserListBoxControl()\r\n";
    echo "  {\r\n";
    echo "    fun = $Fun\r\n";
    echo "    index = this.frameUserList.UserListForm.UserListBox.selectedIndex\r\n";
    echo "    hostAndUsername = this.frameUserList.UserListForm.UserListBox.options[index].value\r\n";
    echo "    if (hostAndUsername.indexOf('@') >= 0)\r\n";
    echo "    {\r\n";
    echo "      host = hostAndUsername.substr(hostAndUsername.indexOf('@') + 1, hostAndUsername.length - hostAndUsername.indexOf('@'))\r\n";
    echo "      user = hostAndUsername.substr(0, hostAndUsername.indexOf('@'))\r\n";
    echo "    }\r\n";
    echo "    else\r\n";
    echo "    {\r\n";
    echo "      host = ''\r\n";
    echo "      user = hostAndUsername\r\n";
    echo "    }\r\n";
    echo "    this.frameGrants.location = 'dbadmin.php?Fun=' + ((fun * 10) + 2) + '&Host=' + host + '&User=' + user\r\n";
    echo "  }\r\n";
    echo "</script>\r\n";
    echo "<html>\r\n";
    echo "  <head>\r\n";
    echo "    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=windows-1252\">\r\n";
    echo "    <title>Databaseadministration</title>\r\n";
    echo "  </head>\r\n";
    echo "  <frameset rows=\"100,*\" frameborder=\"0\" framespacing=\"0\" border=\"0\">\r\n";
    $NewFun = ($Fun * 10) + 1;
    echo "    <frame name=\"frameUserList\" scrolling=\"no\" noresize src=\"dbadmin.php?Fun=$NewFun\">\r\n";
    $NewFun = ($Fun * 10) + 2;
    echo "    <frame name=\"frameGrants\" scrolling=\"auto\" noresize src=\"dbadmin.php?Fun=$NewFun\">\r\n";
    echo "  </frameset>\r\n";
    echo "</html>\r\n";
  }

  function MakeDatabaseAdministrationGrantsUserList($Fun)
  {
    $UserTableName = oswebdb_get_user_table_name();
    $DefaultHostname = oswebdb_privilege_hostname(1);
    $CurrentHostname = oswebdb_privilege_hostname(0);
    $CurrentUsername = oswebdb_privilege_username();
    if ($Result = oswebdb_query("SELECT Host,User FROM $UserTableName ORDER BY User,Host"))
    {
      $Options = "";
      while ($Row = oswebdb_fetch_row($Result))
      {
        $Options = "$Options<option value=\"$Row[1]@$Row[0]\"";
        if ((($Row[0] == $DefaultHostname) || ($Row[0] == $CurrentHostname)) && $Row[1] == $CurrentUsername)
          $Options = "$Options selected";
        if ($Row[0] == $DefaultHostname)
          $HostAndUsername = "$Row[1]@$CurrentHostname";
        else
          $HostAndUsername = "$Row[1]@$Row[0]";
        $Options = "$Options>$HostAndUsername";
      }
      $TabIndex = 1;
      MakeHtmlPageTop("Databaseadministration");
      echo "    <form name=\"UserListForm\" action=\"javascript:parent.changeUserListBoxControl()\">\r\n";
      echo "      <table border=\"0\" cellpadding=\"1\" cellspacing=\"1\" width=\"100%\">\r\n";
      echo "        <tr><td width=\"100%\" colspan=\"2\" valign=\"middle\"><h2><i>Rettigheder</i></h2></td></tr>\r\n";
      $InputUser = MakeHtmlSelect("UserListBox", 0, 0, !oswebdb_allow_select($UserTableName), !oswebdb_allow_select($UserTableName), $TabIndex++, "javascript:parent.changeUserListBoxControl()", $Options);
      echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Bruger :</td><td width=\"99%\">$InputUser</td></tr>\r\n";
      echo "      </table>\r\n";
      echo "    </form>\r\n";
      MakeHtmlPageBottom();
      oswebdb_free_result($Result);
    }
  }

  function MakeDatabaseAdministrationGrantsContent($Fun, $Host, $User)
  {
    if (isset($Host) && isset($User) && isset($_GET["Grant"]) && isset($_GET["Level"]) && isset($_GET["Privilege"]))
    {
      if (!oswebdb_grant_privilege($Host, $User, $_GET["Level"], $_GET["Privilege"]))
      {
        $Error = oswebdb_error();
        MakeHtmlPageTop("Databaseadministration");
        echo "    <h2><strong>$Error</strong></h2>\r\n";
        MakeHtmlPageBottom("");
        exit;
      }
    }
    else if (isset($Host) && isset($User) && isset($_GET["Revoke"]) && isset($_GET["Level"]) && isset($_GET["Privilege"]))
    {
      if (!oswebdb_revoke_privilege($Host, $User, $_GET["Level"], $_GET["Privilege"]))
      {
        $Error = oswebdb_error();
        MakeHtmlPageTop("Databaseadministration");
        echo "    <h2><strong>$Error</strong></h2>\r\n";
        MakeHtmlPageBottom("");
        exit;
      }
    }

    $TabIndex = 1;
    MakeHtmlPageTop("Databaseadministration");
    if (!(isset($Host) && isset($User)))
    {
      $UserTableName = oswebdb_get_user_table_name();
      $CurrentUsername = oswebdb_privilege_username();
      if ($Result = oswebdb_query("SELECT Host,User FROM $UserTableName WHERE User=\"$CurrentUsername\" ORDER BY User,Host"))
      {
        if ($Row = oswebdb_fetch_row($Result))
        {
          $Host = $Row[0];
          $User = $Row[1];
        }
        oswebdb_free_result($Result);
      }
    }
    if (isset($Host) && isset($User))
    {
      echo "    <form name=\"GrantsForm\" action=\"javascript:parent changeUserListBoxControl()\">\r\n";
      echo "      <input type=\"hidden\" name=\"Host\" value=\"$Host\">\r\n";
      echo "      <input type=\"hidden\" name=\"User\" value=\"$User\">\r\n";
      echo "      <table border=\"1\" cellpadding=\"2\" cellspacing=\"0\">\r\n";
      echo "        <tr><td valign=\"middle\" nowrap><strong>Rettigheder</strong></td><td valign=\"middle\" align=\"center\" nowrap><strong>Forespørge</strong><br>(SELECT)</td><td valign=\"middle\" align=\"center\" nowrap><strong>Oprette</strong><br>(INSERT)</td><td valign=\"middle\" align=\"center\" nowrap><strong>Opdatere</strong><br>(UPDATE)</td><td valign=\"middle\" align=\"center\" nowrap><strong>Slette</strong><br>(DELETE)</td><td valign=\"middle\" align=\"center\" nowrap><strong>Tildele rettigheder</strong><br>(GRANT)</td></tr>\r\n";
      $GlobalAllowSelect = oswebdb_allow_select("$User@$Host,*.*");
      $GlobalAllowInsert = oswebdb_allow_insert("$User@$Host,*.*");
      $GlobalAllowUpdate = oswebdb_allow_update("$User@$Host,*.*");
      $GlobalAllowDelete = oswebdb_allow_delete("$User@$Host,*.*");
      $GlobalAllowGrant = oswebdb_allow_grant("$User@$Host,*.*");
      $UserAllowGrant = oswebdb_allow_grant("*.*");
      $InputSelect = MakeHtmlInputCheckBox("", "", $GlobalAllowSelect, !$UserAllowGrant, $TabIndex++, "javascript:parent.changePrivilege(this.checked, '*.*', 'Select')", "");
      $InputInsert = MakeHtmlInputCheckBox("", "", $GlobalAllowInsert, !$UserAllowGrant, $TabIndex++, "javascript:parent.changePrivilege(this.checked, '*.*', 'Insert')", "");
      $InputUpdate = MakeHtmlInputCheckBox("", "", $GlobalAllowUpdate, !$UserAllowGrant, $TabIndex++, "javascript:parent.changePrivilege(this.checked, '*.*', 'Update')", "");
      $InputDelete = MakeHtmlInputCheckBox("", "", $GlobalAllowDelete, !$UserAllowGrant, $TabIndex++, "javascript:parent.changePrivilege(this.checked, '*.*', 'Delete')", "");
      $InputGrant = MakeHtmlInputCheckBox("", "", $GlobalAllowGrant, !$UserAllowGrant, $TabIndex++, "javascript:parent.changePrivilege(this.checked, '*.*', 'Grant option')", "");
      echo "        <tr><td valign=\"middle\" nowrap>Globalt niveau</td><td valign=\"middle\" align=\"center\" nowrap>$InputSelect</td><td valign=\"middle\" align=\"center\" nowrap>$InputInsert</td><td valign=\"middle\" align=\"center\" nowrap>$InputUpdate</td><td valign=\"middle\" align=\"center\" nowrap>$InputDelete</td><td valign=\"middle\" align=\"center\" nowrap>$InputGrant</td></tr>\r\n";
      $DatabaseAllowSelect = oswebdb_allow_select("$User@$Host,*");
      $DatabaseAllowInsert = oswebdb_allow_insert("$User@$Host,*");
      $DatabaseAllowUpdate = oswebdb_allow_update("$User@$Host,*");
      $DatabaseAllowDelete = oswebdb_allow_delete("$User@$Host,*");
      $DatabaseAllowGrant = oswebdb_allow_grant("$User@$Host,*");
      $UserAllowGrant = oswebdb_allow_grant("*");
      $InputSelect = MakeHtmlInputCheckBox("", "", $DatabaseAllowSelect, !$UserAllowGrant || ($DatabaseAllowSelect && $GlobalAllowSelect), $TabIndex++, "javascript:parent.changePrivilege(this.checked, '*', 'Select')", "");
      $InputInsert = MakeHtmlInputCheckBox("", "", $DatabaseAllowInsert, !$UserAllowGrant || ($DatabaseAllowInsert && $GlobalAllowInsert), $TabIndex++, "javascript:parent.changePrivilege(this.checked, '*', 'Insert')", "");
      $InputUpdate = MakeHtmlInputCheckBox("", "", $DatabaseAllowUpdate, !$UserAllowGrant || ($DatabaseAllowUpdate && $GlobalAllowUpdate), $TabIndex++, "javascript:parent.changePrivilege(this.checked, '*', 'Update')", "");
      $InputDelete = MakeHtmlInputCheckBox("", "", $DatabaseAllowDelete, !$UserAllowGrant || ($DatabaseAllowDelete && $GlobalAllowDelete), $TabIndex++, "javascript:parent.changePrivilege(this.checked, '*', 'Delete')", "");
      $InputGrant = MakeHtmlInputCheckBox("", "", $DatabaseAllowGrant, !$UserAllowGrant || ($DatabaseAllowGrant && $GlobalAllowGrant), $TabIndex++, "javascript:parent.changePrivilege(this.checked, '*', 'Grant option')", "");
      echo "        <tr><td valign=\"middle\" nowrap>Database niveau</td><td valign=\"middle\" align=\"center\" nowrap>$InputSelect</td><td valign=\"middle\" align=\"center\" nowrap>$InputInsert</td><td valign=\"middle\" align=\"center\" nowrap>$InputUpdate</td><td valign=\"middle\" align=\"center\" nowrap>$InputDelete</td><td valign=\"middle\" align=\"center\" nowrap>$InputGrant</td></tr>\r\n";
      if ($TableResult = oswebdb_query("SHOW TABLES"))
      {
        while ($TableRow = oswebdb_fetch_row($TableResult))
        {
          $TableName = ucfirst($TableRow[0]);
          $TableAllowSelect = oswebdb_allow_select("$User@$Host,$TableRow[0]");
          $TableAllowInsert = oswebdb_allow_insert("$User@$Host,$TableRow[0]");
          $TableAllowUpdate = oswebdb_allow_update("$User@$Host,$TableRow[0]");
          $TableAllowDelete = oswebdb_allow_delete("$User@$Host,$TableRow[0]");
          $TableAllowGrant = oswebdb_allow_grant("$User@$Host,$TableRow[0]");
          $UserAllowGrant = oswebdb_allow_grant("$TableRow[0]");
          $InputSelect = MakeHtmlInputCheckBox("", "", $TableAllowSelect, !$UserAllowGrant || ($TableAllowSelect && $DatabaseAllowSelect), $TabIndex++, "javascript:parent.changePrivilege(this.checked, '$TableRow[0]', 'Select')", "");
          $InputInsert = MakeHtmlInputCheckBox("", "", $TableAllowInsert, !$UserAllowGrant || ($TableAllowInsert && $DatabaseAllowInsert), $TabIndex++, "javascript:parent.changePrivilege(this.checked, '$TableRow[0]', 'Insert')", "");
          $InputUpdate = MakeHtmlInputCheckBox("", "", $TableAllowUpdate, !$UserAllowGrant || ($TableAllowUpdate && $DatabaseAllowUpdate), $TabIndex++, "javascript:parent.changePrivilege(this.checked, '$TableRow[0]', 'Update')", "");
          $InputDelete = MakeHtmlInputCheckBox("", "", $TableAllowDelete, !$UserAllowGrant || ($TableAllowDelete && $DatabaseAllowDelete), $TabIndex++, "javascript:parent.changePrivilege(this.checked, '$TableRow[0]', 'Delete')", "");
          $InputGrant = MakeHtmlInputCheckBox("", "", $TableAllowGrant, !$UserAllowGrant || ($TableAllowGrant && $DatabaseAllowGrant), $TabIndex++, "javascript:parent.changePrivilege(this.checked, '$TableRow[0]', 'Grant option')", "");
          echo "        <tr><td valign=\"middle\" nowrap>$TableName</td><td valign=\"middle\" align=\"center\" nowrap>$InputSelect</td><td valign=\"middle\" align=\"center\" nowrap>$InputInsert</td><td valign=\"middle\" align=\"center\" nowrap>$InputUpdate</td><td valign=\"middle\" align=\"center\" nowrap>$InputDelete</td><td valign=\"middle\" align=\"center\" nowrap>$InputGrant</td></tr>\r\n";
        }
        oswebdb_free_result($TableResult);
      }
      echo "      </table>\r\n";
      echo "    </form>\r\n";
    }
    MakeHtmlPageBottom();
  }
?>
