<?php
  // Include required files.
  require_once("misc.php");
  require_once("oswebdb/oswebdb.php");
  require_once("oswebdb/tables.php");
  require_once("oswebdb/systems.php");

  // Set error reporting.
  error_reporting((int) GetConfigValue("ErrorReporting"));

  $SystemNo = GetConfigValue("SystemNo");
  if (isset($_GET["UploadType"]))
    $UploadType = $_GET["UploadType"];
  else if (isset($_POST["UploadType"]))
    $UploadType = $_POST["UploadType"];
  if (oswebdb_authorize(oswebdb_getusername(), oswebdb_getpassword()))
  {
    if (oswebdb_selectdb())
    {
    	$UploadResult = "";
      $CanUpload = (oswebdb_is_administrator($SystemNo) || oswebdb_is_configurator($SystemNo)) && (oswebdb_privileges("Websites") > 1 && oswebdb_privileges("Webcontent") > 1 && file_exists("websites.php"));
      if (array_key_exists('TryToUploadFile', $_POST))
      {
      	switch ($_FILES['File']['error'])
      	{
      		case UPLOAD_ERR_OK:
      		  if (is_uploaded_file($_FILES['File']['tmp_name']))
      		  {
      		  	$TargetPath = $_POST["Path"];
      		  	if (DIRECTORY_SEPARTOR != '/')
      		  	{
      		  		$TargetPath = str_replace("/", DIRECTORY_SEPARATOR, $TargetPath);
      		  	}
      		  	$TargetFileName = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . $TargetPath . $_FILES['File']['name'];
      		  	if (copy($_FILES['File']['tmp_name'], $TargetFileName))
      		  	{
      		  		unlink($_FILES['File']['tmp_name']);
      		  		if (isset($_POST["MakeThumbnial"]))
      		  		{
      		  			// Check if GD extension is loaded.
                  if (!extension_loaded('gd') && !extension_loaded('gd2'))
                  {
                  	MakeHtmlPageTop("Upload billede");
                  	echo "      <table border=\"0\" cellpadding=\"1\" cellspacing=\"1\" width=\"100%\">\r\n";
                  	echo "        <tr><td width=\"100%\" valign=\"middle\"><h2><i>Upload</i></h2></td></tr>\r\n";
                  	echo "        <tr><td width=\"100%\" valign=\"middle\">GD is not loaded!</td></tr>\r\n";
                  	echo "      </table>\r\n";
                  	MakeHtmlPageBottom();
                  	oswebdb_close();
                  	exit();
                  }
                  // Make thumbnial.
                  $ThumbnialPath = $_POST["ThumbnialPath"];
           		  	if (DIRECTORY_SEPARTOR != '/')
           		  	{
      		  		    $ThumbnialPath = str_replace("/", DIRECTORY_SEPARATOR, $ThumbnialPath);
      		  	    }
          		  	$ThumbnialFileName = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . $ThumbnialPath . $_FILES['File']['name'];
          		  	if (strtolower($TargetFileName) == strtolower($ThumbnialFileName))
          		  	{
          		  	  $p = strrpos($ThumbnialFileName, '.');
          		  	  if ($p != false)
          		  	  {
          		  	  	$ThumbnialFileName = substr($ThumbnialFileName, 0, $p + 1) . "thumbnial" . substr($ThumbnialFileName, $p, strlen($ThumbnialFileName) - $p);
          		  	  }
          		  	}
          		  	list($WidthOrig, $HeightOrig, $ImageType) = getimagesize($TargetFileName);
          		  	switch ($ImageType)
          		  	{
          		  		case 1:
          		  		  $Image = imagecreatefromgif($TargetFileName);
          		  		  break;
          		  		case 2:
          		  		  $Image = imagecreatefromjpeg($TargetFileName);
          		  		  break;
          		  		case 3:
          		  		  $Image = imagecreatefrompng($TargetFileName);
          		  		  break;
          		  		default:
                   	  MakeHtmlPageTop("Upload billede");
                  	  echo "      <table border=\"0\" cellpadding=\"1\" cellspacing=\"1\" width=\"100%\">\r\n";
                  	  echo "        <tr><td width=\"100%\" valign=\"middle\"><h2><i>Upload</i></h2></td></tr>\r\n";
                  	  echo "        <tr><td width=\"100%\" valign=\"middle\">Unsupported filetype!</td></tr>\r\n";
                  	  echo "      </table>\r\n";
                  	  MakeHtmlPageBottom();
                  	  oswebdb_close();
                  	  exit();
          		  	}
          		  	$ThumbnialImage = imagecreatetruecolor($_POST["ThumbnialWidth"], $_POST["ThumbnialHeight"]);
          		  	// Check if this image is GIF or PNG, then set if transparent.
          		  	if (($ImageType == 1) || ($ImageType == 3))
          		  	{
          		  		imagealphablending($ThumbnialImage, false);
          		  		imagesavealpha($ThumbnialImage, true);
          		  		$Transparent = imagecolorallocatealpha($ThumbnialImage, 255, 255, 255, 127);
          		  		imagefilledrectangle($ThumbnialImage, 0, 0, $_POST["ThumbnialWidth"], $_POST["ThumbnialHeight"], $Transparent);
          		  	}
          		  	imagecopyresampled($ThumbnialImage, $Image, 0, 0, 0, 0, $_POST["ThumbnialWidth"], $_POST["ThumbnialHeight"], $WidthOrig, $HeightOrig);
          		  	// Generate the file, and rename it to $ThumbnialFileName.
          		  	switch ($ImageType)
          		  	{
          		  		case 1:
          		  		  $Image = imagegif($ThumbnialImage, $ThumbnialFileName);
          		  		  break;
          		  		case 2:
          		  		  $Image = imagejpeg($ThumbnialImage, $ThumbnialFileName);
          		  		  break;
          		  		case 3:
          		  		  $Image = imagepng($ThumbnialImage, $ThumbnialFileName);
          		  		  break;
          		  	}
      		  		}
            	  $UploadResult = $_FILES['File']['name'] . " er blevet uploaded!";
      		  	}
      		  	else
            	  $UploadResult = "Kunne ikke uploade " . $_FILES['File']['name'] . ", da den ikke kunne flyttes til den ønskede placering!";
      		  }
      		  else
            	$UploadResult = "Kunne ikke uploade " . $_FILES['File']['name'] . ", da den er en trussel mod systemet!";
      		  break;
      		case UPLOAD_ERR_INI_SIZE:
          	$UploadResult = "Kunne ikke uploade " . $_FILES['File']['name'] . ", da den overstiger den maksimale størrelse!";
      		  break;
      		case UPLOAD_ERR_FORM_SIZE:
          	$UploadResult = "Kunne ikke uploade " . $_FILES['File']['name'] . ", da den overstiger den maksimale størrelse!";
      		  break;
      		case UPLOAD_ERR_PARTIAL:
          	$UploadResult = $_FILES['File']['name'] . " blev ikke uploaded helt!";
      		  break;
      		case UPLOAD_ERR_NO_FILE:
          	$UploadResult = "Ingen fil blev uploaded!";
      		  break;
      		case UPLOAD_ERR_NO_TMP_DIR:
          	$UploadResult = "Kunne ikke uploade " . $_FILES['File']['name'] . ", da der ikke er noget midlertidig bibliotek!";
      		  break;
      		case UPLOAD_ERR_CANT_WRITE:
          	$UploadResult = "Kunne ikke uploade " . $_FILES['File']['name'] . ", da der ikke kunne skrives til disken!";
      		  break;
      		case UPLOAD_ERR_EXTENSION:
          	$UploadResult = "Kunne ikke uploade " . $_FILES['File']['name'] . ", da uploaden blev stoppet grundet extension!";
      		  break;
      		default:
          	$UploadResult = "Kunne ikke uploade " . $_FILES['File']['name'] . ", da der opstod en ukendt fejl!";
      		  break;
      	}
      }
      // WWW-Authenticate for upload.
      if (isset($UploadType) && ($UploadType >= 1) && ($UploadType <= 2) && (GetConfigValue("UploadAuthenticate") == 1))
      {
      	$Realm = GetConfigValue("Realm");
      	if (GetConfigValue("Digest") == 1)
      	{
      		// Digest authorization.
      		if (empty($_SERVER["PHP_AUTH_DIGEST"]))
      		{
      			header("HTTP/1.1 401 Unauthorized");
      			header("WWW-Authenticate: Digest realm=\"" . $Realm . "\",qop=\"auth\",nonce=\"" . uniqid() . "\",opaque=\"" . md5($Realm) . "\"");
      			MakeHtmlPageTop("401 Authorization Required");
      			echo "      <table border=\"0\" cellpadding=\"1\" cellspacing=\"1\" width=\"100%\">\r\n";
      			echo "        <tr><td width=\"100%\" valign=\"middle\"><h2><i>Upload</i></h2></td></tr>\r\n";
      			echo "        <tr><td width=\"100%\" valign=\"middle\">401 Authorization Required</td></tr>\r\n";
      			echo "      </table>\r\n";
      			MakeHtmlPageBottom();
      			oswebdb_close();
      			exit();
      		}
      		echo "OS Debug: Test";
      		oswebdb_close();
      		exit();
      	}
      	else if (!isset($_SERVER["PHP_AUTH_USER"]))
      	{
      		// Basic authorization.
      		header("HTTP/1.0 401 Unauthorized");
      		header("WWW-Authenticate: Basic realm=\"" . $Realm . "\"");
      		MakeHtmlPageTop("401 Authorization Required");
      		echo "      <table border=\"0\" cellpadding=\"1\" cellspacing=\"1\" width=\"100%\">\r\n";
      		echo "        <tr><td width=\"100%\" valign=\"middle\"><h2><i>Upload</i></h2></td></tr>\r\n";
      		echo "        <tr><td width=\"100%\" valign=\"middle\">401 Authorization Required</td></tr>\r\n";
      		echo "      </table>\r\n";
      		MakeHtmlPageBottom();
      		oswebdb_close();
      		exit();
      	}
      }
      // Upload GUI.
      if (isset($UploadType) && $UploadType == 1)
      {
        $TabIndex = 1;
        MakeJavaScript(get_table_field_value(get_system_for_table($SystemNo, 4), 4, 3, "Text2"));
        MakeHtmlPageTop("Upload billede");
        if ($CanUpload)
        {
        	$MakeTumbnial = 0;
        	if (isset($_POST["MakeThumbnial"]))
        	  $MakeTumbnial = $_POST["MakeThumbnial"];
          echo "    <form name=\"UploadForm\" enctype=\"multipart/form-data\" action=\"upload.php\" method=\"post\">\r\n";
          echo "      <input type=\"hidden\" name=\"UploadType\" value=\"$UploadType\">\r\n";
          echo "      <input type=\"hidden\" name=\"TryToUploadFile\" value=\"1\">\r\n";
          echo "      <table border=\"0\" cellpadding=\"1\" cellspacing=\"1\" width=\"100%\">\r\n";
          echo "        <tr><td width=\"100%\" colspan=\"2\" valign=\"middle\"><h2><i>Upload billede</i></h2></td></tr>\r\n";
          if (strlen($UploadResult) > 0)
          {
            echo "        <tr><td width=\"100%\" colspan=\"2\" valign=\"middle\">$UploadResult</td></tr>\r\n";
            echo "        <tr><td width=\"100%\" colspan=\"2\" valign=\"middle\">&nbsp;</td></tr>\r\n";
          }
          MakeFileSelector("Billede :", "File", "", "image/jpg,image/jpeg,image/gif", !$CanUpload, $TabIndex++);
          MakePathSelector(get_system_path_pictures($SystemNo), "Path", $_POST["Path"], !$CanUpload, !$CanUpload, $TabIndex++);
          $Input = MakeHtmlInputCheckBox("MakeThumbnial", "1", $MakeTumbnial, !$CanUpload, $TabIndex++, "javascript:changeMakeThumbnialControl()", "Lav tumbinal");
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\">$Input</td></tr>\r\n";
          $InputWidth = MakeHtmlInputText("ThumbnialWidth", $_POST["ThumbnialWidth"], 4, 4, !$CanUpload || !$MakeTumbnial, !$CanUpload || !$MakeTumbnial, $TabIndex++, "");
          $InputHeight = MakeHtmlInputText("ThumbnialHeight", $_POST["ThumbnialHeight"], 4, 4, !$CanUpload || !$MakeTumbnial, !$CanUpload || !$MakeTumbnial, $TabIndex++, "");
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Størrelse :</td><td width=\"99%\">$InputWidth x $InputHeight</td></tr>\r\n";
          MakePathSelector(get_system_path_pictures($SystemNo), "ThumbnialPath", $_POST["ThumbnialPath"], !$CanUpload || !$MakeTumbnial, !$CanUpload || !$MakeTumbnial, $TabIndex++);
          $Input = MakeHtmlInputSubmit("", "Upload", !$CanUpload, $TabIndex++, "javascript:return validateUploadForm();");
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\">$Input</td></tr>\r\n";
          echo "      </table>\r\n";
          echo "    </form>\r\n";
        }
        MakeHtmlPageBottom();
      }
      else if (isset($UploadType) && $UploadType == 2)
      {
        $TabIndex = 1;
        MakeJavaScript(get_table_field_value(get_system_for_table($SystemNo, 4), 4, 5, "Text1"));
        MakeHtmlPageTop("Upload dokument");
        if ($CanUpload)
        {
          echo "    <form name=\"UploadForm\" enctype=\"multipart/form-data\" action=\"upload.php\" method=\"post\">\r\n";
          echo "      <input type=\"hidden\" name=\"UploadType\" value=\"$UploadType\">\r\n";
          echo "      <input type=\"hidden\" name=\"TryToUploadFile\" value=\"1\">\r\n";
          echo "      <table border=\"0\" cellpadding=\"1\" cellspacing=\"1\" width=\"100%\">\r\n";
          echo "        <tr><td width=\"100%\" colspan=\"2\" valign=\"middle\"><h2><i>Upload dokument</i></h2></td></tr>\r\n";
          if (strlen($UploadResult) > 0)
          {
            echo "        <tr><td width=\"100%\" colspan=\"2\" valign=\"middle\">$UploadResult</td></tr>\r\n";
            echo "        <tr><td width=\"100%\" colspan=\"2\" valign=\"middle\">&nbsp;</td></tr>\r\n";
          }
          MakeFileSelector("Dokument :", "File", "", "", !$CanUpload, $TabIndex++);
          MakePathSelector(get_system_path_documents($SystemNo), "Path", $_POST["Path"], !$CanUpload, !$CanUpload, $TabIndex++);
          $Input = MakeHtmlInputSubmit("", "Upload", !$CanUpload, $TabIndex++, "javascript:return validateUploadForm();");
          echo "        <tr><td width=\"1%\" align=\"right\" nowrap></td><td width=\"99%\">$Input</td></tr>\r\n";
          echo "      </table>\r\n";
          echo "    </form>\r\n";
        }
        MakeHtmlPageBottom();
      }
      else
    	{
        $TabIndex = 1;
        MakeHtmlPageTop("Upload");
        if ($CanUpload)
        {
          echo "    <table border=\"0\" cellpadding=\"1\" cellspacing=\"1\" width=\"100%\">\r\n";
          echo "      <tr><td width=\"100%\" colspan=\"2\" valign=\"middle\"><h2><i>Upload</i></h2></td></tr>\r\n";
          $Input = MakeHtmlInputSubmit("", "Upload", !$CanUpload, $TabIndex++, "");
          echo "      <form action=\"upload.php\" method=\"post\"><input type=\"hidden\" name=\"UploadType\" value=\"1\"><tr><td width=\"1%\" nowrap>$Input</td><td width=\"99%\">Billede<td><tr></form>\r\n";
          $Input = MakeHtmlInputSubmit("", "Upload", !$CanUpload, $TabIndex++, "");
          echo "      <form action=\"upload.php\" method=\"post\"><input type=\"hidden\" name=\"UploadType\" value=\"2\"><tr><td width=\"1%\" nowrap>$Input</td><td width=\"99%\">Dokument<td><tr></form>\r\n";
          echo "    </table>\r\n";
        }
        MakeHtmlPageBottom();
      }
    }
    oswebdb_close();
  }

  function MakeJavaScript($SupportedExtensions)
  {
  	echo "<script type=\"text/javascript\" language=\"JavaScript\">\r\n";
  	echo "  function checkExtension(extension, supportedExtensions)\r\n";
  	echo "  {\r\n";
  	echo "    supportedExtensions = supportedExtensions.toLowerCase();\r\n";
  	echo "    supported = supportedExtensions.split('/'); i = 0;\r\n";
  	echo "    while (i < supported.length)\r\n";
  	echo "    {\r\n";
  	echo "      if (extension == supported[i])\r\n";
  	echo "        return true;\r\n";
  	echo "      i++;\r\n";
  	echo "    }\r\n";
  	echo "    return false;\r\n";
  	echo "  }\r\n\r\n";
  	echo "  function changeMakeThumbnialControl()\r\n";
  	echo "  {\r\n";
  	echo "    UploadForm.ThumbnialWidth.readOnly = !UploadForm.MakeThumbnial.checked\r\n";
  	echo "    UploadForm.ThumbnialWidth.disabled = !UploadForm.MakeThumbnial.checked\r\n";
  	echo "    UploadForm.ThumbnialHeight.readOnly = !UploadForm.MakeThumbnial.checked\r\n";
  	echo "    UploadForm.ThumbnialHeight.disabled = !UploadForm.MakeThumbnial.checked\r\n";
  	echo "    UploadForm.ThumbnialPath.readOnly = !UploadForm.MakeThumbnial.checked\r\n";
  	echo "    UploadForm.ThumbnialPath.disabled = !UploadForm.MakeThumbnial.checked\r\n";
  	echo "  }\r\n\r\n";
  	echo "  function validateUploadForm()\r\n";
  	echo "  {\r\n";
  	echo "    if (UploadForm.File.value.length == 0)\r\n";
  	echo "    {\r\n";
  	echo "      if (UploadForm.UploadType.value == 1)\r\n";
  	echo "      {\r\n";
  	echo "        alert('Der skal vælges et billede!');\r\n";
  	echo "        return false;\r\n";
  	echo "      }\r\n";
  	echo "      else if (UploadForm.UploadType.value == 2)\r\n";
  	echo "      {\r\n";
  	echo "        alert('Der skal vælges et dokument!');\r\n";
  	echo "        return false;\r\n";
  	echo "      }\r\n";
  	echo "    }\r\n";
  	echo "    extension = UploadForm.File.value.toLowerCase();\r\n";
  	echo "    p = extension.lastIndexOf('.');\r\n";
  	echo "    if (p >= 0)\r\n";
  	echo "      extension = extension.substr(p, extension.length - p);\r\n";
  	echo "    if (!checkExtension(extension, '$SupportedExtensions'))\r\n";
  	echo "    {\r\n";
  	echo "      alert('Den valgte filtype er ikke supporteret!');\r\n";
  	echo "      return false;\r\n";
  	echo "    }\r\n";
  	echo "    if (UploadForm.UploadType.value == 1)\r\n";
  	echo "    {\r\n";
  	echo "      if (UploadForm.MakeThumbnial.checked)\r\n";
  	echo "      {\r\n";
  	echo "        if (!(parseInt(UploadForm.ThumbnialWidth.value, 10) > 0))\r\n";
  	echo "        {\r\n";
  	echo "          alert('Bredden skal være større end 0!');\r\n";
  	echo "          return false;\r\n";
  	echo "        }\r\n";
  	echo "        if (!(parseInt(UploadForm.ThumbnialHeight.value, 10) > 0))\r\n";
  	echo "        {\r\n";
  	echo "          alert('Højden skal være større end 0!');\r\n";
  	echo "          return false;\r\n";
  	echo "        }\r\n";
  	echo "      }\r\n";
  	echo "    }\r\n";
  	echo "    return true;\r\n";
  	echo "  }\r\n";
  	echo "</script>\r\n";
  }

  function MakeFileSelector($Text, $Name, $Value, $Accept, $Disabled, $TabIndex)
  {
  	$Input = MakeHtmlInputFile($Name, $Value, $Accept, $Disabled, $TabIndex);
    echo "        <tr><td width=\"1%\" align=\"right\" nowrap>$Text</td><td width=\"99%\">$Input</td></tr>\r\n";
  }

  function GetPathSelectorOptions($Root, $Selected)
  {
  	$Options = "";
  	if ($Path = opendir($Root))
  	{
  		$Options = "$Options<option value=\"$Root\"";
  		if (!isset($Selected) || strlen($Selected) == 0 || $Root == $Selected)
  		{
  		  $Options = "$Options selected";
  		  if (!isset($Selected) || strlen($Selected) == 0)
    		  $Selected = $Root;
  		}
  		$Options = "$Options>$Root";
  		while (($Name = readdir($Path)) != false)
  		{
          if (is_dir("$Root$Name") && $Name != "." && $Name != "..")
          {
            $SubOptions = GetPathSelectorOptions("$Root$Name/", $Selected);
            $Options = "$Options$SubOptions";
          }
  		}
      closedir($Path);
  	}
  	return $Options;
  }

  function MakePathSelector($Root, $Name, $Selected, $ReadOnly, $Disabled, $TabIndex)
  {
  	$Input = MakeHtmlSelect($Name, 0, 0, $ReadOnly, $Disabled, $TabIndex, "", GetPathSelectorOptions($Root, $Selected));
    echo "        <tr><td width=\"1%\" align=\"right\" nowrap>Placering :</td><td width=\"99%\">$Input</td></tr>\r\n";
  }
?>
