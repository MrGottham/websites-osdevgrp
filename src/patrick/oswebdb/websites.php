<?php
  // Include required files.
  require_once("oswebdb/oswebdb.php");
  require_once("oswebdb/tables.php");

  // Function for the websites table.
  function insert_website($SystemNo, $TypeNo, $MenuNo, $Description)
  {
    $Result = 1;
    $TypeSystemNo = get_system_for_table($SystemNo, 4);
    $TypeFields = get_table_field_value($TypeSystemNo, 4, $TypeNo, "ShowFields");
    $Created = date("Y-m-d", time());
    $Fields = "SystemNo,TypeNo,MenuNo,Description,Created";
    $Values = "$SystemNo,$TypeNo,$MenuNo,\"$Description\",\"$Created\"";
    if ($TypeFields & 4)
    {
      $Public = 1;
      if ($TypeFields & 1)
        $Public = (integer) get_table_field_value(get_system_for_table($SystemNo, 1), 1, $MenuNo, "Public");
      $Fields = "$Fields,Public";
      $Values = "$Values,$Public";
    }
    if ($TypeFields & 8)
    {
      $Fields = "$Fields,Active,ActiveFrom,ActiveTo";
      $Values = "$Values,1,NULL,NULL";
    }
    if ($TypeFields & 16)
    {
      $Owner = get_default_owner();
      $Fields = "$Fields,Owner";
      $Values = "$Values,\"$Owner\"";
    }
    if ($TypeFields & 32)
    {
      $Fields = "$Fields,Picture,PictureThumbnial,PictureXJustify,PictureYJustify";
      $Values = "$Values,\"\",\"\",0,0";
    }
    if ($TypeFields & 64)
    {
      $Fields = "$Fields,Address";
      $Values = "$Values,\"\"";
    }
    if ($TypeFields & 128)
    {
      $Fields = "$Fields,Content";
      $Values = "$Values,\"\"";
    }
    if ($TypeFields & 256)
    {
      $Fields = "$Fields,ShowOnHomepage";
      $Values = "$Values,0";
    }
    if ($TypeFields & 512)
    {
      $Fields = "$Fields,Document";
      $Values = "$Values,\"\"";
    }
    if (!oswebdb_query("INSERT INTO Websites ($Fields) VALUES($Values)"))
      $Result = 0;
    return $Result;
  }

  function update_website($SystemNo, $TypeNo, $MenuNo, $Description, $Created, $Public, $Active, $ActiveFrom, $ActiveTo, $Owner, $Picture, $PictureThumbnial, $PictureXJustify, $PictureYJustify, $Address, $Content, $ShowOnHomepage, $Document, $SecureHTTP)
  {
    $Result = 1;
    $TypeSystemNo = get_system_for_table($SystemNo, 4);
    $TypeFields = get_table_field_value($TypeSystemNo, 4, $TypeNo, "ShowFields");
    $Update = "";
    if ($TypeFields & 2)
    {
      if (isset($Created))
      {
        if (strlen($Update) > 0)
          $Update = "$Update,Created=\"$Created\"";
        else
          $Update = "Created=\"$Created\"";
      }
    }
    if ($TypeFields & 4)
    {
      if (isset($Public))
      {
        if (strlen($Update) > 0)
          $Update = "$Update,Public=$Public";
        else
          $Update = "Public=$Public";
      }
      else if (strlen($Update) > 0)
        $Update = "$Update,Public=0";
      else
        $Update = "Public=0";
    }
    if ($TypeFields & 8)
    {
      if (isset($Active))
      {
        if (strlen($Update) > 0)
          $Update = "$Update,Active=$Active";
        else
          $Update = "Active=$Active";
      }
      else if (strlen($Update) > 0)
        $Update = "$Update,Active=0";
      else
        $Update = "Active=0";
      if (isset($ActiveFrom))
      {
        if (strlen($Update) > 0)
          $Update = "$Update,ActiveFrom=\"$ActiveFrom\"";
        else
          $Update = "ActiveFrom=\"$ActiveFrom\"";
      }
      else if (strlen($Update) > 0)
        $Update = "$Update,ActiveFrom=NULL";
      else
        $Update = "ActiveFrom=NULL";
      if (isset($ActiveTo))
      {
        if (strlen($Update) > 0)
          $Update = "$Update,ActiveTo=\"$ActiveTo\"";
        else
          $Update = "ActiveTo=\"$ActiveTo\"";
      }
      else if (strlen($Update) > 0)
        $Update = "$Update,ActiveTo=NULL";
      else
        $Update = "ActiveTo=NULL";
    }
    if ($TypeFields & 16)
    {
      if (isset($Owner))
      {
        if (strlen($Update) > 0)
          $Update = "$Update,Owner=\"$Owner\"";
        else
          $Update = "Owner=\"$Owner\"";
      }
    }
    if ($TypeFields & 32)
    {
      if (isset($Picture))
      {
        if (strlen($Update) > 0)
          $Update = "$Update,Picture=\"$Picture\"";
        else
          $Update = "Picture=\"$Picture\"";
      }
      if (isset($PictureThumbnial))
      {
        if (strlen($Update) > 0)
          $Update = "$Update,PictureThumbnial=\"$PictureThumbnial\"";
        else
          $Update = "PictureThumbnial=\"$PictureThumbnial\"";
      }
      else if (strlen($Update) > 0)
        $Update = "$Update,PictureThumbnial=\"\"";
      else
        $Update = "PictureThumbnial=\"\"";
      if (isset($PictureXJustify))
      {
        if (strlen($Update) > 0)
          $Update = "$Update,PictureXJustify=$PictureXJustify";
        else
          $Update = "PictureXJustify=$PictureXJustify";
      }
      else if (strlen($Update) > 0)
        $Update = "$Update,PictureXJustify=0";
      else
        $Update = "PictureXJustify=0";
      if (isset($PictureYJustify))
      {
        if (strlen($Update) > 0)
          $Update = "$Update,PictureYJustify=$PictureYJustify";
        else
          $Update = "PictureYJustify=$PictureYJustify";
      }
      else if (strlen($Update) > 0)
        $Update = "$Update,PictureYJustify=0";
      else
        $Update = "PictureYJustify=0";
    }
    if ($TypeFields & 64)
    {
      if (isset($Address))
      {
        if (strlen($Update) > 0)
          $Update = "$Update,Address=\"$Address\"";
        else
          $Update = "Address=\"$Address\"";
      }
      if (isset($SecureHTTP))
      {
        if (strlen($Update) > 0)
          $Update = "$Update,SecureHTTP=$SecureHTTP";
        else
          $Update = "SecureHTTP=$SecureHTTP";
      }
      else if (strlen($Update) > 0)
        $Update = "$Update,SecureHTTP=0";
      else
        $Update = "SecureHTTP=0";
    }
    if ($TypeFields & 128)
    {
      if (isset($Content))
      {
        $Content = str_replace("\"", "'", $Content);
        if (strlen($Update) > 0)
          $Update = "$Update,Content=\"$Content\"";
        else
          $Update = "Content=\"$Content\"";
      }
    }
    if ($TypeFields & 256)
    {
      if (isset($ShowOnHomepage))
      {
        if (strlen($Update) > 0)
          $Update = "$Update,ShowOnHomepage=$ShowOnHomepage";
        else
          $Update = "ShowOnHomepage=$ShowOnHomepage";
      }
      else if (strlen($Update) > 0)
        $Update = "$Update,ShowOnHomepage=0";
      else
        $Update = "ShowOnHomepage=0";
    }
    if ($TypeFields & 512)
    {
      if (isset($Document))
      {
        if (strlen($Update) > 0)
          $Update = "$Update,Document=\"$Document\"";
        else
          $Update = "Document=\"$Document\"";
      }
    }
    if (strlen($Update) > 0)
    {
      if (!oswebdb_query("UPDATE Websites SET $Update WHERE SystemNo=$SystemNo AND TypeNo=$TypeNo AND MenuNo=$MenuNo AND Description=\"$Description\""))
        $Result = 0;
    }
    return $Result;
  }

  function delete_website($SystemNo, $TypeNo, $MenuNo, $Description)
  {
    $Result = 1;
    if (!oswebdb_query("DELETE FROM Webcontent WHERE SystemNo=$SystemNo AND TypeNo=$TypeNo AND MenuNo=$MenuNo AND Description=\"$Description\""))
      $Result = 0;
    else if (!oswebdb_query("DELETE FROM Websites WHERE SystemNo=$SystemNo AND TypeNo=$TypeNo AND MenuNo=$MenuNo AND Description=\"$Description\""))
      $Result = 0;
    return $Result;
  }

  function insert_content($SystemNo, $TypeNo, $MenuNo, $Description, $Text, $Created, $Active, $ActiveFrom, $ActiveTo, $Picture, $PictureThumbnial, $PictureXJustify, $PictureYJustify, $GroupNo)
  {
    $Result = 1;
    $TypeSystemNo = get_system_for_table($SystemNo, 4);
    $ContentTypeNo = get_table_field_value($TypeSystemNo, 4, $TypeNo, "GroupNo");
    if ($ContentTypeNo > 0)
    {
      $ContentTypeSystemNo = get_system_for_table($SystemNo, 5);
      $ContentTypeFields = get_table_field_value($ContentTypeSystemNo, 5, $ContentTypeNo, "ShowFields");
      $ContentTypeGroupNo = get_table_field_value($ContentTypeSystemNo, 5, $ContentTypeNo, "GroupNo");
      $Fields = "SystemNo,TypeNo,MenuNo,Description,Text";
      $Values = "$SystemNo,$TypeNo,$MenuNo,\"$Description\",\"$Text\"";
      if ($ContentTypeFields & 2)
      {
        if (isset($Created))
        {
          $Fields = "$Fields,Created";
          $Values = "$Values,\"$Created\"";
        }
        else
        {
          $Fields = "$Fields,Created";
          $Values = "$Values,NULL";
        }
      }
      if ($ContentTypeFields & 8)
      {
        if (isset($Active))
        {
          $Fields = "$Fields,Active";
          $Values = "$Values,$Active";
        }
        else
        {
          $Fields = "$Fields,Active";
          $Values = "$Values,0";
        }
        if (isset($ActiveFrom))
        {
          $Fields = "$Fields,ActiveFrom";
          $Values = "$Values,\"$ActiveFrom\"";
        }
        else
        {
          $Fields = "$Fields,ActiveFrom";
          $Values = "$Values,NULL";
        }
        if (isset($ActiveTo))
        {
          $Fields = "$Fields,ActiveTo";
          $Values = "$Values,\"$ActiveTo\"";
        }
        else
        {
          $Fields = "$Fields,ActiveTo";
          $Values = "$Values,NULL";
        }
      }
      if ($ContentTypeFields & 32)
      {
        if (isset($Picture))
        {
          $Fields = "$Fields,Picture";
          $Values = "$Values,\"$Picture\"";
        }
        else
        {
          $Fields = "$Fields,Picture";
          $Values = "$Values,\"\"";
        }
        if (isset($PictureThumbnial))
        {
          $Fields = "$Fields,PictureThumbnial";
          $Values = "$Values,\"$PictureThumbnial\"";
        }
        else
        {
          $Fields = "$Fields,PictureThumbnial";
          $Values = "$Values,\"\"";
        }
        if (isset($PictureXJustify))
        {
          $Fields = "$Fields,PictureXJustify";
          $Values = "$Values,$PictureXJustify";
        }
        else
        {
          $Fields = "$Fields,PictureXJustify";
          $Values = "$Values,0";
        }
        if (isset($PictureYJustify))
        {
          $Fields = "$Fields,PictureYJustify";
          $Values = "$Values,$PictureYJustify";
        }
        else
        {
          $Fields = "$Fields,PictureYJustify";
          $Values = "$Values,0";
        }
      }
      if ($ContentTypeGroupNo > 0)
      {
        if (isset($GroupNo))
        {
          $Fields = "$Fields,GroupNo";
          $Values = "$Values,$GroupNo";
        }
        else
        {
          $Fields = "$Fields,GroupNo";
          $Values = "$Values,0";
        }
      }
      if (!oswebdb_query("INSERT INTO Webcontent ($Fields) VALUES($Values)"))
        $Result = 0;
    }
    return $Result;
  }

  function update_content($SystemNo, $TypeNo, $MenuNo, $Description, $OldText, $NewText, $Created, $Active, $ActiveFrom, $ActiveTo, $Picture, $PictureThumbnial, $PictureXJustify, $PictureYJustify, $GroupNo)
  {
    $Result = 1;
    $TypeSystemNo = get_system_for_table($SystemNo, 4);
    $ContentTypeNo = get_table_field_value($TypeSystemNo, 4, $TypeNo, "GroupNo");
    if ($ContentTypeNo > 0)
    {
      $ContentTypeSystemNo = get_system_for_table($SystemNo, 5);
      $ContentTypeFields = get_table_field_value($ContentTypeSystemNo, 5, $ContentTypeNo, "ShowFields");
      $ContentTypeGroupNo = get_table_field_value($ContentTypeSystemNo, 5, $ContentTypeNo, "GroupNo");
      $Update = "";
      if ($OldText != $NewText)
      {
        if (strlen($Update) > 0)
          $Update = "$Update,Text=\"$NewText\"";
        else
          $Update = "Text=\"$NewText\"";
      }
      if ($ContentTypeFields & 2)
      {
        if (isset($Created))
        {
          if (strlen($Update) > 0)
            $Update = "$Update,Created=\"$Created\"";
          else
            $Update = "Created=\"$Created\"";
        }
        else if (strlen($Update) > 0)
          $Update = "$Update,Created=NULL";
        else
          $Update = "Created=NULL";
      }
      if ($ContentTypeFields & 8)
      {
        if (isset($Active))
        {
          if (strlen($Update) > 0)
            $Update = "$Update,Active=$Active";
          else
            $Update = "Active=$Active";
        }
        else if (strlen($Update) > 0)
          $Update = "$Update,Active=0";
        else
          $Update = "Active=0";
        if (isset($ActiveFrom))
        {
          if (strlen($Update) > 0)
            $Update = "$Update,ActiveFrom=\"$ActiveFrom\"";
          else
            $Update = "ActiveFrom=\"$ActiveFrom\"";
        }
        else if (strlen($Update) > 0)
          $Update = "$Update,ActiveFrom=NULL";
        else
          $Update = "ActiveFrom=NULL";
        if (isset($ActiveTo))
        {
          if (strlen($Update) > 0)
            $Update = "$Update,ActiveTo=\"$ActiveTo\"";
          else
            $Update = "ActiveTo=\"$ActiveTo\"";
        }
        else if (strlen($Update) > 0)
          $Update = "$Update,ActiveTo=NULL";
        else
          $Update = "ActiveTo=NULL";
      }
      if ($ContentTypeFields & 32)
      {
        if (isset($Picture))
        {
          if (strlen($Update) > 0)
            $Update = "$Update,Picture=\"$Picture\"";
          else
            $Update = "Picture=\"$Picture\"";
        }
        else if (strlen($Update) > 0)
          $Update = "$Update,Picture=\"\"";
        else
          $Update = "Picture=\"\"";
        if (isset($PictureThumbnial))
        {
          if (strlen($Update) > 0)
            $Update = "$Update,PictureThumbnial=\"$PictureThumbnial\"";
          else
            $Update = "PictureThumbnial=\"$PictureThumbnial\"";
        }
        else if (strlen($Update) > 0)
          $Update = "$Update,PictureThumbnial=\"\"";
        else
          $Update = "PictureThumbnial=\"\"";
        if (isset($PictureXJustify))
        {
          if (strlen($Update) > 0)
            $Update = "$Update,PictureXJustify=$PictureXJustify";
          else
            $Update = "PictureXJustify=$PictureXJustify";
        }
        else if (strlen($Update) > 0)
          $Update = "$Update,PictureXJustify=0";
        else
          $Update = "PictureXJustify=0";
        if (isset($PictureYJustify))
        {
          if (strlen($Update) > 0)
            $Update = "$Update,PictureYJustify=$PictureYJustify";
          else
            $Update = "PictureYJustify=$PictureYJustify";
        }
        else if (strlen($Update) > 0)
          $Update = "$Update,PictureYJustify=0";
        else
          $Update = "PictureYJustify=0";
      }
      if ($ContentTypeGroupNo > 0)
      {
        if (isset($GroupNo))
        {
          if (strlen($Update) > 0)
            $Update = "$Update,GroupNo=$GroupNo";
          else
            $Update = "GroupNo=$GroupNo";
        }
        else if (strlen($Update) > 0)
          $Update = "$Update,GroupNo=0";
        else
          $Update = "GroupNo=0";
      }
      if (strlen($Update) > 0)
      {
        if (!oswebdb_query("UPDATE Webcontent SET $Update WHERE SystemNo=$SystemNo AND TypeNo=$TypeNo AND MenuNo=$MenuNo AND Description=\"$Description\" AND Text=\"$OldText\""))
          $Result = 0;
      }
    }
    return $Result;
  }

  function delete_content($SystemNo, $TypeNo, $MenuNo, $Description, $Text)
  {
    $Result = 1;
    if (!oswebdb_query("DELETE FROM Webcontent WHERE SystemNo=$SystemNo AND TypeNo=$TypeNo AND MenuNo=$MenuNo AND Description=\"$Description\" AND Text=\"$Text\""))
      $Result = 0;
    return $Result;
  }

  function get_menu_types($SystemNo)
  {
    $MenuTypes = "";
    $TypeSystemNo = get_system_for_table($SystemNo, 4);
    if ($Result = oswebdb_query("SELECT No,ShowFields FROM Systemtables WHERE SystemNo=$TypeSystemNo AND TableNo=4 ORDER By No,Description"))
    {
      while ($Row = oswebdb_fetch_row($Result))
      {
        if ($Row[1] & 1)
        {
          if (strlen($MenuTypes) > 0)
            $MenuTypes = "$MenuTypes,$Row[0]";
          else
            $MenuTypes = "$Row[0]";
        }
      }
      oswebdb_free_result($Result);
    }
    return $MenuTypes;
  }

  function get_news_types($SystemNo)
  {
    $NewsTypes = "";
    $TypeSystemNo = get_system_for_table($SystemNo, 4);
    if ($Result = oswebdb_query("SELECT No,Properties FROM Systemtables WHERE SystemNo=$TypeSystemNo AND TableNo=4 ORDER By No,Description"))
    {
      while ($Row = oswebdb_fetch_row($Result))
      {
        if ($Row[1] & 16)
        {
          if (strlen($NewsTypes) > 0)
            $NewsTypes = "$NewsTypes,$Row[0]";
          else
            $NewsTypes = "$Row[0]";
        }
      }
      oswebdb_free_result($Result);
    }
    return $NewsTypes;
  }

  function get_showonhomepage_types($SystemNo)
  {
    $MenuTypes = "";
    $TypeSystemNo = get_system_for_table($SystemNo, 4);
    if ($Result = oswebdb_query("SELECT No,ShowFields FROM Systemtables WHERE SystemNo=$TypeSystemNo AND TableNo=4 ORDER By No,Description"))
    {
      while ($Row = oswebdb_fetch_row($Result))
      {
        if ($Row[1] & 256)
        {
          if (strlen($MenuTypes) > 0)
            $MenuTypes = "$MenuTypes,$Row[0]";
          else
            $MenuTypes = "$Row[0]";
        }
      }
      oswebdb_free_result($Result);
    }
    return $MenuTypes;
  }

  function get_website_fields()
  {
    return "TypeNo,MenuNo,Description,Created,Public,Active,ActiveFrom,ActiveTo,Owner,Picture,PictureThumbnial,PictureXJustify,PictureYJustify,Address,Content,ShowOnHomepage,Document,SecureHTTP";
  }

  function get_website_order($TypeFields)
  {
    $Order = "Description";
    if ($TypeFields & 2)
      $Order = "Created DESC,$Order";
    return $Order;
  }

  function get_content_order($ContentTypeFields, $ContentTypeGroupNo)
  {
    $Order = "c.Text";
    if ($ContentTypeFields & 2)
      $Order = "c.Created DESC,$Order";
    if ($ContentTypeGroupNo > 0)
      $Order = "s.Description,$Order";
    return $Order;
  }

  function get_content_statement($SystemNo, $TypeNo, $MenuNo, $Description, $ContentTypeNo)
  {
    $ContentTypeSystemNo = get_system_for_table($SystemNo, 5);
    $ContentTypeGroupNo = get_table_field_value($ContentTypeSystemNo, 5, $ContentTypeNo, "GroupNo");
    $ContentTypeGroupSystemNo = get_system_for_table($SystemNo, $ContentTypeGroupNo);
    $ContentOrder = get_content_order(get_table_field_value($ContentTypeSystemNo, 5, $ContentTypeNo, "ShowFields"), $ContentTypeGroupNo);
    $ContentStatement = "SELECT c.TypeNo,c.MenuNo,c.Description,c.Text,c.Created,c.Active,c.ActiveFrom,c.ActiveTo,c.Picture,c.PictureThumbnial,c.PictureXJustify,c.PictureYJustify,c.GroupNo,c.GroupNo FROM Webcontent AS c WHERE c.SystemNo=$SystemNo AND c.TypeNo=$TypeNo AND c.MenuNo=$MenuNo AND c.Description=\"$Description\" ORDER BY $ContentOrder";
    if ($ContentTypeGroupNo > 0)
      $ContentStatement = "SELECT c.TypeNo,c.MenuNo,c.Description,c.Text,c.Created,c.Active,c.ActiveFrom,c.ActiveTo,c.Picture,c.PictureThumbnial,c.PictureXJustify,c.PictureYJustify,c.GroupNo,s.Description FROM Webcontent AS c LEFT JOIN Systemtables AS s ON s.SystemNo=$ContentTypeGroupSystemNo AND s.TableNo=$ContentTypeGroupNo AND s.No=c.GroupNo WHERE c.SystemNo=$SystemNo AND c.TypeNo=$TypeNo AND c.MenuNo=$MenuNo AND c.Description=\"$Description\" AND (s.No IS NOT NULL OR s.No IS NULL) ORDER BY $ContentOrder";
    return $ContentStatement;
  }

  function get_default_owner()
  {
    return "%";
  }

  function use_website($TypeFields, $Public, $Username, $Password, $Active, $ActiveFrom, $ActiveTo, $Owner, $ShowOnHomepage)
  {
    $Result = 1;
    if ($Result && ($TypeFields & 4))
    {
      $Result = ($Public != 0) || ($Public == 0 && isset($Username) && isset($Password));
    }
    if ($Result && ($TypeFields & 8))
    {
      $Result = ($Active == 1) || ($Active == 2 && strcmp(date("Y-m-d", time()), $ActiveFrom) >= 0 && strcmp(date("Y-m-d", time()), $ActiveTo) <= 0);
    }
    if ($Result && ($TypeFields & 16))
    {
      $Result = ($Owner == get_default_owner()) || ($Owner == oswebdb_privilege_username());
    }
    if ($Result && ($TypeFields & 256))
    {
      $Result = $ShowOnHomepage;
    }
    return $Result;
  }

  function new_website($Created)
  {
    $ToDay = getdate();
    return (integer) (mktime(0, 0, 0, substr($Created, 5, 2), substr($Created, 8, 2) + 7, substr($Created, 0, 4)) > mktime(0, 0, 0, $ToDay['mon'], $ToDay['mday'], $ToDay['year']));
  }

  function get_website_description($TypeFields, $TypeProperties, $Description, $Created, $TypeText)
  {
    $Result = "$Description";
    if ($TypeFields & 2)
    {
      $Year = substr($Created, 0, 4);
      $Month = substr($Created, 5, 2);
      $Date = substr($Created, 8, 2);
      $Result = "[$Date/$Month-$Year] $Result";
    }
    if (($TypeProperties & 256) && strlen($TypeText) > 0)
      $Result = "$TypeText - $Result";
    return $Result;
  }

  function get_website_properties($TypeFields, $Public, $Active, $ActiveFrom, $ActiveTo, $Owner, $ShowOnHomepage)
  {
    $Result = "";
    if ($TypeFields & 4)
    {
      if ($Public)
      {
        if (strlen($Result) > 0)
          $Result = "$Result, Offentlig";
        else
          $Result = "Offentlig";
      }
    }
    if ($TypeFields & 256)
    {
      if ($ShowOnHomepage)
      {
        if (strlen($Result) > 0)
          $Result = "$Result, Vises på forsiden som link";
        else
          $Result = "Vises på forsiden som link";
      }
    }
    if ($TypeFields & 8)
    {
      switch ($Active)
      {
        case 0:
          if (strlen($Result) > 0)
            $Result = "$Result, Passiv";
          else
            $Result = "Passiv";
          break;

        case 1:
          if (strlen($Result) > 0)
            $Result = "$Result, Aktiv";
          else
            $Result = "Aktiv";
          break;

        case 2:
          if (strlen($Result) > 0)
            $Result = "$Result, Aktiv ($ActiveFrom til $ActiveTo)";
          else
            $Result = "Aktiv ($ActiveFrom til $ActiveTo)";
          break;
      }
    }
    if ($TypeFields & 16)
    {
      if ($Owner != get_default_owner())
      {
        $OwnerHostname = oswebdb_privilege_hostname(0);
        $OwnerUsername = oswebdb_privilege_username();
        if (strlen($Result) > 0)
          $Result = "$Result, Ejer: $OwnerUsername@$OwnerHostname";
        else
          $Result = "Ejer: $OwnerUsername@$OwnerHostname";
      }
    }
    return $Result;
  }

  function external_website($TypeFields, $TypeProperties, $Address, $Document)
  {
    $Result = 0;
    if ($TypeProperties & 8)
    {
      if (($TypeFields & 64) && strlen($Address) > 0)
        $Result = 1;
      else if (($TypeFields & 512) && strlen($Document) > 0)
        $Result = 1;
    }
    return $Result;
  }

  function get_website_link($TypeFields, $TypeProperties, $TypeNo, $MenuNo, $Description, $Address, $Document, $SecureHTTP)
  {
    $WebsiteLink = "home.php?TypeNo=$TypeNo&MenuNo=$MenuNo&Description=$Description";
    if (external_website($TypeFields, $TypeProperties, $Address, ""))
    {
      if (strtolower(substr($Address, 0, 8)) == "https://")
        $Address = strtolower(substr($Address, 8, strlen($Address) - 8));
      if (strtolower(substr($Address, 0, 7)) == "http://")
        $Address = strtolower(substr($Address, 7, strlen($Address) - 7));
      if ($SecureHTTP)
        $WebsiteLink = "https://$Address";
      else
        $WebsiteLink = "http://$Address";
    }
    else if (external_website($TypeFields, $TypeProperties, "", $Document))
      $WebsiteLink = "$Document";
    return $WebsiteLink;
  }
?>
