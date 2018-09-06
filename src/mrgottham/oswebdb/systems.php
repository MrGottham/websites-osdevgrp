<?php
  // Include required files.
  require_once("oswebdb/oswebdb.php");

  // Functions for the systems table.
  function update_system($SystemNo, $Title, $Name, $Address1, $Address2, $Address3, $Phone, $Mobile, $Email, $CountryCode, $SeasonNo, $Databaseadministration, $Debate, $Addresses, $Calender, $Allowance, $PathPictures, $DaysPictures, $PathDocuments, $DaysDocuments, $AdminRole, $ConfigRole, $UserRole, $MetaDescription, $MetaKeywords, $MenuTitle)
  {
    $Result = 1;
    $Update = "";
    if (isset($Title))
      $Update = "$Update,Title=\"$Title\"";
    if (isset($Name))
      $Update = "$Update,Name=\"$Name\"";
    if (isset($Address1))
      $Update = "$Update,Address1=\"$Address1\"";
    if (isset($Address2))
      $Update = "$Update,Address2=\"$Address2\"";
    if (isset($Address3))
      $Update = "$Update,Address3=\"$Address3\"";
    if (isset($Phone))
      $Update = "$Update,Phone=\"$Phone\"";
    if (isset($Mobile))
      $Update = "$Update,Mobile=\"$Mobile\"";
    if (isset($Email))
      $Update = "$Update,Email=\"$Email\"";
    if (isset($CountryCode))
      $Update = "$Update,CountryCode=$CountryCode";
    if (isset($SeasonNo))
      $Update = "$Update,SeasonNo=$SeasonNo";
    $Properties = 0;
    if (isset($Databaseadministration))
      $Properties += (int) $Databaseadministration;
    if (isset($Debate))
      $Properties += (int) $Debate;
    else if (oswebdb_get_rows("SELECT SystemNo,Username,Date,Time FROM Debate WHERE SystemNo=$SystemNo") > 0)
      $Properties += 2;
    if (isset($Addresses))
      $Properties += (int) $Addresses;
    else if (oswebdb_get_rows("SELECT SystemNo,No FROM Addresses WHERE SystemNo=$SystemNo") + oswebdb_get_rows("SELECT SystemNo,CountryCode,Zipcode FROM Zipcodes WHERE SystemNo=$SystemNo") + oswebdb_get_rows("SELECT SystemNo,TableNo,No FROM Systemtables WHERE SystemNo=$SystemNo AND TableNo IN (2,3,8,9)") > 0)
      $Properties += 4;
    if (isset($Calender))
      $Properties += (int) $Calender;
    else if (oswebdb_get_rows("SELECT SystemNo,CalId FROM Calapps WHERE SystemNo=$SystemNo") + oswebdb_get_rows("SELECT SystemNo,CalId,UserId FROM Calmerge WHERE SystemNo=$SystemNo") + oswebdb_get_rows("SELECT SystemNo,UserId FROM Calusers WHERE SystemNo=$SystemNo") > 0)
      $Properties += 8;
    if (isset($Allowance))
      $Properties += (int) $Allowance;
    else if (oswebdb_get_rows("SELECT SystemNo,Year,No FROM Allowances WHERE SystemNo=$SystemNo") + oswebdb_get_rows("SELECT SystemNo,Year,No FROM Allowancelines WHERE SystemNo=$SystemNo") + oswebdb_get_rows("SELECT SystemNo,FromCountryCode,FromZipcode,ToCountryCode,ToZipcode FROM Distances WHERE SystemNo=$SystemNo") + oswebdb_get_rows("SELECT SystemNo,CountryCode,Zipcode FROM Zipcodes WHERE SystemNo=$SystemNo") + oswebdb_get_rows("SELECT SystemNo,TableNo,No FROM Systemtables WHERE SystemNo=$SystemNo AND TableNo=2") > 0)
      $Properties += 16;
    $Update = "$Update,Properties=$Properties";
    if (isset($PathPictures))
      $Update = "$Update,PathPictures=\"$PathPictures\"";
    if (isset($DaysPictures))
      $Update = "$Update,DaysPictures=$DaysPictures";
    if (isset($PathDocuments))
      $Update = "$Update,PathDocuments=\"$PathDocuments\"";
    if (isset($DaysDocuments))
      $Update = "$Update,DaysDocuments=$DaysDocuments";
    if (isset($AdminRole))
      $Update = "$Update,AdminRole=$AdminRole";
    if (isset($ConfigRole))
      $Update = "$Update,ConfigRole=$ConfigRole";
    if (isset($UserRole))
      $Update = "$Update,UserRole=$UserRole";
    if (isset($MetaDescription))
      $Update = "$Update,MetaDescription=\"$MetaDescription\"";
    if (isset($MetaKeywords))
      $Update = "$Update,MetaKeywords=\"$MetaKeywords\"";
    if (isset($MenuTitle))
      $Update = "$Update,MenuTitle=\"$MenuTitle\"";
    if (strlen($Update) > 0)
      $Update = substr($Update, 1, strlen($Update) - 1);
    if (!oswebdb_query("UPDATE Systems SET $Update WHERE SystemNo=$SystemNo"))
      $Result = 0;
    return $Result;
  }

  function delete_system($SystemNo)
  {
    $Result = 1;
    for ($i = 0; $i < 15 && $Result; $i++)
    {
      switch ($i)
      {
        case 0:
          $Statement = "DELETE FROM Addressmatches WHERE SystemNo=$SystemNo";
          break;

        case 1:
          $Statement = "DELETE FROM Addresslinks WHERE SystemNo=$SystemNo";
          break;

        case 2:
          $Statement = "DELETE FROM Addresses WHERE SystemNo=$SystemNo";
          break;

        case 3:
          $Statement = "DELETE FROM Calmerge WHERE SystemNo=$SystemNo";
          break;

        case 4:
          $Statement = "DELETE FROM Calapps WHERE SystemNo=$SystemNo";
          break;

        case 5:
          $Statement = "DELETE FROM Calusers WHERE SystemNo=$SystemNo";
          break;

        case 6:
          $Statement = "DELETE FROM Debate WHERE SystemNo=$SystemNo";
          break;

        case 7:
          $Statement = "DELETE FROM Allowancelines WHERE SystemNo=$SystemNo";
          break;

        case 8:
          $Statement = "DELETE FROM Allowances WHERE SystemNo=$SystemNo";
          break;

        case 9:
          $Statement = "DELETE FROM Webcontent WHERE SystemNo=$SystemNo";
          break;

        case 10:
          $Statement = "DELETE FROM Websites WHERE SystemNo=$SystemNo";
          break;

        case 11:
          $Statement = "DELETE FROM Distances WHERE SystemNo=$SystemNo";
          break;

        case 12:
          $Statement = "DELETE FROM Zipcodes WHERE SystemNo=$SystemNo";
          break;

        case 13:
          $Statement = "DELETE FROM Systemtables WHERE SystemNo=$SystemNo";
          break;

        case 14:
          $Statement = "DELETE FROM Systems WHERE SystemNo=$SystemNo";
          break;
      }
      if (!oswebdb_query($Statement))
        $Result = 0;
    }
    return $Result;
  }

  function get_system_properties($SystemNo)
  {
    $SystemProperties = 0;
    if ($Result = oswebdb_query("SELECT Properties FROM Systems WHERE SystemNo=$SystemNo"))
    {
      if ($Row = oswebdb_fetch_row($Result))
        $SystemProperties = $Row[0];
      oswebdb_free_result($Result);
    }
    return $SystemProperties;
  }

  function get_system_country_code($SystemNo)
  {
    $SystemCountryCode = 0;
    if ($Result = oswebdb_query("SELECT CountryCode FROM Systems WHERE SystemNo=$SystemNo"))
    {
      if ($Row = oswebdb_fetch_row($Result))
        $SystemCountryCode = (integer) $Row[0];
      oswebdb_free_result($Result);
    }
    return $SystemCountryCode;
  }

  function get_system_season_no($SystemNo)
  {
    $SystemSeasonNo = 0;
    if ($Result = oswebdb_query("SELECT SeasonNo FROM Systems WHERE SystemNo=$SystemNo"))
    {
      if ($Row = oswebdb_fetch_row($Result))
        $SystemSeasonNo = (integer) $Row[0];
      oswebdb_free_result($Result);
    }
    return $SystemSeasonNo;
  }

  function get_system_path_pictures($SystemNo)
  {
    $SystemPathPictures = ".";
    if ($Result = oswebdb_query("SELECT PathPictures FROM Systems WHERE SystemNo=$SystemNo"))
    {
      if ($Row = oswebdb_fetch_row($Result))
        $SystemPathPictures = ($Row[0] == "/" ? "./" : $Row[0]);
      oswebdb_free_result($Result);
    }
    return $SystemPathPictures;
  }

  function get_system_days_pictures($SystemNo)
  {
    $SystemDaysPictures = 7;
    if ($Result = oswebdb_query("SELECT DaysPictures FROM Systems WHERE SystemNo=$SystemNo"))
    {
      if ($Row = oswebdb_fetch_row($Result))
        $SystemDaysPictures = $Row[0];
      oswebdb_free_result($Result);
    }
    return $SystemDaysPictures;
  }

  function get_system_path_documents($SystemNo)
  {
    $SystemPathDocuments = ".";
    if ($Result = oswebdb_query("SELECT PathDocuments FROM Systems WHERE SystemNo=$SystemNo"))
    {
      if ($Row = oswebdb_fetch_row($Result))
        $SystemPathDocuments = ($Row[0] == "/" ? "./" : $Row[0]);
      oswebdb_free_result($Result);
    }
    return $SystemPathDocuments;
  }

  function get_system_days_documents($SystemNo)
  {
    $SystemDaysDocuments = 7;
    if ($Result = oswebdb_query("SELECT DaysDocuments FROM Systems WHERE SystemNo=$SystemNo"))
    {
      if ($Row = oswebdb_fetch_row($Result))
        $SystemDaysDocuments = $Row[0];
      oswebdb_free_result($Result);
    }
    return $SystemDaysDocuments;
  }
?>
