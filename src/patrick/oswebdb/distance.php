<?php
  // Include required files.
  require_once("oswebdb/oswebdb.php");

  // Functions for the distance table.
  function insert_distance($SystemNo, $FromCountryCode, $FromZipCode, $ToCountryCode, $ToZipCode, $Distance, $Property1, $Property2, $Property3, $Property4, $Property5, $Property6, $Property7, $Property8, $UpdateOther)
  {
    $Result = 1;
    $DistanceSystemNo = get_system_for_distances($SystemNo);
    $Fields = "SystemNo,FromCountryCode,FromZipCode,ToCountryCode,ToZipCode";
    $Values = "$DistanceSystemNo,$FromCountryCode,\"$FromZipCode\",$ToCountryCode,\"$ToZipCode\"";
    if (isset($Distance))
    {
      $Fields = "$Fields,Distance";
      $Values = "$Values,$Distance";
    }
    $Properties = 0;
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
    $Fields = "$Fields,Properties";
    $Values = "$Values,$Properties";
    if (!oswebdb_query("INSERT INTO Distances ($Fields) VALUES($Values)"))
      $Result = 0;
    else if ($UpdateOther)
    {
      if (oswebdb_get_rows("SELECT SystemNo,FromCountryCode,FromZipCode,ToCountryCode,ToZipCode,Distance,Properties FROM Distances WHERE SystemNo=$DistanceSystemNo AND FromCountryCode=$ToCountryCode AND FromZipCode=\"$ToZipCode\" AND ToCountryCode=$FromCountryCode AND ToZipCode=\"$FromZipCode\"") == 0)
        $Result = insert_distance($SystemNo, $ToCountryCode, $ToZipCode, $FromCountryCode, $FromZipCode, $Distance, $Property1, $Property2, $Property3, $Property4, $Property5, $Property6, $Property7, $Property8, 0);
      else
        $Result = update_distance($SystemNo, $ToCountryCode, $ToZipCode, $FromCountryCode, $FromZipCode, $Distance, $Property1, $Property2, $Property3, $Property4, $Property5, $Property6, $Property7, $Property8, 0);
    }
    return $Result;
  }

  function update_distance($SystemNo, $FromCountryCode, $FromZipCode, $ToCountryCode, $ToZipCode, $Distance, $Property1, $Property2, $Property3, $Property4, $Property5, $Property6, $Property7, $Property8, $UpdateOther)
  {
    $Result = 1;
    $DistanceSystemNo = get_system_for_distances($SystemNo);
    $Update = "";
    if (isset($Distance))
    {
      if (strlen($Update) > 0)
        $Update = "$Update,Distance=$Distance";
      else
        $Update = "Distance=$Distance";
    }
    $Properties = 0;
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
    if (strlen($Update) > 0)
      $Update = "$Update,Properties=$Properties";
    else
      $Update = "Properties=$Properties";
    if (!oswebdb_query("UPDATE Distances SET $Update WHERE SystemNo=$DistanceSystemNo AND FromCountryCode=$FromCountryCode AND FromZipCode=\"$FromZipCode\" AND ToCountryCode=$ToCountryCode AND ToZipCode=\"$ToZipCode\""))
      $Result = 0;
    else if ($UpdateOther)
    {
      if (oswebdb_get_rows("SELECT SystemNo,FromCountryCode,FromZipCode,ToCountryCode,ToZipCode,Distance,Properties FROM Distances WHERE SystemNo=$DistanceSystemNo AND FromCountryCode=$ToCountryCode AND FromZipCode=\"$ToZipCode\" AND ToCountryCode=$FromCountryCode AND ToZipCode=\"$FromZipCode\"") == 0)
        $Result = insert_distance($SystemNo, $ToCountryCode, $ToZipCode, $FromCountryCode, $FromZipCode, $Distance, $Property1, $Property2, $Property3, $Property4, $Property5, $Property6, $Property7, $Property8, 0);
      else
        $Result = update_distance($SystemNo, $ToCountryCode, $ToZipCode, $FromCountryCode, $FromZipCode, $Distance, $Property1, $Property2, $Property3, $Property4, $Property5, $Property6, $Property7, $Property8, 0);
    }
    return $Result;
  }

  function delete_distance($SystemNo, $FromCountryCode, $FromZipCode, $ToCountryCode, $ToZipCode, $DeleteOther)
  {
    $Result = 1;
    $DistanceSystemNo = get_system_for_distances($SystemNo);
    if ($Result && $DeleteOther)
      $Result = delete_distance($SystemNo, $ToCountryCode, $ToZipCode, $FromCountryCode, $FromZipCode, 0);
    if ($Result)
    {
      for ($i = 0; $i < 2 && $Result; $i++)
      {
        switch ($i)
        {
          case 0:
            if ($DistanceSystemNo == 0)
              $Statement = "DELETE FROM Allowancelines WHERE FromCountryCode=$FromCountryCode AND FromZipCode=\"$FromZipCode\" AND ToCountryCode=$ToCountryCode AND ToZipCode=\"$ToZipCode\"";
            else
              $Statement = "DELETE FROM Allowancelines WHERE SystemNo=$SystemNo AND FromCountryCode=$FromCountryCode AND FromZipCode=\"$FromZipCode\" AND ToCountryCode=$ToCountryCode AND ToZipCode=\"$ToZipCode\"";
            break;

          case 1:
            $Statement = "DELETE FROM Distances WHERE SystemNo=$DistanceSystemNo AND FromCountryCode=$FromCountryCode AND FromZipCode=\"$FromZipCode\" AND ToCountryCode=$ToCountryCode AND ToZipCode=\"$ToZipCode\"";
            break;
        }
        if (!oswebdb_query($Statement))
          $Result = 0;
      }
    }
    return $Result;
  }

  function get_system_for_distances($SystemNo)
  {
    return 0;
  }

  function get_distance_information($SystemNo, $FromCountryCode, $FromZipCode, $ToCountryCode, $ToZipCode)
  {
    $DistanceInformation = array(0, 0);
    $DistanceSystemNo = get_system_for_distances($SystemNo);
    if ($Result = oswebdb_query("SELECT Distance,Properties FROM Distances WHERE SystemNo=$DistanceSystemNo AND FromCountryCode=$FromCountryCode AND FromZipCode=\"$FromZipCode\" AND ToCountryCode=$ToCountryCode AND ToZipCode=\"$ToZipCode\""))
    {
      if ($Row = oswebdb_fetch_row($Result))
        $DistanceInformation = array($Row[0], $Row[1]);
      oswebdb_free_result($Result);
    }
    return $DistanceInformation;
  }
?>
