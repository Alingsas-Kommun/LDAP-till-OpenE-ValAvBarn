<?php
  // Användare som som skickas med i basic authentication. Namn + Lösenord
  // Vill man ha flera användare kan man lägga till flera rader. Varje nytt system som använder scriptet bör ha en egen användare
  // Tänk på att ha unika och komplicerade lösenord på användarna.
  $api_credentials = array(
    'user1' => 'abc123',
    'opene' => 'abcxyz'
  );

  // Kod för att kolla att de skickar med motsvarande namn och lösenord
  if (!isset($_SERVER['PHP_AUTH_USER']))
  {
    header('WWW-Authenticate: Basic realm="API01"');
    header('HTTP/1.1 401 Unauthorized');
    echo "401";
    exit;
  } else {
    $username = $_SERVER['PHP_AUTH_USER'];
    $password = $_SERVER['PHP_AUTH_PW'];

    if (!array_key_exists($username, $api_credentials))
    {
      header('HTTP/1.1 403 Forbidden');
      exit;
    }
    if ($password != $api_credentials[$username])
    {
      header('HTTP/1.1 403 Forbidden');
      exit;
    }
  }

  // En funktion som hämtar klass och skola för en elev. Personnummer är med som parameter
  function getStudentInfo($workforceid) {

    // Definera LDAP-filter. Prova gärna filter i LDAP-browser först
    $filter = "workforceid=" . $workforceid;

    // Ip-adress eller DNS namn på den server som är LDAP-server. Exempel: 192.168.1.200 eller ldap1.alingsas.se
    $ldap_host = "192.168.1.2";

    // Port som den ska fråga på. 389 för okrypterad överföring men vi kommer aktivera TLS via den porten för kryptering
    // 636 för krypterad anslutning gäller inte längre.Php stödjer inte ldaps bara ldap med TLS
    $ldap_port = "389";

    //Varifrån startar sökningen. Exempel: ou=User,o=Kommun. För AD och LDAP är det andra värde. Duckduckgo'a
    $base_dn = "ou=Users,o=Company";

    // Användare och lösenord för katalogtjänsten
    $ldap_user ="cn=ldap-api01,ou=ldap,ou=system,o=company";
    $ldap_pass = "password";

    // Koppla upp mot LDAPs
    $connect = ldap_connect( $ldap_host, $ldap_port);
    ldap_set_option($connect, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_start_tls($connect);
    $bind = ldap_bind($connect, $ldap_user, $ldap_pass);

    // Gör LDAP-sökningen och koppla dem till array
    $read = ldap_search($connect, $base_dn, $filter);
    $info = ldap_get_entries($connect, $read);

    // Tala om att det är en XML-fil
    header("Content-Type: application/xml;charset=utf-8");

    // Bygg posten för eleven
    echo '<Child>';
    echo '<CitizenIdentifier>' . $workforceid . '</CitizenIdentifier>';
    echo '<Fields>';

    //Snurrar runt i svaren från LDAP och plocka ut managerforceid
    for($counter = 0; $counter<$info["count"]; $counter++)
    {
      for($column = 0; $column<$info[$counter]["count"]; $column++)
      {
        $data = $info[$counter][$column];

        if ($data == 'cidlocation')
        {

          $skola = $info[$counter][$data][0];
          $skola = ucwords($skola);

          echo '<Field>';
          echo '<Name>Skola</Name>';
          echo '<Value>' . $skola . '</Value>';
          echo '</Field>';
        }
        if ($data == 'cidclassrr')
        {
          $klass = substr($info[$counter][$data][0], strpos($info[$counter][$data][0], "#") + 1);

          echo '<Field>';
          echo '<Name>Klass</Name>';
          echo '<Value>' . $klass . '</Value>';
          echo '</Field>';
        }
      }
    }
    echo '</Fields>';
    echo '</Child>';
  }

  // Huvudkoden
  // Definera start på XML
  echo '<Response>';

  // Ta in alla personnummer som kommer separerade med ett ,
  $workforceid_array = str_getcsv($_REQUEST['WORKFORCEID']);

  // För varje personnummer så skapar vi en post via funktionen
  foreach($workforceid_array as $key => $value)
  {
    echo getStudentInfo($value);
  }

  //Stäng XML
  echo '</Response>';

  // Stäng koppling till LDAP
  ldap_close($connect);
?>
