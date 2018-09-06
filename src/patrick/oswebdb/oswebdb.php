<?php
  // Include required files.
  require_once("misc.php");

  // Functions for the oswebdb.
  function oswebdb_getusername()
  {
    if (session_start())
    {
      if (!isset($_SESSION["oswebdb_username"]))
      {
        session_unset();
        session_destroy();
      }
    }
    return oswebdb_decrypt($_SESSION["oswebdb_username"]);
  }

  function oswebdb_setusername($UserName)
  {
    if (session_start())
    {
      if (!isset($_SESSION["oswebdb_username"]))
      {
        $_SESSION["oswebdb_username"] = "";
      }
    }
    $_SESSION["oswebdb_username"] = oswebdb_encrypt($UserName);
  }

  function oswebdb_clearusername()
  {
    if (session_start())
    {
      if (isset($_SESSION["oswebdb_username"]))
        unset($_SESSION["oswebdb_username"]);
      session_unset();
      session_destroy();
    }
  }

  function oswebdb_getpassword()
  {
    if (session_start())
    {
      if (!isset($_SESSION["oswebdb_password"]))
      {
        session_unset();
        session_destroy();
      }
    }
    return oswebdb_decrypt($_SESSION["oswebdb_password"]);
  }

  function oswebdb_setpassword($Password)
  {
    if (session_start())
    {
      if (!isset($_SESSION["oswebdb_password"]))
      {
        $_SESSION["oswebdb_password"] = "";
      }
    }
    $_SESSION["oswebdb_password"] = oswebdb_encrypt($Password);
  }

  function oswebdb_clearpassword()
  {
    if (session_start())
    {
      if (isset($_SESSION["oswebdb_password"]))
        unset($_SESSION["oswebdb_password"]);
      session_unset();
      session_destroy();
    }
  }

  function oswebdb_encrypt($s)
  {
    if (strlen($s) > 0)
    {
      $Result = "";
      for ($i = 0; $i < strlen($s); $i++)
        $Result = sprintf("$Result%02X", ord(chr(ord($s[$i]) + (77 * ($i + 1)))));
      return $Result;
    }
    else
      return $s;
  }

  function oswebdb_decrypt($s)
  {
    if (strlen($s) > 0)
    {
      $Result = ""; $i = 0;
      while (strlen($s) > 0)
      {
        $Result = sprintf("$Result%c", ord(chr(base_convert(substr($s, 0, 2), 16, 10))) - (77 * ($i++ + 1)));
        $s = substr($s, 2, strlen($s) - 2);
      }
      return $Result;
    }
    else
      return $s;
  }

  function oswebdb_authorize($UserName, $Password)
  {
    $HostName = GetConfigValue("HostName");
    if (!isset($UserName) || strlen($Password) == 0)
    {
      MakeHtmlPageTop("401 Authorization Required");
      if (GetConfigValue("ErrorReporting") > 1)
        echo "    <h2><strong>401 Authorization Required</strong></h2>You must be authenticated to use $HostName (Error = Password not submitted)\r\n";
      else
        echo "    <h2><strong>401 Authorization Required</strong></h2>You must be authenticated to use $HostName\r\n";
      MakeHtmlPageBottom();
      exit;
    }
    else if (!oswebdb_connect($UserName, $Password))
    {
      MakeHtmlPageTop("401 Authorization Required");
      if (GetConfigValue("ErrorReporting") > 1)
        echo "    <h2><strong>401 Authorization Required</strong></h2>You must be authenticated to use $HostName (Error = Can't connect to the MySQL server)\r\n";
      else
        echo "    <h2><strong>401 Authorization Required</strong></h2>You must be authenticated to use $HostName\r\n";
      MakeHtmlPageBottom();
      exit;
    }
    oswebdb_setusername($UserName);
    oswebdb_setpassword($Password);
    return 1;
  }

  function oswebdb_setmysqllink($MySqlLink)
  {
    $GLOBALS["MySqlLink"] = $MySqlLink;
  }

  function oswebdb_getmysqllink()
  {
    return $GLOBALS["MySqlLink"];
  }

  function oswebdb_connect($UserName, $Password)
  {
    $Result = 0;
    if (isset($UserName) && isset($Password))
      $MySqlLink = mysqli_connect((string) GetConfigValue("MySqlHost"), $UserName, $Password);
    else
      $MySqlLink = mysqli_connect((string) GetConfigValue("MySqlHost"));
    if ($MySqlLink)
    {
      oswebdb_setmysqllink($MySqlLink);
      $Result = 1;
    }
    return $Result;
  }

  function oswebdb_close()
  {
    return mysqli_close(oswebdb_getmysqllink());
  }

  function oswebdb_selectdb()
  {
    return mysqli_select_db(oswebdb_getmysqllink(), (string) GetConfigValue("MySqlDatabase"));
  }

  function oswebdb_get_rows($Statement)
  {
    $Rows = 0;
    if ($Result = oswebdb_query(oswebdb_getmysqllink(), $Statement))
    {
      $Rows = oswebdb_num_rows($Result);
      oswebdb_free_result($Result);
    }
    return $Rows;
  }

  function oswebdb_query($Statement)
  {
    $Result = mysqli_query(oswebdb_getmysqllink(), $Statement);
    if (!$Result)
    {
      if (GetConfigValue("ErrorReporting") > 1)
      {
        $Error = oswebdb_error();
        exit($Error);
      }
    }
    return $Result;
  }

  function oswebdb_query_without_exit($Statement)
  {
    return mysqli_query(oswebdb_getmysqllink(), $Statement);
  }

  function oswebdb_fetch_row($Result)
  {
    return mysqli_fetch_row($Result);
  }

  function oswebdb_free_result($Result)
  {
    return mysqli_free_result($Result);
  }

  function oswebdb_error()
  {
    return mysqli_error(oswebdb_getmysqllink());
  }

  function oswebdb_field_name($Result, $FieldNo)
  {
    $Properties = mysqli_fetch_field_direct($Result, $FieldNo);
    return is_object($Properties) ? $Properties->name : null;
  }

  function oswebdb_field_len($Result, $FieldNo)
  {
    $Properties = mysqli_fetch_field_direct($Result, $FieldNo);
    return is_object($Properties) ? $Properties->length : null;
  }

  function oswebdb_affected_rows()
  {
    return mysqli_affected_rows(oswebdb_getmysqllink());
  }

  function oswebdb_num_rows($Result)
  {
    return mysqli_num_rows($Result);
  }

  function oswebdb_num_fields($Result)
  {
    return mysqli_num_fields($Result);
  }

  function oswebdb_privilege_values(&$PrivilegeHostname, &$PrivilegeUsername, &$PrivilegeDatabase, &$PrivilegeVersion)
  {
    $PrivilegeHostname = "";
    $PrivilegeUsername = "";
    $PrivilegeDatabase = "";
    $PrivilegeVersion = "";
    // Get the current Hostname, Username, Database and Version.
    if ($Result = oswebdb_query("SELECT USER(),DATABASE(),VERSION()"))
    {
      if ($Row = oswebdb_fetch_row($Result))
      {
        if ($Pos = strpos($Row[0], "@"))
        {
          $PrivilegeUsername = substr($Row[0], 0, $Pos);
          $PrivilegeHostname = substr($Row[0], $Pos + 1, strlen($Row[0]) - $Pos);
        }
        else
          $PrivilegeUsername = $Row[0];
        $PrivilegeDatabase = $Row[1];
        if ($Pos = strpos($Row[2], "."))
          $PrivilegeVersion = substr($Row[2], 0, $Pos);
        else
          $PrivilegeVersion = $Row[2];
      }
      oswebdb_free_result($Result);
    }
  }

  function oswebdb_privilege_hostname($Default)
  {
    if (!$Default)
    {
      oswebdb_privilege_values($PrivilegeHostname, $PrivilegeUsername, $PrivilegeDatabase, $PrivilegeVersion);
      return $PrivilegeHostname;
    }
    else
      return "%";
  }

  function oswebdb_privilege_username()
  {
    oswebdb_privilege_values($PrivilegeHostname, $PrivilegeUsername, $PrivilegeDatabase, $PrivilegeVersion);
    return $PrivilegeUsername;
  }

  function oswebdb_privilege_database()
  {
    oswebdb_privilege_values($PrivilegeHostname, $PrivilegeUsername, $PrivilegeDatabase, $PrivilegeVersion);
    return $PrivilegeDatabase;
  }

  function oswebdb_privileges($TableName)
  {
    $PrivilegeValue = 0;
    oswebdb_privilege_values($PrivilegeHostname, $PrivilegeUsername, $PrivilegeDatabase, $PrivilegeVersion);
    if ($Pos = strpos($TableName, ","))
    {
      $PrivilegeUsername = substr($TableName, 0, $Pos);
      $TableName = substr($TableName, $Pos + 1, strlen($TableName) - $Pos);
      if ($Pos = strpos($PrivilegeUsername, "@"))
      {
        $PrivilegeHostname = substr($PrivilegeUsername, $Pos + 1, strlen($PrivilegeUsername) - $Pos);
        $PrivilegeUsername = substr($PrivilegeUsername, 0, $Pos);
      }
    }
    if ($Pos = strpos($TableName, "."))
    {
      $PrivilegeDatabase = substr($TableName, 0, $Pos);
      $TableName = substr($TableName, $Pos + 1, strlen($TableName) - $Pos);
    }
    // Get privileges for the current user on TableName.
    if (strlen($PrivilegeHostname) > 0 && strlen($PrivilegeUsername) > 0 && strlen($PrivilegeDatabase) > 0 && strlen($PrivilegeVersion) > 0)
    {
      $Result = oswebdb_query_without_exit("SHOW GRANTS FOR \"$PrivilegeUsername\"@\"$PrivilegeHostname\"");
      if (!$Result)
      {
        $PrivilegeHostname = oswebdb_privilege_hostname(1);
        $Result = oswebdb_query("SHOW GRANTS FOR \"$PrivilegeUsername\"@\"$PrivilegeHostname\"");
      }
      if ($Result)
      {
        unset($TableValue);
        unset($DatabaseValue);
        unset($UserValue);
        while ($Row = oswebdb_fetch_row($Result))
        {
          $Find = "ON $PrivilegeDatabase.$TableName";
          if ((int) $PrivilegeVersion >= 4)
            $Find = "ON `$PrivilegeDatabase`.`$TableName`";
          if (stristr($Row[0], $Find))
          {
            $TableValue = 0;
            if (stristr($Row[0], "SELECT") || stristr($Row[0], "ALL PRIVILEGES"))
              $TableValue += 1;
            if (stristr($Row[0], "INSERT") || stristr($Row[0], "ALL PRIVILEGES"))
              $TableValue += 2;
            if (stristr($Row[0], "UPDATE") || stristr($Row[0], "ALL PRIVILEGES"))
              $TableValue += 4;
            if (stristr($Row[0], "DELETE") || stristr($Row[0], "ALL PRIVILEGES"))
              $TableValue += 8;
            if (stristr($Row[0], "WITH GRANT OPTION") || stristr($Row[0], "ALL PRIVILEGES"))
              $TableValue += 64;
            if (stristr($Row[0], "ALL PRIVILEGES"))
              $TableValue += 128;
          }
          $Find = "ON $PrivilegeDatabase.*";
          if ((int) $PrivilegeVersion >= 4)
            $Find = "ON `$PrivilegeDatabase`.*";
          if (stristr($Row[0], $Find))
          {
            $DatabaseValue = 0;
            if (stristr($Row[0], "SELECT") || stristr($Row[0], "ALL PRIVILEGES"))
              $DatabaseValue += 1;
            if (stristr($Row[0], "INSERT") || stristr($Row[0], "ALL PRIVILEGES"))
              $DatabaseValue += 2;
            if (stristr($Row[0], "UPDATE") || stristr($Row[0], "ALL PRIVILEGES"))
              $DatabaseValue += 4;
            if (stristr($Row[0], "DELETE") || stristr($Row[0], "ALL PRIVILEGES"))
              $DatabaseValue += 8;
            if (stristr($Row[0], "WITH GRANT OPTION") || stristr($Row[0], "ALL PRIVILEGES"))
              $DatabaseValue += 64;
            if (stristr($Row[0], "ALL PRIVILEGES"))
              $DatabaseValue += 128;
          }
          $Find = "ON *.*";
          if (stristr($Row[0], $Find))
          {
            $UserValue = 0;
            if (stristr($Row[0], "SELECT") || stristr($Row[0], "ALL PRIVILEGES"))
              $UserValue += 1;
            if (stristr($Row[0], "INSERT") || stristr($Row[0], "ALL PRIVILEGES"))
              $UserValue += 2;
            if (stristr($Row[0], "UPDATE") || stristr($Row[0], "ALL PRIVILEGES"))
              $UserValue += 4;
            if (stristr($Row[0], "DELETE") || stristr($Row[0], "ALL PRIVILEGES"))
              $UserValue += 8;
            if (stristr($Row[0], "WITH GRANT OPTION") || stristr($Row[0], "ALL PRIVILEGES"))
              $UserValue += 64;
            if (stristr($Row[0], "ALL PRIVILEGES"))
              $UserValue += 128;
          }
        }
        if (isset($TableValue))
          $PrivilegeValue += $TableValue;
        else if (isset($DatabaseValue))
          $PrivilegeValue += $DatabaseValue;
        else if (isset($UserValue))
          $PrivilegeValue += $UserValue;
        oswebdb_free_result($Result);
      }
    }
    return $PrivilegeValue;
  }

  function oswebdb_allow_select($TableName)
  {
    return oswebdb_privileges($TableName) & 1;
  }

  function oswebdb_allow_insert($TableName)
  {
    return oswebdb_privileges($TableName) & 2;
  }

  function oswebdb_allow_update($TableName)
  {
    return oswebdb_privileges($TableName) & 4;
  }

  function oswebdb_allow_delete($TableName)
  {
    return oswebdb_privileges($TableName) & 8;
  }

  function oswebdb_allow_grant($TableName)
  {
    return oswebdb_privileges($TableName) & 64;
  }

  function oswebdb_allow_all($TableName)
  {
    return oswebdb_privileges($TableName) & 128;
  }

  function oswebdb_flush_privileges()
  {
    return oswebdb_query("FLUSH PRIVILEGES");
  }

  function oswebdb_get_user_table_name()
  {
    return "mysql.user";
  }

  function oswebdb_insert_user($Host, $User, $Password, $SelectPrivilege, $InsertPrivilege, $UpdatePrivilege, $DeletePrivilege, $GrantPrivilege)
  {
    $Result = 1;
    $UserTableName = oswebdb_get_user_table_name();
    $Fields = "Host,User";
    $Values = "\"$Host\",\"$User\"";
    if (isset($Password))
    {
      $Fields = "$Fields,Password";
      $Values = "$Values,PASSWORD(\"$Password\")";
    }
    if (isset($SelectPrivilege))
    {
      $Fields = "$Fields,Select_priv";
      $Values = "$Values,\"$SelectPrivilege\"";
    }
    else
    {
      $Fields = "$Fields,Select_priv";
      $Values = "$Values,\"N\"";
    }
    if (isset($InsertPrivilege))
    {
      $Fields = "$Fields,Insert_priv";
      $Values = "$Values,\"$InsertPrivilege\"";
    }
    else
    {
      $Fields = "$Fields,Insert_priv";
      $Values = "$Values,\"N\"";
    }
    if (isset($UpdatePrivilege))
    {
      $Fields = "$Fields,Update_priv";
      $Values = "$Values,\"$UpdatePrivilege\"";
    }
    else
    {
      $Fields = "$Fields,Update_priv";
      $Values = "$Values,\"N\"";
    }
    if (isset($DeletePrivilege))
    {
      $Fields = "$Fields,Delete_priv";
      $Values = "$Values,\"$DeletePrivilege\"";
    }
    else
    {
      $Fields = "$Fields,Delete_priv";
      $Values = "$Values,\"N\"";
    }
    if (isset($GrantPrivilege))
    {
      $Fields = "$Fields,Grant_priv";
      $Values = "$Values,\"$GrantPrivilege\"";
    }
    else
    {
      $Fields = "$Fields,Grant_priv";
      $Values = "$Values,\"N\"";
    }
    if (!oswebdb_query("INSERT INTO $UserTableName ($Fields) VALUES($Values)"))
      $Result = 0;
    else
      $Result = oswebdb_flush_privileges();
    return $Result;
  }

  function oswebdb_update_user($Host, $User, $ResetPassword, $Password, $SelectPrivilege, $InsertPrivilege, $UpdatePrivilege, $DeletePrivilege, $GrantPrivilege)
  {
    $Result = 1;
    $UserTableName = oswebdb_get_user_table_name();
    $Set = "";
    if ($ResetPassword)
    {
      if (strlen($Set) > 0)
        $Set = "$Set,Password=PASSWORD(\"$Password\")";
      else
        $Set = "Password=PASSWORD(\"$Password\")";
    }
    if ($SelectPrivilege)
    {
      if (strlen($Set) > 0)
        $Set = "$Set,Select_priv=\"$SelectPrivilege\"";
      else
        $Set = "Select_priv=\"$SelectPrivilege\"";
    }
    else if (strlen($Set) > 0)
      $Set = "$Set,Select_priv=\"N\"";
    else
      $Set = "Select_priv=\"N\"";
    if ($InsertPrivilege)
    {
      if (strlen($Set) > 0)
        $Set = "$Set,Insert_priv=\"$InsertPrivilege\"";
      else
        $Set = "Insert_priv=\"$InsertPrivilege\"";
    }
    else if (strlen($Set) > 0)
      $Set = "$Set,Insert_priv=\"N\"";
    else
      $Set = "Insert_priv=\"N\"";
    if ($UpdatePrivilege)
    {
      if (strlen($Set) > 0)
        $Set = "$Set,Update_priv=\"$UpdatePrivilege\"";
      else
        $Set = "Update_priv=\"$UpdatePrivilege\"";
    }
    else if (strlen($Set) > 0)
      $Set = "$Set,Update_priv=\"N\"";
    else
      $Set = "Update_priv=\"N\"";
    if ($DeletePrivilege)
    {
      if (strlen($Set) > 0)
        $Set = "$Set,Delete_priv=\"$DeletePrivilege\"";
      else
        $Set = "Delete_priv=\"$DeletePrivilege\"";
    }
    else if (strlen($Set) > 0)
      $Set = "$Set,Delete_priv=\"N\"";
    else
      $Set = "Delete_priv=\"N\"";
    if ($GrantPrivilege)
    {
      if (strlen($Set) > 0)
        $Set = "$Set,Grant_priv=\"$GrantPrivilege\"";
      else
        $Set = "Grant_priv=\"$GrantPrivilege\"";
    }
    else if (strlen($Set) > 0)
      $Set = "$Set,Grant_priv=\"N\"";
    else
      $Set = "Grant_priv=\"N\"";
    if (!oswebdb_query("UPDATE $UserTableName SET $Set WHERE Host=\"$Host\" AND User=\"$User\""))
      $Result = 0;
    else
      $Result = oswebdb_flush_privileges();
    return $Result;
  }

  function oswebdb_delete_user($Host, $User)
  {
    $Result = 1;
    $UserTableName = oswebdb_get_user_table_name();
    if (!oswebdb_query("DELETE FROM mysql.columns_priv WHERE Host=\"$Host\" AND User=\"$User\""))
      $Result = 0;
    else if (!oswebdb_query("DELETE FROM mysql.tables_priv WHERE Host=\"$Host\" AND User=\"$User\""))
      $Result = 0;
    else if (!oswebdb_query("DELETE FROM mysql.db WHERE Host=\"$Host\" AND User=\"$User\""))
      $Result = 0;
    else if (!oswebdb_query("DELETE FROM $UserTableName WHERE Host=\"$Host\" AND User=\"$User\""))
      $Result = 0;
    else
      $Result = oswebdb_flush_privileges();
    return $Result;
  }

  function oswebdb_grant_privilege($Host, $User, $Level, $Privilege)
  {
    $Result = 1;
    if (stristr($Privilege, "Select") == false)
    {
      if (oswebdb_allow_select("$User@$Host,$Level"))
        $Privilege = "$Privilege,Select";
    }
    if (stristr($Privilege, "Insert") == false)
    {
      if (oswebdb_allow_insert("$User@$Host,$Level"))
        $Privilege = "$Privilege,Insert";
    }
    if (stristr($Privilege, "Update") == false)
    {
      if (oswebdb_allow_update("$User@$Host,$Level"))
        $Privilege = "$Privilege,Update";
    }
    if (stristr($Privilege, "Delete") == false)
    {
      if (oswebdb_allow_delete("$User@$Host,$Level"))
        $Privilege = "$Privilege,Delete";
    }
    if (stristr($Privilege, "Grant option") == false)
    {
      if (oswebdb_allow_grant("$User@$Host,$Level"))
        $Privilege = "$Privilege,Grant option";
    }
    if (!oswebdb_query("GRANT $Privilege ON $Level TO \"$User\"@\"$Host\""))
      $Result = 0;
    else
      $Result = oswebdb_flush_privileges();
    return $Result;
  }

  function oswebdb_revoke_privilege($Host, $User, $Level, $Privilege)
  {
    $Result = 1;
    if (!oswebdb_query("REVOKE $Privilege ON $Level FROM \"$User\"@\"$Host\""))
      $Result = 0;
    else
      $Result = oswebdb_flush_privileges();
    return $Result;
  }

  function oswebdb_change_password($User, $Password)
  {
  	$Result = 1;
  	if (oswebdb_query("SET PASSWORD FOR $User = PASSWORD(\"$Password\")"))
  	{
  		if ($User == oswebdb_getusername())
  		  oswebdb_setpassword($Password);
  	}
  	else
  	  $Result = 0;
  	return $Result;
  }

  function oswebdb_is_user_in_role($RoleUsers)
  {
  	$Result = 0;
  	if (strlen($RoleUsers) > 0)
  	{
  		$CurrentUser = oswebdb_privilege_username();
  		$RoleUsers = explode(";", $RoleUsers); $i = 0;
  		while ($i < count($RoleUsers) && !$Result)
  		{
  			if (strlen(trim($RoleUsers[$i])) > 0)
    			$Result = trim($RoleUsers[$i]) == $CurrentUser ? 1 : 0;
  			$i++;
  		}
  	}
  	return $Result;
  }

  function oswebdb_is_administrator($SystemNo)
  {
  	$Result = 0;
  	if ($RoleResult = oswebdb_query("SELECT s.AdminRole,st.Text1 FROM Systems AS s LEFT JOIN Systemtables st ON st.SystemNo=0 AND st.TableNo=10 AND st.No=s.AdminRole WHERE s.SystemNo=$SystemNo"))
  	{
  		if ($RoleRow = oswebdb_fetch_row($RoleResult))
  		{
  			if ($RoleRow[0] != 0)
  				$Result = oswebdb_is_user_in_role($RoleRow[1]);
    		else
    		  $Result = 1;
    	}
  		oswebdb_free_result($RoleResult);
  	}
  	return $Result;
  }

  function oswebdb_is_configurator($SystemNo)
  {
  	$Result = 0;
  	if ($RoleResult = oswebdb_query("SELECT s.ConfigRole,st.Text1 FROM Systems AS s LEFT JOIN Systemtables st ON st.SystemNo=0 AND st.TableNo=10 AND st.No=s.ConfigRole WHERE s.SystemNo=$SystemNo"))
  	{
  		if ($RoleRow = oswebdb_fetch_row($RoleResult))
  		{
  			if ($RoleRow[0] != 0)
  				$Result = oswebdb_is_user_in_role($RoleRow[1]);
    		else
    		  $Result = 1;
    	}
  		oswebdb_free_result($RoleResult);
  	}
  	return $Result;
  }

  function oswebdb_is_user($SystemNo)
  {
  	$Result = 0;
  	if ($RoleResult = oswebdb_query("SELECT s.UserRole,st.Text1 FROM Systems AS s LEFT JOIN Systemtables st ON st.SystemNo=0 AND st.TableNo=10 AND st.No=s.UserRole WHERE s.SystemNo=$SystemNo"))
  	{
  		if ($RoleRow = oswebdb_fetch_row($RoleResult))
  		{
  			if ($RoleRow[0] != 0)
  				$Result = oswebdb_is_user_in_role($RoleRow[1]);
    		else
    		  $Result = 1;
    	}
  		oswebdb_free_result($RoleResult);
  	}
  	return $Result;
  }
?>
