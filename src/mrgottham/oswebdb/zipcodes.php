<?php
  // Include required files.
  require_once("oswebdb/oswebdb.php");
  require_once("oswebdb/tables.php");
  require_once("oswebdb/distance.php");

  // Functions for the zipcodes table.
  function insert_zipcode($SystemNo, $CountryCode, $ZipCode, $CityName)
  {
    $Result = 1;
    $ZipCodeSystemNo = get_system_for_zipcodes($SystemNo);
    if (!oswebdb_query("INSERT INTO Zipcodes (SystemNo,CountryCode,ZipCode,CityName) VALUES($ZipCodeSystemNo,$CountryCode,\"$ZipCode\",\"$CityName\")"))
      $Result = 0;
    return $Result;
  }

  function update_zipcode($SystemNo, $CountryCode, $ZipCode, $CityName)
  {
    $Result = 1;
    $ZipCodeSystemNo = get_system_for_zipcodes($SystemNo);
    if (!oswebdb_query("UPDATE Zipcodes SET CityName=\"$CityName\" WHERE SystemNo=$ZipCodeSystemNo AND CountryCode=$CountryCode AND ZipCode=\"$ZipCode\""))
      $Result = 0;
    return $Result;
  }

  function delete_zipcode($SystemNo, $CountryCode, $ZipCode)
  {
    $Result = 1;
    $ZipCodeSystemNo = get_system_for_zipcodes($SystemNo);
    $DistanceSystemNo = get_system_for_distances($SystemNo);
    for ($i = 0; $i < 4 && $Result; $i++)
    {
      switch ($i)
      {
        case 0:
          if ($ZipCodeSystemNo == 0)
            $Statement = "UPDATE Addresses SET ZipCode=\"\" WHERE CountryCode=$CountryCode AND ZipCode=\"$ZipCode\"";
          else
            $Statement = "UPDATE Addresses SET ZipCode=\"\" WHERE SystemNo=$SystemNo AND CountryCode=$CountryCode AND ZipCode=\"$ZipCode\"";
          break;

        case 1:
          if ($ZipCodeSystemNo == 0)
            $Statement = "DELETE FROM Allowancelines WHERE (FromCountryCode=$CountryCode AND FromZipCode=\"$ZipCode\") OR (ToCountryCode=$CountryCode AND ToZipCode=\"$ZipCode\")";
          else
            $Statement = "DELETE FROM Allowancelines WHERE SystemNo=$SystemNo AND ((FromCountryCode=$CountryCode AND FromZipCode=\"$ZipCode\") OR (ToCountryCode=$CountryCode AND ToZipCode=\"$ZipCode\"))";
          break;

        case 2:
          if ($ZipCodeSystemNo == 0)
            $Statement = "DELETE FROM Distances WHERE (FromCountryCode=$CountryCode AND FromZipCode=\"$ZipCode\") OR (ToCountryCode=$CountryCode AND ToZipCode=\"$ZipCode\")";
          else
            $Statement = "DELETE FROM Distances WHERE SystemNo=$DistanceSystemNo AND ((FromCountryCode=$CountryCode AND FromZipCode=\"$ZipCode\") OR (ToCountryCode=$CountryCode AND ToZipCode=\"$ZipCode\"))";
          break;

        case 3:
          $Statement = "DELETE FROM Zipcodes WHERE SystemNo=$ZipCodeSystemNo AND CountryCode=$CountryCode AND ZipCode=\"$ZipCode\"";
          break;
      }
      if (!oswebdb_query($Statement))
        $Result = 0;
    }
    return $Result;
  }

  function get_max_zipcode_length($SystemNo)
  {
    $MaxLength = 0;
    $ZipCodeSystemNo = get_system_for_zipcodes($SystemNo);
    if ($Result = oswebdb_query("SELECT ZipCode FROM Zipcodes WHERE SystemNo=$ZipCodeSystemNo"))
    {
      $MaxLength = oswebdb_field_len($Result, 0);
      oswebdb_free_result($Result);
    }
    return $MaxLength;
  }

  function get_zipcode_length($SystemNo, $CountryCode)
  {
    $ZipCodeLength = get_table_field_value(get_system_for_table($SystemNo, 2), 2, $CountryCode, "Length");
    if ($ZipCodeLength == 0)
      $ZipCodeLength = get_max_zipcode_length($SystemNo);
    return $ZipCodeLength;
  }

  function get_city_name_length($SystemNo)
  {
    $CityNameLength = 0;
    $ZipCodeSystemNo = get_system_for_zipcodes($SystemNo);
    if ($Result = oswebdb_query("SELECT CityName FROM Zipcodes WHERE SystemNo=$ZipCodeSystemNo"))
    {
      $CityNameLength = oswebdb_field_len($Result, 0);
      oswebdb_free_result($Result);
    }
    return $CityNameLength;
  }

  function get_city_name($SystemNo, $CountryCode, $ZipCode)
  {
    $CityName = "";
    $ZipCodeSystemNo = get_system_for_zipcodes($SystemNo);
    if ($Result = oswebdb_query("SELECT CityName FROM Zipcodes WHERE SystemNo=$ZipCodeSystemNo AND CountryCode=$CountryCode AND ZipCode=\"$ZipCode\""))
    {
      if ($Row = oswebdb_fetch_row($Result))
        $CityName = $Row[0];
      oswebdb_free_result($Result);
    }
    return $CityName;
  }

  function get_system_for_zipcodes($SystemNo)
  {
    return 0;
  }
?>
