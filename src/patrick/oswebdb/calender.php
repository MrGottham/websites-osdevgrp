<?php
  // Include required files.
  require_once("oswebdb/oswebdb.php");

  // Functions for the calender tables.
  function insert_appointment($SystemNo, $Date, $FromTime, $ToTime, $Properties, $Subject, $Note, $UserIds)
  {
    $Result = 1;
    $CalId = 1;
    if ($AppointmentResult = oswebdb_query("SELECT CalId FROM Calapps WHERE SystemNo=$SystemNo ORDER BY SystemNo,CalId DESC"))
    {
      if ($AppointmentRow = oswebdb_fetch_row($AppointmentResult))
        $CalId = $AppointmentRow[0] + 1;
      oswebdb_free_result($AppointmentResult);
    }
    if (!oswebdb_query("INSERT INTO Calapps (SystemNo,CalId,Date,FromTime,ToTime,Properties,Subject,Note) VALUES($SystemNo,$CalId,\"$Date\",\"$FromTime\",\"$ToTime\",$Properties,\"$Subject\",\"$Note\")"))
      $Result = 0;
    if ($Result)
    {
      $UserIds = explode(",", $UserIds); $i = 0;
      while ($Result && $i < count($UserIds))
      {
        if (!oswebdb_query("INSERT INTO Calmerge (SystemNo,CalId,UserId,Properties) VALUES($SystemNo,$CalId,$UserIds[$i],$Properties)"))
        {
          oswebdb_query("DELETE FROM Calmerge WHERE SystemNo=$SystemNo AND CalId=$CalId");
          oswebdb_query("DELETE FROM Calapps WHERE SystemNo=$SystemNo AND CalId=$CalId");
          $Result = 0;
        }
        $i++;
      }
      $UserIds = implode(",", $UserIds);
    }
    return $Result;
  }

  function update_appointment($SystemNo, $CalId, $Date, $FromTime, $ToTime, $Properties, $Subject, $Note, $UserIds)
  {
    $Result = 1;
    $Set = "";
    if (isset($Date))
    {
      if (strlen($Set) > 0)
        $Set = "$Set,Date=\"$Date\"";
      else
        $Set = "Date=\"$Date\"";
    }
    if (isset($FromTime))
    {
      if (strlen($Set) > 0)
        $Set = "$Set,FromTime=\"$FromTime\"";
      else
        $Set = "FromTime=\"$FromTime\"";
    }
    if (isset($ToTime))
    {
      if (strlen($Set) > 0)
        $Set = "$Set,ToTime=\"$ToTime\"";
      else
        $Set = "ToTime=\"$ToTime\"";
    }
    if (isset($Properties))
    {
      if (strlen($Set) > 0)
        $Set = "$Set,Properties=$Properties";
      else
        $Set = "Properties=$Properties";
    }
    else if (strlen($Set) > 0)
    {
      $Properties = 0;
      $Set = "$Set,Properties=$Properties";
    }
    else
    {
      $Properties = 0;
      $Set = "Properties=$Properties";
    }
    if (isset($Subject))
    {
      if (strlen($Set) > 0)
        $Set = "$Set,Subject=\"$Subject\"";
      else
        $Set = "Subject=\"$Subject\"";
    }
    if (isset($Note))
    {
      if (strlen($Set) > 0)
        $Set = "$Set,Note=\"$Note\"";
      else
        $Set = "Note=\"$Note\"";
    }
    if (!oswebdb_query("UPDATE Calapps SET $Set WHERE SystemNo=$SystemNo AND CalId=$CalId"))
      $Result = 0;
    else if (!oswebdb_query("DELETE FROM Calmerge WHERE SystemNo=$SystemNo AND CalId=$CalId"))
      $Result = 0;
    else if ($Result)
    {
      $UserIds = explode(",", $UserIds); $i = 0;
      while ($Result && $i < count($UserIds))
      {
        if (!oswebdb_query("INSERT INTO Calmerge (SystemNo,CalId,UserId,Properties) VALUES($SystemNo,$CalId,$UserIds[$i],$Properties)"))
          $Result = 0;
        $i++;
      }
      $UserIds = implode(",", $UserIds);
    }
    return $Result;
  }

  function update_appointment_user_properties($SystemNo, $CalId, $UserIds, $Properties)
  {
    $Result = 1;
    if (!oswebdb_query("UPDATE Calmerge SET Properties=$Properties WHERE SystemNo=$SystemNo AND CalId=$CalId AND UserId IN ($UserIds)"))
      $Result = 0;
    else if ($UserPropertiesResult = oswebdb_query("SELECT Properties FROM Calmerge WHERE SystemNo=$SystemNo AND CalId=$CalId"))
    {
      $UserProperties = 0;
      while ($UserPropertiesRow = oswebdb_fetch_row($UserPropertiesResult))
      {
        if (($UserPropertiesRow[0] & 1) && !($UserProperties & 1))
          $UserProperties += 1;
        if (($UserPropertiesRow[0] & 2) && !($UserProperties & 2))
          $UserProperties += 2;
        if (($UserPropertiesRow[0] & 4) && !($UserProperties & 4))
          $UserProperties += 4;
        if (($UserPropertiesRow[0] & 8) && !($UserProperties & 8))
          $UserProperties += 8;
        if (($UserPropertiesRow[0] & 16) && !($UserProperties & 16))
          $UserProperties += 16;
        if (($UserPropertiesRow[0] & 32) && !($UserProperties & 32))
          $UserProperties += 32;
      }
      oswebdb_free_result($UserPropertiesResult);
      if (($UserProperties & 4) && ($UserProperties & 8))
        $UserProperties -= 8;
      if (($UserProperties & 16) && ($UserProperties & 32))
        $UserProperties -= 16;
      if (!oswebdb_query("UPDATE Calapps SET Properties=$UserProperties WHERE SystemNo=$SystemNo AND CalId=$CalId"))
        $Result = 0;
    }
    else
      $Result = 0;
    return $Result;
  }

  function delete_appointment($SystemNo, $Method, $CalId, $UserIds)
  {
    $Result = 1;
    switch ((integer) $Method)
    {
      case 1:
        if (!oswebdb_query("DELETE FROM Calmerge WHERE SystemNo=$SystemNo AND CalId=$CalId AND UserId IN ($UserIds)"))
          $Result = 0;
        break;

      case 2:
        if (!oswebdb_query("DELETE FROM Calmerge WHERE SystemNo=$SystemNo AND CalId=$CalId"))
          $Result = 0;
        break;
    }
    if ($Result && oswebdb_get_rows("SELECT SystemNo,CalId,UserId,Properties FROM Calmerge WHERE SystemNo=$SystemNo AND CalId=$CalId") == 0)
    {
      if (!oswebdb_query("DELETE FROM Calapps WHERE SystemNo=$SystemNo AND CalId=$CalId"))
        $Result = 0;
    }
    return $Result;
  }

  function insert_user($SystemNo, $UserId, $UserName, $Name, $Initials)
  {
    $Result = 1;
    $Fields = "SystemNo,UserId";
    $Values = "$SystemNo,$UserId";
    if (isset($UserName))
    {
      $Fields = "$Fields,UserName";
      $Values = "$Values,\"$UserName\"";
    }
    if (isset($Name))
    {
      $Fields = "$Fields,Name";
      $Values = "$Values,\"$Name\"";
    }
    if (isset($Initials))
    {
      $Fields = "$Fields,Initials";
      $Values = "$Values,\"$Initials\"";
    }
    if (!oswebdb_query("INSERT INTO Calusers ($Fields) VALUES($Values)"))
      $Result = 0;
    return $Result;
  }

  function update_user($SystemNo, $UserId, $UserName, $Name, $Initials)
  {
    $Result = 1;
    $Set = "";
    if (isset($UserName))
    {
      if (strlen($Set) > 0)
        $Set = "$Set,UserName=\"$UserName\"";
      else
        $Set = "UserName=\"$UserName\"";
    }
    if (isset($Name))
    {
      if (strlen($Set) > 0)
        $Set = "$Set,Name=\"$Name\"";
      else
        $Set = "Name=\"$Name\"";
    }
    if (isset($Initials))
    {
      if (strlen($Set) > 0)
        $Set = "$Set,Initials=\"$Initials\"";
      else
        $Set = "Initials=\"$Initials\"";
    }
    if (!oswebdb_query("UPDATE Calusers SET $Set WHERE SystemNo=$SystemNo AND UserId=$UserId"))
      $Result = 0;
    return $Result;
  }

  function delete_user($SystemNo, $UserId)
  {
    $Result = 1;
    if ($AppointmentResult = oswebdb_query("SELECT CalId FROM Calmerge WHERE SystemNo=$SystemNo AND UserId=$UserId"))
    {
      while ($Result && $AppointmentRow = oswebdb_fetch_row($AppointmentResult))
        $Result = delete_appointment($SystemNo, 1, $AppointmentRow[0], $UserId);
      oswebdb_free_result($AppointmentResult);
    }
    else
      $Result = 0;
    if ($Result)
    {
      if (!oswebdb_query("DELETE FROM Calusers WHERE SystemNo=$SystemNo AND UserId=$UserId"))
        $Result = 0;
    }
    return $Result;
  }

  function get_appointment_ids($SystemNo, $Date, $UserIds)
  {
    $AppointmentIds = "";
    if (isset($UserIds) && strlen($UserIds) > 0)
    {
      if ($Result = oswebdb_query("SELECT ca.CalId FROM Calapps AS ca, Calmerge AS cm WHERE ca.SystemNo=$SystemNo AND ca.Date=\"$Date\" AND cm.SystemNo=$SystemNo AND cm.CalId=ca.CalId AND cm.UserId IN ($UserIds) GROUP BY ca.CalId ORDER BY ca.SystemNo,ca.Date,ca.FromTime,ca.ToTime,ca.CalId"))
      {
        while ($Row = oswebdb_fetch_row($Result))
        {
          if (strlen($AppointmentIds) > 0)
            $AppointmentIds = "$AppointmentIds,$Row[0]";
          else
            $AppointmentIds = "$Row[0]";
        }
        oswebdb_free_result($Result);
      }
    }
    return $AppointmentIds;
  }

  function get_appointment_content($SystemNo, $CalId, $Username, $Password, $PrivateUserIds)
  {
    $AppointmentContent = array(0, 0, 0, "", "", "", "", "", 0, "", "", "");
    if ($Result = oswebdb_query("SELECT SystemNo,CalId,Date,FromTime,ToTime,Properties,Subject,Note FROM Calapps WHERE SystemNo=$SystemNo AND CalId=$CalId"))
    {
      if ($Row = oswebdb_fetch_row($Result))
      {
        $Accessible = 0;
        $FromTime = substr($Row[3], 0, 5);
        $ToTime = substr($Row[4], 0, 5);
        $UserIds = "";
        $Usernames = "";
        if ($UserResult = oswebdb_query("SELECT cu.UserId,cu.Name FROM Calmerge AS cm, Calusers AS cu WHERE cm.SystemNo=$SystemNo AND cm.CalId=$CalId AND cu.SystemNo=$SystemNo AND cu.UserId=cm.UserId GROUP BY cu.UserId ORDER By cu.Name,cu.UserId"))
        {
          $PrivateUserIds = explode(",", $PrivateUserIds);
          while ($UserRow = oswebdb_fetch_row($UserResult))
          {
            if (!$Accessible)
            {
              $i = 0;
              while (!$Accessible && $i < count($PrivateUserIds))
              {
                $Accessible = $UserRow[0] == $PrivateUserIds[$i];
                $i++;
              }
            }
            if (strlen($UserIds) > 0)
              $UserIds = "$UserIds,$UserRow[0]";
            else
              $UserIds = "$UserRow[0]";
            if (strlen($Usernames) > 0)
              $Usernames = "$Usernames<br>$UserRow[1]";
            else
              $Usernames = "$UserRow[1]";
          }
          oswebdb_free_result($UserResult);
        }
        $Properties = $Row[5];
        $Public = $Properties & 1;
        $Private = $Properties & 2;
        $Bell = $Properties & 4;
        $Done = $Properties & 8;
        $Export = $Properties & 16;
        $Exported = $Properties & 32;
        $PropertiesText = "";
        if ($Bell)
        {
          if (strlen($PropertiesText) > 0)
            $PropertiesText = "$PropertiesText,A";
          else
            $PropertiesText = "A";
        }
        if ($Export || $Exported)
        {
          if (strlen($PropertiesText) > 0)
            $PropertiesText = "$PropertiesText,E";
          else
            $PropertiesText = "E";
        }
        if ($Public)
        {
          if (strlen($PropertiesText) > 0)
            $PropertiesText = "$PropertiesText,O";
          else
            $PropertiesText = "O";
        }
        if ($Private)
        {
          if (strlen($PropertiesText) > 0)
            $PropertiesText = "$PropertiesText,P";
          else
            $PropertiesText = "P";
        }
        if (strlen($PropertiesText) == 0)
          $PropertiesText = "&nbsp;";
        $Subject = $Row[6];
        $Note = str_replace("\r\n", "<br>", $Row[7]);
        if (!$Public && !isset($Username) && !isset($Password) && !$Accessible)
        {
          $Subject = "(Ikke offentliggjort for anonyme brugere)";
          $Note = "(Ikke offentliggjort for anonyme brugere)";
        }
        else if ($Private && !$Accessible)
        {
          $Subject = "(Privat)";
          $Note = "(Privat)";
        }
        $AppointmentContent = array($Row[0], $Row[1], $Accessible, "$Row[2]", "$FromTime", "$ToTime", "$UserIds", "$Usernames", $Properties, $PropertiesText, $Subject, $Note);
      }
      oswebdb_free_result($Result);
    }
    return $AppointmentContent;
  }

  function get_appointment_bells($SystemNo, $Date, $Time, $UserIds)
  {
    $AppointmentBells = "";
    if (isset($UserIds) && strlen($UserIds) > 0)
    {
      if ($Result = oswebdb_query("SELECT ca.CalId FROM Calapps AS ca, Calmerge AS cm WHERE ca.SystemNo=$SystemNo AND (ca.Date<\"$Date\" OR (ca.Date=\"$Date\" AND ca.FromTime<=\"$Time\")) AND cm.SystemNo=$SystemNo AND cm.CalId=ca.CalId AND cm.UserId IN ($UserIds) AND (cm.Properties & 4) GROUP BY ca.CalId ORDER BY ca.SystemNo,ca.Date,ca.FromTime,ca.ToTime,ca.CalId"))
      {
        while ($Row = oswebdb_fetch_row($Result))
        {
          if (strlen($AppointmentBells) > 0)
            $AppointmentBells = "$AppointmentBells,$Row[0]";
          else
            $AppointmentBells = "$Row[0]";
        }
        oswebdb_free_result($Result);
      }
    }
    return $AppointmentBells;
  }

  function get_private_user_ids($SystemNo)
  {
    $ID = "";
    $PrivateUsername = oswebdb_privilege_username();
    if ($Result = oswebdb_query("SELECT UserId FROM Calusers WHERE SystemNo=$SystemNo AND UserName=\"$PrivateUsername\" ORDER BY UserId"))
    {
      while ($Row = oswebdb_fetch_row($Result))
      {
        if (strlen($ID) > 0)
          $ID = "$ID,$Row[0]";
        else
          $ID = "$Row[0]";
      }
      oswebdb_free_result($Result);
    }
    return $ID;
  }

  function get_name_from_user_id($SystemNo, $UserId)
  {
    $Name = "";
    if ($Result = oswebdb_query("SELECT Name FROM Calusers WHERE SystemNo=$SystemNo AND UserId=$UserId"))
    {
      if ($Row = oswebdb_fetch_row($Result))
        $Name = $Row[0];
      oswebdb_free_result($Result);
    }
    return $Name;
  }

  function rebuild_calender($SystemNo)
  {
    $Result = 1;
    $DeleteTo = dec_date(getdate(time()), 90);
    $DeleteTo = date("Y-m-d", mktime(0, 0, 0, $DeleteTo['mon'], $DeleteTo['mday'], $DeleteTo['year']));
    if ($AppointmentResult = oswebdb_query("SELECT CalId FROM Calapps WHERE SystemNo=$SystemNo AND Date<\"$DeleteTo\" ORDER BY SystemNo,CalId"))
    {
      while ($Result && $AppointmentRow = oswebdb_fetch_row($AppointmentResult))
        $Result = delete_appointment($SystemNo, 2, $AppointmentRow[0], NULL);
      oswebdb_free_result($AppointmentResult);
    }
    if ($Result)
    {
      if ($AppointmentResult = oswebdb_query("SELECT CalId FROM Calapps WHERE SystemNo=$SystemNo ORDER BY SystemNo,CalId"))
      {
        $NewCalId = 1;
        while ($Result && $AppointmentRow = oswebdb_fetch_row($AppointmentResult))
        {
          if ($AppointmentRow[0] > $NewCalId)
          {
            if (!oswebdb_query("UPDATE Calmerge SET CalId=$NewCalId WHERE SystemNo=$SystemNo AND CalId=$AppointmentRow[0]"))
              $Result = 0;
            else if (!oswebdb_query("UPDATE Calapps SET CalId=$NewCalId WHERE SystemNo=$SystemNo AND CalId=$AppointmentRow[0]"))
            {
              oswebdb_query("UPDATE Calmerge SET CalId=$AppointmentRow[0] WHERE SystemNo=$SystemNo AND CalId=$NewCalId");
              $Result = 0;
            }
          }
          $NewCalId++;
        }
        oswebdb_free_result($AppointmentResult);
      }
    }
    return $Result;
  }

  function export_calender($SystemNo, $Date, $Time, $UserIds)
  {
  	$Result = 1;
    if (isset($UserIds) && strlen($UserIds) > 0)
  	{
    	if ($AppointmentResult = oswebdb_query("SELECT ca.CalId,ca.Subject,ca.Date,ca.FromTime,ca.ToTime,ca.Note,cm.Properties FROM Calapps AS ca, Calmerge AS cm WHERE ca.SystemNo=$SystemNo AND (ca.Date>\"$Date\" OR (ca.Date=\"$Date\" AND ca.FromTime>=\"$Time\")) AND cm.SystemNo=$SystemNo AND cm.CalId=ca.CalId AND cm.UserId IN ($UserIds) AND (cm.Properties & 16) GROUP BY ca.CalId ORDER BY ca.SystemNo,ca.Date,ca.FromTime,ca.ToTime,ca.CalId"))
    	{
    		$FileName = tempnam("temp", "Cal");
    		$Pos = strrpos($FileName, '.');
    		if ($Pos)
    			$FileName = substr($FileName, 0, $Pos);
    		$FileName = "$FileName.csv";
    		if ($FileHandle = fopen($FileName, "w"))
    		{
    			fwrite($FileHandle, "\"Emne\",\"Startdato\",\"Starttidspunkt\",\"Slutdato\",\"Sluttidspunkt\",\"Påmindelse til/fra\",\"Påmindelsesdato\",\"Påmindelsesklokkeslæt\",\"Beskrivelse\",\"Privat\"\r\n");
    			while ($AppointmentRow = oswebdb_fetch_row($AppointmentResult))
    			{
    				$Bell = ($AppointmentRow[6] & 4 ? "Sand" : "Falsk");
    				$Private = ($AppointmentRow[6] & 1 ? "Falsk" : ($AppointmentRow[6] & 2 ? "Sand" : "Sand"));
    			  fwrite($FileHandle, "\"$AppointmentRow[1]\",\"$AppointmentRow[2]\",\"$AppointmentRow[3]\",\"$AppointmentRow[2]\",\"$AppointmentRow[4]\",\"$Bell\",\"$AppointmentRow[2]\",\"$AppointmentRow[3]\",\"$AppointmentRow[5]\",\"$Private\"\r\n");
    			  update_appointment_user_properties($SystemNo, $AppointmentRow[0], $UserIds, $AppointmentRow[6] - 16 + 32);
    			}
    			fclose($FileHandle);
    			$Target = basename($FileName);
    			header("Content-type: application/octet-stream");
    			header("Content-Disposition: attachment; filename=$Target");
    			if (readfile($FileName))
    			{
    			}
    			unlink($FileName);
    		}
    		oswebdb_free_result($AppointmentResult);
    	}
    }
  	return $Result;
  }

  // Functions for the calender.
  function get_date_as_string($Year, $Month, $Date)
  {
    $DayName = strtolower(get_day_name($Year, $Month, $Date));
    $MonthName = strtolower(get_month_name($Month));
    $Holiday = get_holiday($Year, $Month, $Date, $HolidayName);
    if (strlen($HolidayName) > 0)
      return "$DayName den $Date. $MonthName $Year - $HolidayName";
    else
      return "$DayName den $Date. $MonthName $Year";
  }

  function get_day_name($Year, $Month, $Date)
  {
    $Result = "";
    switch (get_day_of_week($Year, $Month, $Date))
    {
      case 0:
        $Result = "Søndag";
        break;

      case 1:
        $Result = "Mandag";
        break;

      case 2:
        $Result = "Tirsdag";
        break;

      case 3:
        $Result = "Onsdag";
        break;

      case 4:
        $Result = "Torsdag";
        break;

      case 5:
        $Result = "Fredag";
        break;

      case 6:
        $Result = "Lørdag";
        break;
    }
    return $Result;
  }

  function get_day_of_week($Year, $Month, $Date)
  {
    $Date = getdate(mktime(0, 0, 0, $Month, $Date, $Year));
    return $Date['wday'];
  }

  function get_month_name($Month)
  {
    $Result = "";
    switch ($Month)
    {
      case 1:
        $Result = "Januar";
        break;

      case 2:
        $Result = "Februar";
        break;

      case 3:
        $Result = "Marts";
        break;

      case 4:
        $Result = "April";
        break;

      case 5:
        $Result = "Maj";
        break;

      case 6:
        $Result = "Juni";
        break;

      case 7:
        $Result = "Juli";
        break;

      case 8:
        $Result = "August";
        break;

      case 9:
        $Result = "September";
        break;

      case 10:
        $Result = "Oktober";
        break;

      case 11:
        $Result = "November";
        break;

      case 12:
        $Result = "December";
        break;
    }
    return $Result;
  }

  function get_holiday($Year, $Month, $Date, &$HolidayName)
  {
    $Holiday = 0;
    $HolidayName = "";
    switch ($Month)
    {
      case 1:
        switch ($Date)
        {
          case 1:
            $Holiday = 1;
            $HolidayName = "Nytårsdag";
            break;

          case 6:
            $HolidayName = "Hellig 3 konger";
            break;
        }
        break;

      case 2:
        switch ($Date)
        {
        	case 5:
        	  $HolidayName = "Kr. prs. Mary";
        	  break;
        }
        break;

      case 3:
        switch ($Date)
        {
          case 20:
            $HolidayName = "Jævndøgn";
            break;
        }
        break;

      case 4:
        switch ($Date)
        {
          case 16:
            $HolidayName = "Dr. Margrethe II";
            break;
        }
        break;

      case 5:
        switch ($Date)
        {
          case 5:
            $HolidayName = "Danmarks befrielse";
            break;

          case 26:
            $HolidayName = "Kr. pr. Frederik";
            break;
        }
        break;

      case 6:
        switch ($Date)
        {
          case 5:
            $HolidayName = "Grundlovsdag";
            break;

          case 7:
            $HolidayName = "Pr. Joachim";
            break;

          case 11:
            $HolidayName = "Pr. Henrik";
            break;

          case 15:
            $HolidayName = "Valdemarsdag";
            break;

          case 21:
            $HolidayName = "Solhverv";
            break;

          case 24:
            $HolidayName = "Skt. Hans dag";
            break;

          case 30:
            $HolidayName = "Prs. Alexandra";
            break;
        }
        break;

      case 7:
        switch ($Date)
        {
        	case 22:
        	  $HolidayName = "Pr. Felix";
        	  break;
        }
        break;

      case 8:
        switch ($Date)
        {
          case 28:
            $HolidayName = "Pr. Nikolai";
            break;
        }
        break;

      case 9:
        switch ($Date)
        {
          case 23:
            $HolidayName = "Jævndøgn";
            break;
        }
        break;

      case 10:
        switch ($Date)
        {
          case 24:
            $HolidayName = "FN-dag";
            break;
        }
        break;

      case 11:
        switch ($Date)
        {
          case 11:
            $HolidayName = "Morten Bisp";
            break;
        }
        break;

      case 12:
        switch ($Date)
        {
          case 24:
            $HolidayName = "Juleaften";
            break;

          case 25:
            $Holiday = 1;
            $HolidayName = "Juledag";
            break;

          case 26:
            $Holiday = 1;
            $HolidayName = "2. juledag";
            break;

          case 31:
            $Holiday = 1;
            $HolidayName = "Nytårsaften";
            break;
        }
        break;
    }
    // Holidays relative to christmas.
    if (get_day_of_week($Year, $Month, $Date) == 0 && $Month > 10)
    {
      $DT = dec_date(getdate(mktime(0, 0, 0, 12, 25, $Year)), get_day_of_week($Year, 12, 25));
      if ($Year == $DT['year'] && $Month == $DT['mon'] && $Date == $DT['mday'])
        $HolidayName = "4. søndag i advent";
      $DT = dec_date($DT, 7);
      if ($Year == $DT['year'] && $Month == $DT['mon'] && $Date == $DT['mday'])
        $HolidayName = "3. søndag i advent";
      $DT = dec_date($DT, 7);
      if ($Year == $DT['year'] && $Month == $DT['mon'] && $Date == $DT['mday'])
        $HolidayName = "2. søndag i advent";
      $DT = dec_date($DT, 7);
      if ($Year == $DT['year'] && $Month == $DT['mon'] && $Date == $DT['mday'])
        $HolidayName = "1. søndag i advent";
    }
    // Holidays relative to easter.
    $DT = get_easter($Year);
    if ($Year == $DT['year'] && $Month == $DT['mon'] && $Date == $DT['mday'] && get_day_of_week($Year, $Month, $Date) == 0)
    {
      $Holiday = 1;
      $HolidayName = "Påskedag";
    }
    $DT = inc_date($DT, 1);
    if ($Year == $DT['year'] && $Month == $DT['mon'] && $Date == $DT['mday'] && get_day_of_week($Year, $Month, $Date) == 1)
    {
      $Holiday = 1;
      $HolidayName = "2. påskedag";
    }
    $DT = dec_date($DT, 3);
    if ($Year == $DT['year'] && $Month == $DT['mon'] && $Date == $DT['mday'] && get_day_of_week($Year, $Month, $Date) == 5)
    {
      $Holiday = 1;
      $HolidayName = "Langfredag";
    }
    $DT = dec_date($DT, 1);
    if ($Year == $DT['year'] && $Month == $DT['mon'] && $Date == $DT['mday'] && get_day_of_week($Year, $Month, $Date) == 4)
    {
      $Holiday = 1;
      $HolidayName = "Skærtorsdag";
    }
    $DT = dec_date($DT, 4);
    if ($Year == $DT['year'] && $Month == $DT['mon'] && $Date == $DT['mday'] && get_day_of_week($Year, $Month, $Date) == 0)
    {
      $Holiday = 1;
      $HolidayName = "Palmesøndag";
    }
    $DT = inc_date($DT, 7 + 26);
    if ($Year == $DT['year'] && $Month == $DT['mon'] && $Date == $DT['mday'] && get_day_of_week($Year, $Month, $Date) == 5)
    {
      $Holiday = 1;
      $HolidayName = "Bededag";
    }
    $DT = inc_date($DT, 13);
    if ($Year == $DT['year'] && $Month == $DT['mon'] && $Date == $DT['mday'] && get_day_of_week($Year, $Month, $Date) == 4)
    {
      $Holiday = 1;
      $HolidayName = "Kr. himmelfartsdag";
    }
    $DT = inc_date($DT, 10);
    if ($Year == $DT['year'] && $Month == $DT['mon'] && $Date == $DT['mday'] && get_day_of_week($Year, $Month, $Date) == 0)
    {
      $Holiday = 1;
      $HolidayName = "Pinsedag";
    }
    $DT = inc_date($DT, 1);
    if ($Year == $DT['year'] && $Month == $DT['mon'] && $Date == $DT['mday'] && get_day_of_week($Year, $Month, $Date) == 1)
    {
      $Holiday = 1;
      $HolidayName = "2. pinsedag";
    }
    return $Holiday;
  }

  function get_easter($Year)
  {
    $a = (integer) ($Year % 19);
    $b = (integer) ($Year / 100);
    $c = (integer) ($Year % 100);
    $o = (integer) ($b / 4);
    $e = (integer) ($b % 4);
    $f = (integer) (($b + 8) / 25);
    $g = (integer) (($b - $f + 1) / 3);
    $h = (integer) ((19 * $a + $b - $o - $g + 15) % 30);
    $i = (integer) ($c / 4);
    $k = (integer) ($c % 4);
    $l = (integer) ((32 + 2 * $e + 2 * $i - $h - $k) % 7);
    $q = (integer) (($a + 11 * $h + 22 * 1) / 451);
    $Month = (integer) (($h + $l - 7 * $q + 114) / 31);
    $Date = (integer) ((($h + $l - 7 * $q + 114) % 31) + 1);
    return getdate(mktime(0, 0, 0, $Month, $Date, $Year));
  }

  function dec_date($DT, $Value)
  {
    $Year = $DT['year'];
    $Month = $DT['mon'];
    $Date = $DT['mday'];
    for ($i = 0; $i < $Value; $i++)
    {
      if (--$Date <= 0)
      {
        if (--$Month <= 0)
        {
          $Month = 12;
          $Year--;
        }
        $Date = get_days_in_month($Year, $Month);
      }
    }
    return getdate(mktime(0, 0, 0, $Month, $Date, $Year));
  }

  function inc_date($DT, $Value)
  {
    $Year = $DT['year'];
    $Month = $DT['mon'];
    $Date = $DT['mday'];
    for ($i = 0; $i < $Value; $i++)
    {
      if (++$Date > get_days_in_month($Year, $Month))
      {
        if (++$Month > 12)
        {
          $Month = 1;
          $Year++;
        }
        $Date = 1;
      }
    }
    return getdate(mktime(0, 0, 0, $Month, $Date, $Year));
  }

  function get_days_in_month($Year, $Month)
  {
    $Result = 0;
    switch ($Month)
    {
      case 1:
        $Result = 31;
        break;

      case 2:
        $Result = 28 + is_leap_year($Year);
        break;

      case 3:
        $Result = 31;
        break;

      case 4:
        $Result = 30;
        break;

      case 5:
        $Result = 31;
        break;

      case 6:
        $Result = 30;
        break;

      case 7:
        $Result = 31;
        break;

      case 8:
        $Result = 31;
        break;

      case 9:
        $Result = 30;
        break;

      case 10:
        $Result = 31;
        break;

      case 11:
        $Result = 30;
        break;

      case 12:
        $Result = 31;
        break;
    }
    return $Result;
  }

  function is_leap_year($Year)
  {
    return ($Year % 4 == 0) && (($Year % 100 != 0) || ($Year % 400 == 0)) && ($Year % 4000 != 0);
  }

  function get_week_no($Year, $Month, $Date)
  {
    $Dow = get_day_of_week($Year, 1, 1);
    if ($Dow == 0)
      $Dow = 6;
    else
      $Dow--;
    for ($i = 1; $i < $Month; $i++)
      $Date += get_days_in_month($Year, $i);
    if ($Dow < 4)
      $WeekNo = floor((($Date + $Dow - 1) / 7)) + 1;
    else
      $WeekNo = floor(($Date + $Dow - 1) / 7);
    if ($WeekNo == 0)
    {
      $Year--;
      $WeekNo = get_week_no($Year, 12, 31);
    }
    else if ($WeekNo == 53)
    {
      if (get_day_of_week($Year, 12, 31) < 4)
        $WeekNo = 1;
    }
    return $WeekNo;
  }
?>
