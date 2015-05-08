<?php

require_once __DIR__.'/vendor/autoload.php';

use Guzzle\Http\Client;
use Guzzle\Http\Message\Response;

$link = isset($_REQUEST['link']) ? $_REQUEST['link'] : '';
$isBehat = isset($_REQUEST['is_behat']) ? (bool)$_REQUEST['is_behat'] : false;

if (isset($_REQUEST['msisdn']) && $_REQUEST['msisdn']) {
    $msisdn = $_REQUEST['msisdn'];

    if ($_REQUEST['flow'] == '3g') {
        $_REQUEST['msisdn'] = '0049' . substr($msisdn, 1);
    }
}

if (isset($_REQUEST['submit'])) {
    $config = array(
        't' => strrev((string)microtime(true)),
    );

    $config = array_merge($config, $_REQUEST);

    unset($config['link']);
    unset($config['submit']);

    if (@$config['flow'] == 'wifi' && $config['operator'] != 4) {
        unset($config['msisdn']);
    }

    $rid = base64_encode(json_encode($config));

    $output = array();

    $url = $link;

    /*
    try {
        $client = new Client(null, array('redirect.disable' => true));
        $request = $client->get($link);
        $response = $request->send();
        $statusCode = $response->getStatusCode();
        if ($statusCode == 301 || $statusCode == 302) {
            $url = (string)$response->getHeader('Location');
            $output[] = sprintf("<p><strong>%s</strong> redirects to <strong>%s</strong></p>\n", $link, $url);
        }
    } catch (Exception $e) {
        die($e->getMessage());
    }
     */

    $url = $url . '&emulate=1&rid=' . urlencode($rid);

    if ($isBehat) {
        header("Location: $url");
        die();
    }

    $output[] = sprintf("<a target=\"_blank\" href=\"%s\">%s</a>\n", $url, $url);
    $output[] = sprintf("<p>MSISDN: %s</p>\n", $msisdn);

    $output = join('', $output);

    $checked = '';
} else {
    $msisdn = '0' . rand(pow(10, 9), pow(10, 10)-1);
    $output = '';
    $checked = 'checked';
}

?>

<!DOCTYPE html>
<html>
    <head>
        <title>Configure Net-M Emulator</title>
    </head>

    <body>
        <p><a href="<?php echo 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">Home</a></p>
        <form method="POST">
            <p>
                Campaign Link:
                <br><br>
                <textarea id="campaign-link" style="width: 640px;" type="text" name="link" rows="3" cols="30"><?php echo $link; ?></textarea>
            </p>
            <p>
                MSISDN: <input id="msisdn" type="text" name="msisdn" value="<?php echo $msisdn; ?>">
                <!-- Low balance: <input type="checkbox" name="low_balance" value="1"><br> -->
            </p>
            <p>
                <input type="radio" name="operator" value="1" <?php echo (isset($_REQUEST['operator']) && $_REQUEST['operator'] == '1') ? 'checked' : ''; echo $checked; ?>> T-Mobile<br>
                <input type="radio" name="operator" value="2" <?php echo (isset($_REQUEST['operator']) && $_REQUEST['operator'] == '2') ? 'checked' : ''; ?>> Vodafone<br>
                <input type="radio" name="operator" value="3" <?php echo (isset($_REQUEST['operator']) && $_REQUEST['operator'] == '3') ? 'checked' : ''; ?>> E-Plus<br>
                <input type="radio" name="operator" value="4" <?php echo (isset($_REQUEST['operator']) && $_REQUEST['operator'] == '4') ? 'checked' : ''; ?>> O2<br>
            </p>
            <p>
                <input type="radio" name="flow" value="wifi" <?php echo (isset($_REQUEST['flow']) && $_REQUEST['flow'] == 'wifi') ? 'checked' : ''; echo $checked; ?>> Wifi Flow<br>
                <input type="radio" name="flow" value="3g" <?php echo (isset($_REQUEST['flow']) && $_REQUEST['flow'] == '3g') ? 'checked' : '' ?>> 3G Flow<br>
            </p>

            <input type="submit" name="submit" value="Generate link">
        </form>

        <p><?php echo $output; ?></p>

        <?php
            if (isset($_REQUEST['submit'])) {
        ?>

<hr>
<h3>Logs</h3>
<ol id="log"></ol>
<script>
    var src = new EventSource('<?php echo "logs.php?rid=$rid"; ?>');

    src.onmessage = function(e) {
        console.log(e.data);
        var el = document.createElement('li');
        el.innerHTML = e.data;

        document.getElementById('log').appendChild(el);
    };
</script>

        <?php
            }
        ?>


    </body>
</html>

