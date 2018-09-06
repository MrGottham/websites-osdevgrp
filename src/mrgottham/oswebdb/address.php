<?php
  // Include required files.
  require_once("oswebdb/oswebdb.php");

  // Functions for the addresses table.
  function insert_address($SystemNo, $No, $Name, $Address1, $Address2, $ZipCode, $CountryCode, $Phone1, $Phone2, $Fax, $BirthDate, $GroupNo, $Public, $Email, $Web, $ParentNo)
  {
    $Result = 1;
    $Fields = "SystemNo,No,Name";
    $Values = "$SystemNo,$No,\"$Name\"";
    if (isset($Address1))
    {
      $Fields = "$Fields,Address1";
      $Values = "$Values,\"$Address1\"";
    }
    if (isset($Address2))
    {
      $Fields = "$Fields,Address2";
      $Values = "$Values,\"$Address2\"";
    }
    if (isset($ZipCode))
    {
      $Fields = "$Fields,ZipCode";
      $Values = "$Values,\"$ZipCode\"";
    }
    if (isset($CountryCode))
    {
      $Fields = "$Fields,CountryCode";
      $Values = "$Values,$CountryCode";
    }
    if (isset($Phone1))
    {
      $Fields = "$Fields,Phone1";
      $Values = "$Values,\"$Phone1\"";
    }
    if (isset($Phone2))
    {
      $Fields = "$Fields,Phone2";
      $Values = "$Values,\"$Phone2\"";
    }
    if (isset($Fax))
    {
      $Fields = "$Fields,Fax";
      $Values = "$Values,\"$Fax\"";
    }
    if (isset($BirthDate))
    {
      $Fields = "$Fields,BirthDate";
      $Values = "$Values,\"$BirthDate\"";
    }
    if (isset($GroupNo))
    {
      $Fields = "$Fields,GroupNo";
      $Values = "$Values,$GroupNo";
    }
    if (isset($Public))
    {
      $Fields = "$Fields,Public";
      $Values = "$Values,$Public";
    }
    if (isset($Email))
    {
      $Fields = "$Fields,Email";
      $Values = "$Values,\"$Email\"";
    }
    if (isset($Web))
    {
      $Fields = "$Fields,Web";
      $Values = "$Values,\"$Web\"";
    }
    if (isset($ParentNo))
    {
      $Fields = "$Fields,ParentNo";
      $Values = "$Values,$ParentNo";
    }
    if (!oswebdb_query("INSERT INTO Addresses ($Fields) VALUES($Values)"))
      $Result = 0;
    return $Result;
  }

  function update_address($SystemNo, $No, $Name, $Address1, $Address2, $ZipCode, $CountryCode, $Phone1, $Phone2, $Fax, $BirthDate, $GroupNo, $Public, $Email, $Web, $ParentNo)
  {
    $Result = 1;
    $Set = "";
    if (isset($Name))
    {
      if (strlen($Set) > 0)
        $Set = "$Set,Name=\"$Name\"";
      else
        $Set = "Name=\"$Name\"";
    }
    if (isset($Address1))
    {
      if (strlen($Set) > 0)
        $Set = "$Set,Address1=\"$Address1\"";
      else
        $Set = "Address1=\"$Address1\"";
    }
    if (isset($Address2))
    {
      if (strlen($Set) > 0)
        $Set = "$Set,Address2=\"$Address2\"";
      else
        $Set = "Address2=\"$Address2\"";
    }
    if (isset($ZipCode))
    {
      if (strlen($Set) > 0)
        $Set = "$Set,ZipCode=\"$ZipCode\"";
      else
        $Set = "ZipCode=\"$ZipCode\"";
    }
    if (isset($CountryCode))
    {
      if (strlen($Set) > 0)
        $Set = "$Set,CountryCode=$CountryCode";
      else
        $Set = "CountryCode=$CountryCode";
    }
    if (isset($Phone1))
    {
      if (strlen($Set) > 0)
        $Set = "$Set,Phone1=\"$Phone1\"";
      else
        $Set = "Phone1=\"$Phone1\"";
    }
    if (isset($Phone2))
    {
      if (strlen($Set) > 0)
        $Set = "$Set,Phone2=\"$Phone2\"";
      else
        $Set = "Phone2=\"$Phone2\"";
    }
    if (isset($Fax))
    {
      if (strlen($Set) > 0)
        $Set = "$Set,Fax=\"$Fax\"";
      else
        $Set = "Fax=\"$Fax\"";
    }
    if (isset($BirthDate))
    {
      if (strlen($Set) > 0)
        $Set = "$Set,BirthDate=\"$BirthDate\"";
      else
        $Set = "BirthDate=\"$BirthDate\"";
    }
    if (isset($GroupNo))
    {
      if (strlen($Set) > 0)
        $Set = "$Set,GroupNo=$GroupNo";
      else
        $Set = "GroupNo=$GroupNo";
    }
    if (isset($Public))
    {
      if (strlen($Set) > 0)
        $Set = "$Set,Public=$Public";
      else
        $Set = "Public=$Public";
    }
    else if (strlen($Set) > 0)
      $Set = "$Set,Public=0";
    else
      $Set = "Public=0";
    if (isset($Email))
    {
      if (strlen($Set) > 0)
        $Set = "$Set,Email=\"$Email\"";
      else
        $Set = "Email=\"$Email\"";
    }
    if (isset($Web))
    {
      if (strlen($Set) > 0)
        $Set = "$Set,Web=\"$Web\"";
      else
        $Set = "Web=\"$Web\"";
    }
    if (isset($ParentNo))
    {
      if (strlen($Set) > 0)
        $Set = "$Set,ParentNo=$ParentNo";
      else
        $Set = "ParentNo=ParentNo";
    }
    if (!oswebdb_query("UPDATE Addresses SET $Set WHERE SystemNo=$SystemNo AND No=$No"))
      $Result = 0;
    return $Result;
  }

  function delete_address($SystemNo, $No)
  {
    $Result = 1;
    if (!oswebdb_query("UPDATE Addresses SET ParentNo=0 WHERE SystemNo=$SystemNo AND ParentNo=$No"))
      $Result = 0;
    else if (!oswebdb_query("DELETE FROM Addressmatches WHERE SystemNo=$SystemNo AND AddressNo=$No"))
      $Result = 0;
    else if (!oswebdb_query("DELETE FROM Addresslinks WHERE SystemNo=$SystemNo AND AddressNo=$No"))
      $Result = 0;
    else if (!oswebdb_query("DELETE FROM Addresses WHERE SystemNo=$SystemNo AND No=$No"))
      $Result = 0;
    return $Result;
  }

  function insert_address_link($SystemNo, $AddressNo, $TableNo, $SeasonNo, $TypeNo, $Property0, $Property1, $Property2, $Property3, $Property4, $Property5, $Property6, $Property7, $Average, $Distance)
  {
    $Result = 1;
    $Fields = "SystemNo,AddressNo,TableNo,SeasonNo,TypeNo";
    $Values = "$SystemNo,$AddressNo,$TableNo,$SeasonNo,$TypeNo";
    $TypeFields = 0;
    if ($TableNo == 8)
      $TypeFields += 1;
    if ($TableNo == 8)
      $TypeFields += 2;
    if ($TableNo == 8)
      $TypeFields += 4;
    if ($TypeFields & 1)
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
      $Fields = "$Fields,Properties";
      $Values = "$Values,$Properties";
    }
    if ($TypeFields & 2)
    {
      if (isset($Average))
      {
        $Average = str_replace(",", ".", str_replace(".", "", $Average));
        if (strlen($Average) == 0)
          $Average = 0;
      }
      else
        $Average = 0;
      $Fields = "$Fields,Average";
      $Values = "$Values,$Average";
    }
    if ($TypeFields & 4)
    {
      if (isset($Distance))
      {
        $Distance = str_replace(",", ".", str_replace(".", "", $Distance));
        if (strlen($Distance) == 0)
          $Distance = 0;
      }
      else
        $Distance = 0;
      $Fields = "$Fields,Distance";
      $Values = "$Values,$Distance";
    }
    if (!oswebdb_query("INSERT INTO Addresslinks ($Fields) VALUES($Values)"))
      $Result = 0;
    return $Result;
  }

  function update_address_link($SystemNo, $AddressNo, $TableNo, $SeasonNo, $TypeNo, $Property0, $Property1, $Property2, $Property3, $Property4, $Property5, $Property6, $Property7, $Average, $Distance)
  {
    $Result = 1;
    $Set = "";
    $TypeFields = 0;
    if ($TableNo == 8)
      $TypeFields += 1;
    if ($TableNo == 8)
      $TypeFields += 2;
    if ($TableNo == 8)
      $TypeFields += 4;
    if ($TypeFields & 1)
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
      if (strlen($Set) > 0)
        $Set = "$Set,Properties=$Properties";
      else
        $Set = "Properties=$Properties";
    }
    if ($TypeFields & 2)
    {
      if (isset($Average))
      {
        $Average = str_replace(",", ".", str_replace(".", "", $Average));
        if (strlen($Average) == 0)
          $Average = 0;
      }
      else
        $Average = 0;
      if (strlen($Set) > 0)
        $Set = "$Set,Average=$Average";
      else
        $Set = "Average=$Average";
    }
    if ($TypeFields & 4)
    {
      if (isset($Distance))
      {
        $Distance = str_replace(",", ".", str_replace(".", "", $Distance));
        if (strlen($Distance) == 0)
          $Distance = 0;
      }
      else
        $Distance = 0;
      if (strlen($Set) > 0)
        $Set = "$Set,Distance=$Distance";
      else
        $Set = "Distance=$Distance";
    }
    if (strlen($Set) > 0)
    {
      if (!oswebdb_query("UPDATE Addresslinks SET $Set WHERE SystemNo=$SystemNo AND AddressNo=$AddressNo AND TableNo=$TableNo AND SeasonNo=$SeasonNo AND TypeNo=$TypeNo"))
        $Result = 0;
    }
    return $Result;
  }

  function delete_address_link($SystemNo, $AddressNo, $TableNo, $SeasonNo, $TypeNo)
  {
    $Result = 1;
    switch ($TableNo)
    {
      case 8:
        // Delete an average type.
        if ($Result)
        {
        }
        break;
    }
    if ($Result)
    {
      if (!oswebdb_query("DELETE FROM Addresslinks WHERE SystemNo=$SystemNo AND AddressNo=$AddressNo AND TableNo=$TableNo AND SeasonNo=$SeasonNo AND TypeNo=$TypeNo"))
        $Result = 0;
    }
    return $Result;
  }

  function insert_address_match($SystemNo, $AddressNo, $TableNo, $SeasonNo, $TypeNo, $Date, $Score, $Entries, $Series, $Point, $Matches)
  {
    $Result = 1;
    $MatchId = 1;
    if ($MatchResult = oswebdb_query("SELECT MatchId FROM Addressmatches WHERE SystemNo=$SystemNo AND AddressNo=$AddressNo AND TableNo=$TableNo AND SeasonNo=$SeasonNo AND TypeNo=$TypeNo ORDER BY MatchId DESC"))
    {
      if ($MatchRow = oswebdb_fetch_row($MatchResult))
        $MatchId = $MatchRow[0] + 1;
      oswebdb_free_result($MatchResult);
    }
    $Fields = "SystemNo,AddressNo,TableNo,SeasonNo,TypeNo,MatchId";
    $Values = "$SystemNo,$AddressNo,$TableNo,$SeasonNo,$TypeNo,$MatchId";
    if (isset($Date))
    {
      $Fields = "$Fields,Date";
      $Values = "$Values,\"$Date\"";
    }
    $Fields = "$Fields,Score";
    if (isset($Score))
    {
      if (((integer) $Score) > 0)
        $Values = "$Values,$Score";
      else
        $Values = "$Values,0";
    }
    else
      $Values = "$Values,0";
    $Fields = "$Fields,Entries";
    if (isset($Entries))
    {
      if (((integer) $Entries) > 0)
        $Values = "$Values,$Entries";
      else
        $Values = "$Values,0";
    }
    else
      $Values = "$Values,0";
    $Fields = "$Fields,Series";
    if (isset($Series))
    {
      if (((integer) $Series) > 0)
        $Values = "$Values,$Series";
      else
        $Values = "$Values,0";
    }
    else
      $Values = "$Values,0";
    $Fields = "$Fields,Point";
    if (isset($Point))
    {
      if (((integer) $Point) > 0)
        $Values = "$Values,$Point";
      else
        $Values = "$Values,0";
    }
    else
      $Values = "$Values,0";
    $Fields = "$Fields,Matches";
    if (isset($Matches))
    {
      if (((integer) $Matches) > 1)
        $Values = "$Values,$Matches";
      else
        $Values = "$Values,1";
    }
    else
      $Values = "$Values,1";
    if (!oswebdb_query("INSERT INTO Addressmatches ($Fields) VALUES($Values)"))
      $Result = 0;
    return $Result;
  }

  function update_address_match($SystemNo, $AddressNo, $TableNo, $SeasonNo, $TypeNo, $MatchId, $Date, $Score, $Entries, $Series, $Point, $Matches)
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
    if (isset($Score))
    {
      if (((integer) $Score) <= 0)
        $Score = 0;
      if (strlen($Set) > 0)
        $Set = "$Set,Score=$Score";
      else
        $Set = "Score=$Score";
    }
    else if (strlen($Str) > 0)
      $Set = "$Set,Score=0";
    else
      $Set = "Score=0";
    if (isset($Entries))
    {
      if (((integer) $Entries) <= 0)
        $Entries = 0;
      if (strlen($Set) > 0)
        $Set = "$Set,Entries=$Entries";
      else
        $Set = "Entries=$Entries";
    }
    else if (strlen($Str) > 0)
      $Set = "$Set,Entries=0";
    else
      $Set = "Entries=0";
    if (isset($Series))
    {
      if (((integer) $Series) <= 0)
        $Series = 0;
      if (strlen($Set) > 0)
        $Set = "$Set,Series=$Series";
      else
        $Set = "Series=$Series";
    }
    else if (strlen($Str) > 0)
      $Set = "$Set,Series=0";
    else
      $Set = "Series=0";
    if (isset($Point))
    {
      if (((integer) $Point) <= 0)
        $Point = 0;
      if (strlen($Set) > 0)
        $Set = "$Set,Point=$Point";
      else
        $Set = "Point=$Point";
    }
    else if (strlen($Str) > 0)
      $Set = "$Set,Point=0";
    else
      $Set = "Point=0";
    if (isset($Matches))
    {
      if (((integer) $Matches) <= 1)
        $Matches = 1;
      if (strlen($Set) > 0)
        $Set = "$Set,Matches=$Matches";
      else
        $Set = "Matches=$Matches";
    }
    else if (strlen($Str) > 0)
      $Set = "$Set,Matches=1";
    else
      $Set = "Matches=1";
    if (!oswebdb_query("UPDATE Addressmatches Set $Set WHERE SystemNo=$SystemNo AND AddressNo=$AddressNo AND TableNo=$TableNo AND SeasonNo=$SeasonNo AND TypeNo=$TypeNo AND MatchId=$MatchId"))
      $Result = 0;
    return $Result;
  }

  function delete_address_match($SystemNo, $AddressNo, $TableNo, $SeasonNo, $TypeNo, $MatchId)
  {
    $Result = 1;
    if (!oswebdb_query("DELETE FROM Addressmatches WHERE SystemNo=$SystemNo AND AddressNo=$AddressNo AND TableNo=$TableNo AND SeasonNo=$SeasonNo AND TypeNo=$TypeNo AND MatchId=$MatchId"))
      $Result = 0;
    return $Result;
  }
?>
