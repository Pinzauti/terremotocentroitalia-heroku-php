<?php
require_once(getenv('HOME') . '/vendor/autoload.php');
use Symfony\Component\Yaml\Yaml;
if (in_array('curl', get_loaded_extensions())) {
    //error_reporting(0);
    set_time_limit(120);
    ini_set('max_execution_time', 120);
    mb_internal_encoding("UTF-8");
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $chi = strip_tags(trim($_POST["chi"]));
        $descrizione = strip_tags(trim($_POST["descrizione"]));
        $descrizione = trim(preg_replace('/\s\s+/', ' ', $descrizione));
        $intestazione = strip_tags(trim($_POST["intestazione"]));
        $iban = strip_tags(trim($_POST["iban"]));
        $bic = strip_tags(trim($_POST["bic"]));
        $postale = strip_tags(trim($_POST["postale"]));
        $causale = strip_tags(trim($_POST["causale"]));
        $link = strip_tags(trim($_POST["link"]));
        $email = strip_tags(trim($_POST["email"]));
        $date = date('d/m/Y');
        if (empty($chi)) {
            http_response_code(400);
            echo "Compila tutti i campi!";
            exit;
        }

        $body = array(
          'chi'           => $chi,
          'descrizione'   => $descrizione,
          'intestazione'  => $intestazione,
          'iban'          => $iban,
          'bic'           => $bic,
          'postale'       => $postale,
          'causale'       => $causale,
          'link'          => $link,
          'email'         => $email,
          'data'          => $date
        );
        $yaml = Yaml::dump($body);
        $data = array(
            "title" => $chi,
            "body" => "<pre><yamldata>$yaml</yamldata></pre>",
            "labels" => [
                "Raccolte Fondi",
                "Form"
            ]
        );
        $data_string = json_encode($data);
        $agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.82 Safari/537.36';
        $username = getenv('GITHUB_USERNAME');
        $password = getenv('GITHUB_PASSWORD');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.github.com/repos/emergenzeHack/terremotocentro_segnalazioni/issues');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept=> application/json', 'Content-Type=> application/json', 'X-Accepted-OAuth-Scopes: repo'));
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, $agent);
        curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($status_code === 201) {
            http_response_code(200);
            curl_close($ch);
        } else {
            http_response_code(400);
            curl_close($ch);
            echo "Non riesco ad aprire la segnalazione!";
        }
    } else {
        http_response_code(403);
        echo "Accesso negato!";
    }
} else {
    http_response_code(400);
    echo "CURL non è installato/attivato su questo server!";
}