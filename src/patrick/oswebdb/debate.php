<?php
  // Include required files.
  require_once("oswebdb/oswebdb.php");

  // Functions for the debate table.
  function insert_debate($SystemNo, $Username, $Email, $Date, $Time, $Subject, $Content, $Reference, $ParentUsername, $ParentDate, $ParentTime)
  {
    $Result = 1;
    $Fields = "SystemNo";
    $Values = "$SystemNo";
    if (isset($Username))
    {
      $Fields = "$Fields,Username";
      $Values = "$Values,\"$Username\"";
    }
    if (isset($Email))
    {
      $Fields = "$Fields,Email";
      $Values = "$Values,\"$Email\"";
    }
    if (isset($Date))
    {
      $Fields = "$Fields,Date";
      $Values = "$Values,\"$Date\"";
    }
    if (isset($Time))
    {
      $Fields = "$Fields,Time";
      $Values = "$Values,\"$Time\"";
    }
    if (isset($Subject))
    {
      $Fields = "$Fields,Subject";
      $Values = "$Values,\"$Subject\"";
    }
    if (isset($Content))
    {
      $Fields = "$Fields,Content";
      $Values = "$Values,\"$Content\"";
    }
    if (isset($Reference))
    {
      $Fields = "$Fields,Reference";
      $Values = "$Values,\"$Reference\"";
    }
    if (isset($ParentUsername))
    {
      $Fields = "$Fields,ParentUsername";
      $Values = "$Values,\"$ParentUsername\"";
    }
    else
    {
      $Fields = "$Fields,ParentUsername";
      $Values = "$Values,NULL";
    }
    if (isset($ParentDate))
    {
      $Fields = "$Fields,ParentDate";
      $Values = "$Values,\"$ParentDate\"";
    }
    else
    {
      $Fields = "$Fields,ParentDate";
      $Values = "$Values,NULL";
    }
    if (isset($ParentTime))
    {
      $Fields = "$Fields,ParentTime";
      $Values = "$Values,\"$ParentTime\"";
    }
    else
    {
      $Fields = "$Fields,ParentTime";
      $Values = "$Values,NULL";
    }
    if (!oswebdb_query("INSERT INTO Debate ($Fields) VALUES($Values)"))
      $Result = 0;
    return $Result;
  }

  function delete_debate($SystemNo, $Username, $Date, $Time)
  {
    $Result = 1;
    if ($ChildResult = oswebdb_query("SELECT Username,Date,Time FROM Debate WHERE SystemNo=$SystemNo AND ParentUsername=\"$Username\" AND ParentDate=\"$Date\" AND ParentTime=\"$Time\" ORDER BY Date DESC,Time DESC,Username"))
    {
      while ($Result && $ChildRow = oswebdb_fetch_row($ChildResult))
        $Result = delete_debate($SystemNo,$ChildRow[0],$ChildRow[1],$ChildRow[2]);
      oswebdb_free_result($ChildResult);
    }
    if ($Result)
    {
      if (!oswebdb_query("DELETE FROM Debate WHERE SystemNo=$SystemNo AND Username=\"$Username\" AND Date=\"$Date\" AND Time=\"$Time\""))
        $Result = 0;
    }
    return $Result;
  }
?>
