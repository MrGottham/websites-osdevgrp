<?php
  // Include required files.
  require_once("oswebdb/oswebdb.php");
  require_once("oswebdb/distance.php");

  // Functions for the allowance table.
  function insert_allowance($SystemNo, $Year, $No, $Description, $Distance1, $Allowance1, $Distance2, $Allowance2, $Distance3, $Allowance3, $Distance4, $Allowance4, $Distance5, $Allowance5, $Addition1, $Addition2)
  {
    $Result = 1;
    $Fields = "SystemNo,Year,No";
    $Values = "$SystemNo,$Year,$No";
    if (isset($Description))
    {
      $Fields = "$Fields,Description";
      $Values = "$Values,\"$Description\"";
    }
    $Fields = get_allowance_insert_fields($Fields, "Distance1");
    $Values = get_allowance_insert_values($Values, $Distance1);
    $Fields = get_allowance_insert_fields($Fields, "Allowance1");
    $Values = get_allowance_insert_values($Values, $Allowance1);
    $Fields = get_allowance_insert_fields($Fields, "Distance2");
    $Values = get_allowance_insert_values($Values, $Distance2);
    $Fields = get_allowance_insert_fields($Fields, "Allowance2");
    $Values = get_allowance_insert_values($Values, $Allowance2);
    $Fields = get_allowance_insert_fields($Fields, "Distance3");
    $Values = get_allowance_insert_values($Values, $Distance3);
    $Fields = get_allowance_insert_fields($Fields, "Allowance3");
    $Values = get_allowance_insert_values($Values, $Allowance3);
    $Fields = get_allowance_insert_fields($Fields, "Distance4");
    $Values = get_allowance_insert_values($Values, $Distance4);
    $Fields = get_allowance_insert_fields($Fields, "Allowance4");
    $Values = get_allowance_insert_values($Values, $Allowance4);
    $Fields = get_allowance_insert_fields($Fields, "Distance5");
    $Values = get_allowance_insert_values($Values, $Distance5);
    $Fields = get_allowance_insert_fields($Fields, "Allowance5");
    $Values = get_allowance_insert_values($Values, $Allowance5);
    $Fields = get_allowance_insert_fields($Fields, "Addition1");
    $Values = get_allowance_insert_values($Values, $Addition1);
    $Fields = get_allowance_insert_fields($Fields, "Addition2");
    $Values = get_allowance_insert_values($Values, $Addition2);
    if (!oswebdb_query("INSERT INTO Allowances ($Fields) VALUES($Values)"))
      $Result = 0;
    return $Result;
  }

  function update_allowance($SystemNo, $Year, $No, $Description, $Distance1, $Allowance1, $Distance2, $Allowance2, $Distance3, $Allowance3, $Distance4, $Allowance4, $Distance5, $Allowance5, $Addition1, $Addition2)
  {
    $Result = 1;
    $Update = "";
    if (isset($Description))
    {
      if (strlen($Update) > 0)
        $Update = "$Update,Description=\"$Description\"";
      else
        $Update = "Description=\"$Description\"";
    }
    $Update = get_allowance_update($Update, "Distance1", $Distance1);
    $Update = get_allowance_update($Update, "Allowance1", $Allowance1);
    $Update = get_allowance_update($Update, "Distance2", $Distance2);
    $Update = get_allowance_update($Update, "Allowance2", $Allowance2);
    $Update = get_allowance_update($Update, "Distance3", $Distance3);
    $Update = get_allowance_update($Update, "Allowance3", $Allowance3);
    $Update = get_allowance_update($Update, "Distance4", $Distance4);
    $Update = get_allowance_update($Update, "Allowance4", $Allowance4);
    $Update = get_allowance_update($Update, "Distance5", $Distance5);
    $Update = get_allowance_update($Update, "Allowance5", $Allowance5);
    $Update = get_allowance_update($Update, "Addition1", $Addition1);
    $Update = get_allowance_update($Update, "Addition2", $Addition2);
    if (!oswebdb_query("UPDATE Allowances SET $Update WHERE SystemNo=$SystemNo AND Year=$Year AND No=$No"))
      $Result = 0;
    return $Result;
  }

  function delete_allowance($SystemNo, $Year, $No)
  {
    $Result = 1;
    if (!oswebdb_query("DELETE FROM Allowancelines WHERE SystemNo=$SystemNo AND Year=$Year AND No=$No"))
      $Result = 0;
    else if (!oswebdb_query("DELETE FROM Allowances WHERE SystemNo=$SystemNo AND Year=$Year AND No=$No"))
      $Result = 0;
    return $Result;
  }

  function insert_allowance_line($SystemNo, $Year, $No, $Date, $FromCountryCode, $FromZipCode, $ToCountryCode, $ToZipCode)
  {
    $Result = 1;
    $LineNo = 1;
    if ($LastLineResult = oswebdb_query("SELECT LineNo FROM Allowancelines WHERE SystemNo=$SystemNo AND Year=$Year AND No=$No ORDER BY LineNo DESC"))
    {
      if ($LastLineRow = oswebdb_fetch_row($LastLineResult))
        $LineNo = $LastLineRow[0] + 1;
      oswebdb_free_result($LastLineResult);
    }
    $Fields = "SystemNo,Year,No,LineNo,Date,FromCountryCode,FromZipCode,ToCountryCode,ToZipCode";
    $Values = "$SystemNo,$Year,$No,$LineNo,\"$Date\",$FromCountryCode,\"$FromZipCode\",$ToCountryCode,\"$ToZipCode\"";
    if (!oswebdb_query("INSERT INTO Allowancelines ($Fields) VALUES($Values)"))
      $Result = 0;
    return $Result;
  }

  function delete_allowance_line($SystemNo, $Year, $No, $LineNo)
  {
    $Result = 1;
    if (!oswebdb_query("DELETE FROM Allowancelines WHERE SystemNo=$SystemNo AND Year=$Year AND No=$No AND LineNo=$LineNo"))
      $Result = 0;
    return $Result;
  }

  function get_allowance_insert_fields($Fields, $FieldName)
  {
    if (strlen($Fields) > 0)
      $Fields = "$Fields,$FieldName";
    else
      $Fields = "$FieldName";
    return $Fields;
  }

  function get_allowance_insert_values($Values, $FieldValue)
  {
    if (isset($FieldValue))
    {
      if ((integer) $FieldValue > 0)
      {
        if (strlen($Values) > 0)
          $Values = "$Values,$FieldValue";
        else
          $Values = "$FieldValue";
      }
      else if (strlen($Values) > 0)
        $Values = "$Values,0";
      else
        $Values = "0";
    }
    else if (strlen($Values) > 0)
      $Values = "$Values,0";
    else
      $Values = "0";
    return $Values;
  }

  function get_allowance_update($Update, $FieldName, $FieldValue)
  {
    if (isset($FieldValue))
    {
      if ((integer) $FieldValue > 0)
      {
        if (strlen($Update) > 0)
          $Update = "$Update,$FieldName=$FieldValue";
        else
          $Update = "$FieldName=$FieldValue";
      }
      else if (strlen($Update) > 0)
        $Update = "$Update,$FieldName=0";
      else
        $Update = "$FieldName=0";
    }
    else if (strlen($Update) > 0)
      $Update = "$Update,$FieldName=0";
    else
      $Update = "$FieldName=0";
    return $Update;
  }

  function get_allowance_description($SystemNo, $Year, $No)
  {
    $AllowanceDescription = "";
    if ($Result = oswebdb_query("SELECT Description FROM Allowances WHERE SystemNo=$SystemNo AND Year=$Year AND No=$No"))
    {
      if ($Row = oswebdb_fetch_row($Result))
        $AllowanceDescription = $Row[0];
      oswebdb_free_result($Result);
    }
    return $AllowanceDescription;
  }

  function get_days_for_allowance($SystemNo, $Year, $No)
  {
    $AllowanceDays = oswebdb_get_rows("SELECT COUNT(l.Date) FROM Allowancelines AS l WHERE l.SystemNo=$SystemNo AND l.Year=$Year AND l.No=$No GROUP BY l.Date");
    return (integer) $AllowanceDays;
  }

  function get_distance_for_allowance($SystemNo, $Year, $No)
  {
    $AllowanceDistance = 0;
    $DistanceSystemNo = get_system_for_distances($SystemNo);
    if ($Result = oswebdb_query("SELECT SUM(d.Distance) FROM Allowancelines AS l, Distances AS d WHERE l.SystemNo=$SystemNo AND l.Year=$Year AND l.No=$No AND d.SystemNo=$DistanceSystemNo AND d.FromCountryCode=l.FromCountryCode AND d.FromZipCode=l.FromZipCode AND d.ToCountryCode=l.ToCountryCode AND d.ToZipCode=l.ToZipCode"))
    {
      if ($Row = oswebdb_fetch_row($Result))
        $AllowanceDistance = $Row[0];
      oswebdb_free_result($Result);
    }
    return (integer) $AllowanceDistance;
  }

  function get_allowance($SystemNo, $Year, $No)
  {
    $Allowance = 0;
    $DistanceSystemNo = get_system_for_distances($SystemNo);
    if ($Result = oswebdb_query("SELECT a.Distance1,a.Allowance1,a.Distance2,a.Allowance2,a.Distance3,a.Allowance3,a.Distance4,a.Allowance4,a.Distance5,a.Allowance5,a.Addition1,a.Addition2,SUM(d.Distance),SUM((d.Properties & 1)=1),SUM((d.Properties & 2)=2) FROM Allowances AS a, Allowancelines AS l, Distances AS d WHERE a.SystemNo=$SystemNo AND a.Year=$Year AND a.No=$No AND l.SystemNo=a.SystemNo AND l.Year=a.Year AND l.No=a.No AND d.SystemNo=$DistanceSystemNo AND d.FromCountryCode=l.FromCountryCode AND d.FromZipCode=l.FromZipCode AND d.ToCountryCode=l.ToCountryCode AND d.ToZipCode=l.ToZipCode GROUP BY l.Date"))
    {
      while ($Row = oswebdb_fetch_row($Result))
        $Allowance += calculate_allowance_for_day($Row[0], $Row[1], $Row[2], $Row[3], $Row[4], $Row[5], $Row[6], $Row[7], $Row[8], $Row[9], $Row[10], $Row[11], $Row[12], $Row[13], $Row[14]);
    }
    return (float) $Allowance;
  }

  function get_distance_for_date($SystemNo, $Year, $No, $Date)
  {
    $DateDistance = 0;
    $DistanceSystemNo = get_system_for_distances($SystemNo);
    if ($Result = oswebdb_query("SELECT SUM(d.Distance) FROM Allowancelines AS l, Distances AS d WHERE l.SystemNo=$SystemNo AND l.Year=$Year AND l.No=$No AND l.Date=\"$Date\" AND d.SystemNo=$DistanceSystemNo AND d.FromCountryCode=l.FromCountryCode AND d.FromZipCode=l.FromZipCode AND d.ToCountryCode=l.ToCountryCode AND d.ToZipCode=l.ToZipCode GROUP BY l.Date"))
    {
      if ($Row = oswebdb_fetch_row($Result))
        $DateDistance = $Row[0];
      oswebdb_free_result($Result);
    }
    return (integer) $DateDistance;
  }

  function get_allowance_for_date($SystemNo, $Year, $No, $Date)
  {
    $DateAllowance = 0;
    $DistanceSystemNo = get_system_for_distances($SystemNo);
    if ($Result = oswebdb_query("SELECT a.Distance1,a.Allowance1,a.Distance2,a.Allowance2,a.Distance3,a.Allowance3,a.Distance4,a.Allowance4,a.Distance5,a.Allowance5,a.Addition1,a.Addition2,SUM(d.Distance),SUM((d.Properties & 1)=1),SUM((d.Properties & 2)=2) FROM Allowances AS a, Allowancelines AS l, Distances AS d WHERE a.SystemNo=$SystemNo AND a.Year=$Year AND a.No=$No AND l.SystemNo=a.SystemNo AND l.Year=a.Year AND l.No=a.No AND l.Date=\"$Date\" AND d.SystemNo=$DistanceSystemNo AND d.FromCountryCode=l.FromCountryCode AND d.FromZipCode=l.FromZipCode AND d.ToCountryCode=l.ToCountryCode AND d.ToZipCode=l.ToZipCode GROUP BY l.Date"))
    {
      if ($Row = oswebdb_fetch_row($Result))
        $DateAllowance = calculate_allowance_for_day($Row[0], $Row[1], $Row[2], $Row[3], $Row[4], $Row[5], $Row[6], $Row[7], $Row[8], $Row[9], $Row[10], $Row[11], $Row[12], $Row[13], $Row[14]);
    }
    return (float) $DateAllowance;
  }

  function calculate_allowance_for_day($Distance1, $Allowance1, $Distance2, $Allowance2, $Distance3, $Allowance3, $Distance4, $Allowance4, $Distance5, $Allowance5, $Addition1, $Addition2, $DayDistance, $DayCountProperty1, $DayCountProperty2)
  {
    $Allowance = 0;
    if ($Distance1 > 0 && $DayDistance > 0)
    {
      if ($DayDistance > $Distance1)
      {
        $Allowance += $Distance1 * $Allowance1;
        $DayDistance -= $Distance1;
      }
      else
      {
        $Allowance += $DayDistance * $Allowance1;
        $DayDistance = 0;
      }
    }
    if ($Distance2 > 0 && $DayDistance > 0)
    {
      if ($DayDistance > ($Distance2 - $Distance1))
      {
        $Allowance += ($Distance2 - $Distance1) * $Allowance2;
        $DayDistance -= ($Distance2 - $Distance1);
      }
      else
      {
        $Allowance += $DayDistance * $Allowance2;
        $DayDistance = 0;
      }
    }
    if ($Distance3 > 0 && $DayDistance > 0)
    {
      if ($DayDistance > ($Distance3 - $Distance2))
      {
        $Allowance += ($Distance3 - $Distance2) * $Allowance3;
        $DayDistance -= ($Distance3 - $Distance2);
      }
      else
      {
        $Allowance += $DayDistance * $Allowance3;
        $DayDistance = 0;
      }
    }
    if ($Distance4 > 0 && $DayDistance > 0)
    {
      if ($DayDistance > ($Distance4 - $Distance3))
      {
        $Allowance += ($Distance4 - $Distance3) * $Allowance4;
        $DayDistance -= ($Distance4 - $Distance3);
      }
      else
      {
        $Allowance += $DayDistance * $Allowance4;
        $DayDistance = 0;
      }
    }
    if ($Distance5 > 0 && $DayDistance > 0)
    {
      if ($DayDistance > ($Distance5 - $Distance4))
      {
        $Allowance += ($Distance5 - $Distance4) * $Allowance5;
        $DayDistance -= ($Distance5 - $Distance4);
      }
      else
      {
        $Allowance += $DayDistance * $Allowance5;
        $DayDistance = 0;
      }
    }
    if ($Addition1 > 0 && $DayCountProperty1 > 0)
    {
      $Allowance += $DayCountProperty1 * $Addition1;
    }
    if ($Addition2 > 0 && $DayCountProperty2 > 0)
      $Allowance += $DayCountProperty2 * $Addition2;
    return (float) ($Allowance / 100);
  }
?>
