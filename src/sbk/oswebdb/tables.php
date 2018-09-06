<?php
  // Include required files.
  require_once("oswebdb/oswebdb.php");
  require_once("oswebdb/zipcodes.php");
  require_once("oswebdb/distance.php");

  // Functions for the systemtables table.
  function insert_systemtable($SystemNo, $TableNo, $No, $Description)
  {
    $Result = 1;
    if (is_table_accessible($SystemNo, $TableNo))
    {
      $TableSystemNo = get_system_for_table($SystemNo, $TableNo);
      $ShowFields = get_table_show_fields($SystemNo, $TableNo);
      $Fields = "";
      $Values = "";
      if (($ShowFields & 1) || $TableNo == 0)
      {
        $Fields = "$Fields,Length";
        switch ($TableNo)
        {
          case 2:
            $Values = "$Values,4";
            break;

          case 8:
            $Values = "$Values,2";
            break;

          default:
            $Values = "$Values,30";
            break;
        }
      }
      if (($ShowFields & 2) || $TableNo == 0)
      {
        $Fields = "$Fields,ShowFields";
        $Values = "$Values,0";
      }
      if ($ShowFields & 4)
      {
        $Fields = "$Fields,Public";
        $Values = "$Values,0";
      }
      if (($ShowFields & 8) || $TableNo == 0)
      {
        $Fields = "$Fields,Common";
        $Values = "$Values,0";
      }
      if ($ShowFields & 16)
      {
        $Fields = "$Fields,Properties";
        $Values = "$Values,0";
      }
      if (($ShowFields & 32) || $TableNo == 0)
      {
        $Fields = "$Fields,Text1";
        $Values = "$Values,\"\"";
      }
      if (($ShowFields & 64) || $TableNo == 0)
      {
        $Fields = "$Fields,Text2";
        $Values = "$Values,\"\"";
      }
      if (($ShowFields & 128) || $TableNo == 0)
      {
        $Fields = "$Fields,Text3";
        $Values = "$Values,\"\"";
      }
      if (($ShowFields & 256) || $TableNo == 0)
      {
        $Fields = "$Fields,GroupNo";
        $Values = "$Values,0";
      }
      if ($ShowFields & 512)
      {
        $Year = date("Y", time());
        $Fields = "$Fields,FromDate,ToDate";
        $Values = "$Values,\"$Year-01-01\",\"$Year-12-31\"";
      }
      if (!oswebdb_query("INSERT INTO Systemtables (SystemNo,TableNo,No,Description$Fields) VALUES($TableSystemNo,$TableNo,$No,\"$Description\"$Values)"))
        $Result = 0;
    }
    return $Result;
  }

  function update_systemtable($SystemNo, $TableNo, $No, $Description, $Length, $Field0, $Field1, $Field2, $Field3, $Field4, $Field5, $Field6, $Field7, $Field8, $Field9, $Field10, $Field11, $Public, $Common, $Property0, $Property1, $Property2, $Property3, $Property4, $Property5, $Property6, $Property7, $Property8, $Property9, $Property10, $Property11, $Text1, $Text2, $Text3, $GroupNo, $FromDate, $ToDate)
  {
    $Result = 1;
    if (is_table_accessible($SystemNo, $TableNo))
    {
      $TableSystemNo = get_system_for_table($SystemNo, $TableNo);
      $ShowFields = get_table_show_fields($SystemNo, $TableNo);
      $Update = "Description=\"$Description\"";
      if (($ShowFields & 1) || $TableNo == 0)
      {
        if (isset($Length))
          $Update = "$Update,Length=$Length";
      }
      if (($ShowFields & 2) || $TableNo == 0)
      {
        $Fields = 0;
        if (isset($Field0))
          $Fields += $Field0;
        if (isset($Field1))
          $Fields += $Field1;
        if (isset($Field2))
          $Fields += $Field2;
        if (isset($Field3))
          $Fields += $Field3;
        if (isset($Field4))
          $Fields += $Field4;
        if (isset($Field5))
          $Fields += $Field5;
        if (isset($Field6))
          $Fields += $Field6;
        if (isset($Field7))
          $Fields += $Field7;
        if (isset($Field8))
          $Fields += $Field8;
        if (isset($Field9))
          $Fields += $Field9;
        if (isset($Field10))
          $Fields += $Field10;
        if (isset($Field11))
          $Fields += $Field11;
        $Update = "$Update,ShowFields=$Fields";
      }
      if ($ShowFields & 4)
      {
        if (isset($Public))
          $Update = "$Update,Public=$Public";
        else
          $Update = "$Update,Public=0";
      }
      if (($ShowFields & 8) || $TableNo == 0)
      {
        if (isset($Common))
          $Update = "$Update,Common=$Common";
        else
          $Update = "$Update,Common=0";
      }
      if ($ShowFields & 16)
      {
        $Properties = 0;
        if (isset($Property0))
          $Properties += $Property0;
        if (isset($Property1))
          $Properties += $Property1;
        if (isset($Property2))
          $Properties += $Property2;
        if (isset($Property3))
          $Properties += $Property3;
        if (isset($Property4))
          $Properties += $Property4;
        if (isset($Property5))
          $Properties += $Property5;
        if (isset($Property6))
          $Properties += $Property6;
        if (isset($Property7))
          $Properties += $Property7;
        if (isset($Property8))
          $Properties += $Property8;
        if (isset($Property9))
          $Properties += $Property9;
        if (isset($Property10))
          $Properties += $Property10;
        if (isset($Property11))
          $Properties += $Property11;
        $Update = "$Update,Properties=$Properties";
      }
      if (($ShowFields & 32) || $TableNo == 0)
      {
        if (isset($Text1))
          $Update = "$Update,Text1=\"$Text1\"";
      }
      if (($ShowFields & 64) || $TableNo == 0)
      {
        if (isset($Text2))
          $Update = "$Update,Text2=\"$Text2\"";
      }
      if ((ShowFields & 128) || $TableNo == 0);
      {
        if (isset($Text3))
          $Update = "$Update,Text3=\"$Text3\"";
      }
      if ((ShowFields & 256) || $TableNo == 0);
      {
        if (isset($GroupNo))
          $Update = "$Update,GroupNo=$GroupNo";
      }
      if ($ShowFields & 512)
      {
        $Year = date("Y", time());
        if (isset($FromDate))
          $Update = "$Update,FromDate=\"$FromDate\"";
        else
          $Update = "$Update,FromDate=\"$Year-01-01\"";
        if (isset($ToDate))
          $Update = "$Update,ToDate=\"$ToDate\"";
        else
          $Update = "$Update,ToDate=\"$Year-12-31\"";
      }
      if (!oswebdb_query("UPDATE Systemtables SET $Update WHERE SystemNo=$TableSystemNo AND TableNo=$TableNo AND No=$No"))
        $Result = 0;
    }
    return $Result;
  }

  function delete_systemtable($SystemNo, $TableNo, $No)
  {
    $Result = 1;
    if (is_table_accessible($SystemNo, $TableNo))
    {
      $TableSystemNo = get_system_for_table($SystemNo, $TableNo);
      switch ($TableNo)
      {
        case 0:
          // Delete a table.
          if ($Result)
          {
            if ($TableSystemNo == 0)
            {
              if (!oswebdb_query("DELETE FROM Addressmatches WHERE TableNo=$No"))
                $Result = 0;
              else if (!oswebdb_query("DELETE FROM Addresslinks WHERE TableNo=$No"))
                $Result = 0;
              else if (!oswebdb_query("DELETE FROM Systemtables WHERE TableNo=$No"))
                $Result = 0;
            }
            else
            {
              $DeleteFromSystem = get_system_for_table($SystemNo, $No);
              if (!oswebdb_query("DELETE FROM Addressmatches WHERE SystemNo=$DeleteFromSystem AND TableNo=$No"))
                $Result = 0;
              else if (!oswebdb_query("DELETE FROM Addresslinks WHERE SystemNo=$DeleteFromSystem AND TableNo=$No"))
                $Result = 0;
              else if (!oswebdb_query("DELETE FROM Systemtables WHERE SystemNo=$DeleteFromSystem AND TableNo=$No"))
                $Result = 0;
            }
          }
          break;

        case 1:
          // Delete a menuitem.
          if ($Result)
          {
            if ($TableSystemNo == 0)
            {
              if (!oswebdb_query("DELETE FROM Webcontent WHERE MenuNo=$No"))
                $Result = 0;
              else if (!oswebdb_query("DELETE FROM Websites WHERE MenuNo=$No"))
                $Result = 0;
            }
            else if (!oswebdb_query("DELETE FROM Webcontent WHERE SystemNo=$SystemNo AND MenuNo=$No"))
              $Result = 0;
            else if (!oswebdb_query("DELETE FROM Websites WHERE SystemNo=$SystemNo AND MenuNo=$No"))
              $Result = 0;
          }
          break;

        case 2:
          // Delete a country code.
          if ($Result)
          {
            if ($TableSystemNo == 0)
            {
              if (!oswebdb_query("UPDATE Addresses SET CountryCode=0,ZipCode=\"\" WHERE CountryCode=$No"))
                $Result = 0;
            }
            else if (!oswebdb_query("UPDATE Addresses SET CountryCode=0,ZipCode=\"\" WHERE SystemNo=$SystemNo AND CountryCode=$No"))
              $Result = 0;
          }
          if ($Result)
          {
            if ($TableSystemNo == 0)
            {
              if (!oswebdb_query("DELETE FROM Allowancelines WHERE FromCountryCode=$No OR ToCountryCode=$No"))
                $Result = 0;
            }
            else if (!oswebdb_query("DELETE FROM Allowancelines WHERE SystemNo=$SystemNo AND (FromCountryCode=$No OR ToCountryCode=$No)"))
              $Result = 0;
          }
          if ($Result)
          {
            if ($TableSystemNo == 0)
            {
              if (!oswebdb_query("DELETE FROM Distances WHERE FromCountryCode=$No OR ToCountryCode=$No"))
                $Result = 0;
            }
            else
            {
              $DistanceSystemNo = get_system_for_distances($SystemNo);
              if (!oswebdb_query("DELETE FROM Distances WHERE SystemNo=$DistanceSystemNo AND (FromCountryCode=$No OR ToCountryCode=$No)"))
                $Result = 0;
            }
          }
          if ($Result)
          {
            if ($TableSystemNo == 0)
            {
              if (!oswebdb_query("DELETE FROM Zipcodes WHERE CountryCode=$No"))
                $Result = 0;
            }
            else
            {
              $ZipCodeSystemNo = get_system_for_zipcodes($SystemNo);
              if (!oswebdb_query("DELETE FROM Zipcodes WHERE SystemNo=$ZipCodeSystemNo AND CountryCode=$No"))
                $Result = 0;
            }
          }
          if ($Result)
          {
            if ($TableSystemNo == 0)
            {
              if (!oswebdb_query("UPDATE Systems SET CountryCode=0 WHERE CountryCode=$No"))
                $Result = 0;
            }
            else if (!oswebdb_query("UPDATE Systems SET CountryCode=0 WHERE SystemNo=$SystemNo AND CountryCode=$No"))
              $Result = 0;
          }
          break;

        case 3:
          // Delete an address group.
          if ($Result)
          {
            if ($TableSystemNo == 0)
            {
              if (!oswebdb_query("UPDATE Addresses SET GroupNo=0,Public=0 WHERE GroupNo=$No"))
                $Result = 0;
            }
            else if (!oswebdb_query("UPDATE Addresses SET GroupNo=0,Public=0 WHERE SystemNo=$SystemNo AND GroupNo=$No"))
              $Result = 0;
          }
          break;

        case 4:
          // Delete a website type.
          if ($Result)
          {
            if ($TableSystemNo == 0)
            {
              if (!oswebdb_query("DELETE FROM Webcontent WHERE TypeNo=$No"))
                $Result = 0;
              else if (!oswebdb_query("DELETE FROM Websites WHERE TypeNo=$No"))
                $Result = 0;
            }
            else if (!oswebdb_query("DELETE FROM Webcontent WHERE SystemNo=$SystemNo AND TypeNo=$No"))
              $Result = 0;
            else if (!oswebdb_query("DELETE FROM Websites WHERE SystemNo=$SystemNo AND TypeNo=$No"))
              $Result = 0;
          }
          break;

        case 5:
          // Delete a webcontent type.
          break;

        case 6:
          // Delete a type of whishes.
          if ($Result)
          {
            if ($TableSystemNo == 0)
            {
              if (!oswebdb_query("UPDATE Webcontent SET GroupNo=0 WHERE TypeNo=7 AND GroupNo=$No"))
                $Result = 0;
            }
            else if (!oswebdb_query("UPDATE Webcontent SET GroupNo=0 WHERE SystemNo=$SystemNo AND TypeNo=7 AND GroupNo=$No"))
              $Result = 0;
          }
          break;

        case 7:
          // Delete a season.
          if ($Result)
          {
            if ($TableSystemNo == 0)
            {
              if (!oswebdb_query("DELETE FROM Addressmatches WHERE SeasonNo=$No"))
                $Result = 0;
              else if (!oswebdb_query("DELETE FROM Addresslinks WHERE SeasonNo=$No"))
                $Result = 0;
              else if (!oswebdb_query("UPDATE Systems SET SeasonNo=0 WHERE SeasonNo=$No"))
                $Result = 0;
            }
            else if (!oswebdb_query("DELETE FROM Addressmatches WHERE SystemNo=$SystemNo AND SeasonNo=$No"))
              $Result = 0;
            else if (!oswebdb_query("DELETE FROM Addresslinks WHERE SystemNo=$SystemNo AND SeasonNo=$No"))
              $Result = 0;
            else if (!oswebdb_query("UPDATE Systems SET SeasonNo=0 WHERE SystemNo=$SystemNo AND SeasonNo=$No"))
              $Result = 0;
          }
          break;

        case 8:
          // Delete a type of average.
          if ($Result)
          {
            if ($TableSystemNo == 0)
            {
              if (!oswebdb_query("DELETE FROM Addresslinks WHERE TableNo=$TableNo AND TypeNo=$No"))
                $Result = 0;
            }
            else if (!oswebdb_query("DELETE FROM Addresslinks WHERE SystemNo=$SystemNo AND TableNo=$TableNo AND TypeNo=$No"))
              $Result = 0;
          }
          break;

        case 9:
          // Delete a matchtype.
          if ($Result)
          {
            if ($TableSystemNo == 0)
            {
              if (!oswebdb_query("DELETE FROM Addressmatches WHERE TableNo=$TableNo AND TypeNo=$No"))
                $Result = 0;
            }
            else if (!oswebdb_query("DELETE FROM Addressmatches WHERE SystemNo=$SystemNo AND TableNo=$TableNo AND TypeNo=$No"))
              $Result = 0;
          }
          break;

        case 10:
          // Delete a role.
          if ($Result)
          {
          	if ($TableSystemNo == 0)
          	{
          		if (!oswebdb_query("UPDATE Systems SET AdminRole=0 WHERE AdminRole=$No"))
          		  $Result = 0;
          		else if (!oswebdb_query("UPDATE Systems SET ConfigRole=0 WHERE ConfigRole=$No"))
          		  $Result = 0;
          		else if (!oswebdb_query("UPDATE Systems SET UserRole=0 WHERE UserRole=$No"))
          		  $Result = 0;
          	}
          	else if (!oswebdb_query("UPDATE Systems SET AdminRole=0 WHERE SystemNo=$SystemNo AND AdminRole=$No"))
          		$Result = 0;
          	else if (!oswebdb_query("UPDATE Systems SET ConfigRole=0 WHERE SystemNo=$SystemNo AND ConfigRole=$No"))
          		$Result = 0;
          	else if (!oswebdb_query("UPDATE Systems SET UserRole=0 WHERE SystemNo=$SystemNo AND UserRole=$No"))
          		$Result = 0;
          }
          break;
      }
      // Clear the GroupNo where the tablerecord is used.
      if ($Result)
      {
        $GroupTables = explode(",", get_groups_for_table($SystemNo, $TableNo));
        for ($i = 0; $i < count($GroupTables) && $Result; $i++)
        {
          if (strlen($GroupTables[$i]) > 0)
          {
            $GroupSystemNo = get_system_for_table($SystemNo, $GroupTables[$i]);
            if ($GroupSystemNo == 0)
            {
              if (!oswebdb_query("UPDATE Systemtables SET GroupNo=0 WHERE TableNo=$GroupTables[$i] AND GroupNo=$No"))
                $Result = 0;
            }
            else if (!oswebdb_query("UPDATE Systemtables SET GroupNo=0 WHERE SystemNo=$GroupSystemNo AND TableNo=$GroupTables[$i] AND GroupNo=$No"))
              $Result = 0;
          }
        }
      }
      if ($Result)
      {
        if (!oswebdb_query("DELETE FROM Systemtables WHERE SystemNo=$TableSystemNo AND TableNo=$TableNo AND No=$No"))
          $Result = 0;
      }
    }
    return $Result;
  }

  function get_table_field_value($SystemNo, $TableNo, $No, $FieldName)
  {
    $FieldValue = "";
    if ($Result = oswebdb_query("SELECT $FieldName FROM Systemtables WHERE SystemNo=$SystemNo AND TableNo=$TableNo AND No=$No"))
    {
      if ($Row = oswebdb_fetch_row($Result))
        $FieldValue = $Row[0];
      oswebdb_free_result($Result);
    }
    return $FieldValue;
  }

  function get_table_field_length($SystemNo, $TableNo, $FieldName)
  {
    $FieldLength = 0;
    $TableSystemNo = get_system_for_table($SystemNo, 0);
    if ($Result = oswebdb_query("SELECT $FieldName,Length FROM Systemtables WHERE SystemNo=$TableSystemNo AND TableNo=0 AND No=$TableNo"))
    {
      if ($Row = oswebdb_fetch_row($Result))
      {
        if (strcmp($FieldName, "Description") == 0)
          $FieldLength = $Row[1];
      }
      if ($FieldLength == 0)
        $FieldLength = oswebdb_field_len($Result, 0);
      oswebdb_free_result($Result);
    }
    return $FieldLength;
  }

  function get_system_for_table($SystemNo, $TableNo)
  {
    return ((int) get_table_field_value(0, 0, $TableNo, "Common")) ? 0 : $SystemNo;
  }

  function get_table_name($SystemNo, $TableNo)
  {
    return get_table_field_value(0, 0, $TableNo, "Description");
  }

  function get_table_show_fields($SystemNo, $TableNo)
  {
    return (int) get_table_field_value(0, 0, $TableNo, "ShowFields");
  }

  function get_table_text1($SystemNo, $TableNo)
  {
    $Text1 = get_table_field_value(0, 0, $TableNo, "Text1");
    if ($TableNo == 0)
      $Text1 = "Længde,1/Felter,2/Offentlig,4/Systemdeling,8/Egenskaber,16/Fritekst 1,32/Fritekst 2,64/Fritekst 3,128/Gruppe,256/Periode,512/";
    return $Text1;
  }

  function get_table_text2($SystemNo, $TableNo)
  {
    $Text2 = get_table_field_value(0, 0, $TableNo, "Text2");
    if ($TableNo == 0)
      $Text2 = "Administrator adgang,4/Konfigurator adgang,2/Bruger adgang,1/";
    return $Text2;
  }

  function get_table_text3($SystemNo, $TableNo)
  {
    $Text3 = get_table_field_value(0, 0, $TableNo, "Text3");
    if ($TableNo == 0)
      $Text3 = "Feltdefinitioner/Egenskabsdefinitioner/Felttekster/Tabel/";
    return $Text3;
  }

  function get_table_group($SystemNo, $TableNo)
  {
    $Group = (int) get_table_field_value(0, 0, $TableNo, "GroupNo");
    if ($TableNo == 0)
      $Group = 0;
    return $Group;
  }

  function get_groups_for_table($SystemNo, $TableNo)
  {
    $Groups = "";
    if ($Result = oswebdb_query("SELECT No FROM Systemtables WHERE SystemNo=0 AND TableNo=0 AND GroupNo=$TableNo ORDER BY SystemNo,TableNo,No"))
    {
      while ($Row = oswebdb_fetch_row($Result))
      {
        if (strlen($Groups) > 0)
          $Groups = "$Groups,$Row[0]";
        else
          $Groups = "$Row[0]";
      }
      oswebdb_free_result($Result);
    }
    return $Groups;
  }

  function is_table_accessible($SystemNo, $TableNo)
  {
  	$Result = 0;
  	$TableAccess = get_table_field_value(get_system_for_table($SystemNo, 0), 0, $TableNo, "Properties");
  	if (($TableAccess & 4) && !$Result)
  	{
  		$Result = oswebdb_is_administrator($SystemNo);
  	}
  	if (($TableAccess & 2) && !$Result)
  	{
  		$Result = oswebdb_is_configurator($SystemNo);
  	}
  	if (($TableAccess & 1) && !$Result)
  	{
  		$Result = oswebdb_is_user($SystemNo);
  	}
  	return $Result;
  }
?>
