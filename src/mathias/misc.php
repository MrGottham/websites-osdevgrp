<?php
  function GetConfigValue($ValueName)
  {
    $Result = "";
    if (file_exists("config.ini"))
    {
      if ($fp = fopen("config.ini", "r"))
      {
        while (strlen($Result) == 0 && !feof($fp))
        {
          if ($fs = fgets($fp, 1024))
          {
            if (stristr($ValueName, substr($fs, 0, strlen($ValueName))))
            {
              $Pos = strpos($fs, "=");
              if ($Pos)
                $Result = ltrim(rtrim(substr($fs, $Pos + 1, strlen($fs) - $Pos)));
            }
          }
        }
        fclose($fp);
      }
    }
    return $Result;
  }

  function FormatNumber($Number, $Decimals, $Fun)
  {
    if ($Decimals > 0)
    {
      $Number = round($Number * pow(10, $Decimals)) / pow(10, $Decimals);
    }
    $Result = str_replace(".", ",", "$Number");
    if ($Decimals > 0)
    {
      $Pos = strpos($Result, ",");
      if ($Pos)
      {
        if (strlen(substr($Result, $Pos + 1, strlen($Result) - $Pos)) > $Decimals)
        {
          $Result = substr($Result, 0, $Pos + 1 + $Decimals);
        }
        else if (strlen(substr($Result, $Pos + 1, strlen($Result) - $Pos)) < $Decimals)
        {
          $Frac = "";
          for ($i = strlen(substr($Result, $Pos + 1, strlen($Result) - $Pos)); $i < $Decimals; $i++)
            $Frac = "0$Frac";
          $Result = "$Result$Frac";
        }
      }
      else
      {
        $Frac = "";
        for ($i = 0; $i < $Decimals; $i++)
          $Frac = "0$Frac";
        $Result = "$Result,$Frac";
      }
    }
    else
    {
      $Pos = strpos($Result, ",");
      if ($Pos)
        $Result = substr($Result, 0, $Pos);
    }
    if ($Fun & 1)
    {
      $To = strlen($Result);
      if (strpos($Result, ","))
        $To = strpos($Result, ",");
      while (($To -= 3) > 0)
      {
        $S1 = substr($Result, 0, $To);
        $S2 = substr($Result, $To, strlen($Result));
        $Result = "$S1.$S2";
        $To = strpos($Result, ".");
      }
    }
    return $Result;
  }

  function MakeHtmlPageTop($Title)
  {
    MakeHtmlPageTopWithLoad($Title, "");
  }

  function MakeHtmlPageTopWithLoad($Title, $Load)
  {
    $BgColor = (string) GetConfigValue("BgColor");
    $Text = (string) GetConfigValue("Text");
    $Link = (string) GetConfigValue("Link");
    $Vlink = (string) GetConfigValue("Vlink");
    $Alink = (string) GetConfigValue("Alink");
    echo "<html>\r\n";
    echo "  <head>\r\n";
    echo "    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=windows-1252\">\r\n";
    echo "    <title>$Title</title>\r\n";
    echo "  </head>\r\n";
    if (strlen($Load) > 0)
      echo "  <body onLoad=\"$Load\" bgcolor=\"$BgColor\" text=\"$Text\" link=\"$Link\" vlink=\"$Vlink\" alink=\"$Alink\">\r\n";
    else
      echo "  <body bgcolor=\"$BgColor\" text=\"$Text\" link=\"$Link\" vlink=\"$Vlink\" alink=\"$Alink\">\r\n";
  }

  function MakeHtmlPageBottom()
  {
    echo "  </body>\r\n";
    echo "</html>\r\n";
  }

  function MakeHtmlPageReload($onLoad)
  {
    $BgColor = (string) GetConfigValue("BgColor");
    $Text = (string) GetConfigValue("Text");
    $Link = (string) GetConfigValue("Link");
    $Vlink = (string) GetConfigValue("Vlink");
    $Alink = (string) GetConfigValue("Alink");
    echo "<html>\r\n";
    echo "  <body onLoad=\"$onLoad\" bgcolor=\"$BgColor\" text=\"$Text\" link=\"$Link\" vlink=\"$Vlink\" alink=\"$Alink\">\r\n";
    echo "  </body>\r\n";
    echo "</html>\r\n";
  }

  function MakeHtmlPrintPageTop($Title)
  {
    echo "<html>\r\n";
    echo "  <head>\r\n";
    echo "    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=windows-1252\">\r\n";
    echo "    <title>$Title</title>\r\n";
    echo "  </head>\r\n";
    echo "  <body bgcolor=\"\" text=\"#000000\" link=\"#000000\" vlink=\"#000000\" alink=\"#000000\">\r\n";
  }

  function MakeHtmlPrintPageBottom()
  {
    echo "  </body>\r\n";
    echo "</html>\r\n";
  }

  function MakeHtmlInputText($Name, $Value, $Size, $MaxLength, $ReadOnly, $Disabled, $TabIndex, $OnChange)
  {
    $Result = "<input type=\"text\"";
    if (isset($Name) && strlen($Name) > 0)
      $Result = "$Result name=\"$Name\"";
    if (isset($Value) && strlen($Value) > 0)
      $Result = "$Result value=\"$Value\"";
    if (isset($Size) && (int) $Size > 0)
    {
      if (MAX_SIZE > 0 && $Size > MAX_SIZE)
        $Size = MAX_SIZE;
      else if (MAX_SIZE == 0 && $Size > 40)
        $Size = 40;
      $Result = "$Result size=\"$Size\"";
    }
    if (isset($Size) && (int) $MaxLength > 0)
      $Result = "$Result maxlength=\"$MaxLength\"";
    if (isset($ReadOnly) && (int) $ReadOnly)
      $Result = "$Result readonly";
    if (isset($Disabled) && (int) $Disabled)
      $Result = "$Result disabled";
    if (isset($TabIndex) && (int) $TabIndex > 0)
      $Result = "$Result tabindex=\"$TabIndex\"";
    if (isset($OnChange) && strlen($OnChange) > 0)
      $Result = "$Result onChange=\"$OnChange\"";
    $Result = "$Result>";
    return $Result;
  }

  function MakeHtmlInputPassword($Name, $Value, $Size, $MaxLength, $ReadOnly, $Disabled, $TabIndex, $OnChange)
  {
    $Result = "<input type=\"password\"";
    if (isset($Name) && strlen($Name) > 0)
      $Result = "$Result name=\"$Name\"";
    if (isset($Value) && strlen($Value) > 0)
      $Result = "$Result value=\"$Value\"";
    if (isset($Size) && (int) $Size > 0)
    {
      if (MAX_SIZE > 0 && $Size > MAX_SIZE)
        $Size = MAX_SIZE;
      else if (MAX_SIZE == 0 && $Size > 40)
        $Size = 40;
      $Result = "$Result size=\"$Size\"";
    }
    if (isset($Size) && (int) $MaxLength > 0)
      $Result = "$Result maxlength=\"$MaxLength\"";
    if (isset($ReadOnly) && (int) $ReadOnly)
      $Result = "$Result readonly";
    if (isset($Disabled) && (int) $Disabled)
      $Result = "$Result disabled";
    if (isset($TabIndex) && (int) $TabIndex > 0)
      $Result = "$Result tabindex=\"$TabIndex\"";
    if (isset($OnChange) && strlen($OnChange) > 0)
      $Result = "$Result onChange=\"$OnChange\"";
    $Result = "$Result autocomplete=\"OFF\">";
    return $Result;
  }

  function MakeHtmlInputCheckBox($Name, $Value, $Checked, $Disabled, $TabIndex, $OnClick, $Text)
  {
    $Result = "<input type=\"checkbox\"";
    if (isset($Name) && strlen($Name) > 0)
      $Result = "$Result name=\"$Name\"";
    if (isset($Value) && strlen($Value) > 0)
      $Result = "$Result value=\"$Value\"";
    if (isset($Checked) && (int) $Checked)
      $Result = "$Result checked";
    if (isset($Disabled) && (int) $Disabled)
      $Result = "$Result disabled";
    if (isset($TabIndex) && (int) $TabIndex > 0)
      $Result = "$Result tabindex=\"$TabIndex\"";
    if (isset($OnClick) && strlen($OnClick) > 0)
      $Result = "$Result onClick=\"$OnClick\"";
    $Result = "$Result>$Text";
    return $Result;
  }

  function MakeHtmlInputRadio($Name, $Value, $Checked, $Disabled, $TabIndex, $OnClick, $Text)
  {
    $Result = "<input type=\"radio\"";
    if (isset($Name) && strlen($Name) > 0)
      $Result = "$Result name=\"$Name\"";
    if (isset($Value) && strlen($Value) > 0)
      $Result = "$Result value=\"$Value\"";
    if (isset($Checked) && (int) $Checked)
      $Result = "$Result checked";
    if (isset($Disabled) && (int) $Disabled)
      $Result = "$Result disabled";
    if (isset($TabIndex) && (int) $TabIndex > 0)
      $Result = "$Result tabindex=\"$TabIndex\"";
    if (isset($OnClick) && strlen($OnClick) > 0)
      $Result = "$Result onClick=\"$OnClick\"";
    $Result = "$Result>$Text";
    return $Result;
  }

  function MakeHtmlInputSubmit($Name, $Value, $Disabled, $TabIndex, $OnClick)
  {
    $Result = "<input type=\"submit\"";
    if (isset($Name) && strlen($Name) > 0)
      $Result = "$Result name=\"$Name\"";
    if (isset($Value) && strlen($Value) > 0)
      $Result = "$Result value=\"$Value\"";
    if (isset($Disabled) && (int) $Disabled)
      $Result = "$Result disabled";
    if (isset($TabIndex) && (int) $TabIndex > 0)
      $Result = "$Result tabindex=\"$TabIndex\"";
    if (isset($OnClick) && strlen($OnClick) > 0)
      $Result = "$Result onClick=\"$OnClick\"";
    $Result = "$Result>";
    return $Result;
  }

  function MakeHtmlInputReset($Name, $Value, $Disabled, $TabIndex, $OnClick)
  {
    $Result = "<input type=\"reset\"";
    if (isset($Name) && strlen($Name) > 0)
      $Result = "$Result name=\"$Name\"";
    if (isset($Value) && strlen($Value) > 0)
      $Result = "$Result value=\"$Value\"";
    if (isset($Disabled) && (int) $Disabled)
      $Result = "$Result disabled";
    if (isset($TabIndex) && (int) $TabIndex > 0)
      $Result = "$Result tabindex=\"$TabIndex\"";
    if (isset($OnClick) && strlen($OnClick) > 0)
      $Result = "$Result onClick=\"$OnClick\"";
    $Result = "$Result>";
    return $Result;
  }

  function MakeHtmlInputButton($Name, $Value, $Disabled, $TabIndex, $OnClick)
  {
    $Result = "<input type=\"button\"";
    if (isset($Name) && strlen($Name) > 0)
      $Result = "$Result name=\"$Name\"";
    if (isset($Value) && strlen($Value) > 0)
      $Result = "$Result value=\"$Value\"";
    if (isset($Disabled) && (int) $Disabled)
      $Result = "$Result disabled";
    if (isset($TabIndex) && (int) $TabIndex > 0)
      $Result = "$Result tabindex=\"$TabIndex\"";
    if (isset($OnClick) && strlen($OnClick) > 0)
      $Result = "$Result onClick=\"$OnClick\"";
    $Result = "$Result>";
    return $Result;
  }

  function MakeHtmlInputFile($Name, $Value, $Accept, $Disabled, $TabIndex)
  {
  	$Result = "<input type=\"file\"";
  	if (isset($Name) && strlen($Name) > 0)
  	  $Result = "$Result name=\"$Name\"";
    if (isset($Value) && strlen($Value) > 0)
      $Result = "$Result value=\"$Value\"";
    if (isset($Accept) && strlen($Accept) > 0)
      $Result = "$Result accept=\"$Accept\"";
    if (isset($Disabled) && (int) $Disabled)
      $Result = "$Result disabled";
    if (isset($TabIndex) && (int) $TabIndex > 0)
      $Result = "$Result tabindex=\"$TabIndex\"";
    $Result = "$Result>";
  	return $Result;
  }

  function MakeHtmlSelect($Name, $Multiple, $Size, $ReadOnly, $Disabled, $TabIndex, $OnChange, $Options)
  {
    $Result = "<select";
    if (isset($Name) && strlen($Name) > 0)
      $Result = "$Result name=\"$Name\"";
    if (isset($Multiple) && (int) $Multiple)
      $Result = "$Result multiple";
    if (isset($Size) && (int) $Size > 0)
      $Result = "$Result size=\"$Size\"";
    if (isset($ReadOnly) && (int) $ReadOnly)
      $Result = "$Result readonly";
    if (isset($Disabled) && (int) $Disabled)
      $Result = "$Result disabled";
    if (isset($TabIndex) && (int) $TabIndex > 0)
      $Result = "$Result tabindex=\"$TabIndex\"";
    if (isset($OnChange) && strlen($OnChange) > 0)
      $Result = "$Result onChange=\"$OnChange\"";
    $Result = "$Result>$Options</select>";
    return $Result;
  }

  function MakeHtmlTextArea($Name, $Rows, $Cols, $Wrap, $ReadOnly, $Disabled, $TabIndex, $OnChange, $Content)
  {
    $Result = "<textarea";
    if (isset($Name) && strlen($Name) > 0)
      $Result = "$Result name=\"$Name\"";
    if (isset($Rows) && (int) $Rows > 0)
      $Result = "$Result rows=\"$Rows\"";
    if (isset($Cols) && (int) $Cols> 0)
      $Result = "$Result cols=\"$Cols\"";
    if (isset($Wrap) && strlen($Wrap) > 0)
      $Result = "$Result wrap=\"$Wrap\"";
    if (isset($ReadOnly) && (int) $ReadOnly)
      $Result = "$Result readonly";
    if (isset($Disabled) && (int) $Disabled)
      $Result = "$Result disabled";
    if (isset($TabIndex) && (int) $TabIndex > 0)
      $Result = "$Result tabindex=\"$TabIndex\"";
    if (isset($OnChange) && strlen($OnChange) > 0)
      $Result = "$Result onChange=\"$OnChange\"";
    $Result = "$Result>$Content</textarea>";
    return $Result;
  }

  function MakeHtmlLink($Name, $HRef, $Target, $Title, $Style, $onClick, $onMouseOver, $onMouseOut, $Text)
  {
    $Result = "<a";
    if (isset($Name) && strlen($Name) > 0)
      $Result = "$Result name=\"$Name\"";
    if (isset($HRef) && strlen($HRef) > 0)
      $Result = "$Result href=\"$HRef\"";
    if (isset($Target) && strlen($Target) > 0)
      $Result = "$Result target=\"$Target\"";
    if (isset($Title) && strlen($Title) > 0)
      $Result = "$Result title=\"$Title\"";
    if (isset($Style) && strlen($Style) > 0)
      $Result = "$Result style=\"$Style\"";
    if (isset($onClick) && strlen($onClick) > 0)
      $Result = "$Result onClick=\"$onClick\"";
    if (isset($onMouseOver) && strlen($onMouseOver) > 0)
      $Result = "$Result onMouseOver=\"$onMouseOver\"";
    if (isset($onMouseOut) && strlen($onMouseOut) > 0)
      $Result = "$Result onMouseOut=\"$onMouseOut\"";
    if (isset($onMouseOut) && strlen($onMouseOut) > 0)
      $Result = "$Result onMouseOut=\"$onMouseOut\"";
    $Result = "$Result>$Text</a>";
    return $Result;
  }
?>
