<?php
  // Include required files.
  require_once("misc.php");
  require_once("oswebdb/oswebdb.php");

  // Set error reporting.
  error_reporting((int) GetConfigValue("ErrorReporting"));

  // Make the html menu page with information from the oswebdb database.
  if (oswebdb_connect(oswebdb_getusername(), oswebdb_getpassword()))
  {
    if (oswebdb_selectdb())
    {
      $SystemNo = (int) GetConfigValue("SystemNo");
      if ($Result = oswebdb_query("SELECT Title FROM Systems WHERE SystemNo=$SystemNo"))
      {
        if ($Row = oswebdb_fetch_row($Result))
        {
          MakeHtmlPageTop($Row[0]);
          MakeHtmlPageBottom();
        }
        oswebdb_free_result($Result);
      }
    }
    oswebdb_close();
  }
?>
